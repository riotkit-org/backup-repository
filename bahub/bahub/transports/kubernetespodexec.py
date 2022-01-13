import time
from typing import List

import yaml
from kubernetes import config, client
from kubernetes.client import V1PodList, V1Pod, V1ObjectMeta
from kubernetes.stream import stream
from kubernetes.stream.ws_client import WSClient, ERROR_CHANNEL
from rkd.api.inputoutput import IO

from bahub.bin import RequiredBinary
from bahub.transports.base import TransportInterface, create_backup_maker_command


class Transport(TransportInterface):
    _v1_core_api: client.CoreV1Api
    _process: WSClient

    _namespace: str
    _selector: str
    _io: IO

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)
        self._namespace = spec.get('namespace')
        self._selector = spec.get('selector')

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "oneOf": [
                {"required": ["namespace", "selector"]},
            ],
            "properties": {
                "selector": {
                    "type": "string",
                    "example": "my-label=myvalue",
                    "default": ""
                },
                "namespace": {
                    "type": "string",
                    "example": "prod",
                    "default": "default"
                }
            }
        }

    @property
    def v1_core_api(self):
        if not hasattr(self, '_v1_core_api'):
            config.load_kube_config()  # todo: Add support for selecting cluster
            self._v1_core_api = client.CoreV1Api()

        return self._v1_core_api

    def prepare_environment(self, binaries: List[RequiredBinary]) -> None:
        pass

    def schedule(self, command: str, definition, is_backup: bool, version: str = "") -> None:
        """
        Runs a `kubectl exec` on already existing POD
        """

        pod_name = self.find_pod_name()
        self.wait_for_pod_to_be_ready(pod_name, self._namespace)

        # todo: fetch_required_tools_from_cache()

        complete_cmd = create_backup_maker_command(command, definition, is_backup, version)
        self.io().debug(f"POD exec: `{complete_cmd}`")

        self._process = stream(
            self._v1_core_api.connect_get_namespaced_pod_exec,
            pod_name,
            self._namespace,
            command=complete_cmd,
            stderr=True,
            stdout=True,
            stdin=False,
            _preload_content=False
        )

    def watch(self) -> bool:
        while self._process.is_open():
            self._process.update(timeout=1)

            out = [
                self._process.readline_stdout() if self._process.peek_stdout() else "",
                self._process.readline_stderr() if self._process.peek_stderr() else ""
            ]

            for line in out:
                if line:
                    self.io().debug(line)

        errors = yaml.load(self._process.read_channel(ERROR_CHANNEL), yaml.FullLoader)

        if "details" in errors:
            for error in errors['details']['causes']:
                if "reason" not in error:
                    self.io().error(error['message'])
                    return False

                if error['reason'] == 'ExitCode' and int(error['message']) > 0:
                    self.io().error(f"Process inside POD exited with status {int(error['message'])}")
                    return False

        return True

    def wait_for_pod_to_be_ready(self, pod_name: str, namespace: str, timeout: int = 120):
        """
        Waits for POD to reach a valid state

        :raises: When timeout hits
        """

        self.io().debug("Waiting for POD to be ready...")

        for i in range(0, timeout):
            pod: V1Pod = self._v1_core_api.read_namespaced_pod(name=pod_name, namespace=namespace)

            if pod.status.phase in ["Ready", "Healthy", "True", "Running"]:
                return True

            self.io().debug(f"Pod not ready. Status: {pod.status.phase}")
            time.sleep(1)

        raise Exception(f"Timed out while waiting for pod '{pod_name}' in namespace '{namespace}'")

    def find_pod_name(self):
        """
        Returns a POD name

        :raises: When no matching POD found
        """

        pods: V1PodList = self.v1_core_api.list_namespaced_pod(self._namespace,
                                                               label_selector=self._selector,
                                                               limit=1)

        if len(pods.items) == 0:
            raise Exception(f'No pods found matching selector {self._selector} in {self._namespace} namespace')

        pod: V1Pod = pods.items[0]
        pod_metadata: V1ObjectMeta = pod.metadata

        return pod_metadata.name


from . import ContainerizedDefinition
from . import BackupDefinition
from ..access import ServerAccess
from ..encryption import Encryption


class DockerVolumesDefinition(ContainerizedDefinition):
    _paths: list

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._container = container
        self._docker_bin = docker_bin
        self._paths = paths

    def get_paths(self) -> list:
        return self._paths

    @staticmethod
    def from_config(config: dict, name: str):
        return DockerVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'docker'),
            paths=config['paths'],
            name=name
        )

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + str(self.get_collection_id()) + ',docker_container=' + \
               str(self.get_container()) + '>'


class DockerOfflineVolumesDefinition(DockerVolumesDefinition):
    _paths = ''
    _temp_image_name = ''
    _temp_cmd = ''

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list,
                 temp_image_name: str,
                 temp_cmd: str,
                 name: str):
        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd,
                         tar_unpack_cmd, container, docker_bin, paths, name)

        self._container = container
        self._docker_bin = docker_bin
        self._paths = paths
        self._temp_image_name = temp_image_name
        self._temp_cmd = temp_cmd

    def get_temp_image_name(self):
        return self._temp_image_name

    def get_temp_cmd(self):
        return self._temp_cmd

    @staticmethod
    def from_config(config: dict, name: str):
        return DockerOfflineVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'docker'),
            paths=config['paths'],
            temp_image_name=config.get('temp_image_name', 'alpine:3.9'),
            temp_cmd=config.get('temp_image_cmd', 'apk add --update xz bzip2 && sleep 3600'),
            name=name
        )

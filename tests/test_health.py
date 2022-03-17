import subprocess
import time

from tests import BaseTestCase


class HealthTest(BaseTestCase):
    def test_health_endpoint(self):
        response = self.get("/health", auth=None)

        assert response.status_code == 200

    def test_readiness_when_postgresql_is_off(self):
        try:
            self.scale("sts", "postgres-postgresql", 0)
            self.wait_for("app.kubernetes.io/instance=postgres", ready=False)
            response = self.get("/ready?code=changeme", auth=None)

            assert response.status_code >= 500, response.content
        finally:
            self.scale("sts", "postgres-postgresql", 1)
            self.wait_for("app.kubernetes.io/instance=postgres", ready=True)

    def test_readiness_when_storage_is_off(self):
        try:
            self.scale("deployment", "minio", 0)
            self.wait_for("app=minio", ready=False)
            response = self.get("/ready?code=changeme", auth=None)

            assert response.status_code >= 500, response.content
        finally:
            self.scale("deployment", "minio", 1)
            self.wait_for("app=minio", ready=True)

    def test_kubernetes_connection_will_be_degraded_if_crds_not_present(self):
        try:
            subprocess.check_call(['kubectl', 'delete', '-f', 'crd'])
            time.sleep(5)

            response = self.get("/ready?code=changeme", auth=None)
            assert response.status_code >= 500, response.content
            assert "configuration provider is not usable" in str(response.content)

        finally:
            subprocess.check_call(['kubectl', 'apply', '-f', 'crd'])
            subprocess.check_call(['kubectl', 'apply', '-f',
                                   'docs/examples/', '-n', 'backup-repository'])  # restore test data

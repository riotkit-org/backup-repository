import subprocess
import time

from tests import BaseTestCase


class HealthTest(BaseTestCase):
    def test_health_endpoint(self):
        response = self.get("/health", auth=None)

        assert response.status_code == 200

    def test_readiness_when_postgresql_is_off(self):
        try:
            self.scale("sts", "postgresql", 0, ns="db")
            self.wait_for("app.kubernetes.io/instance=postgresql", ready=False, ns="db")
            response = self.get("/ready?code=changeme", auth=None)

            assert response.status_code >= 500, response.content
        finally:
            self.scale("sts", "postgresql", 1, ns="db")
            self.wait_for("app.kubernetes.io/instance=postgresql", ready=True, ns="db")

    def test_readiness_when_storage_is_off(self):
        try:
            self.scale("deployment", "minio", 0, ns="storage")
            self.wait_for("app=minio", ready=False, ns="storage")
            response = self.get("/ready?code=changeme", auth=None)

            assert response.status_code >= 500, response.content
        finally:
            self.scale("deployment", "minio", 1, ns="storage")
            self.wait_for("app=minio", ready=True, ns="storage")

    def test_kubernetes_connection_will_be_degraded_if_crds_not_present(self):
        try:
            subprocess.check_call(['kubectl', 'delete', '-f', 'helm/backup-repository-server/templates/crd.yaml'])
            time.sleep(5)

            response = self.get("/ready?code=changeme", auth=None)
            assert response.status_code >= 500, response.content
            assert "configuration provider is not usable" in str(response.content)

        finally:
            subprocess.check_call(['kubectl', 'apply', '-f', 'helm/backup-repository-server/templates/crd.yaml'])
            subprocess.check_call(['kubectl', 'apply', '-f',
                                   'docs/examples/', '-n', 'backups'])  # restore test data

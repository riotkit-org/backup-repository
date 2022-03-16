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

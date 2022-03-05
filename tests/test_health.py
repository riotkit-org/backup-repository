from tests import BaseTestCase


class HealthTest(BaseTestCase):
    def test_health_endpoint(self):
        response = self.get("/health", auth=None)

        assert response.status_code == 200

    def test_readiness_when_postgresql_is_off(self):
        try:
            self.scale("sts", "postgres-postgresql", 0)
            response = self.get("/ready", auth=None)

            assert response.status_code > 500
        finally:
            self.scale("sts", "postgres-postgresql", 1)

    def test_readiness_when_storage_is_off(self):
        try:
            self.scale("deployment", "minio", 0)
            response = self.get("/ready", auth=None)

            assert response.status_code > 500
        finally:
            self.scale("deployment", "minio", 1)

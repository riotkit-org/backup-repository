from tests import BaseTestCase


class HealthTest(BaseTestCase):
    def test_health_endpoint(self):
        response = self.get("/health", auth=None)

        assert response.status_code == 200

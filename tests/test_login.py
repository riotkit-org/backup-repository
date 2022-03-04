from tests import BaseTestCase


class LoginTest(BaseTestCase):
    def test_jwt_gaining(self):
        response = self.post("/api/stable/auth/login", data={'username': 'somebody', 'password': 'invalid'})

        assert response.status_code == 403
        assert "user configuration error" in str(response.content)

    def test_jwt_granted(self):
        response = self.post("/api/stable/auth/login", data={'username': 'admin', 'password': 'admin'})
        data = response.json()

        assert "expire" in data['data']
        assert "sessionId" in data['data']
        assert "token" in data['data']
        assert data['status'] is True

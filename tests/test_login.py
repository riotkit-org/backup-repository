from tests import BaseTestCase


class LoginTest(BaseTestCase):
    def test_jwt_gaining(self):
        response = self.post("/api/stable/auth/login", data={'username': 'somebody', 'password': 'invalid'})

        assert response.status_code == 401
        assert "incorrect Username or Password" in str(response.content)

    def test_jwt_granted(self):
        response = self.post("/api/stable/auth/login", data={'username': 'admin', 'password': 'admin'})
        data = response.json()

        assert "expire" in data['data']
        assert "sessionId" in data['data']
        assert "token" in data['data']
        assert data['status'] is True

    def test_whoami(self):
        token = self.login('admin', 'admin')
        response = self.get("/api/stable/auth/whoami", auth=token).json()

        assert response['data']['email'] == 'riotkit@riseup.net', response
        self.assertNotEqual(response['data']['sessionId'], '')  # not empty

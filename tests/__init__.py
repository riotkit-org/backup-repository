import subprocess
import time
import unittest
import requests
from json import dumps as to_json


class BaseTestCase(unittest.TestCase):
    _base_url: str = 'http://localhost:30080'

    def get(self, url: str, auth: str = None) -> requests.Response:
        headers = {}
        if auth:
            headers['Authorization'] = f'Bearer {auth}'

        return requests.get(f"{self._base_url}{url}", headers=headers)

    def post(self, url: str, data: any, additional_headers: dict = None, auth: str = None) -> requests.Response:
        headers = {}
        if auth:
            headers['Authorization'] = auth

        if additional_headers:
            headers = {**headers, **additional_headers}

        if isinstance(data, dict):
            headers['Content-Type'] = "application/json"
            data = to_json(data)

        return requests.post(f"{self._base_url}{url}", headers=headers, data=data)

    def login(self, username: str, password: str) -> str:
        time.sleep(0.5)
        response = self.post("/api/stable/auth/login", data={'username': username, 'password': password})
        data = response.json()

        assert "token" in data['data'], response.content

        return data['data']['token']

    @staticmethod
    def scale(kind: str, name: str, replicas: int):
        subprocess.check_call(["kubectl", "scale", "-n", "backup-repository", kind, name, f"--replicas={replicas}"])

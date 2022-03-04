import unittest
import requests
from json import dumps as to_json


class BaseTestCase(unittest.TestCase):
    _base_url: str = 'http://localhost:30080'

    def get(self, url: str, auth: str) -> requests.Response:
        headers = {}
        if auth:
            headers['Authorization'] = auth

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

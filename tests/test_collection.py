from tests import BaseTestCase


class CollectionTest(BaseTestCase):
    def test_cannot_upload_if_uploader_role_not_present(self):
        token = self.login('unprivileged', 'admin')
        response = self.post("/api/alpha/repository/collection/iwa-ait/version", "something", auth=f'Bearer {token}')

        assert "not authorized to upload versions to this collection" in str(response.content)

    def test_gpg_detection(self):
        token = self.login('admin', 'admin')
        response = self.post("/api/alpha/repository/collection/iwa-ait/version", "hello this is not a gpg data", auth=f'Bearer {token}')

        assert "cannot upload version. cannot upload file, cannot copy stream, error: first chunk of uploaded data does not contain a valid GPG header" in str(response.content)

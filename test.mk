#
# Common tasks
#

.EXPORT_ALL_VARIABLES:
PATH = $(shell pwd)/.build:$(shell echo $$PATH)
KUBECONFIG = $(shell /bin/bash -c "[[ -f \"$$HOME/.k3d/kubeconfig-bmt.yaml\" ]] && rm -f $$HOME/.k3d/kubeconfig-bmt.yaml; k3d kubeconfig merge bmt > /dev/null")

SERVER_PORT=8050
SERVER_URL=http://127.0.0.1:${SERVER_PORT}

import-examples:
	kubectl apply -f docs/examples/ -n backups

test_health:
	curl -s -X GET '${SERVER_URL}/health'

test_login:
	curl -s -X POST -d '{"username":"admin","password":"admin"}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/login'
	@echo "Now do export TOKEN=..."

test_login_some_user:
	curl -s -X POST -d '{"username":"some-user","password":"admin"}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/login'

test_lookup:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/user/some-user'

test_whoami:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/whoami'

test_logout:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/logout'

test_logout_other_user:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/logout?sessionId=${OTHER_USER_SESSION_ID}'

test_list_auths:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/token'

test_list_auths_other_user:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' '${SERVER_URL}/api/stable/auth/token?userName=some-user'

test_upload_by_form:
	curl -s -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@./storage/.test_data/test.gpg" '${SERVER_URL}/api/alpha/repository/collection/iwa-ait/version'

test_upload_by_form_1mb:
	@echo "-----BEGIN PGP MESSAGE-----" > /tmp/1mb.gpg
	@openssl rand -base64 $$((735*1024*1)) >> /tmp/1mb.gpg
	@echo "-----END PGP MESSAGE-----" >> /tmp/1mb.gpg
	curl -vvv -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@/tmp/1mb.gpg" '${SERVER_URL}/api/alpha/repository/collection/iwa-ait/version' --limit-rate 400K

test_upload_by_form_5mb:
	@echo "-----BEGIN PGP MESSAGE-----" > /tmp/5mb.gpg
	@openssl rand -base64 $$((735*1024*5)) >> /tmp/5mb.gpg
	@echo "-----END PGP MESSAGE-----" >> /tmp/5mb.gpg
	curl -vvv -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@/tmp/5mb.gpg" '${SERVER_URL}/api/alpha/repository/collection/iwa-ait/version' --limit-rate 1000K

test_download:
	curl -vvv -X GET -H 'Authorization: Bearer ${TOKEN}' '${SERVER_URL}/api/alpha/repository/collection/iwa-ait/version/latest' > /tmp/downloaded --limit-rate 100K

test_collection_health:
	curl -s -X GET -H 'Authorization: admin' '${SERVER_URL}/api/stable/repository/collection/iwa-ait/health'

test_list_versions:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' '${SERVER_URL}/api/stable/repository/collection/iwa-ait/version'

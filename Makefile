include k8s.mk

all: build run

test:
	go test -v ./...

setup_api_tests:
	kubectl apply -f "docs/examples/" -n backup-repository
	pipenv install

api_tests:
	pipenv run pytest -s

coverage:
	go test -v ./... -covermode=count -coverprofile=coverage.out

test_health:
	curl -s -X GET 'http://localhost:8080/health'

test_login:
	curl -s -X POST -d '{"username":"admin","password":"admin"}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/login'
	@echo "Now do export TOKEN=..."

test_login_some_user:
	curl -s -X POST -d '{"username":"some-user","password":"admin"}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/login'

test_lookup:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/user/some-user'

test_whoami:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/whoami'

test_logout:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/logout'

test_logout_other_user:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/logout?sessionId=${OTHER_USER_SESSION_ID}'

test_list_auths:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/token'

test_list_auths_other_user:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/token?userName=some-user'

test_upload_by_form:
	curl -s -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@./storage/.test_data/test.gpg" 'http://localhost:8080/api/stable/repository/collection/iwa-ait/version'

test_upload_by_form_1mb:
	@echo "-----BEGIN PGP MESSAGE-----" > /tmp/1mb.gpg
	@openssl rand -base64 $$((735*1024*1)) >> /tmp/1mb.gpg
	@echo "-----END PGP MESSAGE-----" >> /tmp/1mb.gpg
	curl -vvv -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@/tmp/1mb.gpg" 'http://localhost:8080/api/stable/repository/collection/iwa-ait/version' --limit-rate 100K

test_upload_by_form_5mb:
	@echo "-----BEGIN PGP MESSAGE-----" > /tmp/5mb.gpg
	@openssl rand -base64 $$((735*1024*5)) >> /tmp/5mb.gpg
	@echo "-----END PGP MESSAGE-----" >> /tmp/5mb.gpg
	curl -vvv -X POST -H 'Authorization: Bearer ${TOKEN}' -F "file=@/tmp/5mb.gpg" 'http://localhost:8080/api/stable/repository/collection/iwa-ait/version' --limit-rate 1000K

test_download:
	curl -vvv -X GET -H 'Authorization: Bearer ${TOKEN}' 'http://localhost:8080/api/stable/repository/collection/iwa-ait/version/latest' > /tmp/downloaded --limit-rate 100K

test_collection_health:
	curl -s -X GET -H 'Authorization: admin' 'http://localhost:8080/api/stable/repository/collection/iwa-ait/health'

test_list_versions:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' 'http://localhost:8080/api/stable/repository/collection/iwa-ait/version'

postgres:
	docker run -d \
        --name br_postgres \
        -e POSTGRES_PASSWORD=postgres \
        -e POSTGRES_USER=postgres \
        -e POSTGRES_DB=postgres \
        -e PGDATA=/var/lib/postgresql/data/pgdata \
        -v $$(pwd)/.build/postgres:/var/lib/postgresql \
        -p 5432:5432 \
        postgres:14.1-alpine

postgres_refresh:
	docker rm -f br_postgres || true
	sudo rm -rf $$(pwd)/.build/postgres
	make postgres

minio:
	docker run -d \
		--name br_minio \
		-p 9000:9000 \
	    -p 9001:9001 \
	    -v $$(pwd)/.build/minio:/data \
	    -e "MINIO_ROOT_USER=AKIAIOSFODNN7EXAMPLE" \
	    -e "MINIO_ROOT_PASSWORD=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY" \
		quay.io/minio/minio:RELEASE.2022-02-16T00-35-27Z server /data --console-address 0.0.0.0:9001

run:
	export AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE; \
	export AWS_SECRET_ACCESS_KEY=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY; \
	\
	./.build/backup-repository \
		--db-password=postgres \
		--db-user=postgres \
		--db-password=postgres \
		--db-name=postgres \
		--jwt-secret-key="secret key" \
		--storage-url="s3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1"

build:
	go build -tags=nomsgpack -o ./.build/backup-repository


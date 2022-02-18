build:
	go build -tags=nomsgpack .

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

postgres:
	docker run -d \
        --name br_postgres \
        -e POSTGRES_PASSWORD=postgres \
        -e POSTGRES_USER=postgres \
        -e POSTGRES_DB=postgres \
        -e PGDATA=/var/lib/postgresql/data/pgdata \
        -v /tmp/br_postgres:/var/lib/postgresql/data \
        -p 5432:5432 \
        postgres:14.1-alpine

postgres_refresh:
	docker rm -f br_postgres || true
	sudo rm -rf /tmp/br_postgres
	make postgres

minio:
	docker run -d \
		--name br_minio \
		-p 9000:9000 \
	    -p 9001:9001 \
	    -v /tmp/br_minio:/data \
	    -e "MINIO_ROOT_USER=AKIAIOSFODNN7EXAMPLE" \
	    -e "MINIO_ROOT_PASSWORD=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY" \
		quay.io/minio/minio:RELEASE.2022-02-16T00-35-27Z server /data --console-address 0.0.0.0:9001

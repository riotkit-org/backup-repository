build:
	go build -tags=nomsgpack .

test_login:
	curl -s -X POST -d '{"username":"admin","password":"admin"}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/login'

test_lookup:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/user/some-user'

test_whoami:
	curl -s -X GET -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/whoami'

test_logout:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/logout'

test_logout_other_user:
	curl -s -X DELETE -H 'Authorization: Bearer ${TOKEN}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/logout?sessionId=${OTHER_USER_SESSION_ID}'


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

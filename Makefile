include k8s.mk
include test.mk

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
		--health-check-key=changeme \
		--jwt-secret-key="secret key" \
		--storage-io-timeout="5m" \
		--listen=":${SERVER_PORT}" \
		--provider=kubernetes \
		--storage-url="s3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1"

run_with_local_config_storage:
	export AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE; \
	export AWS_SECRET_ACCESS_KEY=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY; \
	\
	./.build/backup-repository \
		--db-password=postgres \
		--db-user=postgres \
		--db-password=postgres \
		--db-name=postgres \
		--health-check-key=changeme \
		--jwt-secret-key="secret key" \
		--storage-io-timeout="5m" \
		--listen=":${SERVER_PORT}" \
		--provider=filesystem \
		--config-local-path=$$(pwd)/docs/examples-filesystem/\
		--storage-url="s3://mybucket?endpoint=localhost:9000&disableSSL=true&s3ForcePathStyle=true&region=eu-central-1"

build:
	CGO_ENABLED=0 GO111MODULE=on go build -tags=nomsgpack -o ./.build/backup-repository

lint:
	export GO111MODULE=on; \
	golangci-lint run \
		--verbose \
		--build-tags build

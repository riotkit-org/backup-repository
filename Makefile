SUDO=sudo
include test.mk

all: build run

test:
	go test -v ./...

setup_api_tests:
	pipenv install

api_tests:
	pipenv run pytest -s

k3d:
	(${SUDO} docker ps | grep k3d-bmt-server-0 > /dev/null 2>&1) || ${SUDO} k3d cluster create bmt --registry-create bmt-registry:0.0.0.0:5000
	cat /etc/hosts | grep "bmt-registry" > /dev/null || (sudo /bin/bash -c "echo '127.0.0.1 bmt-registry' >> /etc/hosts")
	${SUDO} k3d kubeconfig merge bmt

	export KUBECONFIG=~/.k3d/kubeconfig-bmt.yaml
	kubectl create ns backups || true
	kubectl apply -f helm/backup-repository-server/templates/crd.yaml
	kubectl apply -f "docs/examples/" -n backups

coverage:
	go test -v ./... -covermode=count -coverprofile=coverage.out

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

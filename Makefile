include env.mk

SUDO=

.EXPORT_ALL_VARIABLES:
PATH = $(shell pwd)/.build:$(shell echo $$PATH)

all: build run

build:
	CGO_ENABLED=0 GO111MODULE=on go build -tags=nomsgpack -o ./.build/backup-repository

test: ## Unit tests
	go test -v ./... -covermode=count -coverprofile=coverage.out

integration-test: prepare-tools _prepare-env _pytest ## End-To-End tests with Kubernetes
_pytest: ## Shortcut for E2E tests without setting up the environment
	pipenv sync
	pipenv run pytest -s

_prepare-env:
	kubectl apply -f "docs/examples/" -n backups

run:
	export AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE; \
	export AWS_SECRET_ACCESS_KEY=wJaFuCKtnFEMI/CApItaliSM/bPxRfiCYEXAMPLEKEY; \
	\
	backup-repository \
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
	backup-repository \
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

lint:
	export GO111MODULE=on; \
	golangci-lint run \
		--verbose \
		--build-tags build

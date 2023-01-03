SUDO=sudo
include test.mk

.EXPORT_ALL_VARIABLES:
PATH = $(shell pwd)/.build:$(shell echo $$PATH)

all: build run

test:
	go test -v ./...

setup_api_tests:
	pipenv install

api_tests:
	pipenv run pytest -s

prepare-tools:
	mkdir -p .build
	# skaffold
	@test -f .build/skaffold || (curl -sL https://storage.googleapis.com/skaffold/releases/v2.0.0/skaffold-linux-amd64 --output .build/skaffold && chmod +x .build/skaffold)
	# kubectl
	@test -f .build/kubectl || (curl -sL https://dl.k8s.io/release/v1.25.0/bin/linux/amd64/kubectl --output .build/kubectl && chmod +x .build/kubectl)
	# k3d
	@test -f .build/k3d || (curl -sL https://github.com/k3d-io/k3d/releases/download/v5.4.6/k3d-linux-amd64 --output .build/k3d && chmod +x .build/k3d)
	# helm
	@test -f .build/helm || (curl -sL https://get.helm.sh/helm-v3.10.2-linux-amd64.tar.gz --output /tmp/helm.tar.gz && tar xf /tmp/helm.tar.gz -C /tmp && mv /tmp/linux-amd64/helm .build/helm && chmod +x .build/helm)
	# kubens
	@test -f .build/kubens || (curl -sL https://raw.githubusercontent.com/ahmetb/kubectx/master/kubens --output .build/kubens && chmod +x .build/kubens)


k3d: prepare-tools
	(${SUDO} docker ps | grep k3d-bmt-server-0 > /dev/null 2>&1) || ${SUDO} ./.build/k3d cluster create bmt --registry-create bmt-registry:0.0.0.0:5000
	cat /etc/hosts | grep "bmt-registry" > /dev/null || (sudo /bin/bash -c "echo '127.0.0.1 bmt-registry' >> /etc/hosts")
	${SUDO} ./.build/k3d kubeconfig merge bmt

	export KUBECONFIG=~/.k3d/kubeconfig-bmt.yaml
	./.build/kubectl create ns backups || true
	./.build/kubectl apply -f helm/backup-repository-server/templates/crd.yaml
	./.build/kubectl apply -f "docs/examples/" -n backups

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

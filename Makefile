SUDO=

.EXPORT_ALL_VARIABLES:
PATH = $(shell pwd)/.build:$(shell echo $$PATH)

all: build run

build:
	CGO_ENABLED=0 GO111MODULE=on go build -tags=nomsgpack -o ./.build/backup-repository

test: ## Unit tests
	go test -v ./... -covermode=count -coverprofile=coverage.out

integration-test: prepare-tools skaffold-deploy ## End-To-End tests with Kubernetes
	pipenv sync
	pipenv run pytest -s

k3d: prepare-tools
	(${SUDO} docker ps | grep k3d-bmt-server-0 > /dev/null 2>&1) || ${SUDO} k3d cluster create bmt --registry-create bmt-registry:0.0.0.0:5000 --agents 1 -p "30080:30080@agent:0" -p "30081:30081@agent:0" -p "30050:30050@agent:0"
	k3d kubeconfig merge bmt
	kubectl create ns backups || true
	cat /etc/hosts | grep "bmt-registry" > /dev/null || (sudo /bin/bash -c "echo '127.0.0.1 bmt-registry' >> /etc/hosts")

prepare-tools:
	mkdir -p .build
	# skaffold
	@test -f skaffold || (curl -sL https://storage.googleapis.com/skaffold/releases/v2.0.0/skaffold-linux-amd64 --output skaffold && chmod +x skaffold)
	# kubectl
	@test -f kubectl || (curl -sL https://dl.k8s.io/release/v1.25.0/bin/linux/amd64/kubectl --output kubectl && chmod +x kubectl)
	# k3d
	@test -f k3d || (curl -sL https://github.com/k3d-io/k3d/releases/download/v5.4.6/k3d-linux-amd64 --output k3d && chmod +x k3d)
	# helm
	@test -f helm || (curl -sL https://get.helm.sh/helm-v3.10.2-linux-amd64.tar.gz --output /tmp/helm.tar.gz && tar xf /tmp/helm.tar.gz -C /tmp && mv /tmp/linux-amd64/helm helm && chmod +x helm)
	# kubens
	@test -f kubens || (curl -sL https://raw.githubusercontent.com/ahmetb/kubectx/master/kubens --output kubens && chmod +x kubens)

skaffold-deploy: prepare-tools
	skaffold build --tag e2e --default-repo bmt-registry:5000 --push --insecure-registry bmt-registry:5000 --disable-multi-platform-build=true --detect-minikube=false --cache-artifacts=false
	skaffold deploy --tag e2e --assume-yes=true --default-repo bmt-registry:5000

	export KUBECONFIG=~/.k3d/kubeconfig-bmt.yaml; kubectl apply -f "docs/examples/" -n backups

dev: ## Runs the development environment in Kubernetes
	skaffold deploy -p deps
	skaffold dev -p app --tag e2e --assume-yes=true --default-repo bmt-registry:5000 --force=true

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

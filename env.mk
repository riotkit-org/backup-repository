SUDO=

ifneq (,$(wildcard ./.env))
    include .env
    export
endif

# default values
ENV_CLUSTER_NAME ?= "rkt"
ENV_NS ?= "default"
ENV_APP_SVC ?= "service-name"
ENV_PORT_FORWARD ?= "8050:8080"


.EXPORT_ALL_VARIABLES:
PATH = $(shell pwd)/.build:$(shell echo $$PATH)
KUBECONFIG = $(shell echo "$$HOME/.k3d/kubeconfig-${ENV_CLUSTER_NAME}.yaml")

k3d: prepare-tools
	(${SUDO} docker ps | grep k3d-${ENV_CLUSTER_NAME}-server-0 > /dev/null 2>&1) || ${SUDO} k3d cluster create ${ENV_CLUSTER_NAME} --registry-create ${ENV_CLUSTER_NAME}-registry:0.0.0.0:5000 --agents 0
	k3d kubeconfig merge ${ENV_CLUSTER_NAME}
	kubectl create ns ${ENV_NS} || true
	cat /etc/hosts | grep "${ENV_CLUSTER_NAME}-registry" > /dev/null || (sudo /bin/bash -c "echo '127.0.0.1 ${ENV_CLUSTER_NAME}-registry' >> /etc/hosts")

prepare-tools:  ## Installs required tools
	mkdir -p .build
	# skaffold
	@test -f ./.build/skaffold || (curl -sL https://storage.googleapis.com/skaffold/releases/v2.2.0/skaffold-linux-amd64 --output ./.build/skaffold && chmod +x ./.build/skaffold)
	# kubectl
	@test -f ./.build/kubectl || (curl -sL https://dl.k8s.io/release/v1.26.0/bin/linux/amd64/kubectl --output ./.build/kubectl && chmod +x ./.build/kubectl)
	# k3d
	@test -f ./.build/k3d || (curl -sL https://github.com/k3d-io/k3d/releases/download/v5.4.6/k3d-linux-amd64 --output ./.build/k3d && chmod +x ./.build/k3d)
	# helm
	@test -f ./.build/helm || (curl -sL https://get.helm.sh/helm-v3.11.2-linux-amd64.tar.gz --output /tmp/helm.tar.gz && tar xf /tmp/helm.tar.gz -C /tmp && mv /tmp/linux-amd64/helm ./.build/helm && chmod +x ./.build/helm)
	# kubens
	@test -f ./.build/kubens || (curl -sL https://raw.githubusercontent.com/ahmetb/kubectx/master/kubens --output ./.build/kubens && chmod +x ./.build/kubens)

skaffold-deploy: prepare-tools  ## Deploys app with dependencies using Skaffold
	skaffold deploy -p deps
	skaffold build -p app --tag e2e --default-repo ${ENV_CLUSTER_NAME}-registry:5000 --push --insecure-registry ${ENV_CLUSTER_NAME}-registry:5000 --disable-multi-platform-build=true --detect-minikube=false --cache-artifacts=false
	skaffold deploy -p app --tag e2e --assume-yes=true --default-repo ${ENV_CLUSTER_NAME}-registry:5000

	export KUBECONFIG=~/.k3d/kubeconfig-${ENV_CLUSTER_NAME}.yaml
	killall kubectl || true
	kubectl port-forward svc/${ENV_APP_SVC} -n ${ENV_NS} ${ENV_PORT_FORWARD} &

dev: ## Runs the development environment in Kubernetes
	skaffold deploy -p deps
	skaffold dev -p app --tag e2e --assume-yes=true --default-repo ${ENV_CLUSTER_NAME}-registry:5000 --force=true

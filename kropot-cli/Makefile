
TAG=dev
PYTHON=python3
SUDO=sudo
ARCH=x86_64
PIP_BIN=pip3

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test_run: ## Connect to local File Repository testing instance at localhost:8000 port and start streaming
	cd src && ${PYTHON} -m kropotcli \
		--token=test-token-full-permissions \
		--server-url=http://localhost:8000 \
		--storage-path=/tmp/bread-crumbs \
		--db-string=sqlite:///database.db \
		collect

install: ## Install in the system
	${PYTHON} setup.py install

user_install: ## Install in user home directory
	${PYTHON} setup.py install --user

docker_image: ## Build a docker image (params: TAG=dev, ARCH=x86_64)
	${SUDO} ${PIP_BIN} install shyaml
	${SUDO} docker build ../ -f .infrastructure/Dockerfile.${ARCH} \
		--build-arg BUILD_DATE=$$(date --iso-8601=seconds) \
		--build-arg VERSION=$$(make get_version) \
		--build-arg VCS_REF=$$(git rev-parse --verify HEAD) \
		-t quay.io/riotkit/kropotcli:${TAG}

get_version:
	cat ../server/config/version.yaml  | shyaml get-value version


TAG=dev
PYTHON=python3
SUDO=sudo

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test_run: ## Connect to local File Repository testing instance at localhost:8000 port and start streaming
	cd src && ${PYTHON} -m riotkit.filerepository.littlecopymachine \
		--token=test-token-full-permissions \
		--server-url=http://localhost:8000 \
		--storage-path=/tmp/lcm-storage \
		--db-string=sqlite:///database.db \
		collect

install: ## Install in the system
	${PYTHON} setup.py install

user_install: ## Install in user home directory
	${PYTHON} setup.py install --user

docker_x86_64: ## Build a x86_64 docker image (params: TAG)
	${SUDO} docker build ../ -f .infrastructure/Dockerfile.x86_64 -t quay.io/riotkit/little-copy-machine:${TAG}

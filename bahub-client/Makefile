.SILENT:

PIP = sudo pip3
SUDO = sudo
PY_BIN = python3

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build_package: install_requirements ## Build the package
	echo " >> Building package"
	${PY_BIN} ./setup.py build

install_requirements: ## Install requirements
	echo " >> Installing requirements"
	${PIP} install -r ./requirements.txt || true

install: build_package ## Install
	${SUDO} ${PY_BIN} ./setup.py install
	which bahub
	make clean

build_container: ## Build a docker container (parameters: TAG)
	${SUDO} docker build .. -f ./.infrastructure/Dockerfile_bahub.x86_64 -t quay.io/riotkit/bahub:${TAG}
	${SUDO} docker tag quay.io/riotkit/bahub:${TAG} quay.io/riotkit/bahub:latest-build

push_container: ## Push docker container
	${SUDO} docker push quay.io/riotkit/bahub:${TAG}
	${SUDO} docker push quay.io/riotkit/bahub:latest-build

clean: ## Clean up the local build directory
	${SUDO} rm -rf ./build ./bahub.egg-info

run_test_containers: ## Run testing containers and fork to the background
	cd tests && ${SUDO} docker-compose up -d
	${SUDO} docker ps

kill_test_containers: ## Stop and remove all containers
	cd tests && ${SUDO} docker-compose down

test: ## Run tests
	./tests/run.sh

unit_test: ## Run unit tests
	${PY_BIN} -m unittest discover -s ./tests/unit

coverage: ## Generate code coverage
	coverage run --source . -m unittest discover -s ./tests/unit

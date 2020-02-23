.SILENT:

PIP=sudo pip3
SUDO=sudo -E
PY_BIN=python3
SHELL=/bin/bash
PIP_ARGS=

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build_package: install_requirements ## Build the package
	echo " >> Building package"
	${PY_BIN} ./setup.py build

install_requirements: ## Install requirements
	echo " >> Installing requirements"
	${SUDO} ${PIP} install --no-cache-dir ${PIP_ARGS} -r ./requirements.txt

install: build_package ## Install
	${SUDO} ${PY_BIN} ./setup.py install
	which bahub
	make -s clean

build_image: ## Build a docker container (parameters: TAG)
	${SUDO} ${PIP} install ${PIP_ARGS} shyaml
	${SUDO} docker build .. -f ./.infrastructure/Dockerfile_bahub.x86_64 \
		--build-arg BUILD_DATE=$$(date --iso-8601=seconds) \
		--build-arg VERSION=$$(make -s get_version) \
		--build-arg VCS_REF=$$(git rev-parse --verify HEAD) \
		-t quay.io/riotkit/bahub:${TAG}
	${SUDO} docker tag quay.io/riotkit/bahub:${TAG} quay.io/riotkit/bahub:latest-build

push_image: ## Push docker container
	${SUDO} docker push quay.io/riotkit/bahub:${TAG}
	${SUDO} docker push quay.io/riotkit/bahub:latest-build

clean: ## Clean up the local build directory
	set -x; ${SUDO} rm -rf ./build ./src/bahub.egg-info ./.eggs

run_test_containers: ## Run testing containers and fork to the background
	cd ../env && make -s up VARIANT="test postgres bahub-test"

bahub_shell: ## Go into Bahub container
	cd ../env && make -s sh@bahub

kill_test_containers: ## Stop and remove all containers
	cd ../env && make -s down

test: unit_test functional_test ## Run all tests

functional_test: ## Run functional tests (params: SERVER_DOCKER_TAG, TAG)
	export $$(grep -v '^#' ../env/.env | xargs); \
	cd src && ${PY_BIN} -m unittest discover -s ../tests/functional

unit_test: ## Run unit tests
	export $$(grep -v '^#' ../env/.env | xargs); \
	cd src && ${PY_BIN} -m unittest discover -s ../tests/unit

coverage: ## Generate code coverage
	cd src && coverage run --source . -m unittest discover -s ../tests/unit

run_dev: ## Run a development package
	cd src && ${PY_BIN} -m riotkit.filerepository.bahub ${ARGS}

get_version:
	cat ../server/config/version.yaml  | shyaml get-value version

.SILENT:

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

PIP = sudo pip3
SUDO = sudo
PY_BIN = python3

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-25s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## Build the package
build_package: install_requirements
	echo " >> Building package"
	${PY_BIN} ./setup.py build

## Install requirements
install_requirements:
	echo " >> Installing requirements"
	${PIP} install -r ./requirements.txt || true

## Install
install: build_package
	${SUDO} ${PY_BIN} ./setup.py install
	which bahub
	make clean

## Clean up the local build directory
clean:
	${SUDO} rm -rf ./build ./bahub.egg-info

## Run testing containers and fork to the background
run_test_containers:
	cd tests && ${SUDO} docker-compose up -d
	${SUDO} docker ps

## Stop and remove all containers
kill_test_containers:
	cd tests && ${SUDO} docker-compose down

## Run tests
test:
	./tests/run.sh

## Run unit tests
unit_test:
	${PY_BIN} -m unittest discover -s ./tests/unit

## Generate code coverage
coverage:
	coverage run --source . -m unittest discover -s ./tests/unit

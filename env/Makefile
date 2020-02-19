.SILENT:
.PHONY: help

include .env
export $(shell sed 's/=.*//' .env)

IS_CURRENT_ENV_PRESENT := $(shell test -e .current-env && echo -n yes)

ifeq ($(IS_CURRENT_ENV_PRESENT), yes)
	include .current-env
	export $(shell sed 's/=.*//' .env)
endif

SHELL=/bin/bash
SUDO=sudo
COMPOSE_ARGS=docker-compose -p ${COMPOSE_ENV_NAME} -f docker-compose.yaml
BAHUB_PATH=$(shell pwd)/../../bahub-client/
BAHUB_CONFIG_PATH=$(shell pwd)/bahub.conf.yaml

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

up: _write_current_env down ## Bring up the development environment (params: VARIANT). Example: up VARIANT="s3 postgres"
	VARIANT_ARGS=""; \
	set -x; ${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") up --remove-orphans -d --force-recreate

down: ## Delete the development environment
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") down -v --remove-orphans
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") rm -f -s -v

print: ## Print the docker-compose.yaml file (params: VARIANT)
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") config

sh: ## Get a shell, where Symfony console is present
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") exec --user www-data filerepository bash

sh@bahub: ## Get a shell, where Symfony console is present
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") exec --user root bahub bash

wait_for_app: ## Wait for application to be ready
	${SUDO} ${COMPOSE_ARGS} ps
	echo " >> Waiting for the application to start..."
	counter=60; \
	while ! ${SUDO} ${COMPOSE_ARGS} exec filerepository /healthcheck.sh > /dev/null; do \
		counter=$$((counter-1)); \
		echo -n "."; \
		if [[ "$${counter}" == "0" ]]; then \
			echo " >> Application didn't get up, showing logs and continuing to be able to run tests, so the tests could show what could potentially be the cause"; \
			${SUDO} ${COMPOSE_ARGS} logs; \
			exit 0; \
		fi; \
		sleep 1; \
	done; \
	echo " >> Finished waiting"; \
	sleep 5

read_server_logs: ## Read logs from the server (by the container id)
	${SUDO} docker logs $$(${SUDO} docker ps -a --format="{{ .Names }}" | grep fr_tests_filerepository)

wait_for_containers:
	IFS=' ' read -r -a containers <<< "${CONTAINERS}"; \
	for container in "$${containers[@]}"; do \
		echo " >> Waiting for container '$${container}' to get up..."; \
		max_sleep=300; \
		out="$$(${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") ps)"; \
		until [[ "$${out}" == *"$${container}"* ]]; do \
			sleep 0.5; \
			max_sleep=$$((max_sleep-1)); \
			if [[ "$${max_sleep}" == "0" ]]; then \
				echo " !! Container $${container} didnt get up."; \
				exit 1; \
			fi; \
		done; \
	done

_build_variant_list:
	VARIANT_ARGS=""; \
	lstr="${VARIANT}"; \
	ll=($$lstr); \
	for variant in $${ll[@]}; do \
		VARIANT_ARGS="$${VARIANT_ARGS} -f docker-compose.$${variant}.yaml "; \
	done; \
	echo "$${VARIANT_ARGS}"

_write_current_env:
	echo "VARIANT=${VARIANT}" > .current-env

#### Server tests

server@test_api@ci: wait_for_app read_server_logs ## Run API tests in a docker container (params: CODECEPT_ARGS)
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") exec --user www-data filerepository composer install || true
	make -s server@test_api CODECEPT_ARGS="${CODECEPT_ARGS}"

server@test_api: ## Run API tests in a docker container (params: CODECEPT_ARGS)
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") exec --user www-data filerepository /bin/bash -c 'SYMFONY_DEPRECATIONS_HELPER=max[direct]=0 ./vendor/bin/codecept -v -c codeception.local-docker.yml run --html ${CODECEPT_ARGS}'

server@test_unit: ## Run unit tests in a docker container (params PHPUNIT_ARGS)
	${SUDO} ${COMPOSE_ARGS} $$(make -s _build_variant_list VARIANT="${VARIANT}") exec --user www-data filerepository /bin/bash -c 'SYMFONY_DEPRECATIONS_HELPER=max[direct]=0 ./bin/phpunit ${PHPUNIT_ARGS}'


#### Bahub  tests


#### KropotCLI tests


#### Bahub Client related tasks

bahub@backup_postgres: ## Bahub: Backup PostgreSQL
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} backup postgres

bahub@restore_postgres: ## Bahub: Restore PostgreSQL
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} restore postgres

bahub@backup_www_files: ## Bahub: Backup WWW files
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} backup www

bahub@restore_www_files: ## Bahub: Restore WWW files
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} restore www

bahub@volume_backup_www_files: ## Bahub: Backup WWW files (with temporary container)
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} backup www_using_temporary_container

bahub@volume_restore_www_files: ## Bahub: Restore WWW files (with temporary container)
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} restore www_using_temporary_container

bahub@backup_mysql: ## Bahub: Backup MySQL server
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} backup mariadb

bahub@restore_mysql: ## Bahub: Restore MySQL server
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} restore mariadb

bahub: ## Bahub (params: ARGS)
	cd ${BAHUB_PATH} && ./bahub-dev.sh --debug --config=${BAHUB_CONFIG_PATH} ${ARGS}

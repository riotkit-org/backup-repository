.SILENT:
.PHONY: help

include .env
export $(shell sed 's/=.*//' .env)

SHELL=/bin/bash
SUDO=sudo
COMPOSE_ARGS=docker-compose -p fr_tests -f docker-compose.yaml
BAHUB_PATH=$(shell pwd)/../../bahub-client/
BAHUB_CONFIG_PATH=$(shell pwd)/bahub.conf.yaml

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test_api@ci: wait_for_app ## Run API tests in a docker container (params: CODECEPT_ARGS)
	${SUDO} ${COMPOSE_ARGS} exec --user www-data filerepository composer install
	make test_api CODECEPT_ARGS="${CODECEPT_ARGS}"

test_api: ## Run API tests in a docker container (params: CODECEPT_ARGS)
	${SUDO} ${COMPOSE_ARGS} exec --user www-data filerepository ./vendor/bin/codecept -c codeception.local-docker.yml run ${CODECEPT_ARGS}

test_unit: ## Run unit tests in a docker container (params PHPUNIT_ARGS)
	${SUDO} ${COMPOSE_ARGS} exec --user www-data filerepository ./bin/phpunit ${PHPUNIT_ARGS}

up: ## Bring up the development environment (params: VARIANT). Example: up VARIANT="s3 postgres"
	VARIANT_ARGS=""; \
	if [[ "${VARIANT}" != "" ]]; then \
		VARIANT_ARGS=" -f docker-compose.${VARIANT}.yaml"; \
	fi; \
	set -x; ${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") up --remove-orphans -d --force-recreate

down: ## Delete the development environment
	${SUDO} ${COMPOSE_ARGS} rm -f -s -v

print: ## Print the docker-compose.yaml file (params: VARIANT)
	${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") config

sh: ## Get a shell, where Symfony console is present
	${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") exec --user www-data filerepository bash

sh@replica: ## Get a shell, where Symfony console is present
	${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") exec --user www-data replica_filerepository bash

wait_for_app: ## Wait for application to be ready
	${SUDO} ${COMPOSE_ARGS} ps
	echo " >> Waiting for the application to start..."
	counter=60; \
	while ! ${SUDO} ${COMPOSE_ARGS} exec filerepository /healthcheck.sh > /dev/null; do \
		counter=$$((counter-1)); \
		if [[ "$${counter}" == "0" ]]; then \
			echo " >> Application didn't get up"; \
			${SUDO} ${COMPOSE_ARGS} logs; \
			exit 1; \
		fi; \
		sleep 1; \
	done; \
	sleep 5

_build_variant_list:
	VARIANT_ARGS=""; \
	lstr="${VARIANT}"; \
	ll=($$lstr); \
	for variant in $${ll[@]}; do \
		VARIANT_ARGS="$${VARIANT_ARGS} -f docker-compose.$${variant}.yaml "; \
	done; \
	echo "$${VARIANT_ARGS}"

#### Bahub related tasks

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
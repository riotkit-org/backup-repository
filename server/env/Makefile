.SILENT:
.PHONY: help

SHELL=/bin/bash
SUDO=sudo
COMPOSE_ARGS=docker-compose -p fr_tests -f docker-compose.yaml

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test_api: wait_for_app ## Run API tests in a docker container (params: CODECEPT_ARGS)
	${SUDO} ${COMPOSE_ARGS} exec --user www-data filerepository composer install
	${SUDO} ${COMPOSE_ARGS} exec --user www-data filerepository ./vendor/bin/codecept -c codeception.local-docker.yml run ${CODECEPT_ARGS}

up: ## Bring up the development environment (params: VARIANT)
	VARIANT_ARGS=""; \
	if [[ "${VARIANT}" != "" ]]; then \
		VARIANT_ARGS=" -f docker-compose.${VARIANT}.yaml"; \
	fi; \
	set -x; ${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") up --remove-orphans -d --force-recreate

print: ## Print the docker-compose.yaml file (params: VARIANT)
	${SUDO} ${COMPOSE_ARGS} $$(make _build_variant_list VARIANT="${VARIANT}") config

down: ## Delete the development environment
	${SUDO} ${COMPOSE_ARGS} rm -f -s -v

wait_for_app: ## Wait for application to be ready
	echo " >> Waiting for the application to start..."
	counter=300; \
	while ! ${SUDO} ${COMPOSE_ARGS} exec filerepository /healthcheck.sh > /dev/null; do \
		counter=$$((counter-1)); \
		if [[ "$${counter}" == "0" ]]; then \
			echo " >> Application didn't get up"; \
			exit 1; \
		fi; \
		sleep 1; \
	done

_build_variant_list: ## (Params: VARIANT)
	VARIANT_ARGS=""; \
	lstr="${VARIANT}"; \
	ll=($$lstr); \
	for variant in $${ll[@]}; do \
		VARIANT_ARGS="$${VARIANT_ARGS} -f docker-compose.$${variant}.yaml "; \
	done; \
	echo "$${VARIANT_ARGS}"

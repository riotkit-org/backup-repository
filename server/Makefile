.SILENT:
.PHONY: help

SHELL=/bin/bash
SUDO=sudo

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install the backend application
	[[ -f .env ]] || cp .env.dist .env
	mkdir -p ./var/uploads
	if [[ $$(./.infrastructure/helpers/get-env.sh APP_ENV) == "prod" ]]; then \
		composer install --no-dev; \
	else \
		composer install; \
	fi
	make -s migrate

install_frontend: ## Install MinimumUi dependencies
	npm install
	rm -rf ./public/minimumui/components
	mkdir -p ./public/minimumui
	mv node_modules ./public/minimumui/components

migrate: ## Upgrade database to the recent version of the structure
	./bin/console health:wait-for:database --timeout=300
	./bin/console doctrine:migrations:migrate --no-interaction -vv

run_dev: ## Run a developer web server (do not use on production)
	cd env && make -s up VARIANT="test"

deploy: install install_frontend ## Deploy the application

build_image: ## Build docker image (params: TAG)
	${SUDO} docker build .. -f ./.infrastructure/Dockerfile.x86_64 -t quay.io/riotkit/file-repository:${TAG}
	${SUDO} docker tag quay.io/riotkit/file-repository:${TAG} quay.io/riotkit/file-repository:latest-build

push_image: ## Push docker container
	${SUDO} docker push quay.io/riotkit/file-repository:${TAG}
	${SUDO} docker push quay.io/riotkit/file-repository:latest-build

run_container: ## Run docker image (params: TAG)
	${SUDO} docker run --rm --name file-repository -p 80:80 quay.io/riotkit/file-repository:${TAG}

_configure_ci_environment:
	make -s_set_env NAME=APP_ENV VALUE=test
	make -s _set_env NAME=ANTI_HOTLINK_SECRET_METHOD VALUE='"\\$$$$http_x_expiration_time\\$$$$http_test_header MY-AWESOME-SUFFIX"'
	cp ./config/ids_mapping.yaml.example ./config/ids_mapping.yaml
	./bin/console cache:clear --env=test

_erase_all_data:
	rm -rf ./var/uploads/* || true
	rm ./var/data.db || true
	rm -rf ./var/cache/* || true
	composer install
	make migrate

_set_env:
	if grep -q "${NAME}=" .env; then \
		sed -i.bak 's/${NAME}=.*/${NAME}=${VALUE}/g' .env; \
		rm .env.bak || true; \
	else\
		echo '${NAME}=${VALUE}' >> .env;\
	fi

coverage: ## Generate code coverage from unit testing
	SYMFONY_DEPRECATIONS_HELPER=disabled=1 ./bin/phpunit --coverage-text

test_api: build_image ## Run API tests in a docker container (params: CODECEPT_ARGS, VARIANT)
	cd ../env && make -s up server@test_api CODECEPT_ARGS="${CODECEPT_ARGS}" VARIANT="test ${VARIANT}"

test_api@ci: build_image ## Run API tests in a docker container (params: CODECEPT_ARGS, VARIANT)
	cd ../env && make -s up server@test_api@ci CODECEPT_ARGS="${CODECEPT_ARGS}" VARIANT="test ${VARIANT}"

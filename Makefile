.SILENT:
.PHONY: help

SHELL=/bin/bash

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

SUDO=sudo

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

develop: ## Setup development environment
	echo " >> Setting up GIT hooks for development"
	mkdir -p .git/hooks

install: ## Install the backend application
	[[ -f .env ]] || cp .env.dist .env
	mkdir -p ./var/uploads
	if [[ $$(./.infrastructure/helpers/get-env.sh APP_ENV) == "prod" ]]; then \
		composer install --no-dev; \
	else \
		composer install; \
	fi
	make migrate

install_frontend: ## Install MinimumUi dependencies
	npm install
	rm -rf ./public/minimumui/components
	mv node_modules ./public/minimumui/components

migrate: ## Upgrade database to the recent version of the structure
	./bin/console doctrine:migrations:migrate --no-interaction -vv

build_docs: ## Build documentation
	cd ./docs && make html

run_dev: ## Run a developer web server (do not use on production)
	${SUDO} docker run --rm --name file-repository-dev -v $$(pwd):/var/www/html -p 8000:80 wolnosciowiec/file-repository

browse_docs: ## Browse documentation in web browser
	xdg-open ./docs/build/html/index.html

deploy: install install_frontend ## Deploy the application

build@x86_64: ## Build x86_64 image
	${SUDO} docker build . -f ./Dockerfile.x86_64 -t wolnosciowiec/file-repository
	${SUDO} docker tag wolnosciowiec/file-repository wolnosciowiec/file-repository:latest
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository:master
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository:latest

build_bahub@x86_64: ## Build a docker container
	${SUDO} docker build . -f ./Dockerfile_bahub.x86_64 -t wolnosciowiec/file-repository:bahub
	${SUDO} docker tag wolnosciowiec/file-repository:bahub quay.io/riotkit/bahub

run@x86_64: ## Run x86_64 image
	${SUDO} docker run --rm --name file-repository -p 80:80 wolnosciowiec/file-repository

build@arm7hf: ## Build arm7hf image
	${SUDO} docker build . -f ./Dockerfile.arm7hf -t wolnosciowiec/file-repository:v2-arm7hf

_configure_ci_environment:
	make _set_env NAME=APP_ENV VALUE=test
	make _set_env NAME=ANTI_HOTLINK_SECRET_METHOD VALUE='"\\$$$$http_x_expiration_time\\$$$$http_test_header MY-AWESOME-SUFFIX"'
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

test_api: build@x86_64 ## Run API tests in a docker container
	sudo docker run -e API_TESTS=true -e APP_ENV=test --rm wolnosciowiec/file-repository


coverage: ## Generate code coverage from unit testing
	./bin/phpunit --coverage-text

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
	make migrate

install_frontend: ## Install MinimumUi dependencies
	npm install
	rm -rf ./public/minimumui/components
	mkdir -p ./public/minimumui
	mv node_modules ./public/minimumui/components

migrate: ## Upgrade database to the recent version of the structure
	./bin/console doctrine:migrations:migrate --no-interaction -vv

build_docs: ## Build documentation
	cd ./docs && make html

run_dev: ## Run a developer web server (do not use on production) (params: TAG)
	${SUDO} docker run --rm --name file-repository-dev -v $$(pwd):/var/www/html -p 8000:80 quay.io/riotkit/file-repository:${TAG}

deploy: install install_frontend ## Deploy the application

build_container: ## Build docker image (params: TAG)
	${SUDO} docker build .. -f ./.infrastructure/Dockerfile.x86_64 -t quay.io/riotkit/file-repository:${TAG}
	${SUDO} docker tag quay.io/riotkit/file-repository:${TAG} quay.io/riotkit/file-repository:latest-build

push_container: ## Push docker container
	${SUDO} docker push quay.io/riotkit/file-repository:${TAG}
	${SUDO} docker push quay.io/riotkit/file-repository:latest-build

run_container: ## Run docker image (params: TAG)
	${SUDO} docker run --rm --name file-repository -p 80:80 quay.io/riotkit/file-repository:${TAG}

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

test_api: build_container ## Run API tests in a docker container
	sudo docker run -e API_TESTS=true -e APP_ENV=test --rm quay.io/riotkit/file-repository:latest-build

coverage: ## Generate code coverage from unit testing
	./bin/phpunit --coverage-text

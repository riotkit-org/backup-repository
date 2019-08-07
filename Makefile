.SILENT:

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

SUDO = sudo

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

## Setup development environment
develop:
	echo " >> Setting up GIT hooks for development"
	mkdir -p .git/hooks
	cp .gitver/post-commit .git/hooks

## Install the backend application
install:
	mkdir -p ./var/uploads
	composer install
	make migrate

## Install MinimumUi dependencies
install_frontend:
	npm install
	rm -rf ./public/minimumui/components
	mv node_modules ./public/minimumui/components

## Upgrade database to the recent version of the structure
migrate:
	./bin/console doctrine:migrations:migrate --no-interaction -vv

## Build documentation
build_docs:
	cd ./docs && make html

## Run a developer web server (do not use on production)
run_dev:
	${SUDO} docker run --rm --name file-repository-dev -v $$(pwd):/var/www/html -p 8000:80 wolnosciowiec/file-repository

## Browse documentation in web browser
browse_docs:
	xdg-open ./docs/build/html/index.html

## Deploy the application
deploy: install

## Build x86_64 image
build@x86_64:
	${SUDO} docker build . -f ./Dockerfile.x86_64 -t wolnosciowiec/file-repository
	${SUDO} docker tag wolnosciowiec/file-repository wolnosciowiec/file-repository:latest
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository:master
	${SUDO} docker tag wolnosciowiec/file-repository quay.io/riotkit/file-repository:latest

## Build a docker container
build_bahub@x86_64:
	${SUDO} docker build . -f ./Dockerfile_bahub.x86_64 -t wolnosciowiec/file-repository:bahub
	${SUDO} docker tag wolnosciowiec/file-repository:bahub quay.io/riotkit/bahub

## Run x86_64 image
run@x86_64:
	${SUDO} docker run --rm --name file-repository -p 80:80 wolnosciowiec/file-repository

## Build arm7hf image
build@arm7hf:
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

## Run API tests in a docker container
test_api: build@x86_64
	sudo docker run -e API_TESTS=true -e APP_ENV=test --rm wolnosciowiec/file-repository


## Generate code coverage from unit testing
coverage:
	./bin/phpunit --coverage-text

## Start working on next known release
version_set_next:
	echo -n " >> Please enter next version number ex. [v1.2]: "; \
	read VERSION; \
	gitver next $${VERSION}

## Create a stable release and push into the GIT as a tag with generated version.yaml file
release:
	echo " <==============================================="
	echo " <=== Notice: You are going to release a version"
	echo " <===         Please do it with caution"
	echo " <===         If something would go wrong, then"
	echo " <===         you can clone the repository again"
	echo " <===         to make sure you not destroyed"
	echo " <===         anything :-)"
	echo " <==============================================="
	echo ""

	gitver info
	echo " >> ?! You probably want to release the version defined as NEXT (but with 'v' ex. v2.1.0)"

	set -e; \
	echo -n " >> Please enter a release version ex. [v1.0.1]: "; \
	read VERSION; set -x; \
	git tag -a $${VERSION} -m "Release $${VERSION}"; \
	gitver update version.yaml; \
	git add version.yaml; \
	git add .gitver; \
	git commit -m "Release $${VERSION}"; \
	git tag -d $${VERSION}; \
	git tag -a $${VERSION} -m "Release $${VERSION}"; \
	set +x; \
	echo -n " >> You are going to PUBLISH the GIT TAG $${VERSION}, please confirm [yes/no]: "; \
	read confirm; \
	if [[ $${confirm} == "yes" ]] || [[ $${confirm} == "y" ]]; then \
		set -x; git push origin $${VERSION}; \
	fi


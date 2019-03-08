.SILENT:

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

ENV_NAME = file_repository_playground
SUDO = sudo
COMPOSE = docker-compose

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

## Start
start:
	${SUDO} ${COMPOSE} -f ./docker-compose.yml -p ${ENV_NAME} start --build

## Stop
stop:
	${SUDO} ${COMPOSE} -f ./docker-compose.yml -p ${ENV_NAME} stop

## Delete all containers data
rm:
	${SUDO} ${COMPOSE} -f ./docker-compose.yml -p ${ENV_NAME} rm


## Delete whole database
drop_database:
	printf " >> Deleting whole database in 10 seconds... ARE YOU SURE?\n"

	sleep 5
	printf " >> 5 seconds to delete, you can cancel it with CTRL+C\n"
	sleep 5

	make stop
	rm -rf ./data/mysql/*
	printf " >> DONE.\n"

## Perform a backup
perform_backup:
	set -x; ${SUDO} docker exec -it ${ENV_NAME}_backup_client_1 bahub backup web_server_files

## Restore recent version we have
restore_last_backup:
	set -x; ${SUDO} docker exec -it ${ENV_NAME}_backup_client_1 bahub restore web_server_files

## Restore oldest version we have
restore_last_backup:
	set -x; ${SUDO} docker exec -it ${ENV_NAME}_backup_client_1 bahub restore web_server_files first

## List all versions we have stored
restore_last_backup:
	set -x; ${SUDO} docker exec -it ${ENV_NAME}_backup_client_1 bahub list web_server_files

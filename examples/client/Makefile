SUDO = sudo

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

start: ## Start
	${SUDO} docker-compose -p test_client up

make_redis_backup: ## Start manually REDIS backup
	${SUDO} docker-compose -p test_client exec backup bahub backup some_redis_storage

restore_last_redis_backup: ## Restore latest REDIS backup
	${SUDO} docker-compose -p test_client exec backup bahub restore some_redis_storage latest

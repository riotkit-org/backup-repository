SUDO=sudo

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

start: ## Start
	${SUDO} docker-compose -p test_client up -d

sh: ## Get into the playground to check out how to use bahub
	@echo " >> You are in a docker container shell, where bahub is installed and configured"
	@echo ""
	@echo "    Examples of usage:"
	@echo "      bahub --debug backup some_redis_storage"
	@echo "      bahub list some_redis_storage"
	@echo "      bahub --debug restore some_redis_storage v1"
	@echo "      bahub restore some_redis_storage latest"
	@echo ""
	@echo "      Check out configuration: cat /bahub.conf.yaml"
	@echo "      The variables between \$${ } are fetched from environment, check it: type 'env'"
	@echo ""
	@${SUDO} docker-compose -p test_client exec client bash


PYTHON="python3"

help: ## Help
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test_run: ## Connect to local File Repository testing instance at localhost:8000 port and start streaming
	cd src && ${PYTHON} -m riotkit.filerepository.littlecopymachine \
		--token=test-token-full-permissions \
		--server-url=http://localhost:8000 \
		--storage-path=/tmp/lcm-storage \
		--db-string=sqlite:///database.db \
		collect

install: ## Install in the system
	${PYTHON} setup.py install

user_install: ## Install in user home directory
	${PYTHON} setup.py install --user

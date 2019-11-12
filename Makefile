.SILENT:
.PHONY: help

SHELL=/bin/bash
SUDO=sudo

help:
	@grep -E '^[a-zA-Z\-\_0-9\.@]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build_docs: ## Build documentation
	cd ./docs && make html

browse_docs: ## Browse documentation in web browser
	xdg-open ./docs/build/html/index.html

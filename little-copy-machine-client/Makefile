manual_check: ## Connect to local testing instance at localhost:8000 port and start streaming
	cd src && python -m riotkit.filerepository.littlecopymachine \
		--token=test-token-full-permissions \
		--server-url=http://localhost:8000 \
		--storage-path=/tmp/lcm-storage \
		--db-string=sqlite:///database.db \
		collect


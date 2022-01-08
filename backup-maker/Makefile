BIN_PATH=$$(pwd)/.build/backup-maker

build:
	go build -o ${BIN_PATH} github.com/riotkit-org/backup-repository/backup-maker

test:
	cd context && go test
	cd client && go test
	go test

#download:
#	${BIN_PATH} download --save-path /tmp/test \
#		--url $$(cat .build/test/domain.txt) \
#		-i $$(cat .build/test/collection-id.txt) \
#		-t $$(cat .build/test/auth-token.txt) \
#		--log-level debug
#
restore:
	${BIN_PATH} restore --url $$(cat .build/test/domain.txt) \
		-i $$(cat .build/test/collection-id.txt) \
		-t $$(cat .build/test/auth-token.txt) \
		-c "cat - > /tmp/test" \
		--private-key .build/test/backup.key \
		--passphrase riotkit \
		--recipient test@riotkit.org \
		--log-level debug
#
create:
	export BM_AUTH_TOKEN=$$(cat .build/test/auth-token.txt); \
	export BM_COLLECTION_ID=$$(cat .build/test/collection-id.txt); \
	export BM_PASSPHRASE=riotkit; \
	${BIN_PATH} make --url $$(cat .build/test/domain.txt) \
		-c "cat main.go" \
		--key .build/test/backup.key \
		--recipient test@riotkit.org \
		--log-level debug

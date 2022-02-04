build:
	go build -tags=nomsgpack .

test_login:
	curl -X POST -d '{"username":"admin","password":"my_password"}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/login'

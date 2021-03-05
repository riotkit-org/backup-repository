API tests
=========

Backup Repository server API tests. Each scenario is sending HTTP requests and verifying results.

Development environment
-----------------------

```bash
rkd :install
rkd :test:api
rkd :test:api --filter AuthenticationCest
```

Continuous Integration
----------------------

On automated environment in Github Actions the environment is using `integration-env` from this repository.
The tests are performed on docker environment that tries to be similar as much as possible to the production environment.

```bash
# setup environment (one time)
cd ../integration-env
rkd :run

# install libraries (one time)
cd ../api-tests
rkd :install

# run tests (use --filter to run single suites and single tests)
rkd :test:api --docker
rkd :test:api --filter AuthenticationCest --docker
```

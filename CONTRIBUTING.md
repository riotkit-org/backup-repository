Contributing
============

It would be fantastic if you would create an issue or better, create a PR, or do a code review. Contributions are always welcome!
Before doing a contribution please take a look at our guideline.

Architecture
------------

The architecture is module based, like in Golang projects. A little inspiration was taken from PHP and Python to use `Repository pattern`.

**Most of the modules are split into:**
- entity: Structs containing domain logic
- repository: Interaction with data store via `ConfigurationProvider` or `GORM` (only private methods there)
- service: Public methods performing actual actions on the model, repository. There are defined actions that aggregates logic as much as it is possible

**'http' module**

It is a special module that defines all routes, it's authentication and security logic for every endpoint.

- auth: Endpoints related to authorization and users management
- collection: Endpoints for operations on collections
- responses: HTTP responses format standardization
- utils: Various utils for validating user input, checking session etc.
- main: Registers all the endpoints to the router

Testing
-------

1. Application should be tested with all supported software listed in README.md if there is a risk that something could be broken
2. Unit tests coverage is required
3. [API tests in PyTest](./tests) should be written, especially when code is difficult to cover with unit tests

### Manual testing helpers

Take a look at [test.mk](./test.mk) file, which contains Makefile tasks for manual testing, used at development time as handy shortcuts.

```bash
make build run
```

```bash
# at first login and export the retrieved token into the shell
make test_login
export TOKEN=...

# then use prepared curl snippets to test functionalities
make test_collection_health test_whoami # ...
```

### Automated testing

**Unit tests can be executed within:**

```bash
make test

# or
make coverage
```

**There are also E2E tests on real application image:**

```bash
# first time
make setup_api_tests

# then
make api_tests
```

Development environment
-----------------------

Recommended setup is using k3d, which is a K3s lightweight Kubernetes distribution inside a docker container.

**Requirements:**
- [k3d](https://k3d.io/)
- [helm](https://helm.sh/docs/intro/quickstart/)
- [docker](https://docs.docker.com/get-docker/)
- [make](https://www.gnu.org/software/make/)
- [kubectl](https://kubernetes.io/docs/tasks/tools/install-kubectl-linux/)

#### Setting up cluster

```bash
make k3d k8s_postgres k8s_minio k8s_dev_registry
```

#### Promoting changes to the local cluster

```bash
# will build, produce docker, publish docker to local registry and deploy application using helm on local cluster
make k8s_test_promote
```

#### Checking

```bash
curl -vvv http://127.0.0.1:30080/health?code=changeme

# in browser
xdg-open http://127.0.0.1:30080/health?code=changeme
```

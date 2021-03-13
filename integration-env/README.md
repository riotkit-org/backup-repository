Integration environment
=======================

Runs applications in containers. Mainly for functional testing on CI.

Usage
-----

```bash
# trigger rebuild of all our docker images (excluding external images like Selenium)
rkd :build

# run the environment
rkd :run

# run environment with mounted server directories, so the changes to server code will be visible immediately
# (this is helpful for development)
rkd :run --dev
```

Production-like
---------------

There is a rule that on CI the functional tests - E2E, API tests are running on production-like containers to
additionally test the entrypoints, webserver configurations and more production-like settings.

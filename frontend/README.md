Backup Repository Frontend
==========================

Dashboard and management frontend written in Vue.js

Goals
-----

- Provide an overview of granted accesses, created collections, usage
- Easy-to-use backup collection management for users and administrators
- Overview of which backups were submitted and when
- Security auditing of backups

Running development environment
-------------------------------

```bash
npm install
npm run dev
```

End-to-end tests in Chromium
----------------------------

Requires a proper Chromedriver (according to the Chromium browser version) to be installed.

```bash
./vendor/bin/behat
```

Thanks to
---------

Based on [Vue Light Bootstrap Dashboard](http://vuejs.creative-tim.com/vue-light-bootstrap-dashboard) on MIT license.

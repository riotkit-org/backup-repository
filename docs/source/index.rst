.. File Repository documentation master file, created by
   sphinx-quickstart on Thu Jan  3 18:33:15 2019.

Backup Repository's documentation
=================================

A specialized ninja for backup making - complete backups ecosystem. Fully multi-tenant, with its very granular permissions and client-side (E2E) encryption can act as a farm of backups for various people and organizations.


**Main functionality:**

- Fully **multi-tenant**, granular permissions and roles
- **End-To-End encryption**. Server acts as a blob storage, client is encrypting client-side
- Security focused, limit access by IP address, User Agent, limited scope API tokens. In future we may implement "scheduled backup windows" to prevent overwriting backups in short time periods
- Backups rotation strategies
- Very **low resources requirements**, a container with 256 MB ram and 0.5vCPU on a shared VM can fit
- Fully compatible with containerized workflows (**Docker supported** out-of-the-box by both client and server)
- Administrative **frontend in web browser**
- **JSON API**, JSON Web Token (JWT), SWAGGER documentation for the API
- On client side the backups are made on-the-fly, you don't need extra disk space for temporary storage

.. toctree::
   :maxdepth: 5
   :caption: Contents:

   server/index
   docker
   project-rules
   clients/index

From authors
============

Project was started as a part of RiotKit initiative, for the needs of grassroot organizations such as:

- Fighting for better working conditions syndicalist (International Workers Association for example)
- Tenants rights organizations
- Various grassroot organizations that are helping people to organize themselves without authority


Technical description:

Project was created in *Domain Driven* like design in PHP 8, with Symfony 4+ framework.
There are API tests written in *Codeception*, E2E tests in Behat and unit tests written in *PhpUnit*.
Feel free to submit pull requests, report issues, or join our team.
The project is licensed with a MIT license.

.. rst-class:: language-en align-center

*RiotKit Collective*

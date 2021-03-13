Production Environment
======================

Provides a working example of production deployment on small scale.
The setup is targeted to provide a centralised backups hosting for ~10 virtual machines, ~50 small-size services.

**Technology stack:**
- Google Cloud: Object Storage + Compute node
- Terraform + Ansible
- RiotKit Universal Node
- RiotKit Harbor 2.x

**Goals:**
- Provide an example for small scale
- Cheap, partially fits in free tier
- In the cloud, with infrastructure as a code

**Notices when setting up the environment at all:**
- Any kind of lambdas are not enough, as the application is taking large amounts of data as HTTP input. Most of HTTP routers in the cloud have unchangeable limits for connection timeout and body size
- Adding more cores to existing compute node may scale the application, but not that effectively as having multiple compute nodes
- Don't forget about adjusting PHP-FPM amount of worker nodes

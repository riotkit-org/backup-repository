User creation guide
===================

By using `kind: BackupUser` custom resource definition create a user in a GitOps-way.

**backupuser.yaml**

```yaml
---
apiVersion: backups.riotkit.org/v1alpha1
kind: BackupUser
metadata:
    name: admin
spec:
    # best practice is to set this e-mail to same e-mail as GPG key owner e-mail (GPG key used on client side to encrypt files)
    email: example@example.org
    deactivated: false
    organization: "Riotkit"
    about: "System administrator"
    passwordFromRef: 
        name: backup-repository-passwords
        entry: admin
    restrictByIP:
        - 1.2.3.4
    roles:
        - systemAdmin
```

> :information_source: Passwords are not stored in plain-text inside `kind: BackupUser` custom resource definition

Generate a password and encode it. `backup-repository` CLI will encode the password with Argon2 and base64.

```bash
PASSWORD=$(openssl rand -base64 $((1024*1024)) | sha512sum -)
backup-repository --encode-password="${PASSWORD}"
```

Create a `kind: Secret` referenced in `kind: BackupUser`, it will store user password in hashed form.
Password hashed and encoded by `backup-repository` can be inserted into `data` section (not in `stringData` as it is already base64 encoded).

**secret.yaml**

```yaml
---
apiVersion: v1
kind: Secret
metadata:
    name: backup-repository-passwords
type: Opaque
data:
    # admin: admin
    # to generate: `backup-repository --encode-password "admin"
    admin: "JGFyZ29uMmlkJHY9MTkkbT02NTUzNix0PTEscD00JHpuVy9IT2Y4Q3RkdStvNSttYlR2REE9PSRaZlVpRGl2QWV2T2RZNndKYWJBb0FQdmM1a1hsemxDNkg2OFY2dGVmNUY0PQ=="

```

Apply user and password to the cluster.

```bash
# notice: The namespace should match Backup Repository namespace
kubectl apply -f secret.yaml -n backups
kubectl apply -f backupuser.yaml -n backups
```

Generate a token allowing to operate on Backup Repository API.

```bash
PASSWORD="..."
curl -X POST -d '{"username":"admin","password":"${PASSWORD}"}' -H 'Content-Type: application/json' 'http://localhost:8080/api/stable/auth/login' -k
```

Copy the token from the repsponse and keep it safe - it will allow to perform interactions with API as your user.

```json
{
    "data": {
        "expire": "2032-05-25T05:56:45Z",
        "sessionId": "xxxxxxxxxxxxx-USE-THIS-SESSION-ID-TO-REVOKE-YOUR-TOKEN-xxxxxxxxxxxxxxxxxxxxxxx",
        "token": "xxxxxxxxxxxxxxxxxx-COPY-THIS-SECRET-TOKEN-TO-ACCESS-YOUR-API-xxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    },
    "status": true
}
```

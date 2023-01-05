regexp="^v[0-9.]+"
if [[ "$GITHUB_REF" =~ $regexp ]]; then
    pkgver=${GITHUB_REF##*/}
else
    pkgver=0.0.0
fi

pkgname=backup-repository
pkgver=${pkgver/-/}
pkgrel=1
pkgdesc='Backup storage for E2E GPG-encrypted files, with multi-user, quotas, versioning, using a object storage (S3/Min.io/GCS etc.) and deployed on Kubernetes or standalone.'
arch=('x86_64')
url="https://github.com/riotkit-org/backup-repository"
license=('APACHE')
makedepends=('go')

prepare(){
    mkdir -p .build/
}
build() {
    # use already built artifacts
    return 0
}
check() {
    return 0
}
package() {
    install -Dm755 ../.build/backup-repository "$pkgdir"/usr/bin/$pkgname
}

pkgname=backup-repository
pkgver=${GITHUB_REF##*/}
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
    cd ..
    export CGO_CPPFLAGS="${CPPFLAGS}"
    export CGO_CFLAGS="${CFLAGS}"
    export CGO_CXXFLAGS="${CXXFLAGS}"
    export CGO_LDFLAGS="${LDFLAGS}"
    export GOFLAGS="-buildmode=pie -trimpath -ldflags=-linkmode=external -mod=readonly -modcacherw"
    export GOROOT=/usr/lib/go
    go build -o ./.build/backup-repository ./
}
check() {
    return 0
}
package() {
    install -Dm755 ../.build/backup-repository "$pkgdir"/usr/bin/$pkgname
}

FROM gcr.io/distroless/base-debian11
ADD .build/backup-repository /go/bin/backup-repository
ENTRYPOINT ["/go/bin/backup-repository"]

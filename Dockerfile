FROM gcr.io/distroless/base-debian11
ADD .build/backup-repository /go/bin/backup-repository

USER 1002
ENTRYPOINT ["/go/bin/backup-repository"]

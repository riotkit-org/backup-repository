FROM gcr.io/distroless/base-debian11

ENV GIN_MODE=release

ADD .build/backup-repository /go/bin/backup-repository

USER 65532
ENTRYPOINT ["/go/bin/backup-repository"]

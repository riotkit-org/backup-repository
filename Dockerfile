FROM alpine:3.15 as builder
ADD .build/backup-repository /go/bin/backup-repository
RUN chmod +x /go/bin/backup-repository && chmod 755 /go/bin/backup-repository && chown 1001 /go/bin/backup-repository


FROM gcr.io/distroless/base-debian11
ENV GIN_MODE=release
ADD --from=builder /go/bin/backup-repository /go/bin/backup-repository
USER 65532
ENTRYPOINT ["/go/bin/backup-repository"]

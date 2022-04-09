FROM alpine:3.15 as builder
ADD .build/backup-repository /backup-repository
RUN chmod +x /backup-repository && chmod 755 /backup-repository && chown 1001 /backup-repository


FROM gcr.io/distroless/base
ENV GIN_MODE=release
ADD docs /usr/share/backup-repository
COPY --from=builder /backup-repository /backup-repository
USER 65532
ENTRYPOINT ["/backup-repository"]

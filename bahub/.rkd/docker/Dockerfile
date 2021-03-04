FROM python:3.9-alpine3.13
MAINTAINER RiotKit <riotkit.org>

ARG JOBBER_VERSION="1.4.4"
ARG JOBBER_BUILD_SUFFIX="-r0"
ARG USER=bahub
ARG UID=1000
ARG GID=1000
ENV CONFIG="bahub.conf.yaml"
ENV DEBUG="false"

# Create a non-privileged user
RUN addgroup --gid $GID bahub \
    && adduser \
        --disabled-password \
        --gecos "" \
        --home "/home/bahub" \
        --ingroup "$USER" \
        --uid "$UID" \
        "$USER"

WORKDIR /tmp

# Execute everything in bash from now instead of using /bin/sh
RUN apk update && apk add --no-cache bash
SHELL ["/bin/bash", "-c"]

# install docker client, and shell utilities as dependencies required by built-in Bahub adapters
RUN apk add --no-cache libcurl docker git postgresql-client mysql-client tar sudo gnupg curl

# install jobber (a cron-like alternative)
RUN cd / && wget https://github.com/dshearer/jobber/releases/download/v$JOBBER_VERSION/jobber-$JOBBER_VERSION$JOBBER_BUILD_SUFFIX.apk \
    && tar xvf jobber-*.apk \
    && ln -s /usr/libexec/jobber* /usr/bin/ \
    && rm jobber-*.apk

# install Bahub system wide inside container. GIT is required for version information for Python's PBR
ADD bahub /bahub
ADD .git /bahub/.git
WORKDIR /bahub
USER root
RUN set -x; apk add --virtual .build-deps gcc musl-dev python3-dev curl-dev libffi-dev \
    && pip install -r ./requirements.txt \
    && ./setup.py build sdist \
    && ./setup.py install \
    && apk del .build-deps \
    && rm -rf /bahub

# Now we will operate only on /home/bahub directory
ADD bahub/bahub.conf.yaml /home/bahub/bahub.conf.yaml
ADD bahub/.rkd/docker/.jobber /home/bahub/.jobber
RUN mkdir -p /home/bahub/logs /var/jobber/1000 && touch /home/bahub/logs/jobber.log
RUN chown bahub:bahub -R /home/bahub /var/jobber/1000
WORKDIR /home/bahub

# We need root permissions to preserve file permissions, especially on restore
USER root

ENTRYPOINT ["/usr/libexec/jobberrunner"]
CMD ["--unixsocket", "/var/jobber/1000/cmd.sock", "/home/bahub/.jobber"]

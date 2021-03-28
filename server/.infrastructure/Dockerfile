#
# Build frontend
#
FROM alpine:3.13 as frontend_builder

ENV NODE_ENV=production

RUN ls
ADD ./frontend /build
RUN apk --update add npm nghttp2
WORKDIR /build
RUN npm install --also=dev \
    && npm run build


#
# Build backend
#
FROM quay.io/riotkit/php-app:7.4-x86_64

RUN apk --update add multirun libcap libpq

ENV DATABASE_URL=postgres://postgres:postgres@postgres:5432/postgres \
    FS_ADAPTER=local \
    FS_LOCAL_DIRECTORY=%kernel.root_dir%/../var/uploads \
    FS_LOCAL_LAZY=1 \
    FS_LOCAL_WRITEFLAGS= \
    FS_LOCAL_LINKHANDLING= \
    FS_LOCAL_PERMISSIONS= \
    FS_AWSS3V3_CLIENT=s3_client \
    FS_AWSS3V3_BUCKET=misc \
    FS_AWSS3V3_PREFIX= \
    FS_AWSS3V3_OPTIONS_ENDPOINT=http://s3:9000 \
    FS_AWSS3V3_VERSION=latest \
    FS_AWSS3V3_REGION=eu-central-1 \
    FS_AWSS3V3_KEY=some-key \
    FS_AWSS3V3_SECRET=some-secret \
    TOKEN_EXPIRATION_TIME="+30 minutes" \
    TEMP_DIRECTORY="%kernel.project_dir%/var/tmp" \
    ENC_SALT=default-change-me \
    APP_ENV=prod \
    HTTP_TIMEOUT=30 \
    WAIT_FOR_DB_TIMEOUT=30 \
    BACKUP_ONE_VERSION_MAX_SIZE=4GB \
    BACKUP_COLLECTION_MAX_SIZE=15GB \
    READ_ONLY=false \
    HEALTH_CHECK_CODE= \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=15G \
    PHP_POST_MAX_SIZE=15G \
    TZ="UTC" \
    CACHE_ADAPTER_TYPE="cache.adapter.filesystem" \
    CACHE_REDIS_URL="redis://localhost" \
    CACHE_MEMCACHED_PROVIDER="memcached://localhost" \
    SECURITY_ADMIN_TOKEN="" \
    CLEAR_CACHE="true" \
    JWT_PASSPHRASE=""

COPY server/ /home/backuprepository
COPY --from=frontend_builder /build/dist/ /home/backuprepository/public/
COPY frontend/public/img /home/backuprepository/public/img/
RUN mv /home/backuprepository/public/index.html /home/backuprepository/public/frontend.html

ADD server/.infrastructure/container-files/nginx/nginx.conf /etc/nginx/nginx.conf
ADD server/.infrastructure/container-files/php/upload.ini /usr/local/etc/php/conf.d/upload.ini

# hack: Fix for the PHP not in PATH on www-data account
RUN ln -s /usr/local/bin/php /bin/php
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx

WORKDIR "/home/backuprepository/"

# Make sure the permissions are ok
RUN set -x \
    && rm -rf var/cache/* \
    && rm -rf var/log/* \
    && rm -rf var/uploads/* \
    && rm -f var/*.db \
    && rm config/jwt/*.pem || true \
    && mkdir -p var/log var/cache /var/log/nginx config/jwt \
    && cp .env.dist .env \
    && chown www-data:www-data /home/backuprepository/ /var/log/nginx config/jwt -R \
    && php --version \
    && su www-data -s /bin/bash -c "php --version" \
    && su www-data -s /bin/bash -c "composer install --no-scripts --no-dev" \
    && chown www-data:www-data /home/backuprepository/ -R


# Health check
ADD server/.infrastructure/container-files/healthcheck.sh /
RUN chmod +x /healthcheck.sh
HEALTHCHECK --interval=60s --timeout=3s CMD /healthcheck.sh || exit 1

# Entrypoint
ADD server/.infrastructure/container-files/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Record into the CI logs
RUN nginx -v && php --version

USER www-data

#!/bin/bash
set -e

echo " >> Adding required extensions..."

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE EXTENSION "uuid-ossp";
EOSQL

echo " [ OK ] "

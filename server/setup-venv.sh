#!/bin/bash
set -e

if test -d .venv; then
    echo "source ./.venv/bin/activate"
    exit 0
fi

virtualenv .venv
source ./.venv/bin/activate
pip install -r requirements.txt
echo "source ./.venv/bin/activate"

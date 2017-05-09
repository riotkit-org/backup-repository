#!/bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )"
cd ..

PATH="$PATH:./node_modules/bower/bin" bower install
exit $?

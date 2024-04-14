#!/bin/bash
cd ..
sqlite3 /Users/neku/repos/Uguu/dist/Benchmarks/tmp/uguu.sq3 -init /Users/neku/repos/Uguu/src/static/dbSchemas/sqlite_schema.sql ""
./vendor/bin/phpbench run Benchmarks --report=default
rm -rf Benchmarks/tmp/*.jpg
rm -rf Benchmarks/tmp/uguu.sq3
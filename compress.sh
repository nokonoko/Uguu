#!/bin/bash
pathTo="$1"
for file in $(find "$pathTo" -name '*.html' -o -name '*.css' -o -name '*.js')
do
    gzip --best -vk "$file"
done
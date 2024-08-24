#!/bin/bash
rsync -av --exclude 'src/config.json' --exclude 'node_modules' --exclude 'dist' --exclude '.git' --exclude 'build' /Users/neku/repos/Uguu/ neku@uguu1.uguu.se:/var/www/uguu-dev/
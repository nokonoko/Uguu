#!/bin/bash
make DOCKER_TAG="$(cat package.json | grep version | cut -d '"' -f4)" CONTAINER_NAME=uguu purge-container

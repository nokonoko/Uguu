#!/bin/bash
echo ">>> BUILDING UGUU CONTAINER <<<"
make UGUU_RELEASE_VER=$(cat package.json | grep version | cut -d '"' -f4) DOCKER_TAG=$(cat package.json | grep version | cut -d '"' -f4) build-image
echo ">>> DONE! <<<"

echo ">>> Starting Uguu container! <<<"
make DOCKER_TAG=$(cat package.json | grep version | cut -d '"' -f4) CONTAINER_NAME=uguu run-container
echo ">>> DONE! <<<"

MAKE="make"
INSTALL="install"
TAR="tar"
GREP="grep"
NODE="node"
NPM="npm"
NODEJQ="node_modules/node-jq/bin/jq"
SQLITE="sqlite3"
CONF="src/config.json"
PHP="php"
CURL="curl"
DESTDIR = $(shell $(CURDIR)/$(NODEJQ) -r ".dest" $(CURDIR)/$(CONF))
NPX="npx"
PKG_VERSION = $(shell $(CURDIR)/$(NODEJQ) -r ".version" $(CURDIR)/package.json)
TMPDIR = $(shell mktemp -d)
DOCKER_IMAGE = "$(shell basename $(CURDIR) | tr [:upper:] [:lower:])"
DOCKER_TAG="$(DOCKER_TAG)"
CONTAINER_NAME="$(CONTAINER_NAME)"
# default modules
MODULES="php"

pageList = $(shell $(CURDIR)/$(NODEJQ) -r ".pages[]" $(CURDIR)/$(CONF))
noExt = $(shell echo $(i) | cut -d '.' -f1)

all: builddirs npm_dependencies composer ejs minify-all copy-img copy-php

ejs:
	$(foreach i,$(pageList), \
	$(NPX) ejs -f $(CURDIR)/$(CONF) $(CURDIR)/src/templates/$(i) -o $(CURDIR)/build/html/unmin/$(noExt).html;)

minify-all:
	$(NPX) minify-all-cli -s $(CURDIR)/src/static/js -d $(CURDIR)/build/js
	$(NPX) minify-all-cli -s $(CURDIR)/src/static/css -d $(CURDIR)/build/css
	$(NPX) minify-all-cli -s $(CURDIR)/build/html/unmin/ -d $(CURDIR)/build/html/min/

installdirs:
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img/grills

copy-img:
	$(NPX) imagemin $(CURDIR)/src/static/img/*.png -o=$(CURDIR)/build/img/
	$(NPX) imagemin $(CURDIR)/src/static/img/grills/*.png --plugin=pngquant -o=$(CURDIR)/build/img/grills/

copy-php:
	cp -v $(CURDIR)/src/static/php/*.php $(CURDIR)/build/php/

install: installdirs
	cp -rv $(CURDIR)/build/* $(DESTDIR)/
	mv $(DESTDIR)/html/min/* $(DESTDIR)/
	mv $(DESTDIR)/js/* $(DESTDIR)/
	mv $(DESTDIR)/css/* $(DESTDIR)/
	mv $(DESTDIR)/php/* $(DESTDIR)/
	rm -rf $(DESTDIR)/html
	rm -rf $(DESTDIR)/css
	rm -rf $(DESTDIR)/js
	rm -rf $(DESTDIR)/php
	mv $(DESTDIR)/uguu.css $(DESTDIR)/uguu.min.css
	mv $(DESTDIR)/uguu.js $(DESTDIR)/uguu.min.js

dist:
	DESTDIR=$(TMPDIR)/uguu-$(PKGVERSION)
	export DESTDIR
	install
	$(TAR) cJf uguu-$(PKG_VERSION).tar.xz $(DESTDIR)
	rm -rf $(TMPDIR)
	

clean:
	rm -rvf $(CURDIR)/node_modules 
	rm -rvf $(CURDIR)/build
	

uninstall:
	rm -rvf $(DESTDIR)/
	

npm_dependencies:
	$(NPM) install

composer:
	$(CURL) -o composer-setup.php https://raw.githubusercontent.com/composer/getcomposer.org/main/web/installer
	$(PHP) composer-setup.php --quiet
	rm composer-setup.php
	php composer.phar update
	php composer.phar install

build-image:
		tar --exclude='./uguuForDocker.tar.gz' --exclude='./vendor' --exclude='./node_modules' -czf uguuForDocker.tar.gz .
		mv uguuForDocker.tar.gz docker/
		docker build -f docker/Dockerfile --build-arg VERSION=$(UGUU_RELEASE_VER) --no-cache -t $(DOCKER_IMAGE):$(DOCKER_TAG) .

run-container:
		 docker run --name $(CONTAINER_NAME) -d -p 8080:80 -p 8081:443 $(DOCKER_IMAGE):$(DOCKER_TAG)

purge-container:
	if docker images | grep $(DOCKER_IMAGE); then \
	 	docker rm -f $(CONTAINER_NAME) && docker rmi $(DOCKER_IMAGE):$(DOCKER_TAG) || true;\
	fi;		

builddirs:
	mkdir -p $(CURDIR)/build $(CURDIR)/build/img $(CURDIR)/build/html $(CURDIR)/build/html/min $(CURDIR)/build/html/unmin $(CURDIR)/build/js $(CURDIR)/build/css $(CURDIR)/build/php


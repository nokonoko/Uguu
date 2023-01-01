MAKE = "make"
INSTALL = "install"
TAR = "tar"
GREP = "grep"
NODE = "node"
NPM = "npm"
NODEJQ = "node_modules/node-jq/bin/jq"
SQLITE = "sqlite3"
CONF = "src/config.json"
PHP = "php"
CURL = "curl"
DESTDIR = $(shell $(CURDIR)/$(NODEJQ) -r ".dest" $(CURDIR)/$(CONF))
SITEDOMAIN = $(shell $(CURDIR)/$(NODEJQ) -r ".DOMAIN" $(CURDIR)/$(CONF))
FILESDOMAIN = $(shell $(CURDIR)/$(NODEJQ) -r ".FILE_DOMAIN" $(CURDIR)/$(CONF))
CONTACT_EMAIL = $(shell $(CURDIR)/$(NODEJQ) -r ".infoContact" $(CURDIR)/$(CONF))
PKG_VERSION = $(shell $(CURDIR)/$(NODEJQ) -r ".version" $(CURDIR)/package.json)
TMPDIR = $(shell mktemp -d)
DOCKER_IMAGE = "$(shell basename $(CURDIR) | tr [:upper:] [:lower:])"
DOCKER_TAG = "$(DOCKER_TAG)"
CONTAINER_NAME = "$(CONTAINER_NAME)"
# default modules
MODULES="php"

pageList = $(shell $(CURDIR)/$(NODEJQ) -r ".pages[]" $(CURDIR)/$(CONF))
noExt = $(shell echo $(i) | cut -d '.' -f1)

all: builddirs npm_dependencies ejs "node_modules/minify/bin/minify.js"-all copy-img copy-php

ejs:
	$(foreach i,$(pageList), \
	"node_modules/ejs/bin/cli.js" -f $(CURDIR)/$(CONF) $(CURDIR)/src/templates/$(i) -o $(CURDIR)/build/html/unmin/$(noExt).html;)

"node_modules/minify/bin/minify.js"-all:
	"node_modules/minify/bin/minify.js" $(CURDIR)/src/static/js/uguu.js > $(CURDIR)/build/js/uguu.min.js
	"node_modules/minify/bin/minify.js" $(CURDIR)/src/static/css/uguu.css > $(CURDIR)/build/css/uguu.min.css
	"node_modules/minify/bin/minify.js" $(CURDIR)/build/html/unmin/faq.html > $(CURDIR)/build/html/min/faq.html
	"node_modules/minify/bin/minify.js" $(CURDIR)/build/html/unmin/tools.html > $(CURDIR)/build/html/min/index.html
	"node_modules/minify/bin/minify.js" $(CURDIR)/build/html/unmin/tools.html > $(CURDIR)/build/html/min/tools.html

installdirs:
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img/grills

copy-img:
	"node_modules/imagemin-cli/cli.js" $(CURDIR)/src/static/img/*.png -o=$(CURDIR)/build/img/
	"node_modules/imagemin-cli/cli.js" $(CURDIR)/src/static/img/grills/*.png --plugin=pngquant -o=$(CURDIR)/build/img/grills/


copy-php:
	cp -v $(CURDIR)/src/static/php/*.php $(CURDIR)/build/php/
	cp -v $(CURDIR)/src/Classes/*.php $(CURDIR)/build/php/Classes/

install: installdirs
	cp -rv $(CURDIR)/build/* $(DESTDIR)/
	cp $(CURDIR)/src/*.json $(DESTDIR)/
	mv $(DESTDIR)/html/min/* $(DESTDIR)/public/
	mv $(DESTDIR)/js/* $(DESTDIR)/public/
	mv $(DESTDIR)/css/* $(DESTDIR)/public/
	mv $(DESTDIR)/php/* $(DESTDIR)/
	rm -rf $(DESTDIR)/html
	rm -rf $(DESTDIR)/css
	rm -rf $(DESTDIR)/js
	rm -rf $(DESTDIR)/php
	mv $(DESTDIR)/img $(DESTDIR)/public/
	mv $(DESTDIR)/grill.php $(DESTDIR)/public/
	mv $(DESTDIR)/upload.php $(DESTDIR)/public/
	cd $(DESTDIR)/ && $(CURL) -o composer-setup.php https://raw.githubusercontent.com/composer/getcomposer.org/main/web/installer
	cd $(DESTDIR)/ && $(PHP) composer-setup.php --quiet
	cd $(DESTDIR)/ && rm composer-setup.php
	cd $(DESTDIR)/ && php composer.phar update && php composer.phar install && php composer.phar dump-autoload

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

build-container-no-cache:
		tar --exclude='./uguuForDocker.tar.gz' --exclude='./vendor' --exclude='./node_modules' --exclude='./build' --exclude='./dist' --exclude='./.git' -czf uguuForDocker.tar.gz .
		mv uguuForDocker.tar.gz docker/
		docker build -f docker/Dockerfile --build-arg VERSION=$(PKG_VERSION) --no-cache -t uguu:$(PKG_VERSION) .

build-container:
		tar --exclude='./uguuForDocker.tar.gz' --exclude='./vendor' --exclude='./node_modules' --exclude='./build' --exclude='./dist' --exclude='./.git' -czf uguuForDocker.tar.gz .
		mv uguuForDocker.tar.gz docker/
		docker build -f docker/Dockerfile --build-arg DOMAIN=$(SITEDOMAIN) --build-arg FILE_DOMAIN=$(FILESDOMAIN) --build-arg CONTACT_EMAIL=$(FILESDOMAIN) -t uguu:$(PKG_VERSION) .

run-container:
		docker run --name uguu -d -p 8080:80 -p 8081:443 uguu:$(PKG_VERSION)
		docker exec -it uguu /bin/bash service nginx start
		docker exec -it uguu /bin/bash service php8.1-fpm start

purge-containers:
	if docker images | grep uguu; then \
	 	docker rm -f uguu && docker rmi uguu:$(PKG_VERSION) || true;\
	fi;		

remove-container:
	docker rm -f uguu

builddirs:
	mkdir -p $(CURDIR)/build $(CURDIR)/build/img $(CURDIR)/build/html $(CURDIR)/build/html/min $(CURDIR)/build/html/unmin $(CURDIR)/build/js $(CURDIR)/build/css $(CURDIR)/build/php $(CURDIR)/build/php/Classes  $(CURDIR)/build/public


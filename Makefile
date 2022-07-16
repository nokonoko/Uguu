MAKE="make"
INSTALL="install"
TAR="tar"
GREP="grep"
NODE="node"
NPM="npm"
NODEJQ="node_modules/node-jq/bin/jq"
CONF="dist.json"
DESTDIR = $(shell $(CURDIR)/$(NODEJQ) -r ".dest" $(CURDIR)/$(CONF))
NPX="npx"
PKG_VERSION = $(shell $(CURDIR)/$(NODEJQ) -r ".version" $(CURDIR)/package.json)
TMPDIR := $(shell mktemp -d)
DOCKER_IMAGE = "$(shell basename $(CURDIR) | tr [:upper:] [:lower:])"
DOCKER_TAG="$(DOCKER_TAG)"
CONTAINER_NAME="$(CONTAINER_NAME)"
# default modules
MODULES="php"

pageList = $(shell $(CURDIR)/$(NODEJQ) -r ".pages[]" $(CURDIR)/$(CONF))
noExt = $(shell echo $(i) | cut -d '.' -f1)

all: builddirs npm_dependencies ejs minify-all copy-img submodules

ejs:
	$(foreach i,$(pageList), \
	$(NPX) ejs -f $(CURDIR)/$(CONF) $(CURDIR)/templates/$(i) -o $(CURDIR)/build/tmp/html/$(noExt).html;)

minify-all:
	$(NPX) minify-all-cli -s $(CURDIR)/build/tmp/html/ -d $(CURDIR)/build/html
	$(NPX) minify-all-cli -s $(CURDIR)/static/js -d $(CURDIR)/build/js
	$(NPX) minify-all-cli -s $(CURDIR)/static/css -d $(CURDIR)/build/css

installdirs:
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img/grills
ifneq (,$(findstring php,$(MODULES)))
	mkdir -p $(DESTDIR)/includes
endif
ifneq (,$(findstring moe,$(MODULES)))
	mkdir -p $(DESTDIR)/moe/{css,fonts,includes,js,login,panel/css/font,panel/css/images,register,templates}
endif

copy-img:
	cp -v $(CURDIR)/static/img/*.png $(CURDIR)/build/img/
	cp -R $(CURDIR)/static/img/grills $(CURDIR)/build/img/

copy-php:
ifneq ($(wildcard $(CURDIR)/static/php/.),)
	cp -rv $(CURDIR)/static/php/* $(CURDIR)/build/
else
	$(error The php submodule was not found)
endif

install: installdirs
	cp -rv $(CURDIR)/build/* $(DESTDIR)/
	mv $(DESTDIR)/html/* $(DESTDIR)/
	mv $(DESTDIR)/js/* $(DESTDIR)/
	mv $(DESTDIR)/css/* $(DESTDIR)/
	rm -rf $(DESTDIR)/html
	rm -rf $(DESTDIR)/css
	rm -rf $(DESTDIR)/js
	rm -rf $(DESTDIR)/tmp
	mv $(DESTDIR)/uguu.css $(DESTDIR)/uguu.min.css
	mv $(DESTDIR)/app.js $(DESTDIR)/uguu.min.js
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


build-image:
		docker build -f docker/Dockerfile --build-arg VERSION=$(UGUU_RELEASE_VER) --no-cache -t $(DOCKER_IMAGE):$(DOCKER_TAG) .

run-container:
		 docker run --name $(CONTAINER_NAME) -d -p 8080:80 -p 8081:443 --env-file docker/.env $(DOCKER_IMAGE):$(DOCKER_TAG)

purge-container:
	if docker images | grep $(DOCKER_IMAGE); then \
	 	docker rm -f $(CONTAINER_NAME) && docker rmi $(DOCKER_IMAGE):$(DOCKER_TAG) || true;\
	fi;		

builddirs:
	mkdir -p $(CURDIR)/build $(CURDIR)/build/img $(CURDIR)/build/tmp $(CURDIR)/build/tmp/html
ifneq (,$(findstring php,$(MODULES)))
	mkdir -p $(CURDIR)/build/classes $(CURDIR)/build/includes
endif
ifneq (,$(findstring moe,$(MODULES)))
	mkdir -p $(CURDIR)/build/moe/{css,fonts,includes,js,login,panel/css/font,panel/css/images,register,templates}
endif

submodules:
	$(info The following modules will be enabled: $(MODULES))
ifneq (,$(findstring php,$(MODULES)))
	$(MAKE) copy-php
endif
ifneq (,$(findstring moe,$(MODULES)))
	$(MAKE) copy-moe
endif

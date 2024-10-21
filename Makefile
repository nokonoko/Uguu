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
DESTDIR = $(shell $(CURDIR)/$(NODEJQ) -r '.dest // "" | select(. != "") // "dist"' $(CURDIR)/$(CONF))
ifeq ($(DESTDIR),)
DESTDIR := "dist"
endif
SITEDOMAIN = $(shell $(CURDIR)/$(NODEJQ) -r ".DOMAIN" $(CURDIR)/$(CONF))
FILESDOMAIN = $(shell $(CURDIR)/$(NODEJQ) -r ".FILE_DOMAIN" $(CURDIR)/$(CONF))
MAXSIZE = $(shell $(CURDIR)/$(NODEJQ) -r ".max_upload_size" $(CURDIR)/$(CONF))
CONTACT_EMAIL = $(shell $(CURDIR)/$(NODEJQ) -r ".infoContact" $(CURDIR)/$(CONF))
PKG_VERSION = $(shell $(CURDIR)/$(NODEJQ) -r ".version" $(CURDIR)/package.json)
EXPIRE_TIME = $(shell $(CURDIR)/$(NODEJQ) -r ".expireTime" $(CURDIR)/$(CONF))
TMPDIR = $(shell mktemp -d)
DOCKER_IMAGE = "$(shell basename $(CURDIR) | tr [:upper:] [:lower:])"
DOCKER_TAG = "$(DOCKER_TAG)"
CONTAINER_NAME = "$(CONTAINER_NAME)"
pageList = $(shell $(CURDIR)/$(NODEJQ) -r ".pages[]" $(CURDIR)/$(CONF))
noExt = $(shell echo $(i) | cut -d '.' -f1)

all: builddirs npm_dependencies ejs minify copy-img copy-php copy-benchmarks

ejs:
	$(foreach i,$(pageList), \
	"node_modules/ejs/bin/cli.js" -f $(CURDIR)/$(CONF) $(CURDIR)/src/templates/$(i) -o $(CURDIR)/build/html/unmin/$(noExt).html;)
	sed -i '/uguu.min.js/d' $(CURDIR)/build/html/unmin/faq.html
	sed -i '/uguu.min.js/d' $(CURDIR)/build/html/unmin/api.html

minify:
	"node_modules/@node-minify/cli/dist/cli.mjs" --compressor uglify-es --input $(CURDIR)/src/static/js/uguu.js --output $(CURDIR)/build/js/uguu.min.js
	"node_modules/@node-minify/cli/dist/cli.mjs" --compressor cssnano --input $(CURDIR)/src/static/css/uguu.css --output $(CURDIR)/build/css/uguu.min.css
	"node_modules/@node-minify/cli/dist/cli.mjs" --compressor html-minifier --input $(CURDIR)/build/html/unmin/faq.html --output $(CURDIR)/build/html/min/faq.html
	"node_modules/@node-minify/cli/dist/cli.mjs" --compressor html-minifier --input $(CURDIR)/build/html/unmin/api.html --output $(CURDIR)/build/html/min/api.html
	"node_modules/@node-minify/cli/dist/cli.mjs" --compressor html-minifier --input $(CURDIR)/build/html/unmin/index.html --output $(CURDIR)/build/html/min/index.html

installdirs:
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img
	mkdir -p $(DESTDIR)/ $(DESTDIR)/img/grills

copy-img:
	mkdir -p $(CURDIR)/build/img/grills
	mkdir -p $(CURDIR)/build/img
	cp -v $(CURDIR)/src/static/img/*.avif $(CURDIR)/build/img/
	cp -v $(CURDIR)/src/static/img/grills/*.avif $(CURDIR)/build/img/grills/
	"node_modules/imagemin-cli/cli.js" $(CURDIR)/src/static/img/*.png -o=$(CURDIR)/build/img/
	"node_modules/imagemin-cli/cli.js" $(CURDIR)/src/static/img/grills/*.png --plugin=pngquant -o=$(CURDIR)/build/img/grills/


copy-php:
	cp -v $(CURDIR)/src/static/php/*.php $(CURDIR)/build/php/
	cp -v $(CURDIR)/src/Classes/*.php $(CURDIR)/build/php/Classes/

copy-benchmarks:
	cp -v $(CURDIR)/src/Benchmarks/*.php $(CURDIR)/build/php/Benchmarks/
	cp -v $(CURDIR)/src/Benchmarks/file.jpg $(CURDIR)/build/php/Benchmarks/
	cp -v $(CURDIR)/src/Benchmarks/runBenchmark.sh $(CURDIR)/build/php/Benchmarks/

install: installdirs
	rm -rf $(DESTDIR)/*
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
	mv $(DESTDIR)/upload.php $(DESTDIR)/public/
	cd $(DESTDIR)/ && $(CURL) -o composer-setup.php https://raw.githubusercontent.com/composer/getcomposer.org/main/web/installer
	cd $(DESTDIR)/ && $(PHP) composer-setup.php --quiet
	cd $(DESTDIR)/ && rm composer-setup.php
	cd $(DESTDIR)/ && php composer.phar update && php composer.phar install && php composer.phar dump-autoload
	bash ./compress.sh "$(DESTDIR)/public/"

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
		tar --exclude='uguuForDocker.tar.gz' --exclude='vendor' --exclude='node_modules' --exclude='build' --exclude='dist' --exclude='.git' -czf uguuForDocker.tar.gz src docker Makefile package.json package-lock.json
		mv uguuForDocker.tar.gz docker/
		docker build -f docker/Dockerfile --build-arg DOMAIN=$(SITEDOMAIN) --build-arg FILE_DOMAIN=$(FILESDOMAIN) --build-arg CONTACT_EMAIL=$(CONTACT_EMAIL) --build-arg MAX_SIZE=$(MAXSIZE) --build-arg EXPIRE_TIME=$(EXPIRE_TIME) --no-cache -t uguu:$(PKG_VERSION) .

build-container:
		tar --exclude='uguuForDocker.tar.gz' --exclude='vendor' --exclude='node_modules' --exclude='build' --exclude='dist' --exclude='.git' -czf uguuForDocker.tar.gz src docker Makefile package.json package-lock.json
		mv uguuForDocker.tar.gz docker/
		docker build -f docker/Dockerfile --build-arg DOMAIN=$(SITEDOMAIN) --build-arg FILE_DOMAIN=$(FILESDOMAIN) --build-arg CONTACT_EMAIL=$(CONTACT_EMAIL) --build-arg MAX_SIZE=$(MAXSIZE) --build-arg EXPIRE_TIME=$(EXPIRE_TIME) -t uguu:$(PKG_VERSION) .

run-container:
		docker run --name uguu -d -p 80:80 -p 443:443 uguu:$(PKG_VERSION)

purge-containers:
	if docker images | grep uguu; then \
	 	docker rm -f uguu && docker rmi uguu:$(PKG_VERSION) || true;\
	fi;		

remove-container:
	docker rm -f uguu

builddirs:
	mkdir -p $(CURDIR)/build $(CURDIR)/build/img $(CURDIR)/build/html $(CURDIR)/build/html/min $(CURDIR)/build/html/unmin $(CURDIR)/build/js $(CURDIR)/build/css $(CURDIR)/build/php $(CURDIR)/build/php/Classes $(CURDIR)/build/php/Benchmarks $(CURDIR)/build/php/Benchmarks/tmp  $(CURDIR)/build/public


DOCKER_COMPOSE = docker compose -p tld
API = $(DOCKER_COMPOSE) exec php-api
GSE = $(DOCKER_COMPOSE) exec -e HOST_PATH=$(shell pwd) gse
EVENDORS = $(DOCKER_COMPOSE) exec evendors-php
EXTRANET = $(DOCKER_COMPOSE) exec extranet-php
POWERBI = $(DOCKER_COMPOSE) exec powerbi-js
MOBILE = $(DOCKER_COMPOSE) exec mobile-react
CYPRESS = $(DOCKER_COMPOSE) exec mobile-cypress
ANSIBLE = $(DOCKER_COMPOSE) run provisioning
API_PACKAGES = $(DOCKER_COMPOSE) exec twig-helper-test-api
INVENTORY ?= staging
PACKAGE_TRANSLATOR = packages/translator

.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

init-filesystem: ## create necessary files/folder for installation
	[ -d ~/.composer ] || mkdir ~/.composer
	[ -f shared/inc/config.credentials.inc.php ] || cp shared/inc/config.credentials-sample.inc.php shared/inc/config.credentials.inc.php
	cd api && ./bin/install-keys.sh
	cd intranet && ./bin/install-keys.sh

init-tls: ## install mkcert and generate certificates for local https
	@brew install mkcert
	@brew install nss
	@mkcert -install
	@mkcert --cert-file ./docker/certs/localhost.crt --key-file ./docker/certs/localhost.key localhost haproxy 127.0.0.1 ::1
	@cat ./docker/certs/localhost.key ./docker/certs/localhost.crt > ./docker/certs/server.pem
	@cp "$$(mkcert -CAROOT)/rootCA.pem" ./docker/certs/localCA.crt

install: init-filesystem init-tls build start yarn-install webpack-deploy composer-install ckeditor-install yarn-install-mobile yarn-build-mobile ## setup a new dev environment from scratch

start:
	$(DOCKER_COMPOSE) up --remove-orphans -d --scale redis-slave=2 --scale redis-sentinel=3 --scale gse=1
	@touch intranet/.env.local
	@gsed -i -E '/^TRUSTED_PROXIES=.*/d' intranet/.env.local
	@printf "TRUSTED_PROXIES="  >> intranet/.env.local
	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' tld-haproxy-1 >> intranet/.env.local
	@touch api/.env.local
	@gsed -i -E '/^TRUSTED_PROXIES=.*/d' api/.env.local
	@printf "TRUSTED_PROXIES="  >> api/.env.local
	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' tld-haproxy-1 >> api/.env.local
	@touch extranet-new/.env.local
	@gsed -i -E '/^TRUSTED_PROXIES=.*/d' extranet-new/.env.local
	@printf "TRUSTED_PROXIES="  >> extranet-new/.env.local
	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' tld-haproxy-1 >> extranet-new/.env.local

stop: ## stop the containers and remove volumes
	@$(DOCKER_COMPOSE) down --remove-orphans -v

kill: ## kill the containers
	@$(DOCKER_COMPOSE) kill || true

build: ## build the containers
	$(DOCKER_COMPOSE) build --build-arg UID=$(shell id -u) --build-arg GID=$(shell id -g)

rm: ## remove containers
	@$(DOCKER_COMPOSE) rm --force || true

pull: ## pull latest images
	@$(DOCKER_COMPOSE) pull

haproxy-reload: ## reload haproxy
	docker kill -s HUP tld-haproxy-1

composer-install: ## install composer dependencies in all repositories
	$(API) composer install -n --prefer-dist
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/intranet
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/shared/inc
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/admin
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/shopfloor
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/dms
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/packages/twig_helper
	$(GSE) composer install -n --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/packages/feature-doc
	@$(EVENDORS) sh -c "COMPOSER=composer.dev.json composer install -n --prefer-dist --no-interaction --working-dir=/srv/$(PACKAGE_TRANSLATOR)"
	$(EVENDORS) composer install -n --prefer-dist --no-interaction
	$(EXTRANET) composer install -n --prefer-dist --no-interaction

ckeditor-install: #Install the CKEditor library
	@$(GSE) /srv/alvest-web-portals/intranet/bin/console ckeditor:install --clear=drop
	@$(GSE) /srv/alvest-web-portals/intranet/bin/console assets:install

composer-sync: ## sync flex recipes
	@$(API) composer sync-recipes --force -v
	@$(GSE) composer sync-recipes --force -v --working-dir=/srv/alvest-web-portals/intranet

composer-update: ## update composer dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals --with-all-dependencies
#	@$(API) composer update --prefer-dist --no-interaction --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/intranet --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/admin --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/shared/inc --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/extranet --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/shopfloor --with-all-dependencies
#	@$(GSE) composer update --prefer-dist --no-interaction --working-dir=/srv/alvest-web-portals/dms --with-all-dependencies
#	@$(EVENDORS) composer update --prefer-dist --no-interaction --with-all-dependencies

check-outdated: ## check outdated dependencies
	@echo 'api - PHP' && echo '========================='
	@$(API) composer outdated
	@echo 'api.tld-group.com - Flex recipes' && echo '========================='
	@$(API) composer recipes
	@echo '' && echo 'intranet - PHP' && echo '========================='
	@$(GSE) composer outdated --working-dir=/srv/alvest-web-portals/intranet
	@echo '' && echo 'intranet - Flex recipes' && echo '========================='
	@$(GSE) composer recipes --working-dir=/srv/alvest-web-portals/intranet
	@echo '' && echo 'intranet - JS' && echo '========================='
	@$(GSE) sh -c "cd alvest-web-portals/current/intranet && yarn outdated"
	@echo '' && echo 'shared_php_inc - PHP' && echo '========================='
	@$(GSE) composer outdated --working-dir=/srv/alvest-web-portals/shared/inc
	@echo '' && echo 'shopfloor - PHP' && echo '========================='
	@$(GSE) composer outdated --working-dir=/srv/alvest-web-portals/shopfloor
	@echo '' && echo 'shopfloor - JS' && echo '========================='
	@$(GSE) sh -c "cd alvest-web-portals/current/shopfloor && yarn outdated"
	@echo '' && echo 'evendors - PHP' && echo '========================='
	@$(EVENDORS) composer outdated
	@echo '' && echo 'evendors - Flex recipes' && echo '========================='
	@$(EVENDORS) composer recipes
	@echo '' && echo 'evendors - Yarn' && echo '========================='
	@$(EVENDORS) yarn outdated
	@echo '' && echo 'extranet - PHP' && echo '========================='
	@$(EXTRANET) composer outdated
	@echo '' && echo 'extranet - Flex recipes' && echo '========================='
	@$(EXTRANET) composer recipes
	@echo '' && echo 'admin - PHP' && echo '========================='
	@$(GSE) composer outdated --working-dir=/srv/alvest-web-portals/admin
	@echo '' && echo 'docker - PHP' && echo '========================='
	@$(GSE) composer outdated --working-dir=/srv/alvest-web-portals

check-security: ## check with symfony check:security tool
	@echo 'api - PHP' && echo '========================='
	@$(API) symfony check:security || true
	@echo '' && echo 'intranet - PHP' && echo '========================='
	@$(GSE) symfony check:security --dir=/srv/alvest-web-portals/intranet || true
	@echo '' && echo 'shared_php_inc - PHP' && echo '========================='
	@$(GSE) symfony check:security --dir=/srv/alvest-web-portals/shared/inc || true
	@echo '' && echo 'shopfloor - PHP' && echo '========================='
	@$(GSE) symfony check:security --dir=/srv/alvest-web-portals/shopfloor || true
	@echo '' && echo 'evendors - PHP' && echo '========================='
	@$(EVENDORS) symfony check:security || true
	@echo '' && echo 'extranet - PHP' && echo '========================='
	@$(EXTRANET) symfony check:security || true
	@echo '' && echo 'admin - PHP' && echo '========================='
	@$(GSE) symfony check:security --dir=/srv/alvest-web-portals/admin || true
	@echo '' && echo 'docker - PHP' && echo '========================='
	@$(GSE) symfony check:security --dir=/srv/alvest-web-portals || true

db-init: ## import databases. tld.sql, tld_api.sql and activity sql files should be in the docker/import folder
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql  -uroot -h mariadb -e 'DROP DATABASE IF EXISTS tld; CREATE DATABASE tld;'"
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql  -uroot -h mariadb tld< /srv/import/tld.sql"
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql  -uroot -h mariadb -e 'DROP DATABASE IF EXISTS api; CREATE DATABASE api;'"
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql  -uroot -h mariadb api< /srv/import/tld_api.sql"
	# Import activity chunks
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql -uroot -h mariadb api < /srv/import/tld_api_activity_part1.sql"
	$(DOCKER_COMPOSE) exec mariadb sh -c "mysql -uroot -h mariadb api < /srv/import/tld_api_activity_part2.sql"

test: test-lint-yaml test-rector test-phpcs test-phpstan test-behat ## run ALL tests

test-lint-yaml: ## check yaml config in the API
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:yaml /srv/alvest-web-portals/intranet/config --parse-tags
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:yaml @AppBundle --parse-tags
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:yaml @ApiBundle --parse-tags
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:yaml @ActivityBundle --parse-tags
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:yaml @LegacyBundle --parse-tags
	$(GSE) /srv/alvest-web-portals/intranet/bin/console lint:twig /srv/alvest-web-portals/intranet/templates --show-deprecations
	$(API) bin/console lint:yaml ./config --parse-tags
	$(API) bin/console lint:yaml @LegacyBundle --parse-tags
	$(API) bin/console lint:twig ./templates --show-deprecations
	$(EVENDORS) php bin/console lint:twig ./templates --show-deprecations

test-phpcs: ## enforce code style rules in API, intranet, shopfloor and admin repositories
	$(API) composer cs
	$(GSE) composer cs --working-dir=/srv/alvest-web-portals/intranet
	$(GSE) composer cs --working-dir=/srv/alvest-web-portals/shopfloor
	$(GSE) composer cs --working-dir=/srv/alvest-web-portals/dms
	$(GSE) composer cs --working-dir=/srv/alvest-web-portals/admin
	$(GSE) bash -c "cd /srv/alvest-web-portals/packages/twig_helper ; /srv/alvest-web-portals/intranet/vendor/bin/php-cs-fixer fix --no-interaction $(FILTERS)"
	$(GSE) bash -c "cd /srv/alvest-web-portals/packages/feature-doc ; composer cs"
	$(EXTRANET) composer cs
	$(EVENDORS) composer cs
	$(EVENDORS) bash -c "cd /srv/$(PACKAGE_TRANSLATOR) ; /srv/evendors/vendor/bin/php-cs-fixer fix --no-interaction $(FILTERS)"

test-phpstan: ## start phpstan static analysis in API, intranet and shopfloor
	@$(API) vendor/bin/phpstan analyse || true
	@$(GSE) /srv/alvest-web-portals/intranet/vendor/bin/phpstan analyse -c /srv/alvest-web-portals/intranet/phpstan.neon || true
	@$(GSE) /srv/alvest-web-portals/shopfloor/vendor/phpstan/phpstan/phpstan analyse -c /srv/alvest-web-portals/shopfloor/phpstan.neon || true
	@$(GSE) /srv/alvest-web-portals/dms/vendor/phpstan/phpstan/phpstan analyse -c /srv/alvest-web-portals/dms/phpstan.neon || true
	@$(GSE) bash -c "cd /srv/alvest-web-portals/packages/twig_helper ; /srv/alvest-web-portals/packages/twig_helper/vendor/bin/phpstan analyse" || true
	@$(EVENDORS) bash -c "cd /srv/$(PACKAGE_TRANSLATOR) ; /srv/evendors/vendor/bin/phpstan analyse" || true
	@$(EVENDORS) vendor/bin/phpstan analyse || true
	@$(EXTRANET) vendor/bin/phpstan analyse || true

test-phpstan-override-baseline: ## refresh shopfloor phpstan baseline file to accept/remove some errors
	@$(GSE) bash -c "rm /srv/alvest-web-portals/shopfloor/phpstan-baseline.neon"
	@$(GSE) bash -c "touch /srv/alvest-web-portals/shopfloor/output.neon /srv/alvest-web-portals/shopfloor/phpstan-baseline.neon"
	@$(GSE) bash -c "chmod o+w /srv/alvest-web-portals/shopfloor/phpstan-baseline.neon /srv/alvest-web-portals/shopfloor/output.neon"
	@$(GSE) /srv/alvest-web-portals/shopfloor/vendor/phpstan/phpstan/phpstan analyse -c /srv/alvest-web-portals/shopfloor/phpstan.neon --generate-baseline /srv/alvest-web-portals/shopfloor/output.neon
	@$(GSE) bash -c "mv  /srv/alvest-web-portals/shopfloor/output.neon /srv/alvest-web-portals/shopfloor/phpstan-baseline.neon"

	@$(GSE) bash -c "rm /srv/alvest-web-portals/dms/phpstan-baseline.neon"
	@$(GSE) bash -c "touch /srv/alvest-web-portals/dms/output.neon /srv/alvest-web-portals/dms/phpstan-baseline.neon"
	@$(GSE) bash -c "chmod o+w /srv/alvest-web-portals/dms/phpstan-baseline.neon /srv/alvest-web-portals/dms/output.neon"
	@$(GSE) /srv/alvest-web-portals/dms/vendor/phpstan/phpstan/phpstan analyse -c /srv/alvest-web-portals/dms/phpstan.neon --generate-baseline /srv/alvest-web-portals/dms/output.neon
	@$(GSE) bash -c "mv  /srv/alvest-web-portals/dms/output.neon /srv/alvest-web-portals/dms/phpstan-baseline.neon"

test-rector: test-rector-api test-rector-gse
test-rector-api: ## start rector static analysis on the API
	@$(API) vendor/bin/rector process

test-rector-gse: ## start rector static analysis on the intranet
	@$(GSE) /srv/alvest-web-portals/intranet/vendor/bin/rector process -c /srv/alvest-web-portals/intranet/rector.php /srv/alvest-web-portals/intranet/src

test-dump: ## ensure no dump functions are left in the code of the API, intranet, shared_php_inc and extranet repositories
	@$(API) vendor/bin/var-dump-check --symfony --exclude ./vendor .
	@$(GSE) /srv/alvest-web-portals/intranet/vendor/bin/var-dump-check --symfony --exclude /srv/alvest-web-portals/intranet/vendor /srv/alvest-web-portals/intranet
	@$(GSE) /srv/alvest-web-portals/shared/inc/bin/var-dump-check --symfony --exclude /srv/alvest-web-portals/shared/inc/vendor /srv/alvest-web-portals/shared/inc

test-init-legacy: ## initialize legacy database for tests
	@$(DOCKER_COMPOSE) exec -e DB_DISCRIMINATOR=_bkp php-api bin/console doctrine:database:create --if-not-exists --connection=legacy --env test
	@$(DOCKER_COMPOSE) exec php-api sh -c "cat tests/resources/sql/legacy.sql | mysql -h mariadb -uroot -D tld_test_bkp --skip-ssl"

test-init: ## initialize test database with fixtures
	@$(DOCKER_COMPOSE) exec -e DB_DISCRIMINATOR=_bkp php-api bin/console doctrine:database:create --if-not-exists --env test
	@$(DOCKER_COMPOSE) exec -e DB_DISCRIMINATOR=_bkp php-api bin/console doctrine:schema:drop --full-database --force --env test
	@$(DOCKER_COMPOSE) exec -e DB_DISCRIMINATOR=_bkp php-api bin/console doctrine:schema:update -f --env test
	@$(DOCKER_COMPOSE) exec -e DB_DISCRIMINATOR=_bkp php-api bin/console hautelook:fixtures:load --no-bundles -n --env test --purge-with-truncate
	@$(DOCKER_COMPOSE) exec mariadb sh -c "mysql api_test_bkp -uroot -e \"UPDATE user SET password_updated_at='2016-01-25 16:57:56' WHERE username='user-expired@tld.fr';\""
	@$(API) bin/console doctrine:database:create --if-not-exists --env test
	@$(API) bin/console doctrine:database:create --if-not-exists --env test --connection=legacy
	@$(DOCKER_COMPOSE) exec mariadb sh -c "mysqldump -uroot api_test_bkp | mysql -uroot api_test"
	@$(DOCKER_COMPOSE) exec mariadb sh -c "mysqldump -uroot tld_test_bkp | mysql -uroot tld_test"

test-refresh: ## restore last test-reset database
	@$(API) bin/console doctrine:database:create --if-not-exists --env test
	@$(API) bin/console doctrine:database:create --if-not-exists --env test --connection=legacy
	@$(DOCKER_COMPOSE) exec mariadb sh -c "mysqldump -uroot api_test_bkp | mysql -uroot api_test"
	@$(DOCKER_COMPOSE) exec mariadb sh -c "mysqldump -uroot tld_test_bkp | mysql -uroot tld_test"

test-reset: cache-clear test-init-legacy test-init ## clear cache, initialize legacy database, initialize test database

test-behat: test-behat-api test-behat-gse ## launch ALL behat tests (API and intranet)

test-behat-api: ## launch API behat tests
	make test-init-legacy test-init
	make test-refresh
	make test-behat-api-1
	make test-refresh
	make test-behat-api-2
	make test-refresh
	make test-behat-api-3
	make test-refresh
	make test-behat-api-4
	make test-refresh
	make test-behat-api-5
	make test-refresh
	make test-behat-api-6
	make test-refresh
	make test-behat-api-baan
	make test-refresh
	make test-behat-api-legacy

test-behat-api-1: ## launch API behat tests suite 1
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' platform ' && vendor/bin/behat -f progress --strict --suite=platform || error=true \
		&& echo ' api ' && vendor/bin/behat -f progress --strict --suite=api || error=true \
		&& echo ' user ' && vendor/bin/behat -f progress --strict --suite=user || error=true \
		&& echo ' acl ' && vendor/bin/behat -f progress --strict --suite=acl || error=true \
		&& echo ' people ' && vendor/bin/behat -f progress --strict --suite=people || error=true \
		&& echo ' directory ' && vendor/bin/behat -f progress --strict --suite=directory || error=true \
		&& echo ' mis-project ' && vendor/bin/behat -f progress --strict --suite=mis-project || error=true \
		&& echo ' transfer ' && vendor/bin/behat -f progress --strict --suite=transfer || error=true \
		&& echo ' spq ' && vendor/bin/behat -f progress --strict --suite=spq || error=true \
		&& if [ $"$$error$" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg

test-behat-api-2: ## launch API behat tests suite 2
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' sales ' && vendor/bin/behat -f progress --strict --suite=sales || error=true \
		&& echo ' calibrated-tools ' && vendor/bin/behat -f progress --strict --suite=calibrated-tools || error=true \
		&& echo ' changelog ' && vendor/bin/behat -f progress --strict --suite=changelog || error=true \
		&& echo ' cleanliness ' && vendor/bin/behat -f progress --strict --suite=cleanliness || error=true \
		&& echo ' survey ' && vendor/bin/behat -f progress --strict --suite=survey || error=true \
		&& echo ' activity ' && vendor/bin/behat -f progress --strict --suite=activity || error=true \
		&& echo ' module ' && vendor/bin/behat -f progress --strict --suite=module || error=true \
		&& echo ' news ' && vendor/bin/behat -f progress --strict --suite=news || error=true \
		&& echo ' agr ' && vendor/bin/behat -f progress --strict --suite=agr || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg

test-behat-api-3: ## launch API behat tests suite 3
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' er ' && vendor/bin/behat -f progress --strict --suite=er || error=true \
		&& echo ' manual ' && vendor/bin/behat --suite=manual -f progress --strict || error=true \
		&& echo ' iata ' && vendor/bin/behat -f progress --strict --suite=iata || error=true \
		&& echo ' fur ' && vendor/bin/behat -f progress --strict --suite=fur || error=true \
		&& echo ' catalog ' && vendor/bin/behat -f progress --strict --suite=catalog || error=true \
		&& echo ' emission-rating ' && vendor/bin/behat -f progress --strict --suite=emission-rating || error=true \
		&& echo ' competitor ' && vendor/bin/behat -f progress --strict --suite=competitor || error=true \
		&& echo ' dms ' && vendor/bin/behat -f progress --strict --suite=dms || error=true \
		&& echo ' sfr ' && vendor/bin/behat -f progress --strict --suite=sfr || error=true \
		&& echo ' mom ' && vendor/bin/behat -f progress --strict --suite=mom || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg

test-behat-api-4: ## launch API behat tests suite 4
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' sor ' && vendor/bin/behat -f progress --strict --suite=sor || error=true \
		&& echo ' demo ' && vendor/bin/behat -f progress --strict --suite=demo || error=true \
		&& echo ' finance ' && vendor/bin/behat -f progress --strict --suite=finance || error=true \
		&& echo ' sales-areas ' && vendor/bin/behat -f progress --strict --suite=sales-areas || error=true \
		&& echo ' faq ' && vendor/bin/behat -f progress --strict --suite=faq || error=true \
		&& echo ' currency ' && vendor/bin/behat -f progress --strict --suite=currency || error=true \
		&& echo ' transport ' && vendor/bin/behat -f progress --strict --suite=transport || error=true \
		&& echo ' mim ' && vendor/bin/behat -f progress --strict --suite=mim || error=true \
		&& echo ' subscription ' && vendor/bin/behat -f progress --strict --suite=subscription || error=true \
		&& echo ' product manufacturing ' && vendor/bin/behat -f progress --strict --suite=product-manufacturing || error=true \
		&& echo ' field ' && vendor/bin/behat -f progress --strict --suite=field || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg

test-behat-api-5: ## launch API behat tests suite 5
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' manufacturing margin ' && vendor/bin/behat -f progress --strict --suite=manufacturing-margin || error=true \
		&& echo ' ar ' && vendor/bin/behat --suite=ar -f progress --strict || error=true \
		&& echo ' qhse ' && vendor/bin/behat --suite=qhse -f progress --strict || error=true \
		&& echo ' warehouse ' && vendor/bin/behat --suite=warehouse -f progress --strict || error=true \
		&& echo ' spr ' && vendor/bin/behat --suite=spr -f progress --strict || error=true \
		&& echo ' authorized_application ' && vendor/bin/behat --suite=authorized_application -f progress --strict || error=true \
		&& echo ' print ' && vendor/bin/behat --suite=print -f progress --strict || error=true \
		&& echo ' printer ' && vendor/bin/behat --suite=printer -f progress --strict || error=true \
		&& echo ' vendor ' && vendor/bin/behat --suite=vendor -f progress --strict || error=true \
		&& echo ' csr ' && vendor/bin/behat -f progress --strict --suite=csr || error=true \
		&& echo ' pdi ' && vendor/bin/behat -f progress --strict --suite=pdi || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg tests/fixtures/file.xlsx

test-behat-api-6: ## launch API behat tests suite 6
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' scar ' && vendor/bin/behat -f progress --strict --suite=scar || error=true \
		&& echo ' ncr ' && vendor/bin/behat -f progress --strict --suite=ncr || error=true \
		&& echo ' vwc ' && vendor/bin/behat -f progress --strict --suite=vwc || error=true \
		&& echo ' materials ' && vendor/bin/behat -f progress --strict --suite=materials || error=true \
		&& echo ' esr ' && vendor/bin/behat -f progress --strict --suite=esr || error=true \
		&& echo ' odp ' && vendor/bin/behat -f progress --strict --suite=odp || error=true \
		&& echo ' crab ' && vendor/bin/behat -f progress --strict --suite=crab || error=true \
		&& echo ' sageparts ' && vendor/bin/behat -f progress --strict --suite=sageparts || error=true \
		&& echo ' supplier-ranking ' && vendor/bin/behat -f progress --strict --suite=supplier-ranking || error=true \
		&& echo ' notification ' && vendor/bin/behat -f progress --strict --suite=notification || error=true \
		&& echo ' mis ' && vendor/bin/behat -f progress --strict --suite=mis || error=true \
		&& echo ' vendor-user ' && vendor/bin/behat -f progress --strict --suite=vendor-user || error=true \
		&& echo ' toc ' && vendor/bin/behat -f progress --strict --suite=toc || error=true \
		&& echo ' service-areas ' && vendor/bin/behat -f progress --strict --suite=service-areas || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg tests/fixtures/file.xlsx

test-behat-api-7: ## launch API behat tests suite 7
	@$(API) sh -c "\
		vendor/bin/behat -f progress --strict --suite=file-cleanup || error=true \
		&& echo ' ai ' && vendor/bin/behat -f progress --strict --suite=ai || error=true \
		&& echo ' audit ' && vendor/bin/behat -f progress --strict --suite=audit || error=true \
		&& echo ' event ' && vendor/bin/behat -f progress --strict --suite=event || error=true \
		&& echo ' task ' && vendor/bin/behat -f progress --strict --suite=task || error=true \
		&& echo ' base-task ' && vendor/bin/behat -f progress --strict --suite=base-task || error=true \
		&& echo ' pictogram ' && vendor/bin/behat -f progress --strict --suite=pictogram || error=true \
		&& echo ' power-bi ' && vendor/bin/behat -f progress --strict --suite=power-bi || error=true \
		&& echo ' legal ' && vendor/bin/behat -f progress --strict --suite=legal || error=true \
		&& echo ' jira ' && vendor/bin/behat -f progress --strict --suite=jira || error=true \
		&& echo ' document-translation ' && vendor/bin/behat -f progress --strict --suite=document-translation || error=true \
		&& echo ' supplier ' && vendor/bin/behat -f progress --strict --suite=supplier || error=true \
		&& echo ' guest-user ' && vendor/bin/behat -f progress --strict --suite=guest-user || error=true \
		&& if [ "$$error" ]; then exit 1; fi; "
	cd api && git checkout tests/fixtures/image_1200x1200.jpg tests/fixtures/file.xlsx

test-behat-api-ion: ## launch API behat ION tests
	@$(API) php -d memory_limit=800M vendor/bin/behat --suite=ion -f progress --strict

test-behat-api-suite-%: ## launch API behat tests for a given suite
	$(API) php -d memory_limit=4000M vendor/bin/behat --suite=$* -vvv --strict --stop-on-failure
	$(API) php -d memory_limit=800M vendor/bin/behat --suite=file-cleanup -f progress
	cd api && git checkout tests/fixtures/image_1200x1200.jpg tests/fixtures/file.xlsx

test-behat-api-tag-%: ## launch API behat tests for a given suite
	$(API) php -d memory_limit=4000M vendor/bin/behat --tags=@$* -vvv --strict --stop-on-failure
	cd api && git checkout tests/fixtures/image_1200x1200.jpg tests/fixtures/file.xlsx

test-behat-api-baan: ## launch API behat baan tests
	@$(API) php -d memory_limit=800M vendor/bin/behat --suite=baan -f progress --strict

test-behat-api-legacy: ## launch API behat legacy tests
	@$(API) php -d memory_limit=5000M vendor/bin/behat --suite=legacy -f progress

test-phpunit-api: ## launch API phpunit tests
	@$(API)  php -d memory_limit=1500M vendor/bin/phpunit $(ARGS)

test-behat-gse: switch-to-test webpack-deploy-gse test-reset selenium-start
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && bin/console cache:warmup"
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && vendor/bin/behat -f progress -vvv" || true
	make selenium-stop switch-to-dev

test-behat-gse-suite-%: ## launch intranet behat tests with suite
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && bin/console cache:warmup"
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && vendor/bin/behat -f progress --suite=$* --tags=~@javascript -vvv" || true

test-behat-gse-tag-%: switch-to-test webpack-deploy-gse selenium-start ## launch intranet behat tests with tags
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && bin/console cache:warmup"
	@$(GSE) bash -c "cd /srv/alvest-web-portals/intranet && vendor/bin/behat --suite=default --tags=@$* -vvv" || true
	make selenium-stop switch-to-dev

test-phpunit-gse: ## launch intranet phpunit tests
	@$(GSE) php /srv/alvest-web-portals/intranet/vendor/bin/phpunit --color -c /srv/alvest-web-portals/intranet/phpunit.xml.dist

test-phpunit-dms: ## launch intranet phpunit tests
	@$(GSE) php /srv/alvest-web-portals/dms/vendor/bin/phpunit -c /srv/alvest-web-portals/dms/phpunit.xml.dist

test-phpunit-admin: ## launch admin phpunit tests
	@$(GSE) php /srv/alvest-web-portals/admin/vendor/bin/phpunit -c /srv/alvest-web-portals/admin/phpunit.xml.dist

test-phpunit-evendors-unit: ## launch evendors phpunit unit tests
	@$(eval filter ?= '.')
	@$(EVENDORS) php vendor/bin/phpunit --group unit --filter=$(filter)

test-phpunit-evendors-functional: ## launch evendors phpunit functional tests
	@$(eval filter ?= '.')
	@$(EVENDORS) php vendor/bin/phpunit --group functional --filter=$(filter)

test-phpunit-evendors-e2e: test-refresh switch-to-test ## launch evendors phpunit e2e tests
	@$(eval filter ?= '.')
	@$(EVENDORS) php vendor/bin/phpunit --group e2e --filter=$(filter)
	make switch-to-dev

test-phpunit-extranet-e2e: switch-to-test ## launch extranet phpunit e2e tests
	@$(eval filter ?= '.')
	@$(EXTRANET) php vendor/bin/phpunit --group e2e --filter=$(filter)
	make switch-to-dev

test-phpunit-extranet-unit: ## launch extranet phpunit unit tests
	@$(eval filter ?= '.')
	@$(EXTRANET) vendor/bin/phpunit --group unit --filter=$(filter)

test-phpunit-twig-helper: ## launch twig-helper phpunit tests
	@$(eval filter ?= '.')
	@$(EVENDORS) sh -c "COMPOSER=composer-evendors.json composer install -n --prefer-dist --no-interaction --quiet --working-dir=/srv/packages/twig_helper"
	@$(EVENDORS) ../packages/twig_helper/vendor/bin/phpunit -c ../packages/twig_helper/phpunit.xml.dist --filter=$(filter) || true
	@$(GSE) sh -c "COMPOSER=composer-gse.json composer install -n --prefer-dist --no-interaction --quiet --working-dir=/srv/alvest-web-portals/packages/twig_helper"
	@$(GSE) /srv/alvest-web-portals/packages/twig_helper/vendor/bin/phpunit -c /srv/alvest-web-portals/packages/twig_helper/phpunit.xml.dist --filter=$(filter) || true
	@$(DOCKER_COMPOSE) up -d twig-helper-test-api
	@$(API_PACKAGES) sh -c "COMPOSER=composer-api.json composer install -n --prefer-dist --no-interaction --quiet --working-dir=/srv/packages/twig_helper"
	@$(API_PACKAGES) packages/twig_helper/vendor/bin/phpunit -c packages/twig_exithelper/phpunit.xml.dist --filter=$(filter) || true
	@$(DOCKER_COMPOSE) stop twig-helper-test-api

composer-update-twig-helper:
	@$(eval filter ?= '.')
	@$(EVENDORS) sh -c "COMPOSER=composer-evendors.json composer update -n --prefer-dist --no-interaction --quiet --working-dir=/srv/packages/twig_helper"
	@$(GSE) sh -c "COMPOSER=composer-gse.json composer update -n --prefer-dist --no-interaction --quiet --working-dir=/srv/alvest-web-portals/packages/twig_helper"
	@$(DOCKER_COMPOSE) up -d twig-helper-test-api
	@$(API_PACKAGES) sh -c "COMPOSER=composer-api.json composer update -n --prefer-dist --no-interaction --quiet --working-dir=/srv/packages/twig_helper"
	@$(DOCKER_COMPOSE) stop twig-helper-test-api

test-phpunit-evendors: test-phpunit-evendors-unit test-phpunit-evendors-functional test-phpunit-evendors-e2e ## launch evendors phpunit tests

test-mocha-gse: ## launch intranet mocha tests
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run tests"

test-mocha-gse-watch: ## launch intranet mocha tests in watch mode
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run watch"

test-javascript-unit-gse: ## Unit tests on javascript stimulus controllers
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn jest"

yarn-upgrade: ## upgrade yarn dependencies
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet/ && yarn upgrade-interactive --latest"
#	@$(GSE) sh -c "cd /srv/alvest-web-portals/shopfloor/ && yarn upgrade-interactive --latest"
#	@$(EVENDORS) sh -c "yarn upgrade-interactive --latest"
#	@$(API) sh -c "yarn upgrade-interactive --latest"

yarn-install: ## install yarn dependencies (API, intranet, shopfloor)
	make yarn-install-api || true
	make yarn-install-gse || true
	make yarn-install-shop || true

yarn-install-api: ## install API yarn dependencies
	@$(API) yarn install --frozen-lockfile

yarn-install-evendors: ## install evendors yarn dependencies
	@$(EVENDORS) yarn install --frozen-lockfile

npm-install-powerbi:
	@$(POWERBI) npm install

npm-start-powerbi:
	@$(POWERBI) npm start

yarn-install-gse: ## install intranet yarn dependencies
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet/ && yarn install --frozen-lockfile"

yarn-install-shop: ## install shopfloor yarn dependencies
	@$(GSE) sh -c "cd /srv/alvest-web-portals/shopfloor/ && yarn install --frozen-lockfile"

yarn-install-mobile: ## install mobile yarn dependencies
	@$(MOBILE) yarn install --frozen-lockfile

yarn-lint-gse: ## logs all the linter errors on console
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run lint:baseline:check"

yarn-lint-fix-gse: ## fixes auto fixable errors and logs rest of the errors
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run lint:fix > /dev/null 2>&1 || yarn run lint:baseline:check"

yarn-baseline-override-gse: ## fixes auto fixable errors and refresh intranet react baseline file to accept/remove some errors
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run lint:fix > /dev/null 2>&1 || yarn run lint:baseline:override"

yarn-format-gse: ## formats all the files in project
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn run format"

webpack-deploy-gse: ## build intranet assets with webpack
	@$(GSE) sh -c "cd /srv/alvest-web-portals/intranet && yarn encore dev"

webpack-deploy-shop: ## build shopfloor assets with webpack
	@$(GSE) sh -c "cd /srv/alvest-web-portals/shopfloor && yarn run build"

webpack-deploy-api: ## build API assets with webpack
	@$(API) yarn encore dev

yarn-build-mobile: ## build mobile assets with yarn
	@$(MOBILE) yarn build

yarn-start-mobile: ## compile assets and automatically re-compile when files change
	@$(MOBILE) yarn start

yarn-lint-mobile: ## logs all the linter errors on console
	@$(MOBILE) yarn lint

yarn-lint-fix-mobile: ## fixes auto fixable errors and logs rest of the errors
	@$(MOBILE) yarn lint:fix

yarn-format-mobile: ## formats all the files in project
	@$(MOBILE) yarn format

yarn-serve-mobile: ## builds production build and serves on local to enable PWA features
	@$(MOBILE) yarn serve

yarn-cypress-mobile: ## runs cypress e2e tests
	@$(CYPRESS) sh -c "npx cypress install && yarn cypress:parallel" 

webpack-deploy: ## build ALL assets with webpack
	make webpack-deploy-gse
	make webpack-deploy-shop
	make webpack-deploy-api
	make evendors-compile
	make extranet-compile

extranet-compile: ## Compile assets for extranet
	@$(EXTRANET) bin/console importmap:install
	@$(EXTRANET) bin/console asset-map:compile

evendors-compile: ## Compile assets for extranet
	@$(EVENDORS) bin/console importmap:install
	@$(EVENDORS) bin/console asset-map:compile

webpack-dev: ## start webpack dev server with hot module reload
	@$(DOCKER_COMPOSE) exec --index=1 gse sh -c "cd alvest-web-portals/current/intranet && yarn encore dev --watch"

cache-clear: ## clean cache of intranet, API and evendors Symfony apps
	@$(GSE) sh -c "rm -rf /srv/alvest-web-portals/intranet/var/log/*.log /srv/alvest-web-portals/intranet/var/cache/*"
	@$(API) sh -c "rm -rf var/log/*.log var/cache/*"
	@$(EVENDORS) sh -c "rm -rf var/log/*.log var/cache/*"
	@$(EXTRANET) sh -c "rm -rf var/log/*.log var/cache/*"
	@$(EVENDORS) sh -c "rm -rf /srv/$(PACKAGE_TRANSLATOR)/var/log/*.log /srv/$(PACKAGE_TRANSLATOR)/var/cache/*"
	@rm -rf intranet/var/log/*.log intranet/var/cache/*
	@rm -rf api/var/log/*.log api/var/cache/* api/.php-cs-fixer.cache
	@rm -rf evendors/var/log/*.log evendors/var/cache/*
	@rm -rf packages/translator/var/log/*.log packages/translator/var/cache/*

bash-%: ## get a bash terminal into a container
	@docker exec -ti tld-$*-1 bash

api-console: ## run API Symfony command console
	@$(API) bin/console --profile $(CMD)

gse-console: ## run intranet Symfony command console
	@$(GSE) /srv/alvest-web-portals/intranet/bin/console $(CMD)

ux-icons-lock: ## scan intranet and extranet templates and import any missing Symfony UX icons
	@echo 'intranet' && echo '========================='
	@$(GSE) /srv/alvest-web-portals/intranet/bin/console ux:icons:lock
	@echo '' && echo 'extranet-new' && echo '========================='
	@$(EXTRANET) bin/console ux:icons:lock

admin-console: ## run intranet legacy Admin command console
	@$(GSE) /srv/alvest-web-portals/admin/bin/console $(CMD)

evendors-console: ## run evendors command console
	@$(EVENDORS) php bin/console $(CMD)

extranet-console: ## run extranet command console
	@$(EXTRANET) php bin/console $(CMD)

ansible-provisioning: ## run provisioning: default value for INVENTORY is staging, PLAYBOOK needs to be defined, FLAGS can optionally be passed
	@$(ANSIBLE) ansible-playbook -i $(INVENTORY).yml $(PLAYBOOK).yml --vault-password-file .vault $(FLAGS)

ansible-inventory: ## check the Ansible inventory and how Ansible sees it: default value for INVENTORY is staging
	@$(ANSIBLE) ansible-inventory -i $(INVENTORY).yml --vault-password-file .vault --list $(FLAGS)

ansible-vault: ## calls ansible-vault using the .vault file, use CMD to pass your custom command
	$(ANSIBLE) ansible-vault $(CMD) --vault-password-file .vault

ansible-read: ## read a variable encrypted in vault, needs local install of ansible. exemple : make ansible-read VAR="database.baan" FILE="host_vars/web-stag-api-01.tld-america.com.yml"
	ansible localhost -m ansible.builtin.debug -a var="$(VAR)" -e "@provisioning/$(FILE)" --vault-password-file provisioning/.vault

ansible-lint: ## https://ansible-lint.readthedocs.io/en/latest/, performs syntax check and static analysis
	@docker run -v $(shell pwd)/provisioning:/provisioning -w /provisioning quay.io/ansible/creator-ee ansible-lint -p --force-color api.yml evendors.yml extranet.yml mobile.yml portals.yml powerbi.yml

translations-install-dev:
	@$(EVENDORS) sh -c "COMPOSER=composer.dev.json composer install -n --prefer-dist --no-interaction --working-dir=/srv/$(PACKAGE_TRANSLATOR)"

translations-push: ## push new translation english keys
	@$(EVENDORS) sh -c "cd /srv/$(PACKAGE_TRANSLATOR) ; php bin/console translation:push --locales en crowdin"

translations-pull: ## pull all translations FR and ZH from Crowdin
	@$(EVENDORS) sh -c "cd /srv/$(PACKAGE_TRANSLATOR) ; php bin/console translation:pull --locales fr --locales="zh-CN" --force --format=po crowdin"
	mv $(PACKAGE_TRANSLATOR)/translations/*.fr.po $(PACKAGE_TRANSLATOR)/translations/fr/.
	mv $(PACKAGE_TRANSLATOR)/translations/*.zh-CN.po $(PACKAGE_TRANSLATOR)/translations/zh-CN/.

switch-to-test: ## switch to test environment and record mode
	gsed -i '29s/APP_ENV=dev/APP_ENV=prod/g' compose.yml
	gsed -i 's/APP_ENV=dev/APP_ENV=test/g' compose.yml
	gsed -i -E 's/3306\/(tld|api)$$/3306\/\1_test/g' api/.env.local
	gsed -i -E 's/3306\/(tld|api)$$/3306\/\1_test/g' api/.env
	make kill start

switch-to-dev: ## switch to dev environment and replay mode
	gsed -i '29s/APP_ENV=prod/APP_ENV=dev/g' compose.yml
	gsed -i 's/APP_ENV=test/APP_ENV=dev/g' compose.yml
	gsed -i -E 's/3306\/(tld|api)_test$$/3306\/\1/g' api/.env
	gsed -i -E 's/3306\/(tld|api)_test$$/3306\/\1/g' api/.env.local
	make kill start

switch-to-debug: ## switch to debug environment for gse and api
	cp compose.override.yml.dist compose.override.yml
	make kill start

switch-back-to-dev: ## switch back to dev environment for gse and api after debugging
	rm compose.override.yml && git checkout -- compose.override.yml.dist
	make kill start

## Search for missing @group tag
search-for-missing-group-tag:
	$(eval FILES = $(shell find evendors/tests -iname '*Test.php' -exec grep -Li "@group" {} \;))

	@if [ ! -z $(FILES) ]; then \
		echo "\"@group\" tag is missing from the following files:\n"; \
		echo "$(FILES)" | tr " " "\n"; \
		echo ""; \
		exit 1; \
	fi

selenium-start:
	@$(DOCKER_COMPOSE) up -d selenium
	@for i in {1..30}; do \
		if curl -s http://localhost:4444/status | jq '.value.ready' | grep -q true; then \
		    echo 'Selenium is ready'; \
		    exit 0; \
		fi; \
		echo "Selenium is not ready... retry in 1s"; \
		sleep 1; \
	done
	@echo "\r\n\r\nVNC : http://localhost:7900/?autoconnect=1&password=secret\r\n"

selenium-stop:
	@$(DOCKER_COMPOSE) stop selenium


#These should stay commented, it's all command for a Linux environment
#start:
#	$(DOCKER_COMPOSE) up --remove-orphans -d --scale redis-slave=2 --scale redis-sentinel=3 --scale gse=1
#	@touch intranet/.env.local
#	@sed -i -E '/^TRUSTED_PROXIES=.*/d' intranet/.env.local
#	@echo -n "TRUSTED_PROXIES="  >> intranet/.env.local
#	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' tld-haproxy-1 >> intranet/.env.local
#	@touch api/.env.local
#	@sed -i -E '/^TRUSTED_PROXIES=.*/d' api/.env.local
#	@echo -n "TRUSTED_PROXIES="  >> api/.env.local
#	@docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' tld-haproxy-1 >> api/.env.local

#init-tls: ## install mkcert and generate certificates for local https
#	@sudo apt install libnss3-tools
#	@sudo wget -O /usr/local/bin/mkcert https://github.com/FiloSottile/mkcert/releases/download/v1.4.1/mkcert-v1.4.1-linux-amd64
#	@sudo chmod +x /usr/local/bin/mkcert
#	@mkcert -install
#	@mkcert --cert-file ./docker/certs/localhost.crt --key-file ./docker/certs/localhost.key localhost haproxy 127.0.0.1 ::1
#	@cat ./docker/certs/localhost.key ./docker/certs/localhost.crt > ./docker/certs/server.pem
#	@cp "$$(mkcert -CAROOT)/rootCA.pem" ./docker/certs/localCA.crt

#install: init-filesystem init-tls build start yarn-install webpack-deploy composer-install ckeditor-install yarn-install-mobile yarn-build-mobile ## setup a new dev environment from scratch

#test-phpunit-evendors-e2e: switch-to-test test-refresh ## launch evendors phpunit e2e tests
#	@$(eval filter ?= '.')
#	@$(EVENDORS) vendor/bin/phpunit --group e2e --filter=$(filter)
#	make switch-to-dev

#test-phpunit-extranet-unit: ## launch extranet phpunit unit tests
#	@$(eval filter ?= '.')
#	@$(EXTRANET) vendor/bin/phpunit --group unit --filter=$(filter)

#switch-to-test: ## switch to test environment and record mode
#	sed -i '29s/APP_ENV=dev/APP_ENV=prod/g' compose.yml
#	sed -i 's/APP_ENV=dev/APP_ENV=test/g' compose.yml
#	sed -i -E 's/3306\/(tld|api)$$/3306\/\1_test/g' api/.env.local
#	sed -i -E 's/3306\/(tld|api)$$/3306\/\1_test/g' api/.env
#	make kill start

#switch-to-dev: ## switch to dev environment and replay mode
#	sed -i '29s/APP_ENV=prod/APP_ENV=dev/g' compose.yml
#	sed -i 's/APP_ENV=test/APP_ENV=dev/g' compose.yml
#	sed -i -E 's/3306\/(tld|api)_test$$/3306\/\1/g' api/.env
#	sed -i -E 's/3306\/(tld|api)_test$$/3306\/\1/g' api/.env.local
#	make kill start
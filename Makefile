IMAGE_DEV = spyimmo:dev
CURRENT_ENV ?= DEV

DOCKER = docker-compose -p spyimmo

BIND_USER=
ifeq ($(CURRENT_ENV), DEV)
	BIND_USER = --user $(shell id -u):$(shell id -g)
endif

DOCKER_COMPOSER = $(DOCKER) run --rm $(BIND_USER) php-fpm composer

help: ## List all Makefile targets
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Docker
## ------
##
.PHONY: help start init build build-without-cache stop restart php

build: ## Build php-fpm dev image (Use IMAGE_DEV='Your image name' to define the image name)
ifeq ("$(shell docker images -q ${IMAGE_DEV} 2> /dev/null)", "")
	docker build --target dev -t ${IMAGE_DEV} -f infra/docker/php/Dockerfile .
endif

build-without-cache: ## Build php-fpm dev image without cache (Use IMAGE_DEV='Your image name' to define the image name)
	docker build --no-cache --target dev -t ${IMAGE_DEV} -f infra/docker/php/Dockerfile .

init: start create-schema  ## Initialize the project

start: build vendor ## Start all docker containers
	$(DOCKER) up -d --remove-orphans

restart: ## Restart all docker containers stack
	$(DOCKER) restart

stop: ## Stop all docker containers
	${DOCKER} stop

php: ## Open a terminal in php-fpm container
	$(DOCKER) exec php-fpm sh

##
## Composer
## --------
##
.PHONY: install update require

require: ## composer require with R=
	$(DOCKER_COMPOSER) require $(R)

install: vendor ## Install all dependencies

update: ## Update all dependencies (Use "R=something" to add arguments, like a dependency)
	$(DOCKER_COMPOSER) update -n $(R)

composer.lock: composer.json
	$(DOCKER_COMPOSER) update -n

vendor:
	$(DOCKER_COMPOSER) install

##
## Tests
## -----
##
.PHONY:.test unit-test

test: unit-test ## Launch tests

unit-test: vendor ## Launch Unit tests. (Use CURRENT_ENV="TEST|CI" to set environment, R="something" to add arguments, like a group of tests to execute)
	$(DOCKER_TEST) run --rm php-fpm bin/phpunit --configuration=phpunit.xml --verbose --stop-on-failure --colors $(R)

##
## Doctrine
## -----
##
.PHONY: create-schema

create-schema: vendor ## Execute database migration to the latest version
	$(DOCKER) exec php-fpm /app/bin/console doctrine:schema:create

##
## Workers
## -------
##
.PHONY: import-offers

import-offers: vendor ## Initial import pricing events (Use R="--since=date since")
	$(DOCKER) exec php-fpm php /app/bin/console spyimmo:crawl --now $(R)

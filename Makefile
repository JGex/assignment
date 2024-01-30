# Default script to run
.DEFAULT_GOAL := help
.PHONY: help


####
## Makefile var
####

# Sail command
SAIL=./vendor/bin/sail
# Artisan command
CMD_ARTISAN=$(SAIL) artisan
# PHP command
CMD_PHP=$(SAIL) php
# Composer command
CMD_COMPOSER=$(SAIL) composer
# Display a blank line and `[CMD]` before a command
PRE_CMD=printf "\033[1;33m\r\n********************************************\r\n[CMD] \033[0m"


####
## Variable user can pass as arguments
####

# Command to use after artisan php or composer
CMD=-h
# Container to display the log
CONTAINER=
# log size to show
TAIL=20


####
## General commands
####

help: ## Display this help
	@echo ""
	@echo "\033[1;33m Usage\033[0m :"
	@echo ""
	@echo "\033[0;32m   make [rule] ([VAR_1=\"VALUE 1\"] [VAR_2=VALUE_2])\033[0m"
	@echo ""
	@echo "\033[1;33m Rules\033[0m :"
	@echo ""
	@grep -E '^([a-zA-Z0-9\-]+): ?(.*)? ## (.*)$$' ./Makefile \
	| sed -n 's/^\(.*\): \(.*\)\(##.*\)/   \1\3/p' \
	| column -t -s '##'
	@echo ""
	@echo "\033[1;33m Personal rules\033[0m :"
	@echo ""
ifneq (, $(wildcard ./Makefile.local))
	@grep -E '^([a-zA-Z0-9\-]+): ?(.*)? ## (.*)$$' ./Makefile.local \
	| sed -n 's/^\(.*\): \(.*\)\(##.*\)/   \1\3/p' \
	| column -t -s '##'
endif


####
## SAIL commands
####

sail-up: ## Build and start the containers in the docker-compose
	@$(PRE_CMD)
	$(SAIL) up --detach --remove-orphans

sail-stop: ## Stop the containers present in the docker-compose
	@$(PRE_CMD)
	$(SAIL) stop

sail-restart: sail-stop sail-up ## Stop, restart the containers in the docker-compose

sail-build: sail-stop ## Stop, rebuild and start the containers
	@$(PRE_CMD)
	$(SAIL) build --pull --no-cache
	@$(PRE_CMD)
	$(SAIL) up --build --force-recreate --detach --remove-orphans

sail-purge: ## Remove all containers in the docker-compose
	@$(PRE_CMD)
	$(SAIL) down --rmi all --remove-orphans

sail-log: ## TAIL="20" CONTAINER="" Display the $(TAIL) last logs of the container $(CONTAINER) if no container provided, display for all containers
	@$(PRE_CMD)
	$(SAIL) logs -f --tail="$(TAIL)" $(CONTAINER)

php-artisan: ## CMD="-h" Run an artisan command $(CMD)
	@$(PRE_CMD)
	$(CMD_ARTISAN) $(CMD)

php: ## CMD="-h" Run a php command $(CMD)
	@$(PRE_CMD)
	$(CMD_PHP) $(CMD)

composer: ## CMD="-h" Run a composer command $(CMD)
	@$(PRE_CMD)
	$(CMD_COMPOSER) $(CMD)

####
## Personal commands
####

-include Makefile.local

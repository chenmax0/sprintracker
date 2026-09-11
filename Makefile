.DEFAULT_GOAL := help
COMPOSE := docker compose

.PHONY: help up down build restart logs ps sh-php sh-frontend console composer npm migration migrate test-backend

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'

up: ## Démarre la stack de dev (build inclus)
	$(COMPOSE) up -d --build

down: ## Arrête la stack de dev
	$(COMPOSE) down

build: ## Rebuild les images sans démarrer
	$(COMPOSE) build

restart: ## Redémarre la stack de dev
	$(COMPOSE) restart

logs: ## Suit les logs de tous les services
	$(COMPOSE) logs -f

ps: ## Liste les containers et leur statut
	$(COMPOSE) ps

sh-php: ## Ouvre un shell dans le container php
	$(COMPOSE) exec php sh

sh-frontend: ## Ouvre un shell dans le container frontend
	$(COMPOSE) exec frontend sh

console: ## Exécute bin/console (ex: make console cmd="cache:clear")
	$(COMPOSE) exec php bin/console $(cmd)

composer: ## Exécute composer (ex: make composer cmd="require symfony/mailer")
	$(COMPOSE) exec php composer $(cmd)

npm: ## Exécute npm dans le frontend (ex: make npm cmd="install axios")
	$(COMPOSE) exec frontend npm $(cmd)

migration: ## Génère une migration Doctrine à partir des entités
	$(COMPOSE) exec php bin/console make:migration

migrate: ## Applique les migrations Doctrine
	$(COMPOSE) exec php bin/console doctrine:migrations:migrate --no-interaction

test-backend: ## Lance les tests PHPUnit du backend
	$(COMPOSE) exec php bin/phpunit

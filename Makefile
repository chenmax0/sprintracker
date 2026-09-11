.DEFAULT_GOAL := help
COMPOSE := docker compose

.PHONY: help up down build restart logs ps sh-php sh-frontend console composer npm migration migrate test-db-setup test-backend

TEST_DATABASE_URL := postgresql://app:app@database:5432/sprintracker_test?serverVersion=16&charset=utf8

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

test-db-setup: ## Crée et migre la base de test Postgres (idempotent)
	$(COMPOSE) exec -e DATABASE_URL="$(TEST_DATABASE_URL)" -e APP_ENV=test php bin/console doctrine:database:create --if-not-exists
	$(COMPOSE) exec -e DATABASE_URL="$(TEST_DATABASE_URL)" -e APP_ENV=test php bin/console doctrine:migrations:migrate -n

test-backend: test-db-setup ## Lance les tests PHPUnit du backend
	$(COMPOSE) exec -e DATABASE_URL="$(TEST_DATABASE_URL)" -e APP_ENV=test php bin/phpunit

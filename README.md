# Sprintracker

Mini-Jira pour tracker un sprint. Back-end Symfony (API), front-end React (Vite), PostgreSQL.

## Structure

```
backend/    Symfony (API)
frontend/   React + Vite
docker/     Dockerfiles et configs (php, nginx, caddy) pour les deux services
```

## Développement local

```bash
make up
```

- Front-end (Vite, hot reload) : http://localhost:5173
- Back-end (API, via nginx) : http://localhost:8000
- PostgreSQL : localhost:5432

Le code est monté en volume : toute modification dans `backend/` ou `frontend/` est prise en compte à chaud.

Commandes utiles (voir `make help` pour la liste complète) :

```bash
make console cmd="cache:clear"           # console Symfony
make composer cmd="require symfony/mailer"  # ajouter une dépendance PHP
make npm cmd="install axios"             # ajouter une dépendance JS
make migration                           # générer une migration Doctrine
make migrate                             # appliquer les migrations
make logs
make down
```

## Production

```bash
cp .env.example .env
# éditer .env : mots de passe, APP_SECRET, FRONTEND_DOMAIN, API_DOMAIN, CORS_ALLOW_ORIGIN, VITE_API_URL

docker compose -f docker-compose.prod.yml up -d --build
```

Caddy est le seul point d'entrée public (ports 80/443) et sert de reverse proxy HTTPS vers le frontend et l'API. Les autres services (`frontend`, `nginx`, `php`, `database`) ne sont accessibles que via le réseau Docker interne.

- Sans domaine configuré (`FRONTEND_DOMAIN`/`API_DOMAIN` laissés à `localhost`/`api.localhost`) : Caddy sert du HTTPS avec un certificat auto-signé (CA locale), utile pour tester la config sur le VPS avant d'avoir un nom de domaine.
- Dès qu'un vrai domaine est renseigné dans `.env` (ex: `FRONTEND_DOMAIN=sprintracker.exemple.com`, `API_DOMAIN=api.sprintracker.exemple.com`), Caddy obtient et renouvelle automatiquement de vrais certificats Let's Encrypt — aucune autre modification nécessaire. Il faut juste que les enregistrements DNS pointent vers l'IP du VPS avant de relancer la stack.
- Penser à mettre à jour `CORS_ALLOW_ORIGIN` et `VITE_API_URL` en cohérence avec les domaines choisis.

Les images de prod sont construites en multi-stage : dépendances de dev exclues, autoload optimisé (`--classmap-authoritative`), assets front buildés et servis par nginx.

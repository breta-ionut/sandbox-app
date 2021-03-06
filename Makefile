# Docker commands.
start:
		docker-compose up -d

start-rebuild:
		docker-compose up -d --build

stop:
		docker-compose down

restart: stop
		make start

restart-rebuild: stop
		make start-rebuild

configure-env-vars:
		cp -f .env.dist .env

reload-env-vars: configure-env-vars
		make restart

init: configure-env-vars
		make start
		docker-compose exec php composer install
		docker-compose exec node npm install

clean:
		docker system prune -f

clean-hard:
		docker system prune -a --volumes -f
# End of - Docker commands.

# Backend commands.
enter-back:
		docker-compose exec php bash
# End of - Backend commands.

# Frontend commands.
start-front:
		docker-compose exec node npm run start

build-front:
		docker-compose exec node npm run build

build-front-api-doc:
		docker-compose exec node npm run build-api-doc

enter-front:
		docker-compose exec node bash
# End of - Frontend commands.

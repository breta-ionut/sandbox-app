# Docker commands.
start:
		docker-compose up -d

start-rebuild:
		docker-compose up -d --build

stop:
		docker-compose down

restart: stop
		make start

init:
		cp .env.dist .env
		make start
		docker-compose exec php composer install
		docker-compose exec node npm install

clean:
		docker system prune -f

clean-hard:
		docker system prune -a --volumes -f
# End of - Docker commands.

# Frontend commands.
start-front:
		docker-compose exec node npm run start

build-front:
		docker-compose exec node npm run build
# End of - Frontend commands.

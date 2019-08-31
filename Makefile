# Docker commands.
start:
		docker-compose up -d

start-fresh:
		docker-compose up -d --build

stop:
		docker-compose down

restart: stop
		make start

clean:
		docker system prune

clean-hard:
		docker system prune -a
# End of - Docker commands.

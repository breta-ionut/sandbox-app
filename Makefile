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
		docker system prune -f

clean-hard:
		docker system prune -a --volumes -f
# End of - Docker commands.

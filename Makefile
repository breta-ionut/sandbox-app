# Docker commands.
start:
		docker-compose up -d

stop:
		docker-compose down

restart: stop
		make start

clean:
		docker system prune -a
# End of - Docker commands.

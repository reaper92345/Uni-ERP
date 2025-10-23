# Makefile for Inventory System Docker Operations

.PHONY: help build up down logs clean restart shell db-shell rebuild dev prod

# Default target
help:
	@echo "Available commands:"
	@echo "  build     - Build Docker images"
	@echo "  up        - Start containers (default environment)"
	@echo "  down      - Stop and remove containers"
	@echo "  logs      - View container logs"
	@echo "  clean     - Stop containers and remove volumes"
	@echo "  restart   - Restart containers"
	@echo "  shell     - Access application container shell"
	@echo "  db-shell  - Access database container shell"
	@echo "  rebuild   - Rebuild and restart containers"
	@echo "  dev       - Start development environment"
	@echo "  prod      - Start production environment"
	@echo "  status    - Show container status"

# Build Docker images
build:
	docker-compose build

# Start default environment
up:
	docker-compose up -d

# Stop containers
down:
	docker-compose down

# View logs
logs:
	docker-compose logs -f

# Clean up (remove volumes)
clean:
	docker-compose down -v

# Restart containers
restart:
	docker-compose restart

# Access application container shell
shell:
	docker exec -it inventory_app bash

# Access database container shell
db-shell:
	docker exec -it inventory_db mysql -u inventory_user -p inventory_system

# Rebuild and restart
rebuild:
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d

# Development environment
dev:
	docker-compose -f docker-compose.dev.yml up -d

# Production environment
prod:
	docker-compose -f docker-compose.prod.yml up -d

# Show container status
status:
	docker-compose ps

# Backup database
backup:
	docker exec inventory_db mysqldump -u inventory_user -p inventory_system > backup_$(shell date +%Y%m%d_%H%M%S).sql

# Restore database (usage: make restore FILE=backup.sql)
restore:
	@if [ -z "$(FILE)" ]; then \
		echo "Usage: make restore FILE=backup.sql"; \
		exit 1; \
	fi
	docker exec -i inventory_db mysql -u inventory_user -p inventory_system < $(FILE)

# Install dependencies (if using Composer)
install:
	docker exec inventory_app composer install

# Update dependencies
update:
	docker exec inventory_app composer update

# Run tests (if applicable)
test:
	docker exec inventory_app php vendor/bin/phpunit

# Show resource usage
stats:
	docker stats

# Remove all containers, networks, and images
nuke:
	docker-compose down -v --rmi all
	docker system prune -a -f

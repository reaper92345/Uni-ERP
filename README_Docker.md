# Inventory System - Docker Setup

This document provides instructions for running the Inventory System using Docker.

## Prerequisites

- Docker Desktop installed and running
- Docker Compose (included with Docker Desktop)
- At least 2GB of available RAM

## Quick Start

### 1. Development Environment

```bash
# Start the development environment
docker-compose -f docker-compose.dev.yml up -d

# View logs
docker-compose -f docker-compose.dev.yml logs -f

# Stop the environment
docker-compose -f docker-compose.dev.yml down
```

### 2. Production Environment

```bash
# Start the production environment
docker-compose -f docker-compose.prod.yml up -d

# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Stop the environment
docker-compose -f docker-compose.prod.yml down
```

### 3. Default Environment

```bash
# Start the default environment
docker-compose up -d

# View logs
docker-compose logs -f

# Stop the environment
docker-compose down
```

## Access Points

After starting the containers, you can access:

- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Database**: localhost:3306

## Database Credentials

- **Host**: db (container name) or localhost:3306
- **Database**: inventory_system
- **Username**: inventory_user
- **Password**: inventory_password
- **Root Password**: root_password

## Container Management

### View Running Containers
```bash
docker ps
```

### View Container Logs
```bash
# View all logs
docker-compose logs

# View specific service logs
docker-compose logs app
docker-compose logs db

# Follow logs in real-time
docker-compose logs -f app
```

### Access Container Shell
```bash
# Access PHP application container
docker exec -it inventory_app bash

# Access database container
docker exec -it inventory_db mysql -u inventory_user -p
```

### Stop and Remove Containers
```bash
# Stop containers
docker-compose down

# Stop and remove volumes (WARNING: This will delete all data)
docker-compose down -v

# Remove all containers and images
docker-compose down --rmi all
```

## Development Workflow

### 1. Code Changes
The application code is mounted as a volume, so changes are reflected immediately without rebuilding.

### 2. Database Changes
If you modify the database schema:
```bash
# Stop containers
docker-compose down

# Remove volumes to reset database
docker-compose down -v

# Start fresh
docker-compose up -d
```

### 3. Rebuild Application
If you need to rebuild the PHP application:
```bash
# Rebuild without cache
docker-compose build --no-cache app

# Restart services
docker-compose up -d
```

## Troubleshooting

### Common Issues

#### 1. Port Already in Use
If ports 8080, 8081, or 3306 are already in use:
```bash
# Check what's using the port
netstat -ano | findstr :8080

# Kill the process or change ports in docker-compose.yml
```

#### 2. Database Connection Issues
```bash
# Check if database is running
docker ps | grep inventory_db

# Check database logs
docker-compose logs db

# Restart database service
docker-compose restart db
```

#### 3. Permission Issues
```bash
# Fix file permissions
docker exec -it inventory_app chown -R www-data:www-data /var/www/html
```

#### 4. Memory Issues
If you encounter memory issues:
```bash
# Check container resource usage
docker stats

# Increase Docker Desktop memory allocation
# Docker Desktop > Settings > Resources > Memory
```

### Reset Everything
```bash
# Stop all containers
docker-compose down

# Remove all containers, networks, and volumes
docker-compose down -v

# Remove all images
docker system prune -a

# Start fresh
docker-compose up -d
```

## Environment Variables

You can customize the configuration by creating a `.env` file:

```env
# Database Configuration
DB_HOST=db
DB_USER=inventory_user
DB_PASS=inventory_password
DB_NAME=inventory_system

# Application Configuration
APP_ENV=development
DEBUG=true
```

## Production Deployment

For production deployment:

1. Use `docker-compose.prod.yml`
2. Set proper environment variables
3. Use external database if needed
4. Configure SSL/TLS
5. Set up proper backup strategies
6. Monitor container health

## Backup and Restore

### Backup Database
```bash
docker exec inventory_db mysqldump -u inventory_user -p inventory_system > backup.sql
```

### Restore Database
```bash
docker exec -i inventory_db mysql -u inventory_user -p inventory_system < backup.sql
```

## Performance Optimization

### Development
- Use volume mounts for live code editing
- Enable Xdebug for debugging
- Use development-specific configurations

### Production
- Use multi-stage builds
- Optimize PHP settings
- Use Redis for caching (if needed)
- Configure proper logging levels

## Support

If you encounter issues:
1. Check the logs: `docker-compose logs`
2. Verify container status: `docker ps`
3. Check resource usage: `docker stats`
4. Review this documentation
5. Check Docker and Docker Compose versions

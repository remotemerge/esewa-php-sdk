# Use a base image with FPM and Nginx
FROM serversideup/php:8.5-fpm-nginx

# Build arguments
ARG SHOW_WELCOME_MESSAGE=false
ARG COMPOSER_ALLOW_SUPERUSER=0
ARG COMPOSER_ROOT_VERSION=1.0.0
ARG HEALTHCHECK_PATH=/

# Build arguments for user and group
ARG APPLICATION_UID=1000
ARG APPLICATION_GID=1000

# Switch to root user to perform system-level operations
USER root

# Install system dependencies
RUN apt update && \
    apt install -y nano bash && \
    apt clean && \
    rm -rf /var/lib/apt/lists/*

# Install essential PHP extensions
RUN install-php-extensions xdebug

# Configure user permissions
RUN docker-php-serversideup-set-id www-data ${APPLICATION_UID}:${APPLICATION_GID} && \
    docker-php-serversideup-set-file-permissions --owner ${APPLICATION_UID}:${APPLICATION_GID} --service nginx

# Create and configure Composer cache directory
RUN mkdir -p /composer/cache && chown -R ${APPLICATION_UID}:${APPLICATION_GID} /composer/cache

# Create application directory and set ownership
RUN mkdir -p /var/www/html && \
    chown -R ${APPLICATION_UID}:${APPLICATION_GID} /var/www/html

# Switch to unprivileged user for security
USER ${APPLICATION_UID}:${APPLICATION_GID}

# Copy local entrypoint entrypoint.d
COPY --chmod=755 --chown=${APPLICATION_UID}:${APPLICATION_GID} ./.docker/entrypoint.d/ /etc/entrypoint.d/

# Set working directory
WORKDIR /var/www/html

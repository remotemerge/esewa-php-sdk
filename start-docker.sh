#!/bin/bash

# Enable strict error handling
set -euo pipefail

# Configuration
readonly COMPOSE_FILE="compose.yml"
readonly NETWORK_NAME="esewa-network"
readonly PROJECT_NAME="esewa"

# Docker Compose command wrapper
COMPOSE_CMD="docker compose --file ${COMPOSE_FILE} --project-name ${PROJECT_NAME}"

# Set current GID/UID in environment variables
APPLICATION_GID=$(id -g)
APPLICATION_UID=$(id -u)
export APPLICATION_GID APPLICATION_UID

echo "Creating Docker network..."
docker network create "${NETWORK_NAME}" >/dev/null 2>&1 || true

echo "Building Docker images..."
${COMPOSE_CMD} build --build-arg APPLICATION_GID="${APPLICATION_GID}" --build-arg APPLICATION_UID="${APPLICATION_UID}"

echo "Starting Docker containers..."
${COMPOSE_CMD} up

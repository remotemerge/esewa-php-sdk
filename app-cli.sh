#!/bin/bash

# Login to the app container
docker compose --file compose.yml exec --user application pkg-dev-server bash

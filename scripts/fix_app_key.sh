#!/bin/bash
# Generate and update APP_KEY in .env.staging

cd My-Dent-Care/DentalERP

# Generate new APP_KEY
APP_KEY=$(openssl rand -base64 32)

# Update .env.staging
sudo sed -i "s|APP_KEY=base64:replace-with-your-app-key|APP_KEY=base64:$APP_KEY|g" .env.staging

echo "APP_KEY updated successfully"

# Restart containers
sudo docker compose -f docker/compose.staging.yaml restart app

echo "App container restarted"

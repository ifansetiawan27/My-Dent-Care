#!/bin/bash

# Script untuk initial setup setelah deployment
# Usage: ./aws_init.sh <PEM_FILE> <AWS_IP>

if [ "$#" -ne 2 ]; then
    echo "Error: Script memerlukan 2 argumen"
    echo "Usage: $0 <PEM_FILE> <AWS_IP>"
    echo ""
    echo "Contoh:"
    echo "  $0 ~/my-key.pem 54.123.45.67"
    exit 1
fi

PEM_FILE="$1"
AWS_IP="$2"

echo "=========================================="
echo "AWS EC2 Initial Setup"
echo "=========================================="
echo ""

echo "[1/3] Generating APP_KEY..."
ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan key:generate --force'

echo ""
echo "[2/3] Running migrations..."
ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan migrate --force'

echo ""
echo "[3/3] Clearing and caching config..."
ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan config:cache'

echo ""
echo "=========================================="
echo "Initial setup completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test health check: curl http://$AWS_IP:8080/up"
echo "2. Seed data (optional): ./aws_artisan.sh $PEM_FILE $AWS_IP db:seed --force"
echo "3. Check logs: ./aws_logs.sh $PEM_FILE $AWS_IP"
echo ""

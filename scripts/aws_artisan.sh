#!/bin/bash

# Script untuk menjalankan artisan command di AWS EC2
# Usage: ./aws_artisan.sh <PEM_FILE> <AWS_IP> <ARTISAN_COMMAND>

if [ "$#" -lt 3 ]; then
    echo "Error: Script memerlukan minimal 3 argumen"
    echo "Usage: $0 <PEM_FILE> <AWS_IP> <ARTISAN_COMMAND>"
    echo ""
    echo "Contoh:"
    echo "  $0 ~/my-key.pem 54.123.45.67 migrate --force"
    echo "  $0 ~/my-key.pem 54.123.45.67 key:generate"
    echo "  $0 ~/my-key.pem 54.123.45.67 db:seed --force"
    exit 1
fi

PEM_FILE="$1"
AWS_IP="$2"
shift 2
ARTISAN_COMMAND="$@"

echo "Running artisan command on AWS EC2: php artisan $ARTISAN_COMMAND"
echo ""

ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    "cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan $ARTISAN_COMMAND"

echo ""
echo "Command completed!"

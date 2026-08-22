#!/bin/bash

# Script untuk restart containers di AWS EC2
# Usage: ./aws_restart.sh <PEM_FILE> <AWS_IP>

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

echo "Restarting containers on AWS EC2..."

ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml restart'

echo ""
echo "Containers restarted successfully!"

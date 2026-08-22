#!/bin/bash

# Script untuk melihat logs dari container di AWS EC2
# Usage: ./aws_logs.sh <PEM_FILE> <AWS_IP>

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

echo "Connecting to AWS EC2 and streaming logs..."
echo "Press Ctrl+C to stop"
echo ""

ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP" \
    'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml logs -f'

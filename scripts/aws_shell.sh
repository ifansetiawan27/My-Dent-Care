#!/bin/bash

# Script untuk SSH ke AWS EC2 server
# Usage: ./aws_shell.sh <PEM_FILE> <AWS_IP>

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

echo "Connecting to AWS EC2..."
echo ""

ssh -i "$PEM_FILE" -o StrictHostKeyChecking=no ubuntu@"$AWS_IP"

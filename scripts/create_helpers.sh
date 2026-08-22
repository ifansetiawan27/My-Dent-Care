#!/bin/bash

# Helper scripts untuk AWS deployment management
# Buat di folder scripts/

# Script 1: aws_deploy.sh - Deployment utama (sudah ada)

# Script 2: aws_logs.sh - Melihat logs
cat > scripts/aws_logs.sh << 'EOF'
#!/bin/bash
if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <PEM_FILE> <AWS_IP>"
    exit 1
fi
ssh -i "$1" ubuntu@"$2" 'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml logs -f'
EOF
chmod +x scripts/aws_logs.sh

# Script 3: aws_restart.sh - Restart containers
cat > scripts/aws_restart.sh << 'EOF'
#!/bin/bash
if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <PEM_FILE> <AWS_IP>"
    exit 1
fi
ssh -i "$1" ubuntu@"$2" 'cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml restart'
EOF
chmod +x scripts/aws_restart.sh

# Script 4: aws_shell.sh - SSH ke server
cat > scripts/aws_shell.sh << 'EOF'
#!/bin/bash
if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <PEM_FILE> <AWS_IP>"
    exit 1
fi
ssh -i "$1" ubuntu@"$2"
EOF
chmod +x scripts/aws_shell.sh

# Script 5: aws_artisan.sh - Run artisan commands
cat > scripts/aws_artisan.sh << 'EOF'
#!/bin/bash
if [ "$#" -lt 3 ]; then
    echo "Usage: $0 <PEM_FILE> <AWS_IP> <ARTISAN_COMMAND>"
    echo "Example: $0 key.pem 1.2.3.4 migrate --force"
    exit 1
fi
PEM_FILE="$1"
AWS_IP="$2"
shift 2
COMMAND="$@"
ssh -i "$PEM_FILE" ubuntu@"$AWS_IP" "cd My-Dent-Care/DentalERP && sudo docker compose -f docker/compose.staging.yaml exec -T app php artisan $COMMAND"
EOF
chmod +x scripts/aws_artisan.sh

echo "Helper scripts created successfully!"

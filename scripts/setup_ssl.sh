#!/bin/bash

# Setup SSL Certificate with Let's Encrypt for Backend API
# Run this on AWS EC2 (108.136.48.83)

set -e

echo "=========================================="
echo "Setup SSL Certificate with Let's Encrypt"
echo "=========================================="
echo ""

# Check if domain is provided
if [ -z "$1" ]; then
  echo "Usage: ./setup_ssl.sh api.mydentcare.com"
  echo ""
  echo "Note: DNS must be pointed to 108.136.48.83 first"
  exit 1
fi

DOMAIN=$1
EMAIL="admin@mydentcare.com"

echo "Domain: $DOMAIN"
echo "Email: $EMAIL"
echo ""

# 1. Install Certbot
echo "📦 Installing Certbot..."
sudo apt-get update
sudo apt-get install -y certbot

# 2. Stop any service on port 80
echo ""
echo "🛑 Stopping services on port 80..."
sudo systemctl stop nginx 2>/dev/null || true
sudo systemctl stop apache2 2>/dev/null || true

# 3. Obtain SSL certificate
echo ""
echo "🔐 Obtaining SSL certificate from Let's Encrypt..."
sudo certbot certonly --standalone \
  --non-interactive \
  --agree-tos \
  --email $EMAIL \
  -d $DOMAIN

# 4. Install Caddy (simpler than Nginx for reverse proxy)
echo ""
echo "📦 Installing Caddy..."
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update
sudo apt install -y caddy

# 5. Configure Caddy
echo ""
echo "⚙️ Configuring Caddy reverse proxy..."
sudo tee /etc/caddy/Caddyfile > /dev/null <<EOF
$DOMAIN {
    reverse_proxy localhost:8080
    
    header {
        # Security headers
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Frame-Options "SAMEORIGIN"
        X-Content-Type-Options "nosniff"
        X-XSS-Protection "1; mode=block"
        Referrer-Policy "strict-origin-when-cross-origin"
        
        # CORS headers
        Access-Control-Allow-Origin "https://mydentcare.com"
        Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
        Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With"
        Access-Control-Allow-Credentials "true"
    }
    
    # Handle CORS preflight
    @options {
        method OPTIONS
    }
    respond @options 204
}
EOF

# 6. Restart Caddy
echo ""
echo "🔄 Restarting Caddy..."
sudo systemctl enable caddy
sudo systemctl restart caddy

# 7. Verify
echo ""
echo "✅ Verifying SSL setup..."
sleep 2
curl -I https://$DOMAIN/up 2>&1 | head -n 10

echo ""
echo "=========================================="
echo "✅ SSL Setup Complete!"
echo "=========================================="
echo ""
echo "Your API is now available at:"
echo "  https://$DOMAIN"
echo ""
echo "Health check:"
echo "  curl https://$DOMAIN/up"
echo ""
echo "Next Steps:"
echo "1. Update frontend API URL to: https://$DOMAIN/api"
echo "2. Test API: curl https://$DOMAIN/api/v1/auth/login"
echo "3. Update CORS if needed in backend config/cors.php"

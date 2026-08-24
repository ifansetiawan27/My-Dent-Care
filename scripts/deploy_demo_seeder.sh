#!/bin/bash

# Deploy Demo Seeder and Setup SSL
# Run this script on AWS EC2 (108.136.48.83)

set -e

echo "=========================================="
echo "Deploy Demo Seeder & Setup SSL"
echo "=========================================="
echo ""

# 1. Pull latest code
echo "📥 Pulling latest code from GitHub..."
cd ~/My-Dent-Care
git pull origin main

# 2. Run database seeder
echo ""
echo "🌱 Running demo data seeder..."
sudo docker exec dentalerp_staging_app php artisan db:seed --force

# 3. Verify data
echo ""
echo "✅ Verifying seeded data..."
sudo docker exec dentalerp_staging_app php artisan tinker --execute="
echo 'Organizations: ' . App\Domains\Organization\Models\Organization::count();
echo 'Branches: ' . App\Domains\Branch\Models\Branch::count();
echo 'Users: ' . App\Domains\User\Models\User::count();
echo 'Patients: ' . App\Domains\Patient\Models\Patient::count();
echo 'Appointments: ' . App\Domains\Appointment\Models\Appointment::count();
"

echo ""
echo "=========================================="
echo "✅ Demo Data Seeded Successfully!"
echo "=========================================="
echo ""
echo "Demo Credentials:"
echo "─────────────────────────────────────────"
echo "Super Admin:"
echo "  Email: superadmin@demodental.com"
echo "  Password: password123"
echo ""
echo "Doctor:"
echo "  Email: drjane@demodental.com"
echo "  Password: password123"
echo ""
echo "Receptionist:"
echo "  Email: sarah@demodental.com"
echo "  Password: password123"
echo "─────────────────────────────────────────"
echo ""
echo "Next Steps:"
echo "1. Setup SSL: Run setup_ssl.sh"
echo "2. Test API: curl http://108.136.48.83:8080/up"
echo "3. Update frontend: Set API URL in Vercel"

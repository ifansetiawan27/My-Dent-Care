#!/bin/bash
cd ~/My-Dent-Care/DentalERP
sudo cp .env.staging .env.staging.bak2
sudo sed -i 's/APP_KEY=.*/APP_KEY=base64:RGIMlZoV2RwQ57Ua9SHOVfeou8JHmvnHEGltOB\/xwNU=/' .env.staging
echo "APP_KEY updated"
grep APP_KEY .env.staging

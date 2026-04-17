#!/bin/bash

# Pastikan script berhenti jika ada error
set -e

echo "---------------------------------------------------"
echo "🚀 Klinik Sehat Auto-Deploy: $(date)"
echo "---------------------------------------------------"

# 1. Ambil update terbaru
echo "📥 Pulling latest code..."
git pull origin main

# 2. Update dependencies (PHP & JS)
echo "📦 Updating dependencies..."
docker exec klinik-sehat-app composer install --no-dev --optimize-autoloader
docker exec klinik-sehat-app npm install
docker exec klinik-sehat-app npm run build

# 3. Database & Cache
echo "🗄️ Running migrations & clearing cache..."
docker exec klinik-sehat-app php artisan migrate --force
docker exec klinik-sehat-app php artisan optimize:clear
docker exec klinik-sehat-app php artisan optimize

# 4. Restart Reverb (Krusial untuk Real-time)
echo "🔄 Restarting Reverb server..."
docker compose restart klinik-sehat-reverb

echo "---------------------------------------------------"
echo "✅ DEPLOY SUCCESSFUL!"
echo "---------------------------------------------------"
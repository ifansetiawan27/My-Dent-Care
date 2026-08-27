# Frontend Deployment Guide — Vercel

## Quick Deploy

### 1. Install Vercel CLI (one-time)
```bash
npm install -g vercel
```

### 2. Link Project
```bash
cd frontend
vercel link
```

### 3. Set Environment Variables

#### Via Vercel Dashboard (recommended):
1. Go to https://vercel.com/dashboard
2. Select your project: `mydentcare.com`
3. Go to **Settings → Environment Variables**
4. Add/update:

| Variable | Value | Environment |
|---|---|---|
| `VITE_API_BASE_URL` | `https://api.mydentcare.com/api` | Production |
| `VITE_API_BASE_URL` | `http://localhost:8000/api` | Preview/Development |

#### Via CLI:
```bash
vercel env add VITE_API_BASE_URL production
# Enter: https://api.mydentcare.com/api

vercel env add VITE_API_BASE_URL preview
# Enter: http://108.136.48.83:8080/api
```

### 4. Deploy
```bash
# Preview deployment
vercel

# Production deployment
vercel --prod
```

---

## Backend API URL Configuration

### Current Backend URLs:
| Environment | URL |
|---|---|
| Development | `http://localhost:8000/api` |
| Staging (no SSL) | `http://108.136.48.83:8080/api` |
| Production (with SSL) | `https://api.mydentcare.com/api` |

### CORS Configuration

Backend sudah di-config untuk accept requests dari:
- `https://mydentcare.com` (production)
- `http://localhost:5173` (dev)
- `http://127.0.0.1:5173` (dev)

Jika ada domain baru, update di backend:
```bash
# Edit .env.staging di server
CORS_ALLOWED_ORIGINS=https://mydentcare.com,https://newdomain.com,http://localhost:5173
```

Lalu restart:
```bash
ssh ubuntu@108.136.48.83
cd ~/My-Dent-Care/DentalERP
docker compose -f docker/compose.staging.yaml down
docker compose -f docker/compose.staging.yaml up -d
```

---

## Verification

After deployment, test:
```bash
# Check frontend loads
curl -I https://mydentcare.com

# Check API connectivity (from browser DevTools Network tab)
# Should see requests to https://api.mydentcare.com/api/v1/auth/login
```

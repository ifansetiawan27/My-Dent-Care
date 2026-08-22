# Frontend Deployment Guide

## Persiapan

Frontend DentalERP menggunakan Vue 3 + Vite dan siap untuk di-deploy ke berbagai platform hosting.

## File Konfigurasi yang Sudah Disiapkan

- `.env.production` - Environment variables untuk production
- `vercel.json` - Konfigurasi untuk Vercel deployment
- `netlify.toml` - Konfigurasi untuk Netlify deployment
- `public/_redirects` - Redirect rules untuk SPA routing di Netlify

## Langkah Deploy

### Option 1: Vercel (Recommended)

1. Install Vercel CLI (jika belum):
```bash
npm install -g vercel
```

2. Login ke Vercel:
```bash
vercel login
```

3. Deploy dari folder frontend:
```bash
cd frontend
vercel
```

4. Ikuti prompts untuk:
   - Login dengan GitHub/email
   - Confirm project settings
   - Deploy!

5. Set environment variables di Vercel Dashboard:
   - Buka project settings di dashboard
   - Tambahkan `VITE_API_BASE_URL` dengan URL backend production Anda

### Option 2: Netlify

1. Install Netlify CLI (jika belum):
```bash
npm install -g netlify-cli
```

2. Login dan deploy:
```bash
cd frontend
npm run build
netlify deploy --prod
```

3. Atau gunakan Netlify Drop:
   - Build project: `npm run build`
   - Drag & drop folder `dist` ke https://app.netlify.com/drop

4. Set environment variables di Netlify Dashboard:
   - Site settings → Environment variables
   - Tambahkan `VITE_API_BASE_URL`

### Option 3: GitHub Pages

1. Update `vite.config.ts` dengan base URL:
```typescript
export default defineConfig({
  base: '/repo-name/', // nama repository Anda
  // ... config lainnya
})
```

2. Install gh-pages:
```bash
npm install -D gh-pages
```

3. Tambahkan script deploy di `package.json`:
```json
"scripts": {
  "deploy": "npm run build && gh-pages -d dist"
}
```

4. Deploy:
```bash
npm run deploy
```

## Environment Variables

Update file `.env.production` dengan nilai production Anda:

```env
VITE_API_BASE_URL=https://api.yourdomain.com/api
```

**PENTING:** Jangan commit file `.env.production` dengan credentials asli. Gunakan environment variables di platform hosting.

## Checklist Sebelum Deploy

- [ ] Backend API sudah live dan accessible
- [ ] Update `VITE_API_BASE_URL` di environment variables
- [ ] Test build locally: `npm run build && npm run preview`
- [ ] Pastikan semua dependencies sudah terinstall
- [ ] CORS sudah dikonfigurasi di backend untuk domain frontend
- [ ] Laravel Sanctum `SANCTUM_STATEFUL_DOMAINS` sudah update dengan domain frontend

## Testing Production Build

Sebelum deploy, test production build secara lokal:

```bash
cd frontend
npm run build
npm run preview
```

Buka http://localhost:4173 dan pastikan aplikasi berfungsi dengan baik.

## Custom Domain

Setelah deploy, Anda bisa menambahkan custom domain melalui dashboard platform hosting:

- **Vercel:** Settings → Domains
- **Netlify:** Site settings → Domain management

## Troubleshooting

### 404 pada route refresh
Pastikan file routing configuration sudah ada (`vercel.json` atau `public/_redirects`)

### API Connection Failed
Pastikan `VITE_API_BASE_URL` sudah diset dengan benar dan backend accessible

### CORS Error
Update CORS settings di Laravel backend untuk allow domain frontend production

### Build Error
Cek apakah semua dependencies terinstall dengan `npm install`

# My Dent Care - Frontend Setup Guide

## Step 1: Project Initialization

### 1.1 Create Next.js Project

```bash
# Navigate to project root
cd "C:\Users\ifan.setiawan_klikde\Documents\My Dent Care"

# Create Next.js 14+ project with TypeScript and App Router
npx create-next-app@latest dental-erp-frontend --typescript --tailwind --app --src-dir --import-alias "@/*"

# Navigate to frontend directory
cd dental-erp-frontend
```

### 1.2 Install Core Dependencies

```bash
# UI Libraries
npm install @radix-ui/react-dialog @radix-ui/react-dropdown-menu @radix-ui/react-select @radix-ui/react-tabs @radix-ui/react-toast @radix-ui/react-slot
npm install class-variance-authority clsx tailwind-merge
npm install lucide-react

# Animations
npm install framer-motion

# 3D Graphics
npm install three @react-three/fiber @react-three/drei

# State Management & Data Fetching
npm install zustand @tanstack/react-query

# HTTP Client
npm install axios

# Form Handling & Validation
npm install react-hook-form zod @hookform/resolvers

# Date Utilities
npm install date-fns

# Development Tools
npm install -D @types/three
```

### 1.3 Initialize shadcn/ui

```bash
# Initialize shadcn/ui
npx shadcn-ui@latest init

# When prompted:
# - Style: Default
# - Base color: Slate
# - CSS variables: Yes
# - Tailwind config: Yes
# - Components directory: src/components
# - Utils directory: src/lib/utils
# - React Server Components: Yes
# - Write to tailwind.config: Yes
# - Import alias: @/*

# Install essential shadcn components
npx shadcn-ui@latest add button
npx shadcn-ui@latest add card
npx shadcn-ui@latest add input
npx shadcn-ui@latest add label
npx shadcn-ui@latest add form
npx shadcn-ui@latest add toast
npx shadcn-ui@latest add dropdown-menu
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add tabs
npx shadcn-ui@latest add avatar
npx shadcn-ui@latest add badge
npx shadcn-ui@latest add separator
```

### 1.4 Create Environment Variables

```bash
# Create .env.local
echo "NEXT_PUBLIC_API_URL=http://108.136.48.83:8080/api/v1" > .env.local
echo "NEXT_PUBLIC_APP_NAME=My Dent Care" >> .env.local
echo "NEXT_PUBLIC_APP_URL=http://localhost:3000" >> .env.local
```

---

## Step 2: Scalable Folder Structure (Feature-Sliced Design)

```
dental-erp-frontend/
├── src/
│   ├── app/                          # Next.js App Router
│   │   ├── (auth)/                   # Auth routes group
│   │   │   ├── login/
│   │   │   ├── register/
│   │   │   └── forgot-password/
│   │   ├── (dashboard)/              # Dashboard routes group
│   │   │   ├── layout.tsx
│   │   │   ├── page.tsx
│   │   │   ├── patients/
│   │   │   ├── appointments/
│   │   │   ├── treatments/
│   │   │   ├── inventory/
│   │   │   ├── finance/
│   │   │   └── settings/
│   │   ├── layout.tsx                # Root layout
│   │   ├── page.tsx                  # Landing page
│   │   └── globals.css
│   │
│   ├── features/                     # Feature-based modules (DDD approach)
│   │   ├── auth/
│   │   │   ├── components/
│   │   │   ├── hooks/
│   │   │   ├── api/
│   │   │   ├── store/
│   │   │   └── types/
│   │   ├── organization/
│   │   ├── patient/
│   │   ├── appointment/
│   │   ├── treatment/
│   │   ├── inventory/
│   │   └── finance/
│   │
│   ├── components/                   # Shared components
│   │   ├── ui/                       # shadcn/ui components
│   │   ├── layout/
│   │   │   ├── Header.tsx
│   │   │   ├── Sidebar.tsx
│   │   │   └── Footer.tsx
│   │   ├── Hero3D.tsx
│   │   └── PageTransition.tsx
│   │
│   ├── lib/                          # Utilities & configurations
│   │   ├── axios.ts                  # Axios + Sanctum config
│   │   ├── utils.ts                  # shadcn/ui utils
│   │   ├── queryClient.ts            # React Query config
│   │   └── constants.ts
│   │
│   ├── hooks/                        # Shared hooks
│   │   ├── useAuth.ts
│   │   └── useToast.ts
│   │
│   ├── store/                        # Zustand stores
│   │   ├── authStore.ts
│   │   └── uiStore.ts
│   │
│   └── types/                        # TypeScript types
│       ├── api.ts
│       ├── models.ts
│       └── common.ts
│
├── public/
│   ├── images/
│   └── fonts/
│
├── .env.local
├── .env.production
├── next.config.js
├── tailwind.config.ts
├── tsconfig.json
└── package.json
```

---

## Project Created Successfully

The folder structure follows Feature-Sliced Design with domain-driven organization matching the backend architecture.

**Next Steps:**
1. Run the initialization commands above
2. Implement the code files in Steps 3-5
3. Start development server: `npm run dev`

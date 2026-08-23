// src/app/layout.tsx
import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import { Providers } from './providers';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-inter',
});

/**
 * Metadata for SEO optimization (99% Lighthouse score target)
 */
export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000'),
  
  title: {
    default: 'My Dent Care - Dental Clinic ERP Platform',
    template: '%s | My Dent Care',
  },
  
  description:
    'Platform ERP enterprise untuk manajemen klinik gigi multi-cabang. Kelola pasien, appointment, treatment, inventory, dan keuangan dengan sistem terintegrasi.',
  
  keywords: [
    'dental clinic',
    'ERP',
    'klinik gigi',
    'dental management',
    'appointment scheduling',
    'patient management',
    'dental practice software',
    'Indonesia',
    'SATUSEHAT',
    'BPJS',
  ],
  
  authors: [
    {
      name: 'My Dent Care',
      url: 'https://mydentcare.com',
    },
  ],
  
  creator: 'My Dent Care',
  publisher: 'My Dent Care',
  
  formatDetection: {
    email: false,
    address: false,
    telephone: false,
  },
  
  // OpenGraph metadata for social sharing
  openGraph: {
    type: 'website',
    locale: 'id_ID',
    url: '/',
    siteName: 'My Dent Care',
    title: 'My Dent Care - Dental Clinic ERP Platform',
    description:
      'Platform ERP enterprise untuk manajemen klinik gigi multi-cabang. Kelola pasien, appointment, treatment, inventory, dan keuangan dengan sistem terintegrasi.',
    images: [
      {
        url: '/og-image.png',
        width: 1200,
        height: 630,
        alt: 'My Dent Care Platform',
      },
    ],
  },
  
  // Twitter Card metadata
  twitter: {
    card: 'summary_large_image',
    title: 'My Dent Care - Dental Clinic ERP Platform',
    description:
      'Platform ERP enterprise untuk manajemen klinik gigi multi-cabang dengan fitur lengkap dan unlimited users.',
    images: ['/og-image.png'],
    creator: '@mydentcare',
  },
  
  // Robots directives
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-video-preview': -1,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  },
  
  // Icons
  icons: {
    icon: [
      { url: '/favicon.ico' },
      { url: '/favicon-16x16.png', sizes: '16x16', type: 'image/png' },
      { url: '/favicon-32x32.png', sizes: '32x32', type: 'image/png' },
    ],
    apple: [
      { url: '/apple-touch-icon.png' },
    ],
    other: [
      {
        rel: 'mask-icon',
        url: '/safari-pinned-tab.svg',
      },
    ],
  },
  
  // Manifest
  manifest: '/site.webmanifest',
  
  // Verification
  verification: {
    google: 'your-google-verification-code',
    // Add other verification codes as needed
  },
  
  // Alternate languages
  alternates: {
    canonical: '/',
    languages: {
      'id-ID': '/',
      'en-US': '/en',
    },
  },
  
  // Additional metadata
  category: 'Healthcare',
  classification: 'Dental Practice Management Software',
};

/**
 * Root Layout Component
 * 
 * Features:
 * - Semantic HTML structure (main, nav)
 * - SEO optimized with Next.js Metadata API
 * - Font optimization with next/font
 * - Accessibility first (lang, viewport)
 * - Smooth page transitions with Framer Motion
 */
export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html
      lang="id"
      suppressHydrationWarning
      className={`${inter.variable} antialiased`}
    >
      <head>
        {/* Additional meta tags not covered by Metadata API */}
        <meta name="theme-color" content="#0284c7" />
        <meta name="color-scheme" content="light" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="My Dent Care" />
        
        {/* Preconnect to API domain for performance */}
        <link rel="preconnect" href={process.env.NEXT_PUBLIC_API_URL} />
        <link rel="dns-prefetch" href={process.env.NEXT_PUBLIC_API_URL} />
      </head>
      
      <body className={`${inter.className} bg-slate-50 text-slate-900`}>
        {/* Accessibility: Skip to main content link */}
        <a
          href="#main-content"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-sky-600 focus:px-4 focus:py-2 focus:text-white focus:outline-none focus:ring-2 focus:ring-sky-500"
        >
          Skip to main content
        </a>
        
        {/* Providers wrapper for React Query, Zustand, etc. */}
        <Providers>
          {/* Main content with semantic HTML */}
          <main
            id="main-content"
            className="min-h-screen"
            role="main"
            aria-label="Main content"
          >
            {children}
          </main>
          
          {/* Footer */}
          <footer
            className="border-t border-slate-200 bg-white"
            role="contentinfo"
            aria-label="Site footer"
          >
            <div className="container mx-auto px-4 py-8">
              <div className="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div className="space-y-4">
                  <h3 className="text-lg font-semibold text-slate-900">
                    My Dent Care
                  </h3>
                  <p className="text-sm text-slate-600">
                    Platform ERP enterprise untuk manajemen klinik gigi multi-cabang.
                  </p>
                </div>
                
                <nav aria-label="Product links">
                  <h4 className="mb-4 text-sm font-semibold text-slate-900">
                    Produk
                  </h4>
                  <ul className="space-y-2 text-sm text-slate-600">
                    <li>
                      <a href="#features" className="hover:text-sky-600">
                        Fitur
                      </a>
                    </li>
                    <li>
                      <a href="#pricing" className="hover:text-sky-600">
                        Harga
                      </a>
                    </li>
                    <li>
                      <a href="#demo" className="hover:text-sky-600">
                        Demo
                      </a>
                    </li>
                  </ul>
                </nav>
                
                <nav aria-label="Company links">
                  <h4 className="mb-4 text-sm font-semibold text-slate-900">
                    Perusahaan
                  </h4>
                  <ul className="space-y-2 text-sm text-slate-600">
                    <li>
                      <a href="#about" className="hover:text-sky-600">
                        Tentang
                      </a>
                    </li>
                    <li>
                      <a href="#contact" className="hover:text-sky-600">
                        Kontak
                      </a>
                    </li>
                    <li>
                      <a href="#support" className="hover:text-sky-600">
                        Support
                      </a>
                    </li>
                  </ul>
                </nav>
                
                <nav aria-label="Legal links">
                  <h4 className="mb-4 text-sm font-semibold text-slate-900">
                    Legal
                  </h4>
                  <ul className="space-y-2 text-sm text-slate-600">
                    <li>
                      <a href="#privacy" className="hover:text-sky-600">
                        Privacy Policy
                      </a>
                    </li>
                    <li>
                      <a href="#terms" className="hover:text-sky-600">
                        Terms of Service
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
              
              <div className="mt-8 border-t border-slate-200 pt-8 text-center text-sm text-slate-600">
                <p>
                  &copy; {new Date().getFullYear()} My Dent Care. All rights reserved.
                </p>
              </div>
            </div>
          </footer>
        </Providers>
      </body>
    </html>
  );
}

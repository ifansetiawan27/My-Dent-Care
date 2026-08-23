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
 * Metadata for SEO optimization
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
  
  twitter: {
    card: 'summary_large_image',
    title: 'My Dent Care - Dental Clinic ERP Platform',
    description:
      'Platform ERP enterprise untuk manajemen klinik gigi multi-cabang dengan fitur lengkap dan unlimited users.',
    images: ['/og-image.png'],
    creator: '@mydentcare',
  },
  
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
  
  verification: {
    google: 'google-site-verification-code',
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="id" className={inter.variable} suppressHydrationWarning>
      <head>
        <link rel="icon" href="/favicon.ico" sizes="any" />
        <link rel="icon" href="/icon.svg" type="image/svg+xml" />
        <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
        <link rel="manifest" href="/manifest.json" />
        <meta name="theme-color" content="#0ea5e9" />
      </head>
      <body className="min-h-screen bg-white font-sans antialiased">
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}

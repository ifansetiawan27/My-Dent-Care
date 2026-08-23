// src/app/page.tsx
import Hero3D from '@/components/Hero3D';
import FeaturesSection from '@/components/FeaturesSection';
import BenefitsSection from '@/components/BenefitsSection';
import PricingSection from '@/components/PricingSection';
import CTASection from '@/components/CTASection';
import Footer from '@/components/Footer';

/**
 * Home Page Component
 * Landing page untuk My Dent Care Dental ERP Platform
 */
export default function HomePage() {
  return (
    <>
      <Hero3D />
      <FeaturesSection />
      <BenefitsSection />
      <PricingSection />
      <CTASection />
      <Footer />
    </>
  );
}

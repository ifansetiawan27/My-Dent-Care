'use client';

import { useState, useEffect } from 'react';
import { motion, useScroll, useTransform } from 'framer-motion';
import { 
  Building2, 
  Database, 
  Image, 
  Package, 
  LineChart, 
  ShieldCheck,
  Users,
  CheckCircle2,
  ArrowRight,
  Menu,
  X
} from 'lucide-react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls, Sphere, MeshDistortMaterial } from '@react-three/drei';
import LoginForm from '@/components/LoginForm';

export default function LandingPage() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { scrollY } = useScroll();
  const navbarBg = useTransform(
    scrollY,
    [0, 100],
    ['rgba(255, 255, 255, 0)', 'rgba(255, 255, 255, 0.95)']
  );

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-teal-50">
      {/* Floating Navbar */}
      <motion.nav
        style={{ backgroundColor: navbarBg }}
        className="fixed top-0 left-0 right-0 z-50 backdrop-blur-md border-b border-slate-200/50"
      >
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between h-16">
            {/* Logo */}
            <motion.div
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              className="flex items-center space-x-2"
            >
              <div className="w-10 h-10 bg-gradient-medical rounded-lg flex items-center justify-center">
                <ShieldCheck className="w-6 h-6 text-white" />
              </div>
              <span className="text-xl font-bold text-slate-800">My Dent Care</span>
            </motion.div>

            {/* Desktop Navigation */}
            <div className="hidden md:flex items-center space-x-8">
              <a
                href="#features"
                className="text-slate-600 hover:text-primary transition-colors font-medium"
              >
                Features
              </a>
              <a
                href="#pricing"
                className="text-slate-600 hover:text-primary transition-colors font-medium"
              >
                Pricing
              </a>
              <a
                href="#login"
                className="px-6 py-2 bg-gradient-medical text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-primary/30 transition-all duration-300"
              >
                Login to Dashboard
              </a>
            </div>

            {/* Mobile Menu Button */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2 text-slate-600 hover:text-primary"
            >
              {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            className="md:hidden bg-white border-t border-slate-200"
          >
            <div className="container mx-auto px-4 py-4 space-y-3">
              <a
                href="#features"
                onClick={() => setMobileMenuOpen(false)}
                className="block py-2 text-slate-600 hover:text-primary transition-colors font-medium"
              >
                Features
              </a>
              <a
                href="#pricing"
                onClick={() => setMobileMenuOpen(false)}
                className="block py-2 text-slate-600 hover:text-primary transition-colors font-medium"
              >
                Pricing
              </a>
              <a
                href="#login"
                onClick={() => setMobileMenuOpen(false)}
                className="block w-full text-center px-6 py-2 bg-gradient-medical text-white rounded-lg font-semibold"
              >
                Login to Dashboard
              </a>
            </div>
          </motion.div>
        )}
      </motion.nav>

      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center pt-16 overflow-hidden">
        {/* Animated Gradient Blob Background */}
        <div className="absolute inset-0 overflow-hidden">
          <div className="absolute top-1/4 -right-1/4 w-[800px] h-[800px] bg-gradient-to-br from-sky-300/30 to-teal-300/30 rounded-full blur-3xl animate-blob" />
          <div className="absolute -bottom-1/4 -left-1/4 w-[800px] h-[800px] bg-gradient-to-tr from-teal-300/30 to-cyan-300/30 rounded-full blur-3xl animate-blob animation-delay-2000" />
        </div>

        <div className="container mx-auto px-4 relative z-10">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            {/* Left: Content */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="space-y-8"
            >
              <div className="inline-block px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-semibold">
                🚀 Enterprise-Grade Dental ERP
              </div>
              
              <h1 className="text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                Enterprise ERP for{' '}
                <span className="bg-gradient-medical bg-clip-text text-transparent">
                  Modern Dental Clinics
                </span>
              </h1>
              
              <p className="text-xl text-slate-600 leading-relaxed">
                Kelola klinik dental multi-cabang dengan sistem terintegrasi SATUSEHAT & BPJS. 
                Unlimited users, analytics real-time, dan medical imaging ready.
              </p>

              <div className="flex flex-col sm:flex-row gap-4">
                <a
                  href="#pricing"
                  className="group px-8 py-4 bg-gradient-medical text-white rounded-lg font-semibold hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center space-x-2"
                >
                  <span>Start 30-Day Free Trial</span>
                  <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </a>
                <a
                  href="#features"
                  className="px-8 py-4 border-2 border-primary text-primary rounded-lg font-semibold hover:bg-primary hover:text-white transition-all duration-300 flex items-center justify-center"
                >
                  Explore Features
                </a>
              </div>

              {/* Trust Indicators */}
              <div className="flex items-center space-x-6 pt-4">
                <div className="flex items-center space-x-2">
                  <CheckCircle2 className="w-5 h-5 text-teal-500" />
                  <span className="text-sm text-slate-600">No Credit Card Required</span>
                </div>
                <div className="flex items-center space-x-2">
                  <CheckCircle2 className="w-5 h-5 text-teal-500" />
                  <span className="text-sm text-slate-600">Setup in 5 Minutes</span>
                </div>
              </div>
            </motion.div>

            {/* Right: 3D Element with Glow */}
            <motion.div
              initial={{ opacity: 0, scale: 0.8 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 1, delay: 0.3 }}
              className="relative h-[500px] lg:h-[600px]"
            >
              {/* Glowing Gradient Background */}
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="w-96 h-96 bg-gradient-radial from-primary/20 via-teal-400/10 to-transparent rounded-full blur-3xl animate-glow" />
              </div>
              
              {/* 3D Canvas */}
              <div className="relative h-full">
                <Canvas camera={{ position: [0, 0, 5], fov: 75 }}>
                  <ambientLight intensity={0.5} />
                  <directionalLight position={[10, 10, 5]} intensity={1} />
                  <Sphere args={[1, 100, 200]} scale={2.5}>
                    <MeshDistortMaterial
                      color="#0ea5e9"
                      attach="material"
                      distort={0.5}
                      speed={2}
                      roughness={0.2}
                    />
                  </Sphere>
                  <OrbitControls enableZoom={false} autoRotate autoRotateSpeed={1} />
                </Canvas>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Features Bento Grid */}
      <section id="features" className="py-24 relative">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-center mb-16"
          >
            <h2 className="text-4xl font-bold text-slate-900 mb-4">
              Complete Dental Clinic Management
            </h2>
            <p className="text-xl text-slate-600">
              Everything you need to run a modern dental practice
            </p>
          </motion.div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Feature 1: Multi-branch */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.1 }}
              className="glass rounded-2xl p-8 hover:shadow-xl transition-shadow duration-300 lg:col-span-2"
            >
              <div className="flex items-start space-x-4">
                <div className="p-3 bg-gradient-to-br from-sky-400 to-blue-500 rounded-xl">
                  <Building2 className="w-6 h-6 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="text-xl font-bold text-slate-900 mb-2">
                    Multi-Branch Management & SATUSEHAT Integration
                  </h3>
                  <p className="text-slate-600 mb-4">
                    Kelola hingga 100+ cabang dari satu dashboard. Terintegrasi penuh dengan SATUSEHAT dan BPJS untuk klaim digital yang seamless.
                  </p>
                  <div className="flex flex-wrap gap-2">
                    <span className="px-3 py-1 bg-sky-100 text-sky-700 rounded-full text-sm font-medium">
                      Centralized Control
                    </span>
                    <span className="px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-sm font-medium">
                      SATUSEHAT Ready
                    </span>
                    <span className="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                      BPJS Integration
                    </span>
                  </div>
                </div>
              </div>
            </motion.div>

            {/* Feature 2: Medical Imaging */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.2 }}
              className="glass rounded-2xl p-8 hover:shadow-xl transition-shadow duration-300"
            >
              <div className="p-3 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-xl inline-block mb-4">
                <Image className="w-6 h-6 text-white" />
              </div>
              <h3 className="text-xl font-bold text-slate-900 mb-2">
                Advanced Medical Imaging
              </h3>
              <p className="text-slate-600">
                DICOM, PACS, & MicroVis integrations. Store and manage X-rays, CT scans, and intraoral images securely.
              </p>
            </motion.div>

            {/* Feature 3: Inventory */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.3 }}
              className="glass rounded-2xl p-8 hover:shadow-xl transition-shadow duration-300"
            >
              <div className="p-3 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl inline-block mb-4">
                <Package className="w-6 h-6 text-white" />
              </div>
              <h3 className="text-xl font-bold text-slate-900 mb-2">
                Inventory & Sterilization
              </h3>
              <p className="text-slate-600">
                Track materials, instruments, dan Autoclave cycles. Real-time stock alerts dan automated reorder points.
              </p>
            </motion.div>

            {/* Feature 4: Analytics */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.4 }}
              className="glass rounded-2xl p-8 hover:shadow-xl transition-shadow duration-300 lg:col-span-2"
            >
              <div className="flex items-start space-x-4">
                <div className="p-3 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl">
                  <LineChart className="w-6 h-6 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="text-xl font-bold text-slate-900 mb-2">
                    Financial Analytics & Interactive Odontogram
                  </h3>
                  <p className="text-slate-600">
                    Real-time financial dashboards, revenue forecasting, dan interactive digital odontogram untuk treatment planning yang visual.
                  </p>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Pricing Section */}
      <section id="pricing" className="py-24 bg-slate-50/50">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-center mb-16"
          >
            <h2 className="text-4xl font-bold text-slate-900 mb-4">
              Simple, Transparent Pricing
            </h2>
            <p className="text-xl text-slate-600">
              No hidden fees. No user limits. Cancel anytime.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="max-w-lg mx-auto"
          >
            <div className="glass rounded-3xl p-10 shadow-2xl border-2 border-primary/20 relative overflow-hidden">
              {/* Popular Badge */}
              <div className="absolute top-0 right-0">
                <div className="bg-gradient-medical text-white px-6 py-2 rounded-bl-2xl font-semibold">
                  Most Popular
                </div>
              </div>

              <div className="text-center mb-8 pt-6">
                <h3 className="text-3xl font-bold text-slate-900 mb-4">
                  Enterprise Plan
                </h3>
                <div className="mb-6">
                  <span className="text-5xl font-bold text-slate-900">Rp 300.000</span>
                  <span className="text-xl text-slate-600"> / Cabang / Bulan</span>
                </div>
                <p className="text-slate-600">
                  Unlimited users, unlimited features
                </p>
              </div>

              <div className="space-y-4 mb-8">
                {[
                  'Unlimited Users (Doctors, Nurses, Receptionists)',
                  'Multi-Branch Management',
                  'SATUSEHAT & BPJS Integration',
                  'DICOM & PACS Support',
                  'Real-time Analytics',
                  'Interactive Odontogram',
                  'Inventory & Sterilization Tracking',
                  '99%+ Uptime Guarantee',
                  'SSL Encrypted',
                  '24/7 Email Support',
                ].map((feature, index) => (
                  <div key={index} className="flex items-start space-x-3">
                    <CheckCircle2 className="w-5 h-5 text-teal-500 mt-0.5 flex-shrink-0" />
                    <span className="text-slate-700">{feature}</span>
                  </div>
                ))}
              </div>

              <a
                href="#login"
                className="block w-full text-center px-8 py-4 bg-gradient-medical text-white rounded-xl font-bold text-lg hover:shadow-xl hover:shadow-primary/30 transition-all duration-300"
              >
                Start 30-Day Free Trial
              </a>

              <p className="text-center text-sm text-slate-500 mt-4">
                No credit card required • Cancel anytime
              </p>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Login Section */}
      <section id="login" className="py-24 relative overflow-hidden">
        {/* Background decoration */}
        <div className="absolute inset-0">
          <div className="absolute top-0 left-1/4 w-96 h-96 bg-sky-200/20 rounded-full blur-3xl" />
          <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-teal-200/20 rounded-full blur-3xl" />
        </div>

        <div className="container mx-auto px-4 relative z-10">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold text-slate-900 mb-4">
              Access Your Dashboard
            </h2>
            <p className="text-xl text-slate-600">
              Sign in to manage your dental clinics
            </p>
          </div>

          <LoginForm />
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-slate-900 text-white py-12">
        <div className="container mx-auto px-4">
          <div className="grid md:grid-cols-4 gap-8 mb-8">
            <div>
              <div className="flex items-center space-x-2 mb-4">
                <div className="w-8 h-8 bg-gradient-medical rounded-lg flex items-center justify-center">
                  <ShieldCheck className="w-5 h-5 text-white" />
                </div>
                <span className="text-lg font-bold">My Dent Care</span>
              </div>
              <p className="text-slate-400 text-sm">
                Enterprise ERP for modern dental clinics
              </p>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Product</h4>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li><a href="#features" className="hover:text-white transition-colors">Features</a></li>
                <li><a href="#pricing" className="hover:text-white transition-colors">Pricing</a></li>
                <li><a href="#login" className="hover:text-white transition-colors">Login</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Support</h4>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li><a href="#" className="hover:text-white transition-colors">Documentation</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Help Center</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Contact</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Legal</h4>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li><a href="#" className="hover:text-white transition-colors">Privacy Policy</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Terms of Service</a></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-slate-800 pt-8 text-center text-slate-400 text-sm">
            <p>© 2026 My Dent Care. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

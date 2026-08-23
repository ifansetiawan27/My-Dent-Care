'use client';

import { motion } from 'framer-motion';
import { Check, Zap, Building2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

const pricingPlans = [
  {
    name: 'Single Branch',
    description: 'Perfect untuk klinik single-location',
    price: '300K',
    period: 'per bulan',
    features: [
      '1 Cabang Klinik',
      'Unlimited Users',
      'Full Features Access',
      'Patient Management',
      'Appointment Scheduling',
      'Treatment Records',
      'Billing & Invoicing',
      'Inventory Management',
      'Reports & Analytics',
      'SATUSEHAT Integration',
      'BPJS Integration',
      'Cloud Storage 50GB',
      'Email Support',
      'WhatsApp Notification',
    ],
    highlighted: false,
    icon: Building2,
    color: 'from-blue-500 to-cyan-500',
  },
  {
    name: 'Multi Branch',
    description: 'Best untuk jaringan klinik multi-cabang',
    price: '300K',
    period: 'per cabang/bulan',
    features: [
      'Unlimited Branches',
      'Unlimited Users',
      'Full Features Access',
      'Centralized Dashboard',
      'Multi-Branch Analytics',
      'Cross-Branch Inventory',
      'Consolidated Reporting',
      'Branch Performance Tracking',
      'Inter-Branch Transfer',
      'Group Level Management',
      'Priority Support',
      'Dedicated Account Manager',
      'Cloud Storage Unlimited',
      'Custom Integration',
      'Advanced Security Features',
    ],
    highlighted: true,
    icon: Zap,
    color: 'from-purple-500 to-pink-500',
  },
];

export default function PricingSection() {
  return (
    <section id="pricing" className="relative py-20 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 text-white overflow-hidden">
      {/* Animated Background Effects */}
      <motion.div
        animate={{
          scale: [1, 1.5, 1],
          rotate: [0, 90, 0],
        }}
        transition={{
          duration: 20,
          repeat: Infinity,
        }}
        className="absolute top-0 left-1/4 w-[500px] h-[500px] bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full filter blur-3xl opacity-20"
      />
      <motion.div
        animate={{
          scale: [1.5, 1, 1.5],
          rotate: [0, -90, 0],
        }}
        transition={{
          duration: 25,
          repeat: Infinity,
        }}
        className="absolute bottom-0 right-1/4 w-[600px] h-[600px] bg-gradient-to-r from-pink-400 to-orange-500 rounded-full filter blur-3xl opacity-20"
      />
      <div className="absolute inset-0 bg-[url('/grid.svg')] bg-center opacity-5" />

      <div className="container relative mx-auto px-4">
        {/* Section Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="text-center mb-16"
        >
          <span className="inline-block px-4 py-2 mb-4 text-sm font-semibold text-white bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full shadow-xl">
            💰 Harga Transparan & Terjangkau
          </span>
          <h2 className="text-4xl md:text-5xl font-bold mb-4">
            Investasi Yang Worth It
            <span className="block bg-gradient-to-r from-yellow-300 via-pink-300 to-cyan-300 bg-clip-text text-transparent">
              Untuk Masa Depan Klinik Anda
            </span>
          </h2>
          <p className="text-lg text-slate-300 max-w-2xl mx-auto">
            Satu harga flat, semua fitur. Tidak ada hidden cost, tidak ada batasan user.
            <br />
            <span className="font-semibold text-purple-400">Free Trial 30 Hari - No Credit Card Required</span>
          </p>
        </motion.div>

        {/* Pricing Cards */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
          {pricingPlans.map((plan, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: index * 0.2 }}
              className={`relative ${plan.highlighted ? 'lg:scale-105' : ''}`}
            >
              {/* Highlighted Badge */}
              {plan.highlighted && (
                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                  <span className="inline-block px-6 py-2 text-xs font-bold text-white bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 rounded-full shadow-2xl animate-pulse">
                    ⭐ MOST POPULAR
                  </span>
                </div>
              )}

              <div className={`relative bg-white/10 backdrop-blur-xl rounded-3xl p-8 ${plan.highlighted ? 'border-4 border-yellow-400 shadow-2xl shadow-yellow-500/30 scale-105' : 'border-2 border-white/20'} transform transition-all duration-300 hover:scale-105`}>
                {/* Icon */}
                <div className={`inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br ${plan.color} mb-6`}>
                  <plan.icon className="w-8 h-8 text-white" />
                </div>

                {/* Plan Info */}
                <h3 className="text-2xl font-bold mb-2">{plan.name}</h3>
                <p className="text-slate-400 mb-6">{plan.description}</p>

                {/* Price */}
                <div className="mb-8">
                  <div className="flex items-baseline gap-2">
                    <span className="text-5xl font-bold">Rp {plan.price}</span>
                  </div>
                  <p className="text-slate-400 mt-1">{plan.period}</p>
                </div>

                {/* CTA Button */}
                <Button 
                  className={`w-full py-6 text-lg font-bold ${
                    plan.highlighted 
                      ? 'bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 hover:shadow-2xl hover:shadow-orange-500/50' 
                      : 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:shadow-xl hover:shadow-cyan-500/50'
                  } transition-all duration-300 hover:scale-105`}
                >
                  🚀 Mulai Free Trial 30 Hari
                </Button>

                {/* Features List */}
                <ul className="mt-8 space-y-4">
                  {plan.features.map((feature, idx) => (
                    <li key={idx} className="flex items-start gap-3">
                      <Check className="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" />
                      <span className="text-slate-300">{feature}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </motion.div>
          ))}
        </div>

        {/* Additional Info */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.4 }}
          className="mt-16 text-center"
        >
          <div className="inline-block bg-white/5 backdrop-blur-lg rounded-2xl p-8 border border-slate-700">
            <h3 className="text-2xl font-bold mb-4">Ada Pertanyaan tentang Pricing?</h3>
            <p className="text-slate-300 mb-6 max-w-2xl">
              Tim kami siap membantu Anda memilih paket yang tepat untuk kebutuhan klinik Anda.
              <br />
              Hubungi kami untuk konsultasi gratis dan demo langsung.
            </p>
            <div className="flex flex-wrap justify-center gap-4">
              <Button variant="outline" className="border-slate-600 text-white hover:bg-slate-800">
                Hubungi Sales
              </Button>
              <Button className="bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700">
                Jadwalkan Demo
              </Button>
            </div>
          </div>
        </motion.div>

        {/* Guarantee Section */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.6 }}
          className="mt-12 text-center"
        >
          <div className="inline-flex items-center gap-3 px-6 py-3 bg-green-500/10 border border-green-500/30 rounded-full">
            <Check className="w-6 h-6 text-green-400" />
            <span className="text-slate-200">
              <strong className="text-green-400">30 Day Money Back Guarantee</strong> - 100% Risk Free
            </span>
          </div>
        </motion.div>
      </div>
    </section>
  );
}

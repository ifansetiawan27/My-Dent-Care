'use client';

import { motion } from 'framer-motion';
import { 
  Users, 
  Calendar, 
  FileText, 
  DollarSign, 
  Package, 
  BarChart3,
  Shield,
  Cloud,
  Zap,
  Globe,
  Hospital,
  Stethoscope
} from 'lucide-react';

const features = [
  {
    icon: Users,
    title: 'Manajemen Pasien',
    description: 'Kelola data pasien lengkap dengan riwayat medis, rekam odontogram digital, dan dokumen pendukung.',
    color: 'bg-blue-500',
    gradient: 'from-blue-400 to-cyan-500',
  },
  {
    icon: Calendar,
    title: 'Appointment Scheduling',
    description: 'Sistem penjadwalan appointment dengan reminder otomatis via WhatsApp dan kalender terintegrasi.',
    color: 'bg-green-500',
    gradient: 'from-green-400 to-emerald-500',
  },
  {
    icon: Stethoscope,
    title: 'Treatment Management',
    description: 'Catat treatment plan, tindakan medis, dan tracking progress treatment pasien secara real-time.',
    color: 'bg-purple-500',
    gradient: 'from-purple-400 to-pink-500',
  },
  {
    icon: DollarSign,
    title: 'Billing & Invoicing',
    description: 'Generate invoice otomatis, tracking pembayaran, dan integrasi dengan payment gateway.',
    color: 'bg-yellow-500',
    gradient: 'from-yellow-400 to-orange-500',
  },
  {
    icon: Package,
    title: 'Inventory Management',
    description: 'Kelola stok alat & bahan medis dengan sistem reorder point dan multi-supplier management.',
    color: 'bg-red-500',
    gradient: 'from-red-400 to-rose-500',
  },
  {
    icon: BarChart3,
    title: 'Reports & Analytics',
    description: 'Dashboard analytics real-time dengan laporan keuangan, operasional, dan performa klinik.',
    color: 'bg-indigo-500',
    gradient: 'from-indigo-400 to-purple-500',
  },
  {
    icon: Hospital,
    title: 'Multi-Branch Management',
    description: 'Kelola hingga 100+ cabang klinik dalam satu platform dengan sentralisasi data.',
    color: 'bg-pink-500',
    gradient: 'from-pink-400 to-fuchsia-500',
  },
  {
    icon: Globe,
    title: 'Integrasi SATUSEHAT & BPJS',
    description: 'Terintegrasi penuh dengan SATUSEHAT dan sistem BPJS untuk klaim digital.',
    color: 'bg-teal-500',
    gradient: 'from-teal-400 to-cyan-500',
  },
  {
    icon: Shield,
    title: 'Security & Compliance',
    description: 'ISO 27001 compliant dengan enkripsi end-to-end dan Row Level Security (RLS).',
    color: 'bg-orange-500',
    gradient: 'from-orange-400 to-amber-500',
  },
  {
    icon: Cloud,
    title: 'Cloud-Based Platform',
    description: 'Akses dari mana saja dengan infrastruktur AWS yang reliable dan scalable.',
    color: 'bg-cyan-500',
    gradient: 'from-cyan-400 to-blue-500',
  },
  {
    icon: Zap,
    title: 'API Integration Ready',
    description: 'RESTful API dengan OpenAPI 3.1 specification untuk integrasi third-party.',
    color: 'bg-lime-500',
    gradient: 'from-lime-400 to-green-500',
  },
  {
    icon: FileText,
    title: 'Unlimited Users',
    description: 'Tidak ada batasan jumlah user - dokter, perawat, resepsionis, dan staff lainnya.',
    color: 'bg-violet-500',
    gradient: 'from-violet-400 to-purple-500',
  },
];

export default function FeaturesSection() {
  return (
    <section className="relative py-20 bg-gradient-to-br from-orange-50 via-pink-50 to-purple-50 overflow-hidden">
      {/* Animated Background Shapes */}
      <motion.div
        animate={{
          scale: [1, 1.2, 1],
          x: [0, 50, 0],
        }}
        transition={{
          duration: 15,
          repeat: Infinity,
        }}
        className="absolute top-20 left-10 w-72 h-72 bg-gradient-to-r from-yellow-300 to-orange-300 rounded-full opacity-20 blur-3xl"
      />
      <motion.div
        animate={{
          scale: [1.2, 1, 1.2],
          x: [0, -50, 0],
        }}
        transition={{
          duration: 20,
          repeat: Infinity,
        }}
        className="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-r from-purple-300 to-pink-300 rounded-full opacity-20 blur-3xl"
      />
      
      <div className="container mx-auto px-4 relative">
        {/* Section Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="text-center mb-16"
        >
          <span className="inline-block px-4 py-2 mb-4 text-sm font-semibold text-white bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-lg">
            ⚡ Fitur Lengkap & Powerful
          </span>
          <h2 className="text-4xl md:text-5xl font-bold text-slate-900 mb-4">
            Semua Yang Anda Butuhkan
            <span className="block bg-gradient-to-r from-orange-500 via-pink-500 to-purple-500 bg-clip-text text-transparent">
              Dalam Satu Platform
            </span>
          </h2>
          <p className="text-lg text-slate-600 max-w-2xl mx-auto">
            Platform ERP dental clinic paling lengkap di Indonesia dengan fitur-fitur enterprise grade 
            yang dirancang khusus untuk operasional klinik gigi modern.
          </p>
        </motion.div>

        {/* Features Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: index * 0.1 }}
              whileHover={{ y: -8, scale: 1.02, transition: { duration: 0.2 } }}
              className="group relative bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden"
            >
              {/* Gradient Background on Hover */}
              <div className={`absolute inset-0 bg-gradient-to-br ${feature.gradient} opacity-0 group-hover:opacity-10 transition-opacity duration-300`} />
              
              {/* Icon */}
              <div className={`relative inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br ${feature.gradient} mb-6 transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 shadow-lg`}>
                <feature.icon className="w-8 h-8 text-white" />
              </div>

              {/* Content */}
              <h3 className="relative text-xl font-bold text-slate-900 mb-3 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-purple-600 group-hover:to-pink-600 transition-all duration-300">
                {feature.title}
              </h3>
              <p className="relative text-slate-600 leading-relaxed">
                {feature.description}
              </p>

              {/* Decorative Corner */}
              <div className={`absolute -top-8 -right-8 w-24 h-24 bg-gradient-to-br ${feature.gradient} rounded-full opacity-0 group-hover:opacity-20 transition-opacity duration-300`} />
            </motion.div>
          ))}
        </div>

        {/* Bottom CTA */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="text-center mt-16"
        >
          <p className="text-lg text-slate-600 mb-6">
            Dan masih banyak fitur lainnya yang terus berkembang
          </p>
          <a
            href="#pricing"
            className="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-purple-600 via-pink-600 to-orange-600 rounded-xl hover:shadow-2xl hover:shadow-pink-500/50 transition-all duration-300 hover:scale-105"
          >
            🎯 Lihat Semua Fitur Lengkap
            <svg className="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </motion.div>
      </div>

      {/* Background Decoration */}
      <div className="absolute top-0 right-0 w-96 h-96 bg-sky-200 rounded-full filter blur-3xl opacity-20 -z-10" />
      <div className="absolute bottom-0 left-0 w-96 h-96 bg-blue-200 rounded-full filter blur-3xl opacity-20 -z-10" />
    </section>
  );
}

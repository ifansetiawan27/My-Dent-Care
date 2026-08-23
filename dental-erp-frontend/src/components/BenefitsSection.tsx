'use client';

import { motion } from 'framer-motion';
import { 
  Infinity as InfinityIcon, 
  TrendingUp, 
  Lock, 
  Zap, 
  Globe2, 
  Users2,
  HeartHandshake,
  Smartphone
} from 'lucide-react';

const benefits = [
  {
    icon: InfinityIcon,
    title: 'Unlimited Users',
    description: 'Tidak ada batasan jumlah pengguna. Tambahkan dokter, perawat, resepsionis, dan staff sebanyak yang Anda butuhkan tanpa biaya tambahan.',
    gradient: 'from-blue-500 to-cyan-500',
  },
  {
    icon: TrendingUp,
    title: 'Scalable Architecture',
    description: 'Dari 1 klinik hingga 100+ cabang, sistem kami tumbuh bersama bisnis Anda. Domain Driven Design memastikan maintainability jangka panjang.',
    gradient: 'from-purple-500 to-pink-500',
  },
  {
    icon: Lock,
    title: 'Enterprise Security',
    description: 'ISO 27001 compliant dengan Row Level Security (RLS), enkripsi end-to-end, dan compliance penuh dengan regulasi kesehatan Indonesia.',
    gradient: 'from-orange-500 to-red-500',
  },
  {
    icon: Zap,
    title: 'Lightning Fast',
    description: 'Response time <200ms dengan caching Redis dan optimized database queries. User experience yang smooth dan responsive.',
    gradient: 'from-yellow-500 to-orange-500',
  },
  {
    icon: Globe2,
    title: 'Cloud-Based & Reliable',
    description: 'Infrastruktur AWS dengan 99%+ uptime guarantee. Akses dari mana saja, kapan saja dengan automatic backup harian.',
    gradient: 'from-green-500 to-teal-500',
  },
  {
    icon: Users2,
    title: 'Multi-Role Management',
    description: 'Role-based access control (RBAC) untuk Super Admin, Clinic Owner, Dentist, Nurse, dan Receptionist dengan permissions granular.',
    gradient: 'from-indigo-500 to-purple-500',
  },
  {
    icon: HeartHandshake,
    title: 'Integrasi BPJS & SATUSEHAT',
    description: 'Satu-satunya platform dental ERP yang fully integrated dengan SATUSEHAT dan BPJS untuk klaim digital yang seamless.',
    gradient: 'from-pink-500 to-rose-500',
  },
  {
    icon: Smartphone,
    title: 'API-First Design',
    description: 'RESTful API dengan OpenAPI 3.1 specification memungkinkan integrasi mudah dengan mobile apps dan third-party systems.',
    gradient: 'from-cyan-500 to-blue-500',
  },
];

const stats = [
  { value: '99%+', label: 'Uptime Guarantee', color: 'text-green-400', gradient: 'from-green-400 to-emerald-500' },
  { value: '<200ms', label: 'API Response Time', color: 'text-blue-400', gradient: 'from-blue-400 to-cyan-500' },
  { value: '100+', label: 'Cabang Supported', color: 'text-purple-400', gradient: 'from-purple-400 to-pink-500' },
  { value: '∞', label: 'Users per Cabang', color: 'text-pink-400', gradient: 'from-pink-400 to-rose-500' },
];

export default function BenefitsSection() {
  return (
    <section className="relative py-20 bg-gradient-to-br from-cyan-50 via-blue-50 to-purple-50 overflow-hidden">
      {/* Animated Background Shapes */}
      <motion.div
        animate={{
          scale: [1, 1.3, 1],
          rotate: [0, 180, 360],
        }}
        transition={{
          duration: 25,
          repeat: Infinity,
        }}
        className="absolute top-0 right-0 w-96 h-96 bg-gradient-to-r from-cyan-300 to-blue-400 rounded-full opacity-20 blur-3xl"
      />
      <motion.div
        animate={{
          scale: [1.3, 1, 1.3],
          rotate: [360, 180, 0],
        }}
        transition={{
          duration: 30,
          repeat: Infinity,
        }}
        className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-gradient-to-r from-purple-300 to-pink-400 rounded-full opacity-20 blur-3xl"
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
          <span className="inline-block px-4 py-2 mb-4 text-sm font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full shadow-lg">
            🌟 Mengapa My Dent Care?
          </span>
          <h2 className="text-4xl md:text-5xl font-bold text-slate-900 mb-4">
            Platform Dental ERP
            <span className="block bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 bg-clip-text text-transparent">
              Terbaik di Indonesia
            </span>
          </h2>
          <p className="text-lg text-slate-600 max-w-2xl mx-auto">
            Dibangun dengan arsitektur enterprise-grade dan teknologi terkini untuk memastikan 
            klinik Anda tetap competitive dan efficient di era digital.
          </p>
        </motion.div>

        {/* Stats Grid */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.2 }}
          className="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-16"
        >
          {stats.map((stat, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, scale: 0.9 }}
              whileInView={{ opacity: 1, scale: 1 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              className="bg-white/80 backdrop-blur-lg rounded-2xl p-6 text-center shadow-xl border-2 border-white hover:scale-105 transition-all duration-300"
            >
              <div className={`text-5xl md:text-6xl font-bold bg-gradient-to-r ${stat.gradient} bg-clip-text text-transparent mb-2 drop-shadow-lg`}>
                {stat.value}
              </div>
              <div className="text-sm text-slate-700 font-semibold">{stat.label}</div>
            </motion.div>
          ))}
        </motion.div>

        {/* Benefits Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {benefits.map((benefit, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, x: index % 2 === 0 ? -20 : 20 }}
              whileInView={{ opacity: 1, x: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              whileHover={{ scale: 1.05, rotate: 2 }}
              className="group"
            >
              <div className="relative bg-white/90 backdrop-blur-sm rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-white h-full overflow-hidden">
                {/* Animated Gradient Border */}
                <div className={`absolute inset-0 bg-gradient-to-br ${benefit.gradient} opacity-0 group-hover:opacity-20 transition-opacity duration-500 rounded-2xl`} />
                
                {/* Icon with Gradient Background */}
                <div className={`relative inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br ${benefit.gradient} mb-6 transform group-hover:scale-110 group-hover:rotate-12 transition-all duration-500 shadow-xl`}>
                  <benefit.icon className="w-10 h-10 text-white" />
                </div>

                {/* Content */}
                <h3 className="relative text-2xl font-bold text-slate-900 mb-4 transition-all duration-300">
                  {benefit.title}
                </h3>
                <p className="relative text-slate-600 leading-relaxed">
                  {benefit.description}
                </p>

                {/* Decorative Corner Element */}
                <div className={`absolute -bottom-10 -right-10 w-32 h-32 bg-gradient-to-br ${benefit.gradient} rounded-full opacity-0 group-hover:opacity-10 transition-opacity duration-500`} />
              </div>
            </motion.div>
          ))}
        </div>

        {/* Technology Stack Section */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.4 }}
          className="mt-20"
        >
          <div className="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-12 text-white">
            <div className="text-center mb-12">
              <h3 className="text-3xl font-bold mb-4">
                Dibangun dengan Teknologi Terkini
              </h3>
              <p className="text-slate-300 max-w-2xl mx-auto">
                Menggunakan tech stack modern dan proven untuk memastikan performa, 
                security, dan scalability jangka panjang.
              </p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {[
                { name: 'Laravel 12', desc: 'PHP 8.4' },
                { name: 'PostgreSQL', desc: 'Database' },
                { name: 'Redis', desc: 'Cache & Queue' },
                { name: 'AWS', desc: 'Cloud Infrastructure' },
                { name: 'Docker', desc: 'Containerization' },
                { name: 'Next.js', desc: 'Frontend' },
                { name: 'TypeScript', desc: 'Type Safety' },
                { name: 'Tailwind CSS', desc: 'Styling' },
              ].map((tech, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.5, delay: index * 0.05 }}
                  className="bg-white/10 backdrop-blur-lg rounded-xl p-6 text-center border border-white/20 hover:bg-white/20 transition-all duration-300"
                >
                  <div className="font-bold text-lg mb-1">{tech.name}</div>
                  <div className="text-sm text-slate-400">{tech.desc}</div>
                </motion.div>
              ))}
            </div>
          </div>
        </motion.div>
      </div>

      {/* Background Decoration */}
      <div className="absolute top-1/4 right-0 w-96 h-96 bg-purple-200 rounded-full filter blur-3xl opacity-20 -z-10" />
      <div className="absolute bottom-1/4 left-0 w-96 h-96 bg-pink-200 rounded-full filter blur-3xl opacity-20 -z-10" />
    </section>
  );
}

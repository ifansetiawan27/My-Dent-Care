'use client';

import { motion } from 'framer-motion';
import { ArrowRight, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function CTASection() {
  return (
    <section className="relative py-20 overflow-hidden">
      {/* Animated Background Gradient */}
      <div className="absolute inset-0 bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500">
        <div className="absolute inset-0 bg-[url('/grid.svg')] bg-center opacity-10" />
      </div>

      {/* Super Animated Shapes */}
      <motion.div
        animate={{
          scale: [1, 1.5, 1],
          rotate: [0, 360, 0],
          x: [0, 100, 0],
        }}
        transition={{
          duration: 15,
          repeat: Infinity,
          ease: "easeInOut",
        }}
        className="absolute top-10 left-10 w-80 h-80 bg-gradient-to-r from-yellow-300 to-orange-400 rounded-full filter blur-3xl opacity-40"
      />
      <motion.div
        animate={{
          scale: [1.5, 1, 1.5],
          rotate: [360, 0, 360],
          x: [0, -100, 0],
        }}
        transition={{
          duration: 20,
          repeat: Infinity,
          ease: "easeInOut",
        }}
        className="absolute bottom-10 right-10 w-96 h-96 bg-gradient-to-r from-cyan-300 to-blue-400 rounded-full filter blur-3xl opacity-40"
      />
      <motion.div
        animate={{
          scale: [1, 1.3, 1],
          y: [0, 50, 0],
        }}
        transition={{
          duration: 10,
          repeat: Infinity,
        }}
        className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-r from-pink-300 to-purple-400 rounded-full filter blur-3xl opacity-30"
      />

      <div className="container relative mx-auto px-4">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.8 }}
          className="max-w-4xl mx-auto text-center"
        >
          {/* Badge */}
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="inline-flex items-center gap-2 px-6 py-3 mb-8 bg-gradient-to-r from-yellow-400 to-orange-400 backdrop-blur-lg rounded-full border-2 border-white/50 shadow-2xl"
          >
            <Sparkles className="w-5 h-5 text-white animate-pulse" />
            <span className="text-sm font-bold text-white">
              ⚡ Limited Time Offer - Extended Trial Period
            </span>
          </motion.div>

          {/* Heading */}
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.3 }}
            className="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-2xl"
          >
            Siap Transformasi
            <span className="block bg-gradient-to-r from-yellow-200 via-pink-200 to-cyan-200 bg-clip-text text-transparent">
              Klinik Gigi Anda?
            </span>
          </motion.h2>

          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="text-xl text-white/90 mb-10 max-w-2xl mx-auto leading-relaxed"
          >
            Mulai free trial 30 hari tanpa kartu kredit. Setup dalam hitungan menit. 
            Rasakan perbedaan platform ERP dental clinic terbaik di Indonesia.
          </motion.p>

          {/* CTA Buttons */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.5 }}
            className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12"
          >
            <Button 
              size="lg"
              className="group px-10 py-7 text-xl font-bold bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 text-white hover:shadow-2xl hover:shadow-orange-500/50 transition-all duration-300 hover:scale-110"
            >
              🚀 Mulai Free Trial Sekarang
              <ArrowRight className="w-6 h-6 ml-2 group-hover:translate-x-2 transition-transform" />
            </Button>
            
            <Button 
              size="lg"
              className="px-10 py-7 text-xl font-bold text-white border-4 border-white/50 bg-white/20 backdrop-blur-xl hover:bg-white/30 hover:scale-105 transition-all duration-300 shadow-xl"
            >
              📺 Jadwalkan Demo Live
            </Button>
          </motion.div>

          {/* Trust Indicators */}
          <motion.div
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="flex flex-wrap items-center justify-center gap-8 text-white/80"
          >
            <div className="flex items-center gap-2">
              <svg className="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              <span className="text-sm font-medium">No Credit Card Required</span>
            </div>
            
            <div className="flex items-center gap-2">
              <svg className="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              <span className="text-sm font-medium">Setup dalam 5 Menit</span>
            </div>
            
            <div className="flex items-center gap-2">
              <svg className="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              <span className="text-sm font-medium">Cancel Anytime</span>
            </div>
          </motion.div>
        </motion.div>

        {/* Bottom Feature Highlights */}
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.8, delay: 0.7 }}
          className="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto"
        >
          {[
            { title: '24/7 Support', desc: 'Tim support siap membantu kapanpun' },
            { title: 'Data Migration', desc: 'Kami bantu migrate data existing' },
            { title: 'Training Included', desc: 'Onboarding & training gratis' },
          ].map((item, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: 0.8 + index * 0.1 }}
              className="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20 text-center"
            >
              <h4 className="text-lg font-bold text-white mb-2">{item.title}</h4>
              <p className="text-sm text-white/80">{item.desc}</p>
            </motion.div>
          ))}
        </motion.div>
      </div>
    </section>
  );
}

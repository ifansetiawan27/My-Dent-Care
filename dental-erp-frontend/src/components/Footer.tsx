'use client';

import { motion } from 'framer-motion';
import { Mail, Phone, MapPin } from 'lucide-react';

const footerLinks = {
  product: [
    { name: 'Fitur', href: '#features' },
    { name: 'Pricing', href: '#pricing' },
    { name: 'Demo', href: '#demo' },
    { name: 'Changelog', href: '#changelog' },
  ],
  solutions: [
    { name: 'Single Clinic', href: '#single' },
    { name: 'Multi Branch', href: '#multi' },
    { name: 'Integrasi BPJS', href: '#bpjs' },
    { name: 'SATUSEHAT', href: '#satusehat' },
  ],
  resources: [
    { name: 'Documentation', href: '#docs' },
    { name: 'API Reference', href: '#api' },
    { name: 'Support Center', href: '#support' },
    { name: 'Blog', href: '#blog' },
  ],
  company: [
    { name: 'Tentang Kami', href: '#about' },
    { name: 'Karir', href: '#careers' },
    { name: 'Kontak', href: '#contact' },
    { name: 'Partners', href: '#partners' },
  ],
  legal: [
    { name: 'Privacy Policy', href: '#privacy' },
    { name: 'Terms of Service', href: '#terms' },
    { name: 'Security', href: '#security' },
    { name: 'Compliance', href: '#compliance' },
  ],
};

const socialLinks = [
  { 
    name: 'GitHub', 
    icon: () => (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path fillRule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clipRule="evenodd" />
      </svg>
    ),
    href: 'https://github.com/ifansetiawan27/My-Dent-Care' 
  },
  { 
    name: 'LinkedIn', 
    icon: () => (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
      </svg>
    ),
    href: '#' 
  },
  { 
    name: 'Twitter', 
    icon: () => (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
      </svg>
    ),
    href: '#' 
  },
];

export default function Footer() {
  return (
    <footer className="relative bg-slate-900 text-slate-300">
      {/* Main Footer Content */}
      <div className="container mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8 mb-12">
          {/* Brand Column */}
          <div className="lg:col-span-2">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6 }}
            >
              <h3 className="text-2xl font-bold text-white mb-4">My Dent Care</h3>
              <p className="text-slate-400 mb-6 leading-relaxed">
                Platform ERP dental clinic terbaik di Indonesia. 
                Kelola klinik gigi Anda dengan lebih efisien dan modern.
              </p>
              
              {/* Contact Info */}
              <div className="space-y-3">
                <div className="flex items-center gap-3 text-sm">
                  <Mail className="w-4 h-4 text-sky-400" />
                  <a href="mailto:info@mydentcare.id" className="hover:text-sky-400 transition-colors">
                    info@mydentcare.id
                  </a>
                </div>
                <div className="flex items-center gap-3 text-sm">
                  <Phone className="w-4 h-4 text-sky-400" />
                  <a href="tel:+62811234567" className="hover:text-sky-400 transition-colors">
                    +62 811-2345-67
                  </a>
                </div>
                <div className="flex items-start gap-3 text-sm">
                  <MapPin className="w-4 h-4 text-sky-400 mt-1 flex-shrink-0" />
                  <span>Jakarta, Indonesia</span>
                </div>
              </div>

              {/* Social Links */}
              <div className="flex items-center gap-4 mt-6">
                {socialLinks.map((social) => {
                  const Icon = social.icon;
                  return (
                    <a
                      key={social.name}
                      href={social.href}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center hover:bg-sky-600 transition-colors duration-300"
                      aria-label={social.name}
                    >
                      <Icon />
                    </a>
                  );
                })}
              </div>
            </motion.div>
          </div>

          {/* Product Links */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
          >
            <h4 className="text-white font-semibold mb-4">Product</h4>
            <ul className="space-y-3">
              {footerLinks.product.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-sm hover:text-sky-400 transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Solutions Links */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            <h4 className="text-white font-semibold mb-4">Solutions</h4>
            <ul className="space-y-3">
              {footerLinks.solutions.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-sm hover:text-sky-400 transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Resources Links */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.3 }}
          >
            <h4 className="text-white font-semibold mb-4">Resources</h4>
            <ul className="space-y-3">
              {footerLinks.resources.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-sm hover:text-sky-400 transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Company & Legal Links */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.4 }}
          >
            <h4 className="text-white font-semibold mb-4">Company</h4>
            <ul className="space-y-3 mb-6">
              {footerLinks.company.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-sm hover:text-sky-400 transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
            
            <h4 className="text-white font-semibold mb-4">Legal</h4>
            <ul className="space-y-3">
              {footerLinks.legal.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-sm hover:text-sky-400 transition-colors"
                  >
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </motion.div>
        </div>

        {/* Bottom Bar */}
        <motion.div
          initial={{ opacity: 0 }}
          whileInView={{ opacity: 1 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.5 }}
          className="pt-8 border-t border-slate-800"
        >
          <div className="flex flex-col md:flex-row items-center justify-between gap-4">
            <p className="text-sm text-slate-400">
              © {new Date().getFullYear()} My Dent Care. All rights reserved.
            </p>
            
            <div className="flex items-center gap-6 text-sm">
              <span className="text-slate-400">Built with</span>
              <div className="flex items-center gap-3">
                <span className="px-3 py-1 bg-slate-800 rounded-md text-slate-300">Laravel</span>
                <span className="px-3 py-1 bg-slate-800 rounded-md text-slate-300">Next.js</span>
                <span className="px-3 py-1 bg-slate-800 rounded-md text-slate-300">PostgreSQL</span>
              </div>
            </div>
          </div>
        </motion.div>
      </div>

      {/* Decorative Background */}
      <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-sky-500 to-transparent opacity-30" />
    </footer>
  );
}

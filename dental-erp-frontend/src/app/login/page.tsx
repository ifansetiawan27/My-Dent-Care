'use client';

import { useState } from 'react';
import { motion } from 'framer-motion';
import { Mail, Lock, ArrowRight, Eye, EyeOff, ShieldCheck, Building2, CheckCircle2 } from 'lucide-react';
import Link from 'next/link';
import { Canvas } from '@react-three/fiber';
import { OrbitControls, Sphere, MeshDistortMaterial } from '@react-three/drei';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      const response = await fetch('http://108.136.48.83:8080/api/v1/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
          email,
          password,
          remember: rememberMe,
        }),
      });

      if (response.ok) {
        const data = await response.json();
        localStorage.setItem('auth_token', data.token);
        window.location.href = '/dashboard';
      } else {
        const errorData = await response.json();
        setError(errorData.message || 'Invalid email or password');
      }
    } catch (err) {
      setError('Unable to connect to server. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-teal-50 flex items-center justify-center p-4 relative overflow-hidden">
      {/* Animated Background Elements */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-1/4 -right-1/4 w-[600px] h-[600px] bg-gradient-to-br from-sky-300/20 to-teal-300/20 rounded-full blur-3xl animate-blob" />
        <div className="absolute -bottom-1/4 -left-1/4 w-[600px] h-[600px] bg-gradient-to-tr from-teal-300/20 to-cyan-300/20 rounded-full blur-3xl animate-blob animation-delay-2000" />
      </div>

      <div className="w-full max-w-6xl mx-auto relative z-10">
        <div className="grid lg:grid-cols-2 gap-8 items-center">
          {/* Left Side - Branding & 3D Visual */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8 }}
            className="hidden lg:block"
          >
            {/* Logo */}
            <Link href="/" className="inline-flex items-center space-x-3 mb-8">
              <div className="w-12 h-12 bg-gradient-to-br from-sky-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
                <ShieldCheck className="w-7 h-7 text-white" />
              </div>
              <span className="text-2xl font-bold text-slate-800">My Dent Care</span>
            </Link>

            {/* Headline */}
            <h1 className="text-4xl lg:text-5xl font-bold text-slate-900 mb-6 leading-tight">
              Welcome Back to Your{' '}
              <span className="bg-gradient-to-r from-sky-500 to-teal-500 bg-clip-text text-transparent">
                Dental ERP
              </span>
            </h1>

            <p className="text-lg text-slate-600 mb-8">
              Manage your dental clinics with enterprise-grade tools. Access your dashboard to oversee appointments, patient records, inventory, and analytics.
            </p>

            {/* Trust Indicators */}
            <div className="space-y-4 mb-12">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                  <CheckCircle2 className="w-6 h-6 text-teal-600" />
                </div>
                <div>
                  <p className="font-semibold text-slate-800">SATUSEHAT Integrated</p>
                  <p className="text-sm text-slate-600">Compliant with Indonesian health regulations</p>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center">
                  <Building2 className="w-6 h-6 text-sky-600" />
                </div>
                <div>
                  <p className="font-semibold text-slate-800">Multi-Branch Management</p>
                  <p className="text-sm text-slate-600">Control all your clinics from one place</p>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                  <ShieldCheck className="w-6 h-6 text-purple-600" />
                </div>
                <div>
                  <p className="font-semibold text-slate-800">SSL Encrypted</p>
                  <p className="text-sm text-slate-600">Your data is secure with enterprise-grade encryption</p>
                </div>
              </div>
            </div>

            {/* 3D Element */}
            <div className="relative h-64 w-full">
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="w-48 h-48 bg-gradient-radial from-sky-400/20 via-teal-400/10 to-transparent rounded-full blur-2xl animate-glow" />
              </div>
              <Canvas camera={{ position: [0, 0, 4], fov: 60 }}>
                <ambientLight intensity={0.5} />
                <directionalLight position={[10, 10, 5]} intensity={1} />
                <Sphere args={[1, 100, 200]} scale={1.5}>
                  <MeshDistortMaterial
                    color="#0ea5e9"
                    attach="material"
                    distort={0.4}
                    speed={1.5}
                    roughness={0.2}
                  />
                </Sphere>
                <OrbitControls enableZoom={false} autoRotate autoRotateSpeed={0.5} />
              </Canvas>
            </div>
          </motion.div>

          {/* Right Side - Login Form */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8 }}
          >
            {/* Mobile Logo */}
            <Link href="/" className="lg:hidden inline-flex items-center space-x-3 mb-8">
              <div className="w-10 h-10 bg-gradient-to-br from-sky-500 to-teal-500 rounded-xl flex items-center justify-center">
                <ShieldCheck className="w-6 h-6 text-white" />
              </div>
              <span className="text-xl font-bold text-slate-800">My Dent Care</span>
            </Link>

            {/* Login Card */}
            <div className="bg-white/80 backdrop-blur-xl rounded-3xl p-8 lg:p-10 shadow-2xl border border-white/20">
              {/* Header */}
              <div className="mb-8">
                <h2 className="text-3xl font-bold text-slate-900 mb-2">
                  Sign In
                </h2>
                <p className="text-slate-600">
                  Access your dental clinic dashboard
                </p>
              </div>

              {/* Error Message */}
              {error && (
                <motion.div
                  initial={{ opacity: 0, y: -10 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start space-x-3"
                >
                  <div className="flex-shrink-0">
                    <svg className="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                    </svg>
                  </div>
                  <div className="flex-1">
                    <p className="text-sm font-medium text-red-800">{error}</p>
                  </div>
                </motion.div>
              )}

              {/* Login Form */}
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Email Input */}
                <div className="space-y-2">
                  <label
                    htmlFor="email"
                    className="block text-sm font-semibold text-slate-700"
                  >
                    Email Address
                  </label>
                  <div className="relative">
                    <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                      id="email"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      required
                      className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                      placeholder="doctor@clinic.com"
                      disabled={isLoading}
                    />
                  </div>
                </div>

                {/* Password Input */}
                <div className="space-y-2">
                  <label
                    htmlFor="password"
                    className="block text-sm font-semibold text-slate-700"
                  >
                    Password
                  </label>
                  <div className="relative">
                    <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required
                      className="w-full pl-12 pr-14 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all text-slate-900 placeholder:text-slate-400"
                      placeholder="••••••••"
                      disabled={isLoading}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                      disabled={isLoading}
                    >
                      {showPassword ? (
                        <EyeOff className="w-5 h-5" />
                      ) : (
                        <Eye className="w-5 h-5" />
                      )}
                    </button>
                  </div>
                </div>

                {/* Remember Me & Forgot Password */}
                <div className="flex items-center justify-between">
                  <label className="flex items-center space-x-2 cursor-pointer group">
                    <input
                      type="checkbox"
                      checked={rememberMe}
                      onChange={(e) => setRememberMe(e.target.checked)}
                      className="w-4 h-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500 focus:ring-offset-0"
                      disabled={isLoading}
                    />
                    <span className="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">
                      Remember me
                    </span>
                  </label>
                  <Link
                    href="/forgot-password"
                    className="text-sm font-semibold text-sky-600 hover:text-sky-700 transition-colors"
                  >
                    Forgot password?
                  </Link>
                </div>

                {/* Sign In Button */}
                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full bg-gradient-to-r from-sky-500 to-teal-500 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl hover:shadow-sky-500/30 transition-all duration-300 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed group"
                >
                  <span>{isLoading ? 'Signing in...' : 'Sign In to Dashboard'}</span>
                  {!isLoading && (
                    <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                  )}
                </button>
              </form>

              {/* Divider */}
              <div className="relative my-8">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-slate-200"></div>
                </div>
                <div className="relative flex justify-center text-sm">
                  <span className="px-4 bg-white/80 text-slate-500">
                    New to My Dent Care?
                  </span>
                </div>
              </div>

              {/* Sign Up Link */}
              <Link
                href="/#pricing"
                className="block w-full text-center py-4 border-2 border-sky-500 text-sky-600 rounded-xl font-bold hover:bg-sky-500 hover:text-white transition-all duration-300"
              >
                Start 30-Day Free Trial
              </Link>

              {/* Back to Home */}
              <div className="mt-6 text-center">
                <Link
                  href="/"
                  className="text-sm text-slate-600 hover:text-slate-900 transition-colors"
                >
                  ← Back to Homepage
                </Link>
              </div>
            </div>

            {/* Security Badge */}
            <div className="mt-6 text-center">
              <p className="text-xs text-slate-500 flex items-center justify-center space-x-2">
                <ShieldCheck className="w-4 h-4" />
                <span>Secured with Laravel Sanctum • 256-bit SSL Encryption</span>
              </p>
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  );
}

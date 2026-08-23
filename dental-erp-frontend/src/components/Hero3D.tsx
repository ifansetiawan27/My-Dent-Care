// src/components/Hero3D.tsx
'use client';

import { useRef, useState, Suspense } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, PerspectiveCamera, Environment } from '@react-three/drei';
import { motion } from 'framer-motion';
import * as THREE from 'three';
import { Button } from '@/components/ui/button';

/**
 * 3D Tooth Model Component
 * Represents a clean, glowing tooth with smooth geometry
 */
function ToothModel() {
  const meshRef = useRef<THREE.Mesh>(null);
  const [hovered, setHovered] = useState(false);

  // Animate rotation and hover effect
  useFrame((state) => {
    if (meshRef.current) {
      // Gentle rotation
      meshRef.current.rotation.y += 0.005;
      
      // Hover scale effect
      const targetScale = hovered ? 1.1 : 1;
      meshRef.current.scale.lerp(
        new THREE.Vector3(targetScale, targetScale, targetScale),
        0.1
      );
      
      // Floating animation
      meshRef.current.position.y = Math.sin(state.clock.elapsedTime) * 0.2;
    }
  });

  return (
    <mesh
      ref={meshRef}
      onPointerOver={() => setHovered(true)}
      onPointerOut={() => setHovered(false)}
      castShadow
      receiveShadow
    >
      {/* Tooth shape: smooth rounded cylinder */}
      <capsuleGeometry args={[0.8, 2, 32, 64]} />
      
      {/* Clean white material with subtle glow */}
      <meshPhysicalMaterial
        color="#ffffff"
        roughness={0.2}
        metalness={0.1}
        clearcoat={1}
        clearcoatRoughness={0.1}
        emissive="#e0f2ff"
        emissiveIntensity={hovered ? 0.3 : 0.15}
        transparent
        opacity={0.95}
      />
    </mesh>
  );
}

/**
 * 3D Scene Component
 */
function Scene() {
  return (
    <>
      {/* Camera */}
      <PerspectiveCamera makeDefault position={[0, 0, 8]} fov={50} />
      
      {/* Lighting */}
      <ambientLight intensity={0.5} />
      <directionalLight
        position={[10, 10, 5]}
        intensity={1}
        castShadow
        shadow-mapSize-width={1024}
        shadow-mapSize-height={1024}
      />
      <pointLight position={[-10, -10, -5]} intensity={0.5} color="#38bdf8" />
      <spotLight
        position={[0, 10, 0]}
        angle={0.3}
        penumbra={1}
        intensity={1}
        castShadow
        color="#ffffff"
      />
      
      {/* Environment for realistic reflections */}
      <Environment preset="city" />
      
      {/* 3D Tooth Model */}
      <ToothModel />
      
      {/* Orbit Controls */}
      <OrbitControls
        enableZoom={false}
        enablePan={false}
        minPolarAngle={Math.PI / 3}
        maxPolarAngle={Math.PI / 1.5}
        autoRotate
        autoRotateSpeed={0.5}
      />
    </>
  );
}

/**
 * Loading fallback component for Canvas
 * Must use THREE.js objects, not HTML
 */
function CanvasLoader() {
  return (
    <mesh>
      <boxGeometry args={[1, 1, 1]} />
      <meshStandardMaterial color="#0ea5e9" wireframe />
    </mesh>
  );
}

/**
 * Hero3D Component
 * 
 * Features:
 * - Interactive 3D tooth visualization using React Three Fiber
 * - Glassmorphism UI overlay with Framer Motion animations
 * - Fully accessible with ARIA labels
 * - TypeScript typed
 * - Responsive design
 */
export default function Hero3D() {
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2,
        delayChildren: 0.3,
      },
    },
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        type: 'spring' as const,
        stiffness: 100,
        damping: 10,
      },
    },
  };

  return (
    <section
      className="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-gradient-to-br from-purple-600 via-blue-600 to-cyan-500"
      aria-label="Hero section with 3D interactive dental visualization"
    >
      {/* Animated Background Shapes */}
      <motion.div
        animate={{
          scale: [1, 1.2, 1],
          rotate: [0, 90, 0],
        }}
        transition={{
          duration: 20,
          repeat: Infinity,
          ease: "linear",
        }}
        className="absolute -left-1/4 top-0 h-96 w-96 rounded-full bg-pink-500 opacity-30 blur-3xl"
      />
      <motion.div
        animate={{
          scale: [1.2, 1, 1.2],
          rotate: [0, -90, 0],
        }}
        transition={{
          duration: 25,
          repeat: Infinity,
          ease: "linear",
        }}
        className="absolute -right-1/4 bottom-0 h-96 w-96 rounded-full bg-yellow-400 opacity-30 blur-3xl"
      />
      <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-gradient-to-r from-purple-400 to-pink-400 opacity-20 blur-3xl" />

      <div className="container relative z-10 mx-auto grid h-full min-h-screen grid-cols-1 items-center gap-12 px-4 py-20 lg:grid-cols-2">
        
        {/* Left: Text Content with Glassmorphism */}
        <motion.div
          className="flex flex-col items-start justify-center space-y-6"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          <motion.div
            variants={itemVariants}
            className="inline-block rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 px-6 py-2 text-sm font-bold text-white shadow-lg"
          >
            ✨ Enterprise Dental ERP Platform
          </motion.div>

          <motion.h1
            variants={itemVariants}
            className="text-5xl font-bold leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl drop-shadow-2xl"
          >
            My Dent Care
            <span className="block bg-gradient-to-r from-yellow-300 via-pink-300 to-cyan-300 bg-clip-text text-transparent">
              Dental Excellence
            </span>
          </motion.h1>

          <motion.p
            variants={itemVariants}
            className="max-w-lg text-lg text-white/90 drop-shadow-lg"
          >
            Modern ERP platform untuk klinik gigi multi-cabang. 
            Kelola pasien, appointment, treatment, inventory, dan keuangan dalam satu sistem terintegrasi.
          </motion.p>

          <motion.div
            variants={itemVariants}
            className="flex flex-wrap gap-4"
          >
            <Button
              size="lg"
              className="bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 px-8 py-6 text-lg font-bold text-white shadow-2xl transition-all hover:scale-105 hover:shadow-yellow-500/50"
            >
              🚀 Mulai Free Trial 30 Hari
            </Button>
            
            <Button
              size="lg"
              className="border-2 border-white bg-white/20 backdrop-blur-lg px-8 py-6 text-lg font-bold text-white hover:bg-white/30"
            >
              📺 Lihat Demo
            </Button>
          </motion.div>

          <motion.div
            variants={itemVariants}
            className="flex items-center gap-8 pt-6"
          >
            <div className="flex flex-col">
              <span className="text-3xl font-bold text-yellow-300 drop-shadow-lg">300K</span>
              <span className="text-sm text-white/80">per cabang/bulan</span>
            </div>
            
            <div className="h-12 w-px bg-white/30" />
            
            <div className="flex flex-col">
              <span className="text-3xl font-bold text-pink-300 drop-shadow-lg">∞</span>
              <span className="text-sm text-white/80">Unlimited Users</span>
            </div>
            
            <div className="h-12 w-px bg-white/30" />
            
            <div className="flex flex-col">
              <span className="text-3xl font-bold text-cyan-300 drop-shadow-lg">100%</span>
              <span className="text-sm text-white/80">Full Features</span>
            </div>
          </motion.div>
        </motion.div>

        {/* Right: 3D Canvas */}
        <div className="relative h-[500px] w-full lg:h-[600px]">
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.8, delay: 0.5 }}
            className="h-full w-full rounded-3xl bg-white/40 p-8 shadow-2xl backdrop-blur-md"
            role="img"
            aria-label="Interactive 3D dental tooth visualization"
          >
            <div className="h-full w-full rounded-2xl overflow-hidden">
              <Canvas
                gl={{
                  alpha: true,
                  antialias: true,
                  powerPreference: 'high-performance',
                }}
                dpr={[1, 2]}
                style={{ width: '100%', height: '100%' }}
              >
                <Suspense fallback={<CanvasLoader />}>
                  <Scene />
                </Suspense>
              </Canvas>
            </div>
          </motion.div>

          {/* Floating feature cards */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 1, duration: 0.5 }}
            className="absolute -left-4 top-1/4 rounded-xl bg-white/90 p-4 shadow-lg backdrop-blur-sm"
          >
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                <svg className="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span className="text-sm font-semibold text-slate-700">99% Uptime</span>
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 1.2, duration: 0.5 }}
            className="absolute -right-4 bottom-1/4 rounded-xl bg-white/90 p-4 shadow-lg backdrop-blur-sm"
          >
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                <svg className="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <span className="text-sm font-semibold text-slate-700">ISO 27001</span>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}

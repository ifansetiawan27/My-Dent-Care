'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { motion } from 'framer-motion';
import {
  LayoutDashboard,
  Calendar,
  Users,
  FileText,
  Package,
  DollarSign,
  BarChart3,
  Settings,
  LogOut,
  ChevronLeft,
  ChevronRight,
  Bell,
  Search,
  User,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const menuItems = [
  {
    title: 'Dashboard',
    icon: LayoutDashboard,
    href: '/dashboard',
  },
  {
    title: 'Appointments',
    icon: Calendar,
    href: '/dashboard/appointments',
  },
  {
    title: 'Patients',
    icon: Users,
    href: '/dashboard/patients',
  },
  {
    title: 'Treatments',
    icon: FileText,
    href: '/dashboard/treatments',
  },
  {
    title: 'Inventory',
    icon: Package,
    href: '/dashboard/inventory',
  },
  {
    title: 'Billing',
    icon: DollarSign,
    href: '/dashboard/billing',
  },
  {
    title: 'Reports',
    icon: BarChart3,
    href: '/dashboard/reports',
  },
  {
    title: 'Settings',
    icon: Settings,
    href: '/dashboard/settings',
  },
];

interface DashboardLayoutProps {
  children: React.ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);
  const pathname = usePathname();

  return (
    <div className="flex h-screen bg-slate-50">
      {/* Sidebar */}
      <motion.aside
        animate={{ width: collapsed ? 80 : 280 }}
        className="bg-white border-r border-slate-200 flex flex-col"
      >
        {/* Logo */}
        <div className="h-16 flex items-center justify-between px-4 border-b border-slate-200">
          {!collapsed && (
            <h1 className="text-xl font-bold text-sky-600">My Dent Care</h1>
          )}
          {collapsed && (
            <span className="text-xl font-bold text-sky-600">MDC</span>
          )}
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setCollapsed(!collapsed)}
            className="ml-auto"
          >
            {collapsed ? <ChevronRight className="w-4 h-4" /> : <ChevronLeft className="w-4 h-4" />}
          </Button>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto py-4">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const isActive = pathname === item.href;

            return (
              <Link key={item.href} href={item.href}>
                <motion.div
                  whileHover={{ x: 4 }}
                  className={`flex items-center gap-3 px-4 py-3 mx-2 rounded-lg transition-colors ${
                    isActive
                      ? 'bg-sky-50 text-sky-600'
                      : 'text-slate-600 hover:bg-slate-50'
                  }`}
                >
                  <Icon className="w-5 h-5 flex-shrink-0" />
                  {!collapsed && <span className="font-medium">{item.title}</span>}
                </motion.div>
              </Link>
            );
          })}
        </nav>

        {/* Logout */}
        <div className="p-4 border-t border-slate-200">
          <Button
            variant="ghost"
            className="w-full justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
          >
            <LogOut className="w-5 h-5 mr-3" />
            {!collapsed && <span>Logout</span>}
          </Button>
        </div>
      </motion.aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Header */}
        <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
          {/* Search */}
          <div className="flex-1 max-w-lg">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
              <Input
                placeholder="Search patients, appointments..."
                className="pl-10"
              />
            </div>
          </div>

          {/* Right Side */}
          <div className="flex items-center gap-4">
            {/* Notifications */}
            <Button variant="ghost" size="sm" className="relative">
              <Bell className="w-5 h-5" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full" />
            </Button>

            {/* User Menu */}
            <DropdownMenu>
              <DropdownMenuTrigger>
                <Button variant="ghost" className="flex items-center gap-2">
                  <Avatar className="w-8 h-8">
                    <AvatarImage src="/avatar.jpg" />
                    <AvatarFallback>DR</AvatarFallback>
                  </Avatar>
                  <div className="text-left hidden md:block">
                    <p className="text-sm font-medium">Dr. John Doe</p>
                    <p className="text-xs text-slate-500">Dentist</p>
                  </div>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel>My Account</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem>
                  <User className="w-4 h-4 mr-2" />
                  Profile
                </DropdownMenuItem>
                <DropdownMenuItem>
                  <Settings className="w-4 h-4 mr-2" />
                  Settings
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem className="text-red-600">
                  <LogOut className="w-4 h-4 mr-2" />
                  Logout
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto p-6">
          {children}
        </main>
      </div>
    </div>
  );
}

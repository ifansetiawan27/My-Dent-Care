'use client';

import { 
  Users, 
  Calendar, 
  DollarSign, 
  TrendingUp,
  Activity,
  Clock,
  CheckCircle,
  XCircle,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  AreaChart,
  Area,
  BarChart,
  Bar,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';

// Mock data
const statsCards = [
  {
    title: 'Total Patients',
    value: '2,543',
    change: '+12.5%',
    icon: Users,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
  },
  {
    title: "Today's Appointments",
    value: '24',
    change: '+5',
    icon: Calendar,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
  },
  {
    title: 'Revenue (This Month)',
    value: 'Rp 45.2M',
    change: '+8.2%',
    icon: DollarSign,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
  },
  {
    title: 'Success Rate',
    value: '94.6%',
    change: '+2.1%',
    icon: TrendingUp,
    color: 'text-orange-600',
    bgColor: 'bg-orange-50',
  },
];

const revenueData = [
  { month: 'Jan', revenue: 35000000, patients: 120 },
  { month: 'Feb', revenue: 38000000, patients: 135 },
  { month: 'Mar', revenue: 42000000, patients: 150 },
  { month: 'Apr', revenue: 39000000, patients: 142 },
  { month: 'May', revenue: 45000000, patients: 165 },
  { month: 'Jun', revenue: 48000000, patients: 178 },
];

const treatmentData = [
  { name: 'Scaling', value: 450, color: '#0ea5e9' },
  { name: 'Filling', value: 320, color: '#8b5cf6' },
  { name: 'Root Canal', value: 180, color: '#f59e0b' },
  { name: 'Extraction', value: 120, color: '#ef4444' },
  { name: 'Crown', value: 90, color: '#10b981' },
];

const appointmentStatusData = [
  { status: 'Completed', value: 85, color: '#10b981' },
  { status: 'Scheduled', value: 42, color: '#0ea5e9' },
  { status: 'Cancelled', value: 8, color: '#ef4444' },
  { status: 'No Show', value: 5, color: '#64748b' },
];

const upcomingAppointments = [
  { id: 1, patient: 'John Doe', time: '09:00', type: 'Scaling', doctor: 'Dr. Smith' },
  { id: 2, patient: 'Jane Smith', time: '10:30', type: 'Filling', doctor: 'Dr. Johnson' },
  { id: 3, patient: 'Bob Wilson', time: '11:00', type: 'Check-up', doctor: 'Dr. Smith' },
  { id: 4, patient: 'Alice Brown', time: '13:30', type: 'Root Canal', doctor: 'Dr. Davis' },
  { id: 5, patient: 'Charlie Green', time: '14:00', type: 'Crown', doctor: 'Dr. Johnson' },
];

export default function DashboardPage() {
  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-slate-900">Dashboard</h1>
        <p className="text-slate-600 mt-1">Welcome back! Here's what's happening today.</p>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statsCards.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <Card key={index}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm text-slate-600 mb-1">{stat.title}</p>
                    <h3 className="text-2xl font-bold text-slate-900">{stat.value}</h3>
                    <p className="text-sm text-green-600 mt-1">{stat.change} from last month</p>
                  </div>
                  <div className={`${stat.bgColor} p-3 rounded-lg`}>
                    <Icon className={`w-6 h-6 ${stat.color}`} />
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Chart */}
        <Card>
          <CardHeader>
            <CardTitle>Revenue Overview</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <AreaChart data={revenueData}>
                <defs>
                  <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#0ea5e9" stopOpacity={0.8}/>
                    <stop offset="95%" stopColor="#0ea5e9" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip formatter={(value) => `Rp ${(value as number / 1000000).toFixed(1)}M`} />
                <Area type="monotone" dataKey="revenue" stroke="#0ea5e9" fillOpacity={1} fill="url(#colorRevenue)" />
              </AreaChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Treatment Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Treatment Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={treatmentData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${((percent || 0) * 100).toFixed(0)}%`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {treatmentData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Bottom Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Upcoming Appointments */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="flex items-center justify-between">
              <span>Today's Appointments</span>
              <span className="text-sm font-normal text-slate-600">24 scheduled</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {upcomingAppointments.map((appointment) => (
                <div key={appointment.id} className="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                  <div className="flex items-center gap-4">
                    <div className="flex items-center justify-center w-12 h-12 bg-sky-100 rounded-lg">
                      <Clock className="w-6 h-6 text-sky-600" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-slate-900">{appointment.patient}</h4>
                      <p className="text-sm text-slate-600">{appointment.type}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="font-semibold text-slate-900">{appointment.time}</p>
                    <p className="text-sm text-slate-600">{appointment.doctor}</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Appointment Status */}
        <Card>
          <CardHeader>
            <CardTitle>Appointment Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {appointmentStatusData.map((item, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="w-3 h-3 rounded-full" style={{ backgroundColor: item.color }} />
                    <span className="text-sm text-slate-600">{item.status}</span>
                  </div>
                  <span className="font-semibold text-slate-900">{item.value}</span>
                </div>
              ))}
            </div>
            
            <div className="mt-6">
              <ResponsiveContainer width="100%" height={200}>
                <PieChart>
                  <Pie
                    data={appointmentStatusData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    fill="#8884d8"
                    paddingAngle={5}
                    dataKey="value"
                  >
                    {appointmentStatusData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

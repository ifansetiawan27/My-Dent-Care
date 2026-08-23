'use client';

import { useState } from 'react';
import { 
  Calendar as CalendarIcon, 
  Plus, 
  Search, 
  Filter,
  Clock,
  User,
  MapPin,
  MoreVertical,
  CheckCircle,
  XCircle,
  Eye,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

// Mock data
const appointments = [
  {
    id: 1,
    patient: 'John Doe',
    phone: '+62 812-3456-7890',
    doctor: 'Dr. Sarah Smith',
    date: '2026-08-23',
    time: '09:00',
    duration: '30 min',
    type: 'Scaling & Polishing',
    status: 'confirmed',
    notes: 'Regular checkup',
  },
  {
    id: 2,
    patient: 'Jane Smith',
    phone: '+62 813-4567-8901',
    doctor: 'Dr. Michael Johnson',
    date: '2026-08-23',
    time: '10:30',
    duration: '60 min',
    type: 'Root Canal Treatment',
    status: 'confirmed',
    notes: 'Follow-up appointment',
  },
  {
    id: 3,
    patient: 'Bob Wilson',
    phone: '+62 814-5678-9012',
    doctor: 'Dr. Sarah Smith',
    date: '2026-08-23',
    time: '11:00',
    duration: '45 min',
    type: 'Tooth Filling',
    status: 'waiting',
    notes: 'New patient',
  },
  {
    id: 4,
    patient: 'Alice Brown',
    phone: '+62 815-6789-0123',
    doctor: 'Dr. Emily Davis',
    date: '2026-08-23',
    time: '13:30',
    duration: '90 min',
    type: 'Crown Installation',
    status: 'confirmed',
    notes: 'Final appointment',
  },
  {
    id: 5,
    patient: 'Charlie Green',
    phone: '+62 816-7890-1234',
    doctor: 'Dr. Michael Johnson',
    date: '2026-08-23',
    time: '14:00',
    duration: '30 min',
    type: 'Consultation',
    status: 'cancelled',
    notes: 'Patient requested reschedule',
  },
];

const statusColors = {
  confirmed: 'bg-green-100 text-green-700 border-green-300',
  waiting: 'bg-yellow-100 text-yellow-700 border-yellow-300',
  completed: 'bg-blue-100 text-blue-700 border-blue-300',
  cancelled: 'bg-red-100 text-red-700 border-red-300',
};

export default function AppointmentsPage() {
  const [view, setView] = useState<'list' | 'calendar'>('list');
  const [selectedAppointment, setSelectedAppointment] = useState<typeof appointments[0] | null>(null);

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-slate-900">Appointments</h1>
          <p className="text-slate-600 mt-1">Manage patient appointments and schedule</p>
        </div>
        <div className="flex items-center gap-3">
          <Button variant="outline" className="gap-2">
            <Filter className="w-4 h-4" />
            Filter
          </Button>
          <Button className="gap-2 bg-sky-600 hover:bg-sky-700">
            <Plus className="w-4 h-4" />
            New Appointment
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600">Today's Total</p>
                <h3 className="text-2xl font-bold text-slate-900">24</h3>
              </div>
              <div className="bg-blue-50 p-3 rounded-lg">
                <CalendarIcon className="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600">Confirmed</p>
                <h3 className="text-2xl font-bold text-green-600">18</h3>
              </div>
              <div className="bg-green-50 p-3 rounded-lg">
                <CheckCircle className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600">Waiting</p>
                <h3 className="text-2xl font-bold text-yellow-600">4</h3>
              </div>
              <div className="bg-yellow-50 p-3 rounded-lg">
                <Clock className="w-6 h-6 text-yellow-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600">Cancelled</p>
                <h3 className="text-2xl font-bold text-red-600">2</h3>
              </div>
              <div className="bg-red-50 p-3 rounded-lg">
                <XCircle className="w-6 h-6 text-red-600" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Search and View Toggle */}
      <Card>
        <CardContent className="p-6">
          <div className="flex items-center justify-between">
            <div className="flex-1 max-w-md">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                <Input
                  placeholder="Search appointments..."
                  className="pl-10"
                />
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Button 
                variant={view === 'list' ? 'default' : 'outline'}
                onClick={() => setView('list')}
              >
                List View
              </Button>
              <Button 
                variant={view === 'calendar' ? 'default' : 'outline'}
                onClick={() => setView('calendar')}
              >
                Calendar View
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Appointments List */}
      <Card>
        <CardHeader>
          <CardTitle>Today's Schedule - {new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {appointments.map((appointment) => (
              <div
                key={appointment.id}
                className="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors"
              >
                <div className="flex items-center gap-4 flex-1">
                  {/* Time */}
                  <div className="flex flex-col items-center justify-center w-20 h-20 bg-white rounded-lg border border-slate-200">
                    <span className="text-2xl font-bold text-slate-900">{appointment.time.split(':')[0]}</span>
                    <span className="text-sm text-slate-600">{appointment.time.split(':')[1]}</span>
                  </div>

                  {/* Patient Info */}
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h4 className="font-semibold text-slate-900">{appointment.patient}</h4>
                      <Badge className={statusColors[appointment.status as keyof typeof statusColors]}>
                        {appointment.status}
                      </Badge>
                    </div>
                    <div className="grid grid-cols-2 gap-2 text-sm text-slate-600">
                      <div className="flex items-center gap-2">
                        <User className="w-4 h-4" />
                        {appointment.doctor}
                      </div>
                      <div className="flex items-center gap-2">
                        <Clock className="w-4 h-4" />
                        {appointment.duration}
                      </div>
                      <div className="flex items-center gap-2">
                        <MapPin className="w-4 h-4" />
                        {appointment.type}
                      </div>
                    </div>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2">
                  <Dialog>
                    <DialogTrigger>
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={() => setSelectedAppointment(appointment)}
                      >
                        <Eye className="w-4 h-4 mr-2" />
                        View Details
                      </Button>
                    </DialogTrigger>
                    <DialogContent className="max-w-2xl">
                      <DialogHeader>
                        <DialogTitle>Appointment Details</DialogTitle>
                      </DialogHeader>
                      {selectedAppointment && (
                        <div className="space-y-4">
                          <div className="grid grid-cols-2 gap-4">
                            <div>
                              <label className="text-sm font-medium text-slate-600">Patient Name</label>
                              <p className="text-lg font-semibold">{selectedAppointment.patient}</p>
                            </div>
                            <div>
                              <label className="text-sm font-medium text-slate-600">Phone</label>
                              <p className="text-lg font-semibold">{selectedAppointment.phone}</p>
                            </div>
                            <div>
                              <label className="text-sm font-medium text-slate-600">Doctor</label>
                              <p className="text-lg font-semibold">{selectedAppointment.doctor}</p>
                            </div>
                            <div>
                              <label className="text-sm font-medium text-slate-600">Date & Time</label>
                              <p className="text-lg font-semibold">{selectedAppointment.date} at {selectedAppointment.time}</p>
                            </div>
                            <div>
                              <label className="text-sm font-medium text-slate-600">Treatment Type</label>
                              <p className="text-lg font-semibold">{selectedAppointment.type}</p>
                            </div>
                            <div>
                              <label className="text-sm font-medium text-slate-600">Duration</label>
                              <p className="text-lg font-semibold">{selectedAppointment.duration}</p>
                            </div>
                          </div>
                          <div>
                            <label className="text-sm font-medium text-slate-600">Notes</label>
                            <p className="text-sm text-slate-700 bg-slate-50 p-3 rounded-lg mt-1">{selectedAppointment.notes}</p>
                          </div>
                          <div className="flex gap-2 pt-4">
                            <Button className="flex-1 bg-green-600 hover:bg-green-700">
                              <CheckCircle className="w-4 h-4 mr-2" />
                              Confirm
                            </Button>
                            <Button variant="outline" className="flex-1">
                              Reschedule
                            </Button>
                            <Button variant="destructive" className="flex-1">
                              <XCircle className="w-4 h-4 mr-2" />
                              Cancel
                            </Button>
                          </div>
                        </div>
                      )}
                    </DialogContent>
                  </Dialog>

                  <DropdownMenu>
                    <DropdownMenuTrigger>
                      <Button variant="ghost" size="sm">
                        <MoreVertical className="w-4 h-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem>Edit</DropdownMenuItem>
                      <DropdownMenuItem>Reschedule</DropdownMenuItem>
                      <DropdownMenuItem>View Medical Record</DropdownMenuItem>
                      <DropdownMenuItem className="text-red-600">Cancel</DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

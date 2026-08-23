'use client';

import { useState } from 'react';
import { X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

// Dental notation system (FDI/ISO notation)
const teeth = {
  upperRight: [18, 17, 16, 15, 14, 13, 12, 11],
  upperLeft: [21, 22, 23, 24, 25, 26, 27, 28],
  lowerLeft: [31, 32, 33, 34, 35, 36, 37, 38],
  lowerRight: [48, 47, 46, 45, 44, 43, 42, 41],
};

const conditionColors = {
  healthy: 'bg-green-50 border-green-500',
  caries: 'bg-red-100 border-red-500',
  filled: 'bg-blue-100 border-blue-500',
  missing: 'bg-gray-100 border-gray-400',
  crown: 'bg-yellow-100 border-yellow-500',
  implant: 'bg-purple-100 border-purple-500',
  rootCanal: 'bg-orange-100 border-orange-500',
};

const conditionLabels = {
  healthy: { label: 'Healthy', color: 'bg-green-500' },
  caries: { label: 'Caries', color: 'bg-red-500' },
  filled: { label: 'Filled', color: 'bg-blue-500' },
  missing: { label: 'Missing', color: 'bg-gray-400' },
  crown: { label: 'Crown', color: 'bg-yellow-500' },
  implant: { label: 'Implant', color: 'bg-purple-500' },
  rootCanal: { label: 'Root Canal', color: 'bg-orange-500' },
};

interface ToothCondition {
  [key: number]: keyof typeof conditionColors;
}

interface MedicalRecordModalProps {
  isOpen: boolean;
  onClose: () => void;
  patientName: string;
  patientId: string;
}

export default function MedicalRecordModal({
  isOpen,
  onClose,
  patientName,
  patientId,
}: MedicalRecordModalProps) {
  // Mock data - tooth conditions
  const [toothConditions, setToothConditions] = useState<ToothCondition>({
    16: 'caries',
    17: 'filled',
    26: 'crown',
    36: 'missing',
    37: 'rootCanal',
    46: 'filled',
    47: 'caries',
  });

  const [selectedTooth, setSelectedTooth] = useState<number | null>(null);
  const [selectedCondition, setSelectedCondition] = useState<keyof typeof conditionColors>('healthy');

  if (!isOpen) return null;

  const handleToothClick = (toothNumber: number) => {
    setSelectedTooth(toothNumber);
  };

  const handleConditionChange = (condition: keyof typeof conditionColors) => {
    if (selectedTooth) {
      setToothConditions((prev) => ({
        ...prev,
        [selectedTooth]: condition,
      }));
      setSelectedCondition(condition);
    }
  };

  const ToothElement = ({ number }: { number: number }) => {
    const condition = toothConditions[number] || 'healthy';
    const isSelected = selectedTooth === number;

    return (
      <button
        onClick={() => handleToothClick(number)}
        className={`
          relative w-10 h-14 rounded-lg border-2 transition-all
          ${conditionColors[condition]}
          ${isSelected ? 'ring-4 ring-sky-500 scale-110' : 'hover:scale-105'}
          flex flex-col items-center justify-center
        `}
      >
        <span className="text-xs font-bold">{number}</span>
        {condition !== 'healthy' && condition !== 'missing' && (
          <div className="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white" />
        )}
      </button>
    );
  };

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="sticky top-0 bg-white border-b border-slate-200 p-6 flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Medical Record - Odontogram</h2>
            <p className="text-slate-600 mt-1">
              {patientName} (ID: {patientId})
            </p>
          </div>
          <Button variant="ghost" size="sm" onClick={onClose}>
            <X className="w-5 h-5" />
          </Button>
        </div>

        <div className="p-6 space-y-6">
          {/* Legend */}
          <Card>
            <CardContent className="p-4">
              <h3 className="text-sm font-semibold text-slate-900 mb-3">Tooth Condition Legend</h3>
              <div className="flex flex-wrap gap-3">
                {Object.entries(conditionLabels).map(([key, { label, color }]) => (
                  <button
                    key={key}
                    onClick={() => handleConditionChange(key as keyof typeof conditionColors)}
                    className={`
                      flex items-center gap-2 px-3 py-2 rounded-lg border-2 transition-all
                      ${selectedCondition === key ? 'border-sky-500 bg-sky-50' : 'border-slate-200 hover:border-slate-300'}
                    `}
                  >
                    <div className={`w-4 h-4 rounded ${color}`} />
                    <span className="text-sm font-medium">{label}</span>
                  </button>
                ))}
              </div>
              {selectedTooth && (
                <p className="text-sm text-slate-600 mt-3">
                  Selected: Tooth #{selectedTooth} - Click a condition above to update
                </p>
              )}
            </CardContent>
          </Card>

          {/* Odontogram */}
          <Card>
            <CardContent className="p-6">
              <div className="space-y-6">
                {/* Upper Teeth */}
                <div className="space-y-2">
                  <p className="text-xs font-semibold text-slate-600 text-center">UPPER</p>
                  
                  {/* Upper Right and Upper Left */}
                  <div className="flex justify-center gap-8">
                    {/* Upper Right */}
                    <div className="flex gap-1 items-end">
                      {teeth.upperRight.map((tooth) => (
                        <ToothElement key={tooth} number={tooth} />
                      ))}
                    </div>

                    {/* Divider */}
                    <div className="w-px bg-slate-300" />

                    {/* Upper Left */}
                    <div className="flex gap-1 items-end">
                      {teeth.upperLeft.map((tooth) => (
                        <ToothElement key={tooth} number={tooth} />
                      ))}
                    </div>
                  </div>
                </div>

                {/* Horizontal Divider */}
                <div className="relative">
                  <div className="absolute inset-0 flex items-center">
                    <div className="w-full border-t-2 border-slate-300" />
                  </div>
                  <div className="relative flex justify-center">
                    <span className="bg-white px-4 text-xs font-semibold text-slate-500">
                      BITE LINE
                    </span>
                  </div>
                </div>

                {/* Lower Teeth */}
                <div className="space-y-2">
                  <div className="flex justify-center gap-8">
                    {/* Lower Left */}
                    <div className="flex gap-1 items-start">
                      {teeth.lowerLeft.map((tooth) => (
                        <ToothElement key={tooth} number={tooth} />
                      ))}
                    </div>

                    {/* Divider */}
                    <div className="w-px bg-slate-300" />

                    {/* Lower Right */}
                    <div className="flex gap-1 items-start">
                      {teeth.lowerRight.map((tooth) => (
                        <ToothElement key={tooth} number={tooth} />
                      ))}
                    </div>
                  </div>
                  <p className="text-xs font-semibold text-slate-600 text-center">LOWER</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Treatment History */}
          <Card>
            <CardContent className="p-6">
              <h3 className="text-lg font-semibold text-slate-900 mb-4">Treatment History</h3>
              <div className="space-y-3">
                {[
                  { date: '2026-08-20', tooth: 17, treatment: 'Filling', doctor: 'Dr. Smith', notes: 'Composite filling on occlusal surface' },
                  { date: '2026-07-15', tooth: 26, treatment: 'Crown Installation', doctor: 'Dr. Johnson', notes: 'Porcelain crown installed' },
                  { date: '2026-06-10', tooth: 37, treatment: 'Root Canal', doctor: 'Dr. Davis', notes: 'Root canal treatment completed' },
                  { date: '2026-05-05', tooth: 16, treatment: 'Diagnosis', doctor: 'Dr. Smith', notes: 'Caries detected, treatment scheduled' },
                ].map((record, index) => (
                  <div key={index} className="flex items-start justify-between p-4 bg-slate-50 rounded-lg">
                    <div className="flex items-start gap-4">
                      <Badge className="bg-sky-100 text-sky-700 border-sky-300">
                        Tooth #{record.tooth}
                      </Badge>
                      <div>
                        <h4 className="font-semibold text-slate-900">{record.treatment}</h4>
                        <p className="text-sm text-slate-600 mt-1">{record.notes}</p>
                        <p className="text-xs text-slate-500 mt-2">{record.doctor}</p>
                      </div>
                    </div>
                    <span className="text-sm text-slate-600">{record.date}</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Actions */}
          <div className="flex gap-3">
            <Button className="flex-1" onClick={onClose}>
              Save Changes
            </Button>
            <Button variant="outline" className="flex-1" onClick={onClose}>
              Cancel
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}

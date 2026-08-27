# Demo Data Guide — My Dent Care

**Untuk:** Super Admin Demo  
**Login:** http://localhost:5173/login  
**Email:** `superadmin@demodental.com` | **Password:** `password123`

---

## 📊 Data Summary

| Modul | Jumlah Data | Status |
|---|---|---|
| Organization | 1 | Demo Dental Clinic Group |
| Branch | 1 | Demo Dental Jakarta Pusat |
| Users | 3 | Super Admin, Doctor, Receptionist |
| Patients | 3 | John Doe, Maria Garcia, Robert Chen |
| Appointments | 8 | 3 completed, 4 scheduled, 1 cancelled |
| Treatments | 5 | Scaling, filling, crown, RCT, extraction |
| Invoices | 5 | 2 paid, 2 sent, 1 draft |
| Inventory Items | 8 | Dental supplies & equipment |
| Pharmacy Items | 6 | Medications & consumables |
| Lab Orders | 3 | Crown, temporary, denture |
| EMR Records | 3 | Full patient encounters |
| Odontograms | 15 | Tooth records across 3 patients |
| Radiology | 3 orders, 2 images, 2 reports | Panoramic, periapical, bitewing |
| CRM Contacts | 3 | Complaint, inquiry, reminder |
| Finance | 10 COA, 2 journal entries | Balanced double-entry |

---

## 👥 Demo Users

| Role | Email | Password | Permissions |
|---|---|---|---|
| **Super Admin** | `superadmin@demodental.com` | `password123` | Full access |
| **Doctor** | `drjane@demodental.com` | `password123` | Clinical access |
| **Receptionist** | `sarah@demodental.com` | `password123` | Front desk access |

---

## 📋 Appointments (8 records)

| # | Patient | Doctor | Date | Time | Status | Reason |
|---|---|---|---|---|---|---|
| 1 | John Doe | Dr. Jane | Yesterday | 09:00 | ✅ Completed | General Checkup |
| 2 | Maria Garcia | Dr. Jane | Yesterday | 10:00 | ✅ Completed | Tooth Extraction |
| 3 | Robert Chen | Dr. Jane | 2 days ago | 14:00 | ✅ Completed | Teeth Cleaning |
| 4 | John Doe | Dr. Jane | Tomorrow | 09:00 | 📅 Scheduled | Follow-up Checkup |
| 5 | Maria Garcia | Dr. Jane | Tomorrow | 11:00 | 📅 Scheduled | Crown Placement |
| 6 | Robert Chen | Dr. Jane | Day after tomorrow | 10:00 | 📅 Scheduled | Filling |
| 7 | John Doe | Dr. Jane | Next week | 15:00 | 📅 Scheduled | Root Canal |
| 8 | Maria Garcia | Dr. Jane | Last week | 09:00 | ❌ Cancelled | Emergency (rescheduled) |

---

## 🦷 Treatments (5 records)

| # | Patient | Procedure | Tooth | Status | Cost |
|---|---|---|---|---|---|
| 1 | John Doe | Scaling & Polishing | All | ✅ Completed | Rp 350.000 |
| 2 | Maria Garcia | Tooth Extraction | #38 (Wisdom) | ✅ Completed | Rp 500.000 |
| 3 | Robert Chen | Composite Filling | #16 | 📋 Planned | Rp 450.000 |
| 4 | John Doe | Crown (Porcelain) | #21 | 🔄 In Progress | Rp 2.500.000 |
| 5 | Maria Garcia | Root Canal Treatment | #36 | 📋 Planned | Rp 1.500.000 |

---

## 💰 Invoices (5 records)

| # | Patient | Amount | Paid | Status | Date |
|---|---|---|---|---|---|
| 1 | John Doe | Rp 350.000 | Rp 350.000 | ✅ Paid | Yesterday |
| 2 | Maria Garcia | Rp 500.000 | Rp 500.000 | ✅ Paid | Yesterday |
| 3 | Robert Chen | Rp 450.000 | Rp 0 | 📤 Sent | Today |
| 4 | John Doe | Rp 2.500.000 | Rp 1.250.000 | 📤 Sent (50%) | Today |
| 5 | Maria Garcia | Rp 1.500.000 | Rp 0 | 📝 Draft | - |

---

## 📦 Inventory Items (8 records)

| # | Item Name | Category | Quantity | Min Stock | Unit | Price |
|---|---|---|---|---|---|---|
| 1 | Local Anesthetic (Lidocaine) | Consumables | 50 | 10 | vial | Rp 25.000 |
| 2 | Composite Resin (A2) | Restorative | 20 | 5 | tube | Rp 150.000 |
| 3 | Alginate Impression Material | Impression | 15 | 5 | kg | Rp 200.000 |
| 4 | Disposable Gloves (Box) | PPE | 30 | 10 | box | Rp 50.000 |
| 5 | Surgical Mask (Box) | PPE | 40 | 20 | box | Rp 35.000 |
| 6 | Diamond Bur Set | Equipment | 5 | 2 | set | Rp 500.000 |
| 7 | Mouth Mirror | Equipment | 25 | 10 | pcs | Rp 75.000 |
| 8 | Extraction Forceps | Equipment | 8 | 3 | pcs | Rp 350.000 |

---

## 💊 Pharmacy Items (6 records)

| # | Item Name | Batch | Qty | Expiry | Price |
|---|---|---|---|---|---|
| 1 | Amoxicillin 500mg | AMX-2026-001 | 200 | Dec 2027 | Rp 5.000/tab |
| 2 | Ibuprofen 400mg | IBU-2026-002 | 150 | Mar 2028 | Rp 3.000/tab |
| 3 | Paracetamol 500mg | PAR-2026-003 | 300 | Jun 2028 | Rp 2.000/tab |
| 4 | Chlorhexidine Mouthwash | CHX-2026-004 | 50 | Sep 2027 | Rp 25.000/bottle |
| 5 | Fluoride Gel | FLG-2026-005 | 30 | Dec 2027 | Rp 75.000/tube |
| 6 | Eugenol | EUG-2026-006 | 10 | Mar 2028 | Rp 100.000/bottle |

---

## 🔬 Lab Orders (3 records)

| # | Patient | Type | Tooth | Status | Lab | Cost |
|---|---|---|---|---|---|---|
| 1 | John Doe | Porcelain Crown | #21 | ✅ Completed | Dental Lab Pro | Rp 1.800.000 |
| 2 | Maria Garcia | Temporary Crown | #36 | 🔄 In Progress | Dental Lab Pro | Rp 500.000 |
| 3 | Robert Chen | Partial Denture | #14-#16 | ⏳ Pending | Dental Lab Pro | Rp 3.500.000 |

---

## 🏥 EMR Records (3 records)

| # | Patient | Date | Chief Complaint | Diagnosis | Treatment | Notes |
|---|---|---|---|---|---|---|
| 1 | John Doe | Yesterday | Routine checkup, sensitivity | Mild enamel wear, early caries #21 | Scaling, crown prep | BP: 120/80, HR: 72, Temp: 36.5°C |
| 2 | Maria Garcia | Yesterday | Wisdom tooth pain | Impacted #38 with pericoronitis | Extraction #38 | BP: 110/70, HR: 68, Temp: 36.8°C |
| 3 | Robert Chen | 2 days ago | Cavity in upper molar | Caries #16 (MOD) | Filling planned | BP: 130/85, HR: 76, Temp: 36.6°C |

---

## 🦷 Odontograms (15 tooth records)

### John Doe
| Tooth | Status | Notes |
|---|---|---|
| #16 | 🟢 Healthy | - |
| #21 | 🟡 Restored | Composite filling, needs crown |
| #36 | 🟢 Healthy | - |

### Maria Garcia
| Tooth | Status | Notes |
|---|---|---|
| #14 | 🟢 Healthy | - |
| #36 | 🔴 Root Canal | RCT planned |
| #38 | ⚫ Extracted | Extracted yesterday |

### Robert Chen
| Tooth | Status | Notes |
|---|---|---|
| #16 | 🟠 Carious | MOD caries, filling planned |
| #23 | 🟢 Healthy | - |
| #46 | 🟡 Filled | Amalgam filling (old) |

---

## 📸 Radiology

### Orders (3)
| # | Patient | Type | Date | Status |
|---|---|---|---|---|
| 1 | John Doe | Panoramic X-Ray | Yesterday | ✅ Completed |
| 2 | Maria Garcia | Periapical #36 | Yesterday | ✅ Completed |
| 3 | Robert Chen | Bitewing | 2 days ago | ⏳ Pending |

### Images (2)
| # | Patient | Type | File |
|---|---|---|---|
| 1 | John Doe | Panoramic | `panoramic_john_2026.dcm` |
| 2 | Maria Garcia | Periapical | `periapical_maria_36.dcm` |

### Reports (2)
| # | Patient | Findings | Recommendation |
|---|---|---|---|
| 1 | John Doe | Mild bone loss #21, caries #16 | Crown #21, filling #16 |
| 2 | Maria Garcia | Impacted #38, periapical lesion | Extract #38, monitor #36 |

---

## 📞 CRM Contacts (3 records)

| # | Name | Type | Status | Subject |
|---|---|---|---|---|
| 1 | John Doe | Complaint | ✅ Resolved | Sensitivity after scaling |
| 2 | Maria Garcia | Inquiry | 📋 Follow-up | Insurance coverage for crown |
| 3 | Robert Chen | Reminder | 🆕 New | 6-month checkup reminder |

---

## 💼 Finance

### Chart of Accounts (10 accounts)
| Code | Account | Type | Balance |
|---|---|---|---|
| 1001 | Cash | Asset | Rp 15.000.000 |
| 1002 | Bank Account | Asset | Rp 45.000.000 |
| 1003 | Accounts Receivable | Asset | Rp 4.450.000 |
| 1004 | Inventory | Asset | Rp 8.500.000 |
| 2001 | Accounts Payable | Liability | Rp 2.000.000 |
| 3001 | Owner's Equity | Equity | Rp 50.000.000 |
| 4001 | Service Revenue | Revenue | Rp 850.000 |
| 4002 | Product Sales | Revenue | Rp 0 |
| 5001 | Salary Expense | Expense | Rp 0 |
| 5002 | Supplies Expense | Expense | Rp 1.200.000 |

### Journal Entries (2)
| # | Date | Description | Debit | Credit |
|---|---|---|---|---|
| 1 | Yesterday | Payment - John Doe scaling | Cash: Rp 350.000 | Service Revenue: Rp 350.000 |
| 2 | Yesterday | Payment - Maria Garcia extraction | Cash: Rp 500.000 | Service Revenue: Rp 500.000 |

---

## 🔄 How to Reset Demo Data

```bash
# Full reset (destroys all data + re-seeds)
docker compose -f docker/compose.yaml exec app php artisan migrate:fresh --seed --seeder=DemoSeeder --force
docker compose -f docker/compose.yaml exec app php artisan db:seed --class=ExtendedDemoSeeder

# Just re-extended data (keeps base org/users)
docker compose -f docker/compose.yaml exec app php artisan db:seed --class=ExtendedDemoSeeder
```

---

**Last Updated:** 2026-08-27  
**Seeded:** ✅ 84 total records across 19 tables

# Migration Guide - Member Profile Fields

## Overview
This guide explains the database migration for adding missing fields to the `member_profiles` table.

## Files Changed

### 1. Database Migration
- **File**: `database/migrations/add_missing_member_profile_fields.sql`
- **Purpose**: Add 4 new columns to member_profiles table

### 2. Model Update
- **File**: `app/Models/MemberProfileModel.php`
- **Change**: Added new fields to allowedFields array

### 3. View Update (Partial)
- **File**: `app/Views/member/profile/edit.php`
- **Change**: Added NIDN/NIP and WhatsApp input fields

### 4. Controller Update
- **File**: `app/Controllers/Member/ProfileController.php`
- **Change**: Updated updateData to include all fields

---

## Step 1: Run SQL Migration

**IMPORTANT**: Run this SQL script on your database:

```bash
# Using MySQL command line
mysql -u root -p spk_db < database/migrations/add_missing_member_profile_fields.sql

# OR using phpMyAdmin
# - Open phpMyAdmin
# - Select database 'spk_db'
# - Go to SQL tab
# - Copy-paste content from add_missing_member_profile_fields.sql
# - Click "Go"
```

### Fields Added:

1. **religion** - ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya')
2. **marital_status** - ENUM('Belum Menikah', 'Menikah', 'Cerai', 'Cerai Mati')
3. **employment_type** - ENUM('Dosen Tetap', 'Dosen Tidak Tetap', 'Tendik Tetap', 'Tendik Tidak Tetap', 'Honorer', 'Lainnya')
4. **employee_id** - VARCHAR(50) - Nomor Induk Pegawai

---

## Step 2: Pull Latest Code

```bash
cd C:\laragon\www\spk-app
git pull origin claude/fix-fileupload-service-01AMVAwyxgNZ5EyMjeA5CyQp
```

---

## Step 3: Clear Cache

```bash
# Clear CodeIgniter cache
del /Q C:\laragon\www\spk-app\writable\cache\*.php

# Restart Laragon (Apache/Nginx)
```

---

## Step 4: Test

1. Login as member
2. Go to http://localhost:8080/member/profile/edit
3. Check that all fields display correctly
4. Try updating profile
5. Verify data saved correctly in database

---

## Field Mapping (Database vs View)

### ✅ Fields Already in View and Database

| View Field Name | Database Column | Type |
|----------------|-----------------|------|
| full_name | full_name | VARCHAR(255) |
| nik | nik | VARCHAR(20) |
| nidn_nip | nidn_nip | VARCHAR(30) |
| employee_id | employee_id | VARCHAR(50) |
| gender | gender | ENUM |
| religion | religion | ENUM |
| marital_status | marital_status | ENUM |
| birth_place | birth_place | VARCHAR(100) |
| birth_date | birth_date | DATE |
| phone | phone | VARCHAR(20) |
| whatsapp | whatsapp | VARCHAR(20) |
| address | address | TEXT |
| province_id | province_id | INT |
| regency_id | regency_id | INT |
| postal_code | postal_code | VARCHAR(10) |
| university_id | university_id | INT |
| study_program_id | study_program_id | INT |
| employment_type | employment_type | ENUM |
| position | job_position | VARCHAR(100) |
| join_date | join_date | DATE |

### ⚠️ Fields in Database but NOT YET in View (Need to be Added Manually)

| Database Column | Purpose | Recommended Position in View |
|----------------|---------|------------------------------|
| employment_status_id | Status Kepegawaian (PNS/Non-PNS/Kontrak) | After employment_type |
| salary_payer | Pemberi Gaji (KAMPUS/PEMERINTAH/YAYASAN/LAINNYA) | After employment_status_id |
| salary_range_id | Range Gaji | After salary_payer |
| work_start_date | Tanggal Mulai Bekerja | After salary_range_id |
| skills | Keahlian | Near bottom (textarea) |
| motivation | Motivasi Bergabung | Near bottom (textarea) |

### 📝 Field Notes

1. **employment_type vs employment_status_id**:
   - `employment_type` = Jenis pegawai (Dosen Tetap, Dosen Tidak Tetap, Tendik, dll)
   - `employment_status_id` = Status kepegawaian (PNS, Non-PNS, Kontrak, dll) - References master table

2. **position vs job_position**:
   - View uses "position" as field name
   - Database uses "job_position" as column name
   - Controller maps correctly

3. **employee_id vs nidn_nip**:
   - Both exist in database
   - `nidn_nip` = NIDN for lecturers or NIP for employees
   - `employee_id` = General employee number

---

## Important Notes

1. **Data Migration**: Existing member data will have NULL values for new fields
2. **Validation**: New fields are nullable, so no validation errors
3. **View Completion**: Some fields still need to be added to edit.php manually
4. **Email Verification**: Issue with activation_token not being generated during registration - STILL PENDING FIX

---

## Next Steps (Manual)

If you want ALL database fields to be editable in the view:

1. Edit `app/Views/member/profile/edit.php`
2. Add form fields for:
   - employment_status_id (dropdown from employment_statuses table)
   - salary_payer (already in controller, add to view)
   - salary_range_id (already in controller, add to view)
   - work_start_date (date input)
   - skills (textarea)
   - motivation (textarea)
3. These dropdowns are already loaded in controller (`$employment_statuses`, `$salary_payers`, `$salary_ranges`)

---

## Troubleshooting

**Problem**: "Unknown column 'religion'" error
**Solution**: Make sure you ran the SQL migration script

**Problem**: Form data not saving
**Solution**: Check that field names in view match those in controller's updateData array

**Problem**: Dropdown showing "Pilih..." but no options
**Solution**: Check that controller is loading the master data (provinces, employment_statuses, etc.)

---

Last Updated: 2025-11-23

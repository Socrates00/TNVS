# TNVS Fleet Management System - Enhanced Version

## What's New in Version 2.0

Your TNVS fleet management system has been upgraded with three comprehensive feature sets:

---

## 🚗 Enhanced Vehicle Profiles

### Fuel Management
- Track fuel consumption per vehicle
- Record fuel costs and fuel type
- Calculate fuel efficiency metrics
- Monitor fuel spending trends
- Link fuel entries to drivers

**Access:** Vehicle Profile → Fuel Management tab

### Document Vault
- Store digital copies of:
  - OR (Official Receipt)
  - CR (Certificate of Registration)
  - Insurance Policies
  - LTFRB Permits
- Set expiration dates
- Automatic 30-day renewal alerts
- Track document history

**Access:** Vehicle Profile → Document Vault tab

### Odometer Tracking
- Record mileage readings with timestamps
- Calculate miles between readings
- Automate maintenance schedules based on mileage
- Maintain complete mileage history

**Access:** Vehicle Profile → Odometer tab

---

## 👥 Driver Management System

### License Monitoring
- Track license number and expiration
- Automatic alerts for expiring licenses
- Renewal reminders 30 days before expiration
- Compliance tracking

**Access:** Drivers page

### Performance Metrics
- Overall performance rating (0-5 stars)
- Fuel efficiency score
- Punctuality rating
- Safety incident tracking
- View detailed performance dashboard

**Access:** Click "View Profile" on any driver

### Attendance/Shifts
- Clock in/out system
- Track shift duration
- Record miles driven per shift
- Monitor fuel consumed per shift
- View shift history and trends

**Access:** Drivers page → Clock In/Out buttons

---

## 🔧 Comprehensive Maintenance Module

### Service History
- Log all vehicle maintenance work
- Record service type and details
- Track cost per service
- Document parts used
- Maintain complete service records
- Filter by vehicle, date, or service type

**Access:** Maintenance page or Vehicle Profile → Maintenance tab

### Predictive Alerts
- Automatic alerts based on mileage
- Time-based alerts for due dates
- Severity levels (Info/Warning/Critical)
- Color-coded alert system:
  - 🔵 Blue = Information
  - 🟡 Yellow = Warning (30 days out)
  - 🔴 Red = Critical
- Dismiss alerts after completion
- Track resolved vs. active alerts

**Access:** Maintenance page (top section)

### Parts Inventory
- Add and manage spare parts
- Track stock levels
- Set reorder points
- Automatic low-stock alerts
- Unit cost and total inventory value
- Category organization
- Supplier tracking
- Compatibility notes
- Usage history and logs

**Access:** Parts Inventory page

---

## Quick Start Guide

### 1. Initialize Database
```
Visit: http://localhost/TNVS/database_setup.php
```
This creates all necessary tables for the new features.

### 2. Add Drivers
```
Go to: Drivers page
Click: "+ Add Driver"
```

### 3. Manage Vehicles
```
Go to: Admin Dashboard → Fleet Dashboard tab
Or: Click "View" next to vehicle to access detailed profile
```

### 4. Track Fuel & Documents
```
Vehicle Profile → Fuel Management or Document Vault tabs
```

### 5. Maintain Services
```
Go to: Maintenance page
Add service records and view predictive alerts
```

### 6. Manage Parts
```
Go to: Parts Inventory page
Track stock levels and parts usage
```

---

## File Structure

### New Pages
- **drivers.php** - Driver management, clock in/out, license monitoring
- **driver_profile.php** - Individual driver performance dashboard
- **vehicle_profile.php** - Vehicle details, fuel, documents, odometer
- **maintenance.php** - Service records and predictive alerts
- **parts_inventory.php** - Parts management and tracking
- **database_setup.php** - Database initialization script

### Database Tables Created
1. `drivers` - Driver information and ratings
2. `driver_shifts` - Shift tracking and attendance
3. `fuel_management` - Fuel consumption records
4. `vehicle_documents` - Document storage and expiration
5. `odometer_readings` - Mileage tracking
6. `maintenance_records` - Service history
7. `maintenance_alerts` - Predictive alerts
8. `parts_inventory` - Parts management
9. `parts_usage_log` - Parts usage tracking

---

## Key Features

✅ **Real-time Alerts**
- License expiration warnings
- Document renewal reminders
- Low parts inventory alerts
- Maintenance due notifications

✅ **Performance Tracking**
- Driver efficiency and safety metrics
- Vehicle maintenance history
- Fuel consumption analysis
- Parts usage trends

✅ **Comprehensive Reporting**
- Service history by vehicle
- Driver performance dashboard
- Maintenance cost tracking
- Parts inventory valuation

✅ **Predictive Maintenance**
- Automatic alerts based on mileage
- Time-based due date tracking
- Customizable alert severity
- Easy alert dismissal workflow

✅ **Document Management**
- Digital document storage
- Expiration date tracking
- Compliance alerts
- Historical records

---

## System Requirements

- **PHP**: 7.0+
- **MySQL**: 5.7+
- **Database**: `byahero_db`
- **Connection**: localhost, no password required

---

## Default Credentials

| Setting | Value |
|---------|-------|
| Host | localhost |
| DB Name | byahero_db |
| Username | root |
| Password | (empty) |

---

## Navigation

**From Admin Dashboard:**
1. Click tabs at the top:
   - Fleet Dashboard (vehicles)
   - Drivers
   - Maintenance
   - Parts
2. Or use quick links to jump to each module

**Direct URLs:**
- `/admin.php` - Main dashboard
- `/drivers.php` - Driver management
- `/vehicle_profile.php?id=1` - Vehicle details
- `/maintenance.php` - Maintenance records
- `/parts_inventory.php` - Parts management

---

## Tips & Tricks

1. **Set Due Dates**: When adding maintenance, set "Next Due Date" to auto-generate predictive alerts
2. **Monitor Licenses**: Check Drivers page regularly for license expiration warnings
3. **Track Fuel**: Record fuel entries immediately for accurate consumption tracking
4. **Low Stock**: Parts inventory shows low-stock items in RED
5. **Multiple Tabs**: Vehicle profiles are split into tabs for easy organization

---

## Next Steps

1. Run `database_setup.php` to initialize database
2. Add your first driver
3. Add vehicles and assign drivers
4. Start tracking fuel consumption
5. Add documents to document vault
6. Record maintenance services
7. Build parts inventory
8. Monitor performance metrics

---

## Support & Documentation

See **SETUP_GUIDE.md** for detailed feature documentation and troubleshooting.

**Version**: 2.0 Enhanced  
**Last Updated**: March 2026

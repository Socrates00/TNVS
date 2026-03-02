# TNVS Enhanced Features - Setup Guide

This document provides instructions for implementing and using the new enhanced features for the TNVS (ByaHERO) fleet management system.

## New Features Overview

### 1. **Enhanced Vehicle Profiles**
- **Fuel Management**: Track fuel consumption, costs, and efficiency metrics per vehicle
- **Document Vault**: Store and manage vehicle documents (OR, CR, Insurance, LTFRB Permits) with expiration alerts
- **Odometer Tracking**: Record mileage readings to automate maintenance schedules

### 2. **Driver Management System**
- **License Monitoring**: Track driver's license numbers and expiration dates
- **Performance Metrics**: Monitor fuel efficiency scores, safety incidents, punctuality ratings
- **Attendance/Shifts**: Track driver clock-in/out times and shift details

### 3. **Comprehensive Maintenance Module**
- **Service History**: Log all maintenance work with details of services performed
- **Predictive Alerts**: Automatic alerts for maintenance due at specific mileage or dates
- **Parts Inventory**: Manage spare parts stock levels with low-stock warnings

---

## STEP 1: Database Setup

### Run the Database Setup Script

1. Navigate to your TNVS folder in a web browser
2. Visit: `http://localhost/TNVS/database_setup.php`
3. This will create all necessary database tables:
   - `drivers`
   - `driver_shifts`
   - `fuel_management`
   - `vehicle_documents`
   - `odometer_readings`
   - `maintenance_records`
   - `maintenance_alerts`
   - `parts_inventory`
   - `parts_usage_log`

### Tables Created

**drivers** - Main driver information
- License number and expiration
- Performance ratings
- Safety metrics

**driver_shifts** - Track driver work times
- Clock-in/out times
- Miles driven
- Fuel consumed per shift

**fuel_management** - Fuel consumption tracking
- Fuel date, amount, and cost
- Fuel type (Diesel, Gasoline, LPG)
- Odometer reading at fuel-up

**vehicle_documents** - Document vault
- Document type (OR, CR, Insurance, LTFRB)
- Issue and expiration dates
- File paths for storage

**odometer_readings** - Mileage tracking
- Recorded mileage
- Notes and timestamps

**maintenance_records** - Service history
- Service type and details
- Cost and work performed
- Parts used in service
- Next due mileage/date

**maintenance_alerts** - Predictive alerts
- Alert status (active/dismissed/resolved)
- Severity levels (info/warning/critical)
- Auto-generated when maintenance records set next due dates

**parts_inventory** - Spare parts management
- Part names, codes, categories
- Stock levels and reorder points
- Unit costs and suppliers

---

## STEP 2: Access the New Modules

All new features are accessible from the admin dashboard or directly via URLs:

### Main Admin Dashboard
- **URL**: `/admin.php`
- Features tabbed navigation to all modules

### Driver Management
- **URL**: `/drivers.php`
- Add new drivers
- Clock in/out
- View detailed driver profiles
- Monitor license expiration alerts

### Driver Profiles
- **URL**: `/driver_profile.php?id={driver_id}`
- Performance metrics and ratings
- Shift history
- Fuel efficiency stats

### Vehicle Profiles
- **URL**: `/vehicle_profile.php?id={vehicle_id}`
- Fuel management
- Document vault
- Odometer tracking
- Maintenance history
- Tabs for easy navigation

### Maintenance Management
- **URL**: `/maintenance.php`
- Add service records
- View maintenance history
- Monitor predictive alerts
- Track parts used

### Parts Inventory
- **URL**: `/parts_inventory.php`
- Add new parts
- Track stock levels
- Monitor low-stock items
- View parts usage history

---

## STEP 3: Feature Usage Guide

### FUEL MANAGEMENT

**How to add fuel entries:**
1. Go to Vehicle Profile → Fuel Management tab
2. Click "Add Fuel Entry"
3. Fill in:
   - Fuel Date
   - Liters Added
   - Cost
   - Fuel Type (Diesel/Gasoline/LPG)
   - Odometer Reading
4. Submit

**View Fuel Statistics:**
- Total fillups
- Average liters per fillup
- Total cost
- Average cost per fillup

---

### DOCUMENT VAULT

**How to add documents:**
1. Go to Vehicle Profile → Document Vault tab
2. Click "Add Document"
3. Select Document Type:
   - OR (Official Receipt)
   - CR (Certificate of Registration)
   - Insurance Policy
   - LTFRB Permit
4. Set Expiration Date for automatic alerts
5. Store file path reference

**Expiration Alerts:**
- Documents expiring within 30 days are flagged
- Automatic alerts appear in vehicle overview
- Easy renewal reminders

---

### DRIVER MANAGEMENT

**How to add drivers:**
1. Go to Drivers page
2. Click "+ Add Driver"
3. Enter:
   - First Name & Last Name
   - License Number
   - License Expiration Date
   - Phone & Email
4. Submit

**Clock In/Out:**
1. Go to Drivers page
2. Click "Clock In" to start shift
3. Click "Clock Out" to end shift
4. Automatically records shift duration, miles, and fuel

**Monitor Licenses:**
- Licenses expiring within 30 days show automatic warnings
- Driver status shows if currently clocked in

**View Driver Profile:**
1. Click "View Profile" on any driver
2. See:
   - Performance metrics
   - Total shifts and miles
   - Recent shift history
   - Safety incident count
   - Punctuality rating

---

### MAINTENANCE MANAGEMENT

**How to add maintenance records:**
1. Go to Maintenance page
2. Click "+ Add Service Record"
3. Select:
   - Vehicle
   - Service Type (Oil Change, Tire Rotation, etc.)
   - Service Date
   - Performed By
4. Optionally enter:
   - Cost
   - Odometer reading at service
   - Next due mileage
   - Next due date
   - Parts used

**Predictive Alerts:**
- When you set "Next Due At Mileage" or "Next Due Date", a predictive alert is automatically created
- Alerts turn RED when critical
- Alerts show severity levels (info/warning/critical)
- Dismiss after completing maintenance (click "Dismiss")

**Service History:**
- All maintenance records logged
- Searchable by vehicle and date
- Cost tracking for budgeting
- Parts used documented

---

### PARTS INVENTORY

**How to add parts:**
1. Go to Parts Inventory page
2. Click "+ Add Part"
3. Enter:
   - Part Name *
   - Part Code
   - Category *
   - Initial Stock *
   - Reorder Level *
   - Unit Cost
   - Supplier
   - Compatible Vehicles
4. Submit

**Manage Stock:**
- Low stock items are highlighted in RED
- Automatic alerts when stock falls below reorder level
- View total inventory value
- See stock levels by category

**Categories:**
- Engine
- Brakes
- Tires
- Filters
- Fluids
- Belts
- Electrical
- Other

**Track Usage:**
- Parts usage logged when used in maintenance
- View recent usage history
- Monitor which parts are most used

---

## STEP 4: Key Metrics & Reports

### Driver Performance Dashboard
Each driver has metrics for:
- **Total Shifts**: Number of shifts worked
- **Total Miles**: Cumulative distance driven
- **Total Fuel**: Liters consumed across all shifts
- **Fuel Efficiency Score**: Performance metric (5.0 = excellent)
- **Punctuality Rating**: Based on on-time completion
- **Safety Incidents**: Count of reported incidents

### Vehicle Health Dashboard
Each vehicle tracks:
- **Current Odometer**: Latest mileage reading
- **Total Fuel Spent**: Cumulative fuel cost
- **Average Fuel Consumption**: Liters per fillup
- **Maintenance Schedule**: Upcoming due dates
- **Document Status**: Expiration warnings

### Maintenance Alerts
Automatically generated for:
- Tasks due at specific mileage
- Documents expiring soon
- License expirations
- Low parts inventory

---

## STEP 5: Troubleshooting

### Database Connection Error
- Check `byahero_db` exists in your MySQL
- Verify credentials in PHP files match your MySQL setup
- Default: localhost, root (no password)

### Tables Not Created
- Clear browser cache
- Try running database_setup.php again
- Check if all tables exist in phpMyAdmin

### Missing Navigation Links
- Clear browser cache
- Log out and log back in
- Refresh the admin.php page

### Alerts Not Showing
- Ensure maintenance records have "Next Due Date" set
- Check that dates are in the future
- System shows alerts only 30 days before expiration

---

## Default System Configuration

- **Database**: byahero_db
- **Host**: localhost
- **User**: root
- **Password**: (empty)
- **Timezone**: Server default
- **Currency**: ₱ Philippine Peso (can be changed in code)

---

## File Manifest

**New Files Created:**
- `database_setup.php` - Database initialization
- `drivers.php` - Driver management page
- `driver_profile.php` - Individual driver profile
- `maintenance.php` - Maintenance module
- `vehicle_profile.php` - Vehicle details and fuel/documents
- `parts_inventory.php` - Parts inventory management

**Files Modified:**
- `admin.php` - Added tabs and navigation to new modules

---

## Next Steps

1. ✅ Run database_setup.php
2. ✅ Add sample drivers
3. ✅ Add sample vehicles with profiles
4. ✅ Record fuel entries for vehicles
5. ✅ Add maintenance records
6. ✅ Set up parts inventory
7. ✅ Monitor alerts and metrics

---

## Support

For questions or issues:
- Check the Troubleshooting section above
- Review the code comments in each PHP file
- Ensure all database tables are created correctly
- Verify file permissions are set correctly

---

**System Version:** 2.0 Enhanced
**Last Updated:** 2026-03-01

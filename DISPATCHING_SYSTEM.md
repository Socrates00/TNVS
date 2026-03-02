# TNVS Dispatching System Documentation

## Overview

The TNVS Dispatching System is a complete ride request management and driver assignment solution. It enables customers to book rides and allows admins to manually assign drivers to ride requests, track active trips, and manage the overall system.

---

## System Flow

### 1. **Customer Books a Ride** (customer.php)

**What Happens:**
- Customer opens the app and selects pickup/destination locations
- Selects a ride type (Sedan, 6-Str, Moto)
- Chooses payment method
- Clicks "Confirm Booking"

**Data Saved:**
- A new booking is created in the `bookings` table
- Status: `pending` (waiting for admin assignment)
- Driver ID: `NULL` (no driver assigned yet)
- Booking ID: Unique identifier (e.g., BK20250302143052XXXX)

**Database Fields:**
```
id, booking_id, user_id, driver_id (NULL), ride_type_id,
pickup_location, destination_location, pickup_lat, pickup_lng,
dest_lat, dest_lng, estimated_distance, estimated_fare,
payment_method, promo_code, status='pending', created_at, completed_at=NULL
```

---

### 2. **Admin Views Pending Requests** (dispatching.php)

**What the Admin Sees:**
- Dashboard with real-time statistics:
  - Number of pending requests
  - Number of active drivers
  - Ongoing trips count
  - Completed trips today

- **Pending Ride Requests Section:**
  - Lists all rides with status='pending'
  - Shows customer name, pickup/destination, distance, fare, payment method
  - Two buttons per request:
    - **"Assign Driver"** - Opens a modal to select and assign a driver
    - **"Reject"** - Cancels the booking

- **Ongoing Trips Section:**
  - Shows trips with status='accepted' or 'in_progress'
  - Displays driver name, customer, route, ride type
  - Action buttons to update trip status

---

### 3. **Admin Assigns a Driver** (dispatching.php)

**Process:**
1. Admin clicks "Assign Driver" button on a pending request
2. A modal popup appears showing:
   - Customer name (auto-filled)
   - Dropdown list of available drivers with ratings
3. Admin selects a driver from the dropdown
4. Clicks "Assign Driver" button

**Database Update:**
- Booking's `driver_id` is set to the selected driver's ID
- Booking's `status` changes to `accepted`
- System automatically triggers driver assignment

**Result:**
- Page refreshes and shows success message
- Request moves from "Pending Requests" to "Ongoing Trips"
- Driver can now see the assigned ride in their app

---

### 4. **Trip Lifecycle** (dispatching.php)

#### Status Flow:

```
pending → accepted → in_progress → completed
           ↓
        (optional) cancelled
```

#### Admin Actions:

1. **Assign Driver**: `pending` → `accepted`
   - Admin manually selects and assigns a driver
   - Driver receives the booking notification

2. **Start Trip**: `accepted` → `in_progress`
   - Driver has picked up the customer
   - Trip is now active

3. **Complete Trip**: `in_progress` → `completed`
   - Driver has reached destination
   - Trip is finalized with timestamp

4. **Reject**: `pending` → `cancelled`
   - Admin rejects the booking
   - Customer can request another ride

---

## Database Schema

### bookings Table

```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id VARCHAR(20) UNIQUE,        -- Unique booking reference
    user_id INT,                           -- Customer ID
    driver_id INT,                         -- Assigned driver ID (NULL if pending)
    ride_type_id INT,                      -- Type of ride
    
    -- Location Data
    pickup_location VARCHAR(300),
    destination_location VARCHAR(300),
    pickup_lat DECIMAL(10,8),
    pickup_lng DECIMAL(11,8),
    dest_lat DECIMAL(10,8),
    dest_lng DECIMAL(11,8),
    
    -- Trip Details
    estimated_distance DECIMAL(10,2),     -- Distance in km
    estimated_fare DECIMAL(10,2),         -- Calculated fare
    actual_fare DECIMAL(10,2),            -- Final amount charged
    
    payment_method VARCHAR(50),           -- 'cash', 'card', 'gcash'
    promo_code VARCHAR(50),
    
    -- Status & Timestamps
    status ENUM('pending', 'accepted', 'in_progress', 'completed', 'cancelled') 
           DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_user (user_id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (ride_type_id) REFERENCES ride_types(id) ON DELETE SET NULL
);
```

### drivers Table

```sql
CREATE TABLE drivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    driver_id VARCHAR(20) UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    contact_number VARCHAR(20),
    license_number VARCHAR(50) UNIQUE,
    license_expiration DATE,
    
    status ENUM('active', 'on_trip', 'suspended') DEFAULT 'active',
    performance_rating DECIMAL(3,2) DEFAULT 5.00,
    
    total_trips INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Key Features

### 1. **Customer Booking (customer.php)**
- ✅ Real-time location detection using Geolocation API
- ✅ Autocomplete suggestions for destinations (Nominatim)
- ✅ Real-time fare calculation based on distance
- ✅ Multiple ride types with different pricing
- ✅ Payment method selection
- ✅ Booking confirmation tracking

### 2. **Admin Dispatching (dispatching.php)**
- ✅ Real-time dashboard with live statistics
- ✅ Pending requests queue with full trip details
- ✅ Manual driver assignment with modal interface
- ✅ Driver rating display for informed selection
- ✅ One-click booking rejection
- ✅ Trip status management (Start/Complete)
- ✅ Ongoing trips tracking

### 3. **API Endpoints (api_dispatch.php)**
- `get_pending_requests` - Fetch all pending bookings
- `get_active_trips` - Fetch ongoing trips
- `assign_driver` - Assign driver to booking
- `reject_booking` - Cancel booking
- `get_available_drivers` - List available drivers
- `update_trip_status` - Update trip status

---

## How to Use

### For Customers:

1. Open **customer.php**
2. Allow location access
3. Enter destination in the search field
4. Select ride type from available options
5. Choose payment method
6. Click "Confirm Booking"
7. Wait for driver assignment

### For Admins:

1. Open **dispatching.php**
2. Review pending ride requests in the "Pending Ride Requests" section
3. For each request:
   - Click "Assign Driver" to select a driver from the list
   - OR click "Reject" to cancel the booking
4. Monitor ongoing trips in the "Ongoing Trips" section
5. Click "Start Trip" when driver picks up customer
6. Click "Complete Trip" when destination is reached

---

## Configuration

### Database Connection
All files use the following credentials (set in each PHP file):

```php
$conn = new mysqli("localhost", "root", "", "byahero_db");
```

**Update if needed:**
- Server: localhost
- User: root
- Password: (empty)
- Database: byahero_db

### Ride Types
Default ride types (created in customer.php):

| Name | Icon | Base Price | Per KM | Capacity |
|------|------|------------|--------|----------|
| ByaHERO Sedan | fa-car | ₱50 | ₱15 | 4 |
| ByaHERO 6-Str | fa-van-shuttle | ₱80 | ₱20 | 6 |
| ByaHERO Moto | fa-motorcycle | ₱30 | ₱8 | 1 |

Fare Calculation Formula:
```
Fare = Base Price + (Distance in KM × Per KM Price)
```

---

## File Structure

```
TNVS/
├── customer.php              # Customer booking interface
├── dispatching.php           # Admin dispatch & assignment dashboard
├── api_dispatch.php          # API endpoints for dispatching operations
├── database_setup.php        # Database initialization script
├── driver_profile.php        # Driver management
├── customers.php             # Customers management
├── admin.php                 # General admin panel
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── customer_nav.php
└── [other files...]
```

---

## Technical Details

### Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7+ with MySQLi
- **Maps/Geocoding:** Leaflet + Nominatim OpenStreetMap
- **Database:** MySQL
- **UI Framework:** Font Awesome Icons, Custom CSS

### Security Features

- CSRF token validation on booking form
- SQL prepared statements to prevent SQL injection
- Input sanitization and escaping
- Session-based authentication

### Real-time Features

- Geolocation API for automatic location detection
- Nominatim geocoding for address lookups
- Real-time distance calculation using Haversine formula
- Live fare estimation based on distance and ride type

---

## Troubleshooting

### Issue: Bookings not appearing in dispatching.php

**Solution:**
1. Check database connection in dispatching.php
2. Verify `bookings` table exists: `SHOW TABLES;`
3. Check if `ride_types` table has entries
4. Ensure customer.php successfully created the booking with INSERT

### Issue: Driver assignment modal appears empty

**Solution:**
1. Check if drivers exist in `drivers` table
2. Verify drivers have `status = 'active'`
3. Check browser console for JavaScript errors

### Issue: Fare calculation shows ₱0

**Solution:**
1. Ensure destination is selected (lat/lng not empty)
2. Check if ride type is properly selected
3. Verify JavaScript calculateFare() function is running
4. Check browser console for JS errors

### Issue: Location detection not working

**Solution:**
1. Enable location access in browser settings
2. Use HTTPS (some browsers require this)
3. Check browser's geolocation permissions
4. Fallback: Manual location entry is not available - only geocoding via destination

---

## Future Enhancements

- [ ] Real-time GPS tracking with live map
- [ ] Automatic driver assignment based on location
- [ ] SMS/Email notifications to customers and drivers
- [ ] Rating and review system
- [ ] In-app chat between driver and customer
- [ ] Dynamic surge pricing based on demand
- [ ] Driver availability status updates
- [ ] Multiple booking cancellations with reason tracking

---

## Support

For issues or questions, check:
1. Browser console (F12) for JavaScript errors
2. Server error logs
3. Database logs
4. Session and authentication status

---

**Version:** 1.0
**Last Updated:** March 2, 2026
**System Status:** ✓ OPERATIONAL


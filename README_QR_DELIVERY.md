# FoodChap QR Code Delivery Verification System

## Overview
This system generates a unique QR code for each order that is used to verify delivery. When the delivery personnel scans the QR code, the order status is automatically updated to "completed" along with the delivery location and notes.

## Features

### 1. **QR Code Generation**
- A unique QR code is generated for each order when placed
- QR code is displayed in the order confirmation receipt
- QR code can be printed or viewed on mobile device

### 2. **Delivery Verification**
- Dedicated delivery verification page at `/delivery_verify.php`
- Delivery personnel scan the QR code to confirm delivery
- GPS location is captured automatically
- Optional delivery notes can be added
- Order status is automatically updated to "completed"

### 3. **Order Receipt with QR Code**
- Receipt displays the QR code prominently
- Receipt includes all order details
- QR code can be printed or saved
- Receipt shows delivery address and customer info

## Installation & Setup

### Step 1: Run Database Migration
Execute the SQL migration to add necessary columns to your orders table:

```bash
mysql -u root -p food_delivery < database_migration_qr_code.sql
```

Or manually run the SQL commands in your database management tool (phpMyAdmin, MySQL Workbench, etc.)

### Step 2: Verify Files
Ensure these files exist in your project:
- `index.php` - Customer order placement with QR code display
- `delivery_verify.php` - Delivery personnel verification page
- `database_migration_qr_code.sql` - Database schema updates

## Usage

### For Customers

1. **Place Order**
   - Browse menu and add items to cart
   - Click "Proceed to Checkout"
   - Enter delivery address and select payment method
   - Order is confirmed

2. **Receive QR Code**
   - Order confirmation shows unique QR code
   - QR code is displayed as scannable image
   - QR code value (32 character hex string) is shown below image
   - Option to print receipt with QR code

3. **Share with Delivery Personnel**
   - Show QR code to delivery person when they arrive
   - They will scan it to confirm delivery

### For Delivery Personnel

1. **Access Verification Page**
   - Open `/delivery_verify.php` in mobile browser
   - Page is optimized for mobile devices

2. **Verify Delivery**
   - Option to capture current GPS location
   - Scan QR code from customer's phone or printed receipt
   - Alternatively, paste QR code if scanner unavailable
   - Add optional delivery notes
   - Click "Confirm Delivery"

3. **Confirmation**
   - System verifies QR code
   - Order status changes to "completed"
   - GPS coordinates are recorded
   - Delivery notes are saved
   - Receipt preview is shown with order details

## Database Schema

### New Columns Added to `orders` Table:

```sql
qr_code VARCHAR(50)        -- Unique QR code token
completed_at TIMESTAMP     -- When delivery was confirmed
delivery_lat DECIMAL(10,8) -- GPS latitude at delivery
delivery_lng DECIMAL(11,8) -- GPS longitude at delivery
delivery_notes TEXT        -- Delivery personnel notes
order_number VARCHAR(20)   -- Formatted order number (000001, 000002, etc.)
```

### Order Status Flow:

1. `pending` → Initial status when order is placed
2. `confirmed` → Status after payment (when QR code is assigned)
3. `completed` → Status when delivery is verified via QR code

## Technical Details

### QR Code Generation

QR codes are generated using the **QR Server API** (free, no dependencies required):
```
https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=[QR_CODE_VALUE]
```

**Security:**
- Each QR code is a unique 32-character hexadecimal token
- Generated using `random_bytes(16)` for cryptographic randomness
- QR codes can only be used once (for confirmed orders)
- Once scanned and verified, the code is tied to that delivery

### API Endpoints

#### Checkout & Order Placement
- **POST** `/index.php?place_order=1`
- Returns: QR code, QR URL, order number, total amount

#### Delivery Verification
- **POST** `/delivery_verify.php?verify_qr=1`
- Required fields: `qr_code`, `delivery_lat`, `delivery_lng`, `delivery_notes`
- Returns: Order details, order items, success/error message

## Error Handling

### Common Issues & Solutions

1. **"Invalid or already completed order"**
   - QR code doesn't exist or order is not in "confirmed" status
   - Check if order number is correct

2. **Location cannot be obtained**
   - Ensure browser has permission to access GPS
   - Check if device has GPS enabled
   - Try again or manually note location if necessary

3. **QR code not scanning**
   - Ensure good lighting conditions
   - Check if QR code image is clear and not damaged
   - Use alternative: copy-paste QR code text directly

## Security Considerations

1. **QR Code Uniqueness**: Each order gets a unique QR code
2. **One-Time Use**: Each QR code can verify delivery once
3. **Location Verification**: GPS coordinates are logged with each delivery
4. **Session Management**: Orders are tied to user sessions
5. **Database Integrity**: QR codes are stored as unique keys in database

## Testing

### Test Scenario

1. Register new customer account
2. Add items to cart and place order
3. Note the QR code displayed
4. Open `delivery_verify.php` in separate browser/device
5. Either:
   - Scan QR code image
   - Copy-paste the QR code text
   - Allow GPS location access
   - Add optional notes
6. Click "Confirm Delivery"
7. Verify order status changes to "completed" in database

### Test Queries

```sql
-- View all orders with QR codes
SELECT id, order_number, qr_code, status, completed_at, delivery_lat, delivery_lng 
FROM orders 
WHERE qr_code IS NOT NULL 
ORDER BY id DESC;

-- Find orders by QR code
SELECT * FROM orders WHERE qr_code = 'your_qr_code_here';

-- View completed orders with delivery details
SELECT id, order_number, delivery_address, delivery_lat, delivery_lng, delivery_notes, completed_at
FROM orders
WHERE status = 'completed'
ORDER BY completed_at DESC;
```

## Mobile Optimization

- Delivery verification page is fully responsive
- Optimized for mobile browsers
- Large touch-friendly buttons
- GPS location capture built-in
- QR code scanning works with mobile device cameras

## Future Enhancements

1. **QR Code Expiration**: Set expiration time for QR codes
2. **Delivery Tracking**: Real-time location tracking
3. **SMS Notifications**: Send QR code via SMS to customer
4. **Signature Capture**: Digital signature from delivery person
5. **Photo Proof**: Capture delivery photo
6. **Rating System**: Customer rating after delivery confirmation

## Support & Troubleshooting

For issues:
1. Check database migration was applied correctly
2. Verify all columns exist in orders table
3. Check browser console for JavaScript errors
4. Ensure GPS permission is granted on mobile device
5. Test with sample QR code in delivery_verify.php

## File Summary

| File | Purpose |
|------|---------|
| `index.php` | Customer portal with QR code display |
| `delivery_verify.php` | Delivery personnel verification interface |
| `database_migration_qr_code.sql` | Database schema updates |
| `README_QR_DELIVERY.md` | This documentation |

---

**Version**: 1.0  
**Last Updated**: February 2026  
**Created for**: FoodChap Tanzania Food Delivery System

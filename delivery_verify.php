<?php
session_start();
include 'config/connection.php';

// Handle QR code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_qr'])) {
    $qr_code = trim($_POST['qr_code'] ?? '');
    $delivery_lat = floatval($_POST['delivery_lat'] ?? 0);
    $delivery_lng = floatval($_POST['delivery_lng'] ?? 0);
    $delivery_notes = sanitize($_POST['delivery_notes'] ?? '', 'string');

    if (!$qr_code) {
        echo json_encode(['success' => false, 'message' => 'QR code is required']);
        exit;
    }

    if (!isset($conn) || $conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit;
    }

    // Verify QR code and get order
    $sql = "SELECT o.id, o.order_number, o.user_id, o.total_amount, o.status, o.delivery_address, u.phone_number 
            FROM orders o 
            INNER JOIN users u ON o.user_id = u.id 
            WHERE o.qr_code = ? AND o.status = 'confirmed' LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('s', $qr_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or already completed order']);
        exit;
    }

    $order = $result->fetch_assoc();
    $stmt->close();

    // Update order status to completed
    $completed_at = date('Y-m-d H:i:s');
    $update_sql = "UPDATE orders SET status = 'completed', completed_at = ?, delivery_lat = ?, delivery_lng = ?, delivery_notes = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        exit;
    }

    $update_stmt->bind_param('sddsi', $completed_at, $delivery_lat, $delivery_lng, $delivery_notes, $order['id']);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();

        // Get order items for receipt
        $items_sql = "SELECT oi.*, m.item_name FROM order_items oi 
                      INNER JOIN menu_items m ON oi.menu_item_id = m.id 
                      WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param('i', $order['id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        $items_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Order delivered successfully!',
            'order' => [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'total_amount' => $order['total_amount'],
                'delivery_address' => $order['delivery_address'],
                'phone' => $order['phone_number']
            ],
            'items' => $items
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $update_stmt->error]);
        $update_stmt->close();
        exit;
    }
}

// Helper function
function sanitize($data, $type = 'string') {
    if ($data === null) return false;
    switch ($type) {
        case 'string':
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;
        default:
            return false;
    }
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodChap - Delivery Verification</title>
    <link href="Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="Assets/sweetalert2/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .delivery-container {
            max-width: 500px;
            width: 100%;
        }

        .delivery-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        .delivery-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .delivery-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .delivery-header p {
            color: #666;
            font-size: 14px;
        }

        .qr-input-group {
            position: relative;
            margin-bottom: 30px;
        }

        .qr-input-group input {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-family: monospace;
            transition: all 0.3s;
        }

        .qr-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .location-input {
            display: none;
        }

        .location-input.active {
            display: block;
        }

        .location-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
            width: 100%;
            transition: background 0.3s;
        }

        .location-btn:hover {
            background: #5568d3;
        }

        .verify-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }

        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .verify-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .success-animation {
            animation: successPulse 0.5s ease-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .location-display {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            display: none;
        }

        .location-display.active {
            display: block;
        }

        .notes-textarea {
            width: 100%;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 80px;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .notes-textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
        }

        .info-box i {
            color: #667eea;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="delivery-container">
        <div class="delivery-card">
            <div class="delivery-header">
                <h1><i class="fas fa-box"></i></h1>
                <h1>Order Delivery</h1>
                <p>Verify delivery with QR code</p>
            </div>

            <div class="info-box">
                <i class="fas fa-qrcode"></i>
                <strong>Scan or enter the QR code</strong> from the order to confirm delivery
            </div>

            <form id="deliveryForm">
                <div class="qr-input-group">
                    <input 
                        type="text" 
                        id="qrInput" 
                        placeholder="Paste QR code here or scan..."
                        autocomplete="off"
                        autofocus
                    >
                </div>

                <button type="button" class="location-btn" id="getLocationBtn">
                    <i class="fas fa-map-marker-alt me-2"></i> Get Current Location
                </button>

                <div class="location-display" id="locationDisplay"></div>
                <input type="hidden" id="deliveryLat" value="0">
                <input type="hidden" id="deliveryLng" value="0">

                <textarea 
                    class="notes-textarea" 
                    id="deliveryNotes" 
                    placeholder="Add delivery notes (optional)..."
                ></textarea>

                <button type="submit" class="verify-btn">
                    <i class="fas fa-check-circle me-2"></i> Confirm Delivery
                </button>
            </form>
        </div>
    </div>

    <script src="Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/sweetalert2/sweetalert2.all.min.js"></script>

    <script>
        const qrInput = document.getElementById('qrInput');
        const deliveryForm = document.getElementById('deliveryForm');
        const getLocationBtn = document.getElementById('getLocationBtn');
        const deliveryLat = document.getElementById('deliveryLat');
        const deliveryLng = document.getElementById('deliveryLng');
        const locationDisplay = document.getElementById('locationDisplay');
        const deliveryNotes = document.getElementById('deliveryNotes');

        // Get location
        getLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                getLocationBtn.disabled = true;
                getLocationBtn.textContent = 'Getting location...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        deliveryLat.value = lat;
                        deliveryLng.value = lng;
                        
                        locationDisplay.textContent = `Latitude: ${lat.toFixed(4)}, Longitude: ${lng.toFixed(4)}`;
                        locationDisplay.classList.add('active');
                        
                        getLocationBtn.disabled = false;
                        getLocationBtn.textContent = '✓ Location Captured';
                        getLocationBtn.style.background = '#28a745';
                    },
                    function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Location Error',
                            text: 'Unable to get your location. Please enable location access.',
                        });
                        getLocationBtn.disabled = false;
                        getLocationBtn.textContent = 'Get Current Location';
                    }
                );
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Not Supported',
                    text: 'Geolocation is not supported by this browser.',
                });
            }
        });

        // Handle form submission
        deliveryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const qrCode = qrInput.value.trim();
            
            if (!qrCode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing QR Code',
                    text: 'Please scan or enter the QR code.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('verify_qr', '1');
            formData.append('qr_code', qrCode);
            formData.append('delivery_lat', deliveryLat.value);
            formData.append('delivery_lng', deliveryLng.value);
            formData.append('delivery_notes', deliveryNotes.value);

            const submitBtn = deliveryForm.querySelector('.verify-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';

            fetch('delivery_verify.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate success
                    deliveryForm.classList.add('success-animation');
                    
                    // Show success message with order details
                    let itemsHtml = '<table class="table table-sm"><tbody>';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            itemsHtml += `<tr><td>${item.item_name}</td><td>x${item.quantity}</td><td>TZS ${(item.price * item.quantity).toLocaleString()}</td></tr>`;
                        });
                    }
                    itemsHtml += '</tbody></table>';

                    Swal.fire({
                        icon: 'success',
                        title: 'Delivery Confirmed!',
                        html: `
                            <div class="text-start">
                                <p><strong>Order #${data.order.order_number}</strong></p>
                                <p><strong>Address:</strong> ${data.order.delivery_address}</p>
                                <p><strong>Phone:</strong> ${data.order.phone}</p>
                                ${itemsHtml}
                                <hr>
                                <p><strong>Total Amount:</strong> TZS ${data.order.total_amount.toLocaleString()}</p>
                            </div>
                        `,
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'Done'
                    }).then(() => {
                        // Reset form
                        deliveryForm.reset();
                        qrInput.focus();
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirm Delivery';
                        deliveryForm.classList.remove('success-animation');
                        locationDisplay.classList.remove('active');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message || 'Unable to verify this order.',
                        confirmButtonColor: '#667eea'
                    });
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Confirm Delivery';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#667eea'
                });
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm Delivery';
            });
        });

        // Auto-focus on QR input
        window.addEventListener('load', function() {
            qrInput.focus();
        });
    </script>
</body>
</html>

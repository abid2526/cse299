<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'textilemanagementsystem');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the order ID from the URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    die("Invalid order ID.");
}

// Fetch order details
$order_query = "
    SELECT o.order_id, o.order_date, o.status, o.total_amount, 
           c.name AS customer_name, c.email, c.phone_number, c.address, 
           cr.name AS carrier_name
    FROM orders o
    INNER JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN carriers cr ON o.carrier_id = cr.carrier_id
    WHERE o.order_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param('i', $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order_details = $order_result->fetch_assoc();

if (!$order_details) {
    die("Order not found.");
}

// Fetch order items
$order_items_query = "
    SELECT f.fabric_name AS fabric_name, f.fabric_type AS type, oi.quantity, f.price_per_meter AS price, (oi.quantity * f.price_per_meter) AS total_price
    FROM order_items oi
    INNER JOIN fabrics f ON oi.fabric_id = f.fabric_id
    WHERE oi.order_id = ?";
    
$item_stmt = $conn->prepare($order_items_query);
$item_stmt->bind_param('i', $order_id);
$item_stmt->execute();
$order_items_result = $item_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Order #<?php echo $order_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .order-details, .customer-details {
            margin-bottom: 20px;
        }
        .order-details h3, .customer-details h3 {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .total {
            font-weight: bold;
            font-size: 16px;
        }
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            width: 150px;
        }
        .print-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FabricFlow System</h1>
            <h2>Invoice</h2>
        </div>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> <?php echo $order_details['order_id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo $order_details['order_date']; ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order_details['status']); ?></p>
            <p><strong>Carrier:</strong> <?php echo htmlspecialchars($order_details['carrier_name'] ?: 'N/A'); ?></p>
        </div>
        
        <div class="customer-details">
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['phone_number']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Fabric Name</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $order_items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fabric_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo number_format($row['total_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="total">Total Amount</td>
                    <td class="total"><?php echo number_format($order_details['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <button class="print-btn" onclick="window.print()">Print Invoice</button>
    </div>
</body>
</html>

<?php
$conn->close();
?>

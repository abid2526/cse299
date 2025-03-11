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

// Get the carrier_id from the GET parameter
if (!isset($_GET['carrier_id']) || empty($_GET['carrier_id'])) {
    echo "<p>Carrier ID not provided. Please go back and select a carrier.</p>";
    exit();
}

$carrier_id = intval($_GET['carrier_id']);

// Fetch carrier details
$carrier_query = "SELECT * FROM carriers WHERE carrier_id = ?";
$carrier_stmt = $conn->prepare($carrier_query);
$carrier_stmt->bind_param('i', $carrier_id);
$carrier_stmt->execute();
$carrier_result = $carrier_stmt->get_result();

if ($carrier_result->num_rows === 0) {
    echo "<p>Carrier not found. Please go back and select a valid carrier.</p>";
    exit();
}

$carrier = $carrier_result->fetch_assoc();

// Fetch orders associated with this carrier
$order_query = "
    SELECT o.order_id, o.order_date, o.total_amount, o.status
    FROM orders o
    WHERE o.carrier_id = ?
    ORDER BY o.order_date DESC
";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param('i', $carrier_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carrier Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
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
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .btn.back {
            background-color: #6c757d;
        }
        .btn.back:hover {
            background-color: #5a6268;
        }
        .no-orders {
            text-align: center;
            color: #555;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Carrier Order History</h1>
        <h2>Carrier: <?php echo htmlspecialchars($carrier['name']); ?></h2>
        <p>Email: <?php echo htmlspecialchars($carrier['email']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($carrier['phone_number']); ?></p>
        <p>Company: <?php echo htmlspecialchars($carrier['company']); ?></p>

        <a href="carrier_list.php" class="btn back">Back to Carrier List</a>

        <?php if ($order_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
                <?php while ($order = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="no-orders">No orders found for this carrier.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$order_stmt->close();
$carrier_stmt->close();
$conn->close();
?>

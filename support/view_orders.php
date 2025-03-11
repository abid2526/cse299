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

// Get customer ID from the GET parameter
if (!isset($_GET['customer_id']) || empty($_GET['customer_id'])) {
    echo "Customer ID is required.";
    exit();
}
$customer_id = intval($_GET['customer_id']);

// Fetch customer information
$customer_query = "SELECT name, email FROM customers WHERE customer_id = ?";
$customer_stmt = $conn->prepare($customer_query);
$customer_stmt->bind_param('i', $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
if ($customer_result->num_rows === 0) {
    echo "Customer not found.";
    exit();
}
$customer = $customer_result->fetch_assoc();

// Fetch orders for the customer
$order_query = "SELECT o.order_id, o.order_date, o.total_amount, o.status 
                FROM orders o 
                WHERE o.customer_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param('i', $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order History for <?php echo htmlspecialchars($customer['name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
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
        .btn-invoice {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #17a2b8;
            color: white;
            border-radius: 4px;
        }
        .btn-invoice:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order History for <?php echo htmlspecialchars($customer['name']); ?></h1>
        <p>Email: <?php echo htmlspecialchars($customer['email']); ?></p>
        <?php if ($order_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($order = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <a href="invoice.php?order_id=<?php echo $order['order_id']; ?>" class="btn-invoice">View Invoice</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No orders found for this customer.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>

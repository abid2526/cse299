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

// Fetch customers for the dropdown
$customer_query = "SELECT customer_id, name FROM customers";
$customer_result = $conn->query($customer_query);

// Fetch carriers for the dropdown
$carrier_query = "SELECT carrier_id, name FROM carriers";
$carrier_result = $conn->query($carrier_query);

// Fetch fabrics for selection
$fabric_query = "SELECT fabric_id, fabric_name, fabric_type, price_per_meter FROM fabrics WHERE stock_quantity	 > 0";
$fabric_result = $conn->query($fabric_query);

// Add a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_id = $_POST['customer_id'];
    $carrier_id = !empty($_POST['carrier_id']) ? $_POST['carrier_id'] : NULL;
    $order_date = $_POST['order_date'];
    $status = $_POST['status'];
    $total_amount = 0;

    // Insert the new order
    $order_query = "INSERT INTO orders (customer_id, carrier_id, order_date, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param('iiss', $customer_id, $carrier_id, $order_date, $status);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Process fabrics and quantities
        if (!empty($_POST['fabric_id']) && !empty($_POST['quantity'])) {
            foreach ($_POST['fabric_id'] as $key => $fabric_id) {
                $quantity = $_POST['quantity'][$key];
                if ($quantity > 0) {
                    // Fetch fabric price
                    $fabric_price_query = "SELECT price_per_meter FROM fabrics WHERE fabric_id = ?";
                    $fabric_stmt = $conn->prepare($fabric_price_query);
                    $fabric_stmt->bind_param('i', $fabric_id);
                    $fabric_stmt->execute();
                    $fabric_stmt->bind_result($price);
                    $fabric_stmt->fetch();
                    $fabric_stmt->close();

                    $total_amount += $price * $quantity;

                    // Insert into order_items
                    $order_item_query = "INSERT INTO order_items (order_id, fabric_id, quantity) VALUES (?, ?, ?)";
                    $item_stmt = $conn->prepare($order_item_query);
                    $item_stmt->bind_param('iii', $order_id, $fabric_id, $quantity);
                    $item_stmt->execute();
                    $item_stmt->close();

                    // Reduce stock in the fabrics table
                    $update_stock_query = "UPDATE fabrics SET stock_quantity = stock_quantity - ? WHERE fabric_id = ?";
                    $stock_stmt = $conn->prepare($update_stock_query);
                    $stock_stmt->bind_param('ii', $quantity, $fabric_id);
                    $stock_stmt->execute();
                    $stock_stmt->close();
                }
            }

            // Update the total amount in the orders table
            $update_order_query = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
            $update_stmt = $conn->prepare($update_order_query);
            $update_stmt->bind_param('di', $total_amount, $order_id);
            $update_stmt->execute();
            $update_stmt->close();

            echo "<div class='success-message'>Order added successfully!</div>";
        } else {
            echo "<div class='error-message'>No fabrics selected or quantities provided.</div>";
        }
    } else {
        echo "<div class='error-message'>Error creating order: " . $conn->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Order</title>
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
        h1 {
            text-align: center;
            color: #333;
        }
        .form-input {
            margin-bottom: 15px;
        }
        .form-input label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-input select, .form-input input {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .fabric-list {
            margin-bottom: 15px;
        }
        .fabric-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .fabric-list table th, .fabric-list table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .fabric-list table th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn.add {
            background-color: #28a745;
            color: white;
        }
        .btn.add:hover {
            background-color: #218838;
        }
        .error-message, .success-message {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #dc3545;
            color: white;
        }
        .success-message {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Order</h1>
        <form method="POST">
            <div class="form-input">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php while ($row = $customer_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['customer_id']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-input">
                <label for="carrier_id">Carrier</label>
                <select id="carrier_id" name="carrier_id">
                    <option value="">Select Carrier</option>
                    <?php while ($row = $carrier_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['carrier_id']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-input">
                <label for="order_date">Order Date</label>
                <input type="date" id="order_date" name="order_date" required>
            </div>
            <div class="form-input">
                <label for="status">Status</label>
                <input type="text" id="status" name="status" required>
            </div>
            <div class="fabric-list">
                <h3>Fabrics</h3>
                <table>
                    <tr>
                        <th>Select</th>
                        <th>Fabric Name</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                    <?php while ($row = $fabric_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="fabric_id[]" value="<?php echo $row['fabric_id']; ?>">
                            </td>
                            <td><?php echo htmlspecialchars($row['fabric_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['fabric_type']); ?></td>
                            <td><?php echo number_format($row['price_per_meter'], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[]" min="0" placeholder="0">
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <button type="submit" name="add_order" class="btn add">Add Order</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>

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

// Add a new fabric
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fabric'])) {
    $fabric_name = !empty($_POST['new_fabric_name']) ? $_POST['new_fabric_name'] : $_POST['fabric_name'];
    $fabric_type = !empty($_POST['new_fabric_type']) ? $_POST['new_fabric_type'] : $_POST['fabric_type'];
    $price_per_meter = $_POST['price_per_meter'];
    $stock_quantity = $_POST['stock_quantity'];

    // Validate inputs
    if (empty($fabric_name) || empty($fabric_type)) {
        echo "<div class='error-message'>Fabric name and type are required!</div>";
    } else {
        $insert_query = "INSERT INTO fabrics (fabric_name, fabric_type, price_per_meter, stock_quantity) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('ssdi', $fabric_name, $fabric_type, $price_per_meter, $stock_quantity);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Fabric added successfully.</div>";
        } else {
            echo "<div class='error-message'>Error adding fabric: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Delete a fabric
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fabric'])) {
    $fabric_id = $_POST['fabric_id'];

    $delete_query = "DELETE FROM fabrics WHERE fabric_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $fabric_id);

    if ($stmt->execute()) {
        echo "<div class='success-message'>Fabric deleted successfully.</div>";
    } else {
        echo "<div class='error-message'>Error deleting fabric: " . $conn->error . "</div>";
    }

    $stmt->close();
}

// Fetch all fabrics
$query = "SELECT * FROM fabrics";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Fabrics</title>
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
        .form-input {
            margin-bottom: 15px;
        }
        .form-input label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-input input, .form-input select {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn.add {
            background-color: #28a745;
            color: white;
        }
        .btn.add:hover {
            background-color: #218838;
        }
        .btn.delete {
            background-color: #dc3545;
            color: white;
        }
        .btn.delete:hover {
            background-color: #c82333;
        }
        .success-message, .error-message {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .success-message {
            background-color: #28a745;
            color: white;
        }
        .error-message {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Fabrics</h1>

        <!-- Add Fabric Form -->
        <form method="POST">
            <div class="form-input">
                <label for="fabric_name">Fabric Name</label>
                <select id="fabric_name" name="fabric_name">
                    <option value="">Select Fabric Name</option>
                    <option value="Cotton">Cotton</option>
                    <option value="Silk">Silk</option>
                    <option value="Polyester">Polyester</option>
                    <option value="Wool">Wool</option>
                    <option value="Denim">Denim</option>
                    <option value="Linen">Linen</option>
                    <option value="Rayon">Rayon</option>
                    <option value="Nylon">Nylon</option>
                </select>
                <input type="text" name="new_fabric_name" placeholder="Or Add New Fabric Name" />
            </div>

            <div class="form-input">
                <label for="fabric_type">Fabric Type</label>
                <select id="fabric_type" name="fabric_type">
                    <option value="">Select Fabric Type</option>
                    <option value="Natural">Natural</option>
                    <option value="Synthetic">Synthetic</option>
                    <option value="Blend">Blend</option>
                    <option value="Semi-synthetic">Semi-synthetic</option>
                </select>
                <input type="text" name="new_fabric_type" placeholder="Or Add New Fabric Type" />
            </div>

            <div class="form-input">
                <label for="price_per_meter">Price Per Meter</label>
                <input type="number" step="0.01" id="price_per_meter" name="price_per_meter" required>
            </div>
            <div class="form-input">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" required>
            </div>
            <button type="submit" name="add_fabric" class="btn add">Add Fabric</button>
        </form>

        <!-- Fabric Table -->
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Fabric ID</th>
                    <th>Fabric Name</th>
                    <th>Fabric Type</th>
                    <th>Price Per Meter</th>
                    <th>Stock Quantity</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['fabric_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['fabric_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['fabric_type']); ?></td>
                        <td><?php echo number_format($row['price_per_meter'], 2); ?></td>
                        <td><?php echo $row['stock_quantity']; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="fabric_id" value="<?php echo $row['fabric_id']; ?>">
                                <button type="submit" name="delete_fabric" class="btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No fabrics found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
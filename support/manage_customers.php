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

// Add a new customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $registration_date = $_POST['registration_date'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($registration_date)) {
        echo "<div class='error-message'>Name, Email, and Registration Date are required!</div>";
    } else {
        $insert_query = "INSERT INTO customers (name, email, phone_number, address, registration_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('sssss', $name, $email, $phone_number, $address, $registration_date);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Customer added successfully.</div>";
        } else {
            echo "<div class='error-message'>Error adding customer: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Delete a customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];

    $delete_query = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $customer_id);

    if ($stmt->execute()) {
        echo "<div class='success-message'>Customer deleted successfully.</div>";
    } else {
        echo "<div class='error-message'>Error deleting customer: " . $conn->error . "</div>";
    }

    $stmt->close();
}

// Fetch all customers
$query = "SELECT * FROM customers";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
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
        .form-input input, .form-input textarea {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-input textarea {
            height: 60px;
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
        <h1>Manage Customers</h1>

        <!-- Add Customer Form -->
        <form method="POST">
            <div class="form-input">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-input">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-input">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number">
            </div>
            <div class="form-input">
                <label for="address">Address</label>
                <textarea id="address" name="address"></textarea>
            </div>
            <div class="form-input">
                <label for="registration_date">Registration Date</label>
                <input type="date" id="registration_date" name="registration_date" required>
            </div>
            <button type="submit" name="add_customer" class="btn add">Add Customer</button>
        </form>

        <!-- Customer Table -->
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['registration_date']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                                <button type="submit" name="delete_customer" class="btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No customers found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>

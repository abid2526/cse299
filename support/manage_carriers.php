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

// Add a new carrier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_carrier'])) {
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $company = $_POST['company'];
    $date_of_registration = $_POST['date_of_registration'];

    // Validate inputs
    if (empty($name) || empty($phone_number) || empty($email) || empty($date_of_registration)) {
        echo "<div class='error-message'>All fields are required!</div>";
    } else {
        $insert_query = "INSERT INTO carriers (name, phone_number, email, company, date_of_registration) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('sssss', $name, $phone_number, $email, $company, $date_of_registration);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Carrier added successfully.</div>";
        } else {
            echo "<div class='error-message'>Error adding carrier: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Delete a carrier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_carrier'])) {
    $carrier_id = $_POST['carrier_id'];

    $delete_query = "DELETE FROM carriers WHERE carrier_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $carrier_id);

    if ($stmt->execute()) {
        echo "<div class='success-message'>Carrier deleted successfully.</div>";
    } else {
        echo "<div class='error-message'>Error deleting carrier: " . $conn->error . "</div>";
    }

    $stmt->close();
}

// Fetch all carriers
$query = "SELECT * FROM carriers";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Carriers</title>
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
        .form-input input {
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
        <h1>Manage Carriers</h1>

        <!-- Add Carrier Form -->
        <form method="POST">
            <div class="form-input">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-input">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required>
            </div>
            <div class="form-input">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-input">
                <label for="company">Company</label>
                <input type="text" id="company" name="company">
            </div>
            <div class="form-input">
                <label for="date_of_registration">Date of Registration</label>
                <input type="date" id="date_of_registration" name="date_of_registration" required>
            </div>
            <button type="submit" name="add_carrier" class="btn add">Add Carrier</button>
        </form>

        <!-- Carrier Table -->
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Carrier ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Date of Registration</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['carrier_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['company']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_registration']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="carrier_id" value="<?php echo $row['carrier_id']; ?>">
                                <button type="submit" name="delete_carrier" class="btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No carriers found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>

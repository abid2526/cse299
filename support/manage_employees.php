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

// Add a new employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $date_of_joining = $_POST['date_of_joining'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($role) || empty($date_of_joining)) {
        echo "<div class='error-message'>All fields are required!</div>";
    } else {
        $insert_query = "INSERT INTO employees (name, email, role, date_of_joining) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('ssss', $name, $email, $role, $date_of_joining);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Employee added successfully.</div>";
        } else {
            echo "<div class='error-message'>Error adding employee: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// Delete an employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee'])) {
    $employee_id = $_POST['employee_id'];

    $delete_query = "DELETE FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $employee_id);

    if ($stmt->execute()) {
        echo "<div class='success-message'>Employee deleted successfully.</div>";
    } else {
        echo "<div class='error-message'>Error deleting employee: " . $conn->error . "</div>";
    }

    $stmt->close();
}

// Fetch all employees
$query = "SELECT * FROM employees";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Employees</title>
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
        <h1>Manage Employees</h1>

        <!-- Add Employee Form -->
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
                <label for="role">Role</label>
                <input type="text" id="role" name="role" required>
            </div>
            <div class="form-input">
                <label for="date_of_joining">Date of Joining</label>
                <input type="date" id="date_of_joining" name="date_of_joining" required>
            </div>
            <button type="submit" name="add_employee" class="btn add">Add Employee</button>
        </form>

        <!-- Employee Table -->
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Date of Joining</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['employee_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_joining']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="employee_id" value="<?php echo $row['employee_id']; ?>">
                                <button type="submit" name="delete_employee" class="btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No employees found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>

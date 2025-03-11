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

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Sorting functionality
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'carrier_id'; // Default sort column
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC'; // Default sort order is ASC

// Whitelist allowed columns for sorting to prevent SQL injection
$allowed_sort_columns = ['carrier_id', 'name', 'email', 'registration_date'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'carrier_id';
}

// Prepare query with sorting and search
if ($search) {
    $query = "SELECT * FROM carriers WHERE name LIKE ? OR email LIKE ? ORDER BY $sort $order";
    $stmt = $conn->prepare($query);
    $search_param = "%$search%";
    $stmt->bind_param('ss', $search_param, $search_param);
} else {
    $query = "SELECT * FROM carriers ORDER BY $sort $order";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carrier List</title>
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
        .search-bar {
            text-align: right;
            margin-bottom: 15px;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            font-size: 14px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-bar button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        th a {
            color: white;
            text-decoration: none;
        }
        th a:hover {
            text-decoration: underline;
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
        .btn.view-orders {
            background-color: #28a745;
        }
        .btn.view-orders:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Carrier List</h1>

        <!-- Search Bar -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Carrier Table -->
        <table>
            <tr>
                <th><a href="?sort=carrier_id&order=<?php echo ($sort === 'carrier_id' && $order === 'ASC') ? 'desc' : 'asc'; ?>">Carrier ID</a></th>
                <th><a href="?sort=name&order=<?php echo ($sort === 'name' && $order === 'ASC') ? 'desc' : 'asc'; ?>">Name</a></th>
                <th><a href="?sort=email&order=<?php echo ($sort === 'email' && $order === 'ASC') ? 'desc' : 'asc'; ?>">Email</a></th>
                <th>Phone Number</th>
                <th>Address</th>
                <th><a href="?sort=registration_date&order=<?php echo ($sort === 'registration_date' && $order === 'ASC') ? 'desc' : 'asc'; ?>">Registration Date</a></th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['carrier_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['company']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_registration']); ?></td>
                        <td>
                        <a href="view_carrier_orders.php?carrier_id=<?php echo $row['carrier_id']; ?>" class="btn view-orders">View Orders</a>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No carriers found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginRegister/login.php");
    exit();
}

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Include database connection
include('../CRUD/db_connection.php');

// Handle Add Purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_purchase'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);

    // Insert new purchase into the database
    $query = "INSERT INTO purchases (user_id, item_name, amount, purchase_date) 
              VALUES ('$user_id', '$item_name', '$amount', '$purchase_date')";
    if (mysqli_query($conn, $query)) {
        $success_message = "Purchase added successfully!";
        
        // Redirect after successfully adding the purchase to prevent resubmission
        header('Location: delete_purchase.php');
        exit();
    } else {
        $error_message = "Error adding purchase: " . mysqli_error($conn);
    }
}

// Handle Edit Purchase
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    // Fetch the current data for the selected purchase
    $query = "SELECT * FROM purchases WHERE id = '$edit_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $purchase = mysqli_fetch_assoc($result);

    if (!$purchase) {
        $error_message = "Purchase not found.";
    }

    // Update the purchase if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_purchase'])) {
        $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);

        $update_query = "UPDATE purchases SET item_name = '$item_name', amount = '$amount', purchase_date = '$purchase_date' 
                         WHERE id = '$edit_id' AND user_id = '$user_id'";

        if (mysqli_query($conn, $update_query)) {
            $success_message = "Purchase updated successfully!";
            header('Location: delete_purchase.php'); // Redirect after successful update
            exit();
        } else {
            $error_message = "Error updating purchase: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Purchase
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete purchase from the database
    $delete_query = "DELETE FROM purchases WHERE id = '$delete_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Purchase deleted successfully!";
    } else {
        $error_message = "Error deleting purchase: " . mysqli_error($conn);
    }
}

// Fetch all purchases for the user
$query = "SELECT * FROM purchases WHERE user_id = '$user_id' ORDER BY purchase_date DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Purchases</title>
    <style>
            /* General Reset and Global Styles */
            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: #2980b9;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Header */
        .header {
            text-align: center;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        /* Dashboard Container */
        .dashboard-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Purchase Form Styles */
        .purchase-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .purchase-form h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .purchase-form label {
            font-size: 1rem;
            margin-bottom: 5px;
            display: block;
        }

        .purchase-form input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .purchase-form button {
            width: 100%;
            padding: 12px;
            background-color: #2980b9;
            color: white;
            font-size: 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .purchase-form button:hover {
            background-color: #1abc9c;
        }

        /* Error and Success Messages */
        .error {
            color: #e74c3c;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .success {
            color: #2ecc71;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        /* Purchases List Table */
        .purchase-list {
            margin-top: 30px;
        }

        .purchase-list h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .purchase-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .purchase-list th,
        .purchase-list td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .purchase-list th {
            background-color: #2980b9;
            color: white;
        }

        .purchase-list tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .purchase-list tr:hover {
            background-color: #ecf0f1;
        }

        .purchase-list td a {
            margin-right: 10px;
            color: #2980b9;
        }

        .purchase-list td a:hover {
            color: #16a085;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }

            .purchase-form {
                padding: 15px;
            }

            .purchase-form button {
                padding: 10px;
            }

            .purchase-list table {
                font-size: 0.9rem;
            }

            .purchase-list th,
            .purchase-list td {
                padding: 10px;
            }
        }

        /* Button to go back to dashboard */
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2980b9;
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #16a085;
        }

        /* Edit and Delete Buttons */
        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Purchase Management</h1>
        <p>Manage, add, and edit your purchases</p>
    </div>

    <div class="dashboard-container">

        <!-- Add Purchase Form -->
        <div class="purchase-form">
            <h2>Add a New Purchase</h2>

            <?php
            if (isset($success_message)) {
                echo "<p class='success'>$success_message</p>";
            }

            if (isset($error_message)) {
                echo "<p class='error'>$error_message</p>";
            }
            ?>

            <form action="delete_purchase.php" method="POST">
                <label for="item_name">Item Name:</label>
                <input type="text" name="item_name" id="item_name" required>
                <br>

                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" required>
                <br>

                <label for="purchase_date">Purchase Date:</label>
                <input type="date" name="purchase_date" id="purchase_date" required>
                <br>

                <button type="submit" name="add_purchase">Add Purchase</button>
            </form>
        </div>

        <!-- Edit Purchase Form -->
        <?php if (isset($purchase)) { ?>
            <div class="purchase-form">
                <h2>Edit Purchase</h2>

                <form action="delete_purchase.php?edit_id=<?php echo $purchase['id']; ?>" method="POST">
                    <label for="item_name">Item Name:</label>
                    <input type="text" name="item_name" id="item_name" value="<?php echo htmlspecialchars($purchase['item_name']); ?>" required>
                    <br>

                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" value="<?php echo htmlspecialchars($purchase['amount']); ?>" required>
                    <br>

                    <label for="purchase_date">Purchase Date:</label>
                    <input type="date" name="purchase_date" id="purchase_date" value="<?php echo htmlspecialchars($purchase['purchase_date']); ?>" required>
                    <br>

                    <button type="submit" name="update_purchase">Update Purchase</button>
                </form>
            </div>
        <?php } ?>

        <!-- Display Purchases Table -->
        <div class="purchase-list">
            <h2>Your Purchase Records</h2>

            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Amount</th>
                        <th>Purchase Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                                    <td>" . number_format($row['amount'], 2) . "</td>
                                    <td>" . date('F j, Y', strtotime($row['purchase_date'])) . "</td>
                                    <td>
                                        <a href='delete_purchase.php?edit_id=" . $row['id'] . "'>
                                            <button class='btn-edit'>Edit</button>
                                        </a> |
                                        <a href='delete_purchase.php?delete_id=" . $row['id'] . "' 
                                            onclick='return confirm(\"Are you sure you want to delete this purchase?\");'>
                                            <button class='btn-delete'>Delete</button>
                                        </a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No purchase records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>

</body>

</html>

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

// Handle Add Expense
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    // Insert new expense into the database
    $query = "INSERT INTO expenses (user_id, category, amount, date) 
              VALUES ('$user_id', '$category', '$amount', '$date')";
    if (mysqli_query($conn, $query)) {
        // Redirect to avoid re-submission after refresh
        header('Location: view_expenses.php');
        exit();
    } else {
        $error_message = "Error adding expense: " . mysqli_error($conn);
    }
}

// Handle Edit Expense (Populating form with current values)
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    // Fetch the current data for the selected expense
    $query = "SELECT * FROM expenses WHERE id = '$edit_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $expense = mysqli_fetch_assoc($result);

    if (!$expense) {
        $error_message = "Expense not found.";
    }

    // Process the form submission for updating the expense
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_expense'])) {
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);

        // Update the expense in the database
        $update_query = "UPDATE expenses SET category = '$category', amount = '$amount', date = '$date' 
                         WHERE id = '$edit_id' AND user_id = '$user_id'";

        if (mysqli_query($conn, $update_query)) {
            // Redirect to avoid re-submission after refresh
            header('Location: view_expenses.php');
            exit();
        } else {
            $error_message = "Error updating expense: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Expense
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete expense from the database
    $delete_query = "DELETE FROM expenses WHERE id = '$delete_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Expense deleted successfully!";
    } else {
        $error_message = "Error deleting expense: " . mysqli_error($conn);
    }
}

// Fetch all expenses for the user
$query = "SELECT * FROM expenses WHERE user_id = '$user_id' ORDER BY date DESC";
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
    <title>Manage Expenses</title>
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

        /* Form Styles */
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-container h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .form-container label {
            font-size: 1rem;
            margin-bottom: 5px;
            display: block;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container button {
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

        .form-container button:hover {
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

        /* Expense List Table */
        .expense-list {
            margin-top: 30px;
        }

        .expense-list h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .expense-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .expense-list th,
        .expense-list td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .expense-list th {
            background-color: #2980b9;
            color: white;
        }

        .expense-list tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .expense-list tr:hover {
            background-color: #ecf0f1;
        }

        .expense-list td a {
            margin-right: 10px;
            color: #2980b9;
        }

        .expense-list td a:hover {
            color: #16a085;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }

            .form-container {
                padding: 15px;
            }

            .form-container button {
                padding: 10px;
            }

            .expense-list table {
                font-size: 0.9rem;
            }

            .expense-list th,
            .expense-list td {
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

    <div class="dashboard-container">

        <!-- Add Expense Form -->
        <div class="form-container">
            <h2><?php echo isset($expense) ? 'Edit Expense' : 'Add a New Expense'; ?></h2>

            <?php
            if (isset($success_message)) {
                echo "<p class='success'>$success_message</p>";
            }

            if (isset($error_message)) {
                echo "<p class='error'>$error_message</p>";
            }
            ?>

            <form action="view_expenses.php<?php echo isset($expense) ? '?edit_id=' . $expense['id'] : ''; ?>" method="POST">
                <label for="category">Category:</label>
                <input type="text" name="category" id="category" required value="<?php echo isset($expense) ? htmlspecialchars($expense['category']) : ''; ?>">

                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" required value="<?php echo isset($expense) ? htmlspecialchars($expense['amount']) : ''; ?>">

                <label for="date">Date:</label>
                <input type="date" name="date" id="date" required value="<?php echo isset($expense) ? htmlspecialchars($expense['date']) : ''; ?>">

                <button type="submit" name="<?php echo isset($expense) ? 'update_expense' : 'add_expense'; ?>">
                    <?php echo isset($expense) ? 'Update Expense' : 'Add Expense'; ?>
                </button>
            </form>
        </div>

        <!-- Display Expenses Table -->
        <div class="expense-list">
            <h2>Your Expense Records</h2>

            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['category']) . "</td>
                                    <td>" . number_format($row['amount'], 2) . "</td>
                                    <td>" . date('F j, Y', strtotime($row['date'])) . "</td>
                                    <td>
                                        <a href='view_expenses.php?edit_id=" . $row['id'] . "'>
                                            <button class='btn-edit'>Edit</button>
                                        </a> |
                                        <a href='view_expenses.php?delete_id=" . $row['id'] . "' 
                                            onclick='return confirm(\"Are you sure you want to delete this expense?\");'>
                                            <button class='btn-delete'>Delete</button>
                                        </a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No expense records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>

</body>

</html>

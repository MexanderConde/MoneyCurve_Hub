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

// Handle Add Income
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_income'])) {
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $source = mysqli_real_escape_string($conn, $_POST['source']);
    $date_added = mysqli_real_escape_string($conn, $_POST['date_added']);

    // Insert new income into the database
    $query = "INSERT INTO income (user_id, amount, source, date_added) 
              VALUES ('$user_id', '$amount', '$source', '$date_added')";
    if (mysqli_query($conn, $query)) {
        $success_message = "Income added successfully!";
        header("Location: add_income.php"); // Redirect to avoid form resubmission
        exit();
    } else {
        $error_message = "Error adding income: " . mysqli_error($conn);
    }
}

// Handle Edit Income
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    // Fetch the current data for the selected income record
    $query = "SELECT * FROM income WHERE id = '$edit_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $income = mysqli_fetch_assoc($result);

    if (!$income) {
        $error_message = "Income not found.";
    }

    // Update the income if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_income'])) {
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $source = mysqli_real_escape_string($conn, $_POST['source']);
        $date_added = mysqli_real_escape_string($conn, $_POST['date_added']);

        $update_query = "UPDATE income SET amount = '$amount', source = '$source', date_added = '$date_added' 
                         WHERE id = '$edit_id' AND user_id = '$user_id'";

        if (mysqli_query($conn, $update_query)) {
            $success_message = "Income updated successfully!";
            header("Location: add_income.php"); // Redirect after successful update
            exit();
        } else {
            $error_message = "Error updating income: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Income
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete income from the database
    $delete_query = "DELETE FROM income WHERE id = '$delete_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Income deleted successfully!";
    } else {
        $error_message = "Error deleting income: " . mysqli_error($conn);
    }
}

// Fetch all incomes for the user
$query = "SELECT * FROM income WHERE user_id = '$user_id' ORDER BY date_added DESC";
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
    <title>Manage Income</title>
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
            background-color: #16a085;
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

        /* Income List Table */
        .income-list {
            margin-top: 30px;
        }

        .income-list h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .income-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .income-list th,
        .income-list td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .income-list th {
            background-color: #2980b9;
            color: white;
        }

        .income-list tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .income-list tr:hover {
            background-color: #ecf0f1;
        }

        .income-list td a {
            margin-right: 10px;
            color: #2980b9;
        }

        .income-list td a:hover {
            color: #16a085;
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

    </style>
</head>

<body>

    <div class="header">
        <h1>Income Management</h1>
        <p>Manage, add, and edit your income records</p>
    </div>

    <div class="dashboard-container">

        <!-- Add Income Form -->
        <div class="form-container">
            <h2>Add a New Income</h2>

            <?php
            if (isset($success_message)) {
                echo "<p class='success'>$success_message</p>";
            }

            if (isset($error_message)) {
                echo "<p class='error'>$error_message</p>";
            }
            ?>

            <form action="add_income.php" method="POST">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" required>
                <br>

                <label for="source">Source:</label>
                <input type="text" name="source" id="source" required>
                <br>

                <label for="date_added">Date Added:</label>
                <input type="date" name="date_added" id="date_added" required>
                <br>

                <button type="submit" name="add_income">Add Income</button>
            </form>
        </div>

        <!-- Edit Income Form -->
        <?php if (isset($income)) { ?>
            <div class="form-container">
                <h2>Edit Income</h2>

                <form action="add_income.php?edit_id=<?php echo $income['id']; ?>" method="POST">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" value="<?php echo htmlspecialchars($income['amount']); ?>" required>
                    <br>

                    <label for="source">Source:</label>
                    <input type="text" name="source" id="source" value="<?php echo htmlspecialchars($income['source']); ?>" required>
                    <br>

                    <label for="date_added">Date Added:</label>
                    <input type="date" name="date_added" id="date_added" value="<?php echo htmlspecialchars($income['date_added']); ?>" required>
                    <br>

                    <button type="submit" name="update_income">Update Income</button>
                </form>
            </div>
        <?php } ?>

        <!-- Display Income Table -->
        <div class="income-list">
            <h2>Your Income Records</h2>

            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Source</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>" . number_format($row['amount'], 2) . "</td>
                                    <td>" . htmlspecialchars($row['source']) . "</td>
                                    <td>" . date('F j, Y', strtotime($row['date_added'])) . "</td>
                                    <td>
                                        <a href='add_income.php?edit_id=" . $row['id'] . "'>
                                            <button class='btn-edit'>Edit</button>
                                        </a> |
                                        <a href='add_income.php?delete_id=" . $row['id'] . "' 
                                            onclick='return confirm(\"Are you sure you want to delete this income?\");'>
                                            <button class='btn-delete'>Delete</button>
                                        </a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No income records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>

</body>

</html>

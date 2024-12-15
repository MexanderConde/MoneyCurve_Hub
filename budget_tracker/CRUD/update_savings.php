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

// Handle Add Savings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_savings'])) {
    $goal_name = mysqli_real_escape_string($conn, $_POST['goal_name']);
    $amount_saved = mysqli_real_escape_string($conn, $_POST['amount_saved']);
    $target_amount = mysqli_real_escape_string($conn, $_POST['target_amount']);
    $date_saved = mysqli_real_escape_string($conn, $_POST['date_saved']);

    // Insert new savings into the database
    $query = "INSERT INTO savings (user_id, goal_name, amount_saved, target_amount, date_saved) 
              VALUES ('$user_id', '$goal_name', '$amount_saved', '$target_amount', '$date_saved')";
    if (mysqli_query($conn, $query)) {
        header('Location: update_savings.php'); // Redirect to prevent form resubmission
        exit();
    } else {
        $error_message = "Error adding savings goal: " . mysqli_error($conn);
    }
}

// Handle Edit Savings
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    // Fetch the current data for the selected savings goal
    $query = "SELECT * FROM savings WHERE id = '$edit_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $savings = mysqli_fetch_assoc($result);

    if (!$savings) {
        $error_message = "Savings goal not found.";
    }

    // Update the savings if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_savings'])) {
        $goal_name = mysqli_real_escape_string($conn, $_POST['goal_name']);
        $amount_saved = mysqli_real_escape_string($conn, $_POST['amount_saved']);
        $target_amount = mysqli_real_escape_string($conn, $_POST['target_amount']);
        $date_saved = mysqli_real_escape_string($conn, $_POST['date_saved']);

        $update_query = "UPDATE savings SET goal_name = '$goal_name', amount_saved = '$amount_saved', target_amount = '$target_amount', date_saved = '$date_saved' 
                         WHERE id = '$edit_id' AND user_id = '$user_id'";

        if (mysqli_query($conn, $update_query)) {
            header('Location: update_savings.php'); // Redirect after successful update
            exit();
        } else {
            $error_message = "Error updating savings goal: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Savings
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete savings goal from the database
    $delete_query = "DELETE FROM savings WHERE id = '$delete_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $delete_query)) {
        header('Location: update_savings.php'); // Redirect after successful deletion
        exit();
    } else {
        $error_message = "Error deleting savings goal: " . mysqli_error($conn);
    }
}

// Fetch all savings for the user
$query = "SELECT * FROM savings WHERE user_id = '$user_id' ORDER BY date_saved DESC";
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
    <title>Update Savings</title>
    <style>
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

        .dashboard-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

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

        /* Savings List Table */
        .savings-list {
            margin-top: 30px;
        }

        .savings-list h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .savings-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .savings-list th,
        .savings-list td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .savings-list th {
            background-color: #2980b9;
            color: white;
        }

        .savings-list tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .savings-list tr:hover {
            background-color: #ecf0f1;
        }

        .savings-list td a {
            margin-right: 10px;
            color: #2980b9;
        }

        .savings-list td a:hover {
            color: #16a085;
        }

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

        /* Button Styles */
        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;  /* Ensure anchor behaves like a button */
            text-decoration: none;  /* Remove default underline */
            color: white !important;  /* Force text color to white */
            text-align: center;  /* Align text in the center */
        }

        /* Edit Button */
        .btn-edit {
            background-color: #28a745;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        /* Delete Button */
        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Update Your Savings Goals</h1>
    </div>

    <div class="dashboard-container">
        <?php
        if (isset($error_message)) {
            echo "<div class='error'>$error_message</div>";
        }
        if (isset($success_message)) {
            echo "<div class='success'>$success_message</div>";
        }
        ?>

        <!-- Add/Edit Savings Form -->
        <div class="form-container">
            <h2><?php echo isset($savings) ? 'Edit Savings Goal' : 'Add Savings Goal'; ?></h2>
            <form method="POST">
                <label for="goal_name">Goal Name</label>
                <input type="text" id="goal_name" name="goal_name" value="<?php echo isset($savings) ? $savings['goal_name'] : ''; ?>" required>

                <label for="amount_saved">Amount Saved</label>
                <input type="number" id="amount_saved" name="amount_saved" value="<?php echo isset($savings) ? $savings['amount_saved'] : ''; ?>" required>

                <label for="target_amount">Target Amount</label>
                <input type="number" id="target_amount" name="target_amount" value="<?php echo isset($savings) ? $savings['target_amount'] : ''; ?>" required>

                <label for="date_saved">Date Saved</label>
                <input type="date" id="date_saved" name="date_saved" value="<?php echo isset($savings) ? $savings['date_saved'] : ''; ?>" required>

                <button type="submit" name="<?php echo isset($savings) ? 'update_savings' : 'add_savings'; ?>">
                    <?php echo isset($savings) ? 'Update Savings' : 'Add Savings'; ?>
                </button>
            </form>
        </div>

        <!-- Savings List -->
        <div class="savings-list">
            <h2>Your Savings Goals</h2>
            <table>
                <tr>
                    <th>Goal Name</th>
                    <th>Amount Saved</th>
                    <th>Target Amount</th>
                    <th>Date Saved</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['goal_name']; ?></td>
                    <td><?php echo $row['amount_saved']; ?></td>
                    <td><?php echo $row['target_amount']; ?></td>
                    <td><?php echo $row['date_saved']; ?></td>
                    <td>
                        <a href="update_savings.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="update_savings.php?delete_id=<?php echo $row['id']; ?>" class="btn-delete">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>
</body>

</html>

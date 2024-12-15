<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Session not set: Please log in again.";
    header("Location: ../LoginRegister/login.php");
    exit();
} else {
    // Debugging: Display user_id (remove this line in production)
    echo "Session user_id: " . $_SESSION['user_id'];
}

include('../CRUD/db_connection.php');

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Fetch savings data for the logged-in user
$query = "SELECT * FROM savings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Prepare data for the chart
$savings_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $savings_data[] = [
        'goal_name' => $row['goal_name'],
        'amount_saved' => $row['amount_saved'],
        'target_amount' => $row['target_amount']
    ];

}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome Icons -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for charts -->
    <style>
        /* General reset and font settings */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #ecf0f1;
            color: #333;
        }

        h1, p {
            margin-bottom: 15px;
        }

        /* Dashboard container */
        .dashboard-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header styling */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        /* Dashboard content */
        .dashboard-content {
            display: flex;
            justify-content: space-between;
            gap: 30px;
        }

        .dashboard-nav ul {
            list-style-type: none;
            padding: 0;
            width: 25%;
        }

        .dashboard-nav li {
            margin: 20px 0;
        }

        .dashboard-nav a {
            text-decoration: none;
            color: #2980b9;
            font-size: 1.3rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .dashboard-nav a:hover {
            background-color: #ecf0f1;
            color: #1abc9c;
        }

        /* Chart Section */
        .chart-section {
            width: 65%;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        #savingsChart {
            width: 100%;
            height: 300px;
        }

        /* Footer and logout button styling */
        .footer {
            text-align: center;
            margin-top: 40px;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #e74c3c;
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-content {
                flex-direction: column;
                gap: 20px;
            }

            .dashboard-nav ul {
                width: 100%;
            }

            .chart-section {
                width: 100%;
            }
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Here is your dashboard.</p>
        </header>

        <div class="dashboard-content">
            <!-- Dashboard Menu -->
            <nav class="dashboard-nav">
                <ul>
                    <li><a href="add_income.php"><i class="fas fa-plus-circle"></i> Add Income</a></li>
                    <li><a href="view_expenses.php"><i class="fas fa-list-ul"></i> View Expenses</a></li>
                    <li><a href="update_savings.php"><i class="fas fa-piggy-bank"></i> Update Savings</a></li>
                    <li><a href="delete_purchase.php"><i class="fas fa-trash-alt"></i> Delete Purchase</a></li>
                </ul>
            </nav>

            <!-- Interactive Chart -->
            <section class="chart-section">
                <canvas id="savingsChart"></canvas>
            </section>
        </div>

        <footer class="footer">
            <a href="../LoginRegister/logout.php" class="logout-btn">Logout</a>
        </footer>   
    </div>

    <script>
        // Pass PHP data into JavaScript
        var savingsData = <?php echo json_encode($savings_data); ?>;

        // Prepare chart data
        var labels = savingsData.map(function(item) {
            return item.goal_name;
        });

        var amountsSaved = savingsData.map(function(item) {
            return item.amount_saved;
        });

        var targetAmounts = savingsData.map(function(item) {
            return item.target_amount;
        });

        // Chart.js code to create the savings bar chart
        var ctx = document.getElementById('savingsChart').getContext('2d');
        var savingsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Amount Saved',
                    data: amountsSaved,
                    backgroundColor: '#2980b9',
                    borderColor: '#2980b9',
                    borderWidth: 1
                }, {
                    label: 'Target Amount',
                    data: targetAmounts,
                    backgroundColor: '#16a085',
                    borderColor: '#16a085',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

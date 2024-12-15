<?php
session_start();
include('../CRUD/db_connection.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  $query = "SELECT * FROM users WHERE username = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, 's', $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verify the password
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id']; // Ensure you fetch and set the correct user ID from the database
      $_SESSION['user_id'] = $user['id']; // Save session with user ID
      $_SESSION['username'] = $username; // Save username for display purposes
      header('Location: ../CRUD/dashboard.php'); // Redirect to dashboard
      exit();
    } else {
      $error_message = "Invalid username or password.";
    }
  } else {
    $error_message = "Invalid username or password.";
  }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    /* Form Container Styling */
    .auth-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: #f8f9fa;
      padding: 20px;
    }

    .auth-form {
      background: #ffffff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      padding: 30px;
      border-radius: 8px;
      width: 100%;
      max-width: 400px;
    }

    .auth-form h2 {
      margin-bottom: 20px;
      font-size: 1.8rem;
      color: #333;
      text-align: center;
    }

    .auth-form input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1rem;
    }

    .auth-form button {
      width: 105.3%;
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px;
      font-size: 1.1rem;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .auth-form button:hover {
      background-color: #0056b3;
    }

    .auth-form .link {
      display: block;
      margin-top: 15px;
      font-size: 0.9rem;
      text-align: center;
    }

    .auth-form .link a {
      color: #007bff;
      text-decoration: none;
      transition: color 0.3s;
    }

    .auth-form .link a:hover {
      color: #0056b3;
    }
  </style>
</head>

<body>
  <h2>MoneyCurve Hub</h2>

  <?php if ($error_message): ?>
    <div class="error"><?= htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <div class="auth-container">
    <form class="auth-form" action="login.php" method="POST">
      <h2>Login</h2>
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <div class="link">
        Don't have an account? <a href="register.php">Register here</a>
      </div>
    </form>
  </div>

</body>

</html>
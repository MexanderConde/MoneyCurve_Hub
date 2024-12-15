<?php
// register.php
session_start();
include('../CRUD/db_connection.php');

// Initialize message variables
$error_message = '';
$success_message = ''; // Added to avoid undefined variable notice

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

  if ($password !== $confirm_password) {
    $error_message = "Passwords do not match.";
  } else {
    // Check if user exists
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      if (mysqli_num_rows($result) > 0) {
        $error_message = "Username or email already exists.";
      } else {
        // Insert new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        if ($stmt) {
          mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashed_password);

          if (mysqli_stmt_execute($stmt)) {
            // Redirect to login page after successful registration
            header('Location: login.php?success=registered');
            exit();
          } else {
            $error_message = "Error: " . mysqli_error($conn);
          }
        } else {
          $error_message = "Error preparing statement for user registration: " . mysqli_error($conn);
        }
      }
    } else {
      $error_message = "Error preparing statement for user check: " . mysqli_error($conn);
    }
  }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <style>
    .auth-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: #f8f9fa;
      padding: 20px;
      background-color: #2c3e50;
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
  <?php elseif ($success_message): ?>
    <div class="success"><?= $success_message; ?></div>
  <?php endif; ?>

  <div class="auth-container">
    <form class="auth-form" action="register.php" method="POST">
      <h2>Register</h2>
      <input type="text" name="username" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit">Register</button>
      <div class="link">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </form>
  </div>

</body>

</html>
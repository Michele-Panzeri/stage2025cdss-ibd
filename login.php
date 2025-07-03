<?php
session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Cerca l'utente nel database
    $stmt = $conn->prepare("SELECT id, password, token FROM medici WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifica la password
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["email"] = $email;
            $_SESSION["token"] = $row["token"];

            header("Location: dashboard.php");
            exit();
        } else {
            echo "Password errata.";
        }
    } else {
        echo "Email non trovata.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            margin: 0;
            padding: 40px 20px; /* stesso padding del form di registrazione */
            display: flex;
            justify-content: center;
        }
        .container {
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            margin: auto;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #388e3c;
        }
        input {
            width: 250px;
            margin: 10px auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fff;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            display: block;
        }
        input:focus {
            border-color: #66bb6a;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #43a047;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #388e3c;
        }
        .link {
            margin-top: 15px;
            display: block;
            color: #388e3c;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <a href="registrazione.php" class="link">Non hai un account? Registrati</a>
</div>
</body>
</html>

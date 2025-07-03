<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$token = $_SESSION["token"];
$email = $_SESSION["email"];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h2 {
            color: #388E3C;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        .token-box {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .btn {
            display: block;
            text-decoration: none;
            background: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 16px;
        }
        .btn:hover {
            background: #388E3C;
        }
        .logout {
            background: #d32f2f;
        }
        .logout:hover {
            background: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Benvenuto nella tua Dashboard</h2>
        <p>Il tuo token per il questionario è:</p>
        <div class="token-box"><?php echo $token; ?></div>

        <!-- Link al questionario su LimeSurvey -->
        <a class="btn" href="https://stagelimesurvey.altervista.org/limesurvey/index.php/913884?token=<?php echo urlencode($token); ?>&email=<?php echo urlencode($email); ?>">Vai al questionario</a>

        <a class="btn logout" href="logout.php">Logout</a>
    </div>
</body>
</html>

<?php
session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $provenienza = $_POST["provenienza"];
    $eta = $_POST["eta"];
    $gender = $_POST["gender"];
    $profilo = $_POST["profilo"];
    $istituzione = $_POST["istituzione"];
    $esperienza = $_POST["esperienza"];
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    
    // Generazione del token
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $token = '';
    for ($i = 0; $i < 15; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }

    // Controllo se l'email esiste già
    $check_stmt = $conn->prepare("SELECT id FROM medici WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "Email già registrata.";
        exit();
    }
    $check_stmt->close();

    // Inserimento nel database `medici`
$stmt = $conn->prepare("
    INSERT INTO medici (
        email, password, age, gender, token, provenienza, profilo_professionale, istituzione_principale, esperienza_ibd
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssissssss", $email, $password, $eta, $gender, $token, $provenienza, $profilo, $istituzione, $esperienza);

    if ($stmt->execute()) {
        // Inserimento del token in LimeSurvey
        $limesurvey_token_stmt = $conn->prepare("
            INSERT INTO tokens_913884 (
                email, token, emailstatus, language, sent, remindersent, remindercount, completed, usesleft
            ) VALUES (?, ?, 'OK', 'it', 'N', 'N', 0, 'N', 1)
        ");
        $limesurvey_token_stmt->bind_param("ss", $email, $token);

        if ($limesurvey_token_stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            echo "Errore nell'inserimento del token in LimeSurvey.";
        }

        $limesurvey_token_stmt->close();
    } else {
        echo "Errore nella registrazione.";
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
    <title>Registrazione</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e8f5e9;
        margin: 0;
        padding: 40px 20px; /* aumenta il padding top e bottom */
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

    input, select {
        width: 250px;
        margin: 10px auto;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #fff;
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
        display: block;
    }

    select[name="esperienza"] {
        margin-bottom: 30px;
    }

    input:focus, select:focus {
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
    <h2>Registrazione</h2>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>

<select name="provenienza" required>
    <option value="">Provenienza professionale</option>
    <?php
    $json = file_get_contents("lista_paesi.json");
    if ($json !== false) {
        $countries = json_decode($json, true);
        usort($countries, function($a, $b) {
            return strcmp($a['name']['common'], $b['name']['common']);
        });
        foreach ($countries as $country) {
            $name = htmlspecialchars($country['name']['common']);
            echo "<option value=\"$name\">$name</option>";
        }
    } else {
        echo "<option value=\"\">Errore nel caricamento dei paesi</option>";
    }
    ?>
</select>


        <select name="eta" required>
    		<option value="">Età</option>
    		<?php
    		for ($i = 18; $i <= 80; $i++) {
    		    echo "<option value='$i'>$i anni</option>";
    		}
    		?>
		</select><br>

        <select name="gender" required>
            <option value="">Genere</option>
            <option value="male">Maschio</option>
            <option value="female">Femmina</option>
            <option value="non-binary">Non-binary</option>
        </select><br>

        <select name="profilo" required>
            <option value="">Profilo professionale</option>
            <option value="Specializzando">Medico Specializzando</option>
            <option value="Specialista">Medico Specialista</option>
        </select><br>

        <select name="istituzione" required>
            <option value="">Istituzione principale</option>
            <option value="Centro comunitario">Centro comunitario (I o II livello)</option>
            <option value="Centro di riferimento">Centro di riferimento terziario</option>
        </select><br>

        <select name="esperienza" required>
            <option value="">Esperienza in IBD colonoscopy</option>
            <option value="Meno di 200">Meno di 200 procedure</option>
            <option value="200-1000">200-1000 procedure</option>
            <option value="Oltre 1000">Oltre 1000 procedure</option>
        </select><br>

        <button type="submit">Registrati</button>
    </form>
    <a href="login.php" class="link">Hai già un account? Vai al login</a>
</div>

</body>
</html>

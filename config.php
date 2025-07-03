<?php
$host = "localhost";
$username = "stagelimesurvey"; // Cambia con i dati di Altervista
$password = "";
$dbname = "my_stagelimesurvey";

// Connessione al database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Creazione della tabella medici se non esiste
$sql = "CREATE TABLE IF NOT EXISTS medici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(10) NOT NULL,
    token VARCHAR(50) UNIQUE NOT NULL,
    provenienza VARCHAR(100) NOT NULL,  
    profilo_professionale VARCHAR(50) NOT NULL,  
    istituzione_principale VARCHAR(100) NOT NULL, 
    esperienza_ibd VARCHAR(50) NOT NULL  
)";
$conn->query($sql);

?>

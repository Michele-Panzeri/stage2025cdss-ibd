<?php
// connessione al database
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Analizza Dati</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e8f5e9;
        margin: 0;
        padding: 20px 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: #ffffff;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    h2 {
        text-align: center;
        color: #388e3c;
        margin-bottom: 15px;
        margin-top: 0;
    }

    form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    select, input[type="date"], input[type="text"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #ffffff;
        font-size: 16px;
        box-sizing: border-box;
        height: 45px;
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
    }

    select:focus, input[type="date"]:focus, input[type="text"]:focus {
        border-color: #66bb6a;
        outline: none;
    }

    .date-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .date-group label {
        font-size: 14px;
        color: #555;
        margin-bottom: 2px;
    }

    button[type="submit"] {
        grid-column: 1 / -1;
        padding: 12px;
        font-size: 16px;
        background-color: #43a047;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: #388e3c;
    }

    #results {
        margin-top: 20px;
    }

    .dropdown-multi {
        position: relative;
        width: 100%;
    }

    .dropdown-multi button {
        width: 100%;
        height: 45px;
        padding: 10px;
        font-size: 16px;
        border-radius: 5px;
        background-color: #ffffff;
        color: #333;
        border: 1px solid #ccc;
        text-align: left;
        cursor: pointer;
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .dropdown-multi button:hover {
        background-color: #f5f5f5;
        border-color: #66bb6a;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: white;
        border: 1px solid #aaa;
        padding: 10px;
        border-radius: 5px;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        z-index: 100;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .dropdown-content label {
        display: block;
        margin-bottom: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .dropdown-content label:hover {
        background-color: #f5f5f5;
    }

    .select-with-offset {
        margin-top: 24px;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Filtra e Analizza Risultati</h2>

    <form id="filterForm" method="post">
        <!-- Provenienza -->
        <select name="provenienza">
            <option value="">Provenienza professionale</option>
        </select>

        <!-- Età -->
        <select name="eta">
            <option value="">Età</option>
            <?php
            for ($i = 18; $i <= 80; $i++) {
                echo "<option value='$i'>$i anni</option>";
            }
            ?>
        </select>

        <!-- Genere -->
        <select name="gender">
            <option value="">Genere</option>
            <option value="male">Maschio</option>
            <option value="female">Femmina</option>
            <option value="non-binary">Non-binary</option>
        </select>

        <!-- Profilo Professionale -->
        <select name="profilo_professionale">
            <option value="">Profilo professionale</option>
            <option value="Specializzando">Medico Specializzando</option>
            <option value="Specialista">Medico Specialista</option>
        </select>

        <!-- Istituzione -->
        <select name="istituzione_principale" class="select-with-offset">
            <option value="">Istituzione principale</option>
            <option value="Centro comunitario">Centro comunitario (I o II livello)</option>
            <option value="Centro di riferimento">Centro di riferimento terziario</option>
        </select>

        <!-- Esperienza IBD -->
        <select name="esperienza_ibd" class="select-with-offset">
            <option value="">Esperienza in IBD colonoscopy</option>
            <option value="Meno di 200">Meno di 200 procedure</option>
            <option value="200-1000">200-1000 procedure</option>
            <option value="Oltre 1000">Oltre 1000 procedure</option>
        </select>

        <!-- Data Inizio -->
        <div class="date-group">
            <label for="start_date">Data inizio</label>
            <input type="date" name="start_date" id="start_date">
        </div>

        <!-- Data Fine -->
        <div class="date-group">
            <label for="end_date">Data fine</label>
            <input type="date" name="end_date" id="end_date">
        </div>

        <!-- Selezione email in menu espandibile -->
        <div class="dropdown-multi">
            <button type="button" onclick="toggleDropdown()">Seleziona email...</button>
            <div id="emailDropdown" class="dropdown-content">
                <?php
                $emailResult = $conn->query("SELECT DISTINCT email FROM medici ORDER BY email ASC");
                while ($row = $emailResult->fetch_assoc()) {
                    echo "<label><input type='checkbox' name='email[]' value='{$row['email']}'> {$row['email']}</label>";
                }
                ?>
            </div>
        </div>

        <!-- Bottone Cerca -->
        <button type="submit">Cerca e Analizza Dati</button>
    </form>

    <div id="results">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i filtri dal form
            $provenienza = $_POST['provenienza'] ?? '';
            $eta = $_POST['eta'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $profilo = $_POST['profilo_professionale'] ?? '';
            $istituzione = $_POST['istituzione_principale'] ?? '';
            $esperienza = $_POST['esperienza_ibd'] ?? '';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $emailList = $_POST['email'] ?? [];

            // Costruzione della query dinamica
            $where = [];
            $params = [];
            $types = '';

            if (!empty($provenienza)) {
                $where[] = 'm.provenienza = ?';
                $params[] = $provenienza;
                $types .= 's';
            }

            if (!empty($eta)) {
                $where[] = 'm.age = ?';
                $params[] = $eta;
                $types .= 'i';
            }

            if (!empty($gender)) {
                $where[] = 'm.gender = ?';
                $params[] = $gender;
                $types .= 's';
            }

            if (!empty($profilo)) {
                $where[] = 'm.profilo_professionale = ?';
                $params[] = $profilo;
                $types .= 's';
            }

            if (!empty($istituzione)) {
                $where[] = 'm.istituzione_principale = ?';
                $params[] = $istituzione;
                $types .= 's';
            }

            if (!empty($esperienza)) {
                $where[] = 'm.esperienza_ibd = ?';
                $params[] = $esperienza;
                $types .= 's';
            }

            if (!empty($startDate)) {
                $where[] = 's.submitdate >= ?';
                $params[] = $startDate;
                $types .= 's';
            }

            if (!empty($endDate)) {
                $where[] = 's.submitdate <= ?';
                $params[] = $endDate;
                $types .= 's';
            }

            if (!empty($emailList)) {
                $placeholders = implode(',', array_fill(0, count($emailList), '?'));
                $where[] = 's.token IN (SELECT token FROM medici WHERE email IN (' . $placeholders . '))';
                $params = array_merge($params, $emailList);
                $types .= str_repeat('s', count($emailList));
            }

            $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $query = "
                SELECT 
    				s.token, 
    				s.submitdate, 
    				s.913884X32X47, 
    				s.913884X33X51, 
    				s.913884X12X13, 
    				s.913884X12X15, 
    				s.913884X12X16, 
    				s.913884X12X17,
    				m.email, 
    				m.gender, 
    				m.age, 
    				m.provenienza
                FROM survey_913884 s
                JOIN medici m ON s.token = m.token
                $whereClause
                ORDER BY s.submitdate DESC
            ";

            $stmt = $conn->prepare($query);
            if ($types) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div style='margin-top: 40px;'>";
    echo "<h4>Risposte filtrate</h4>";
    echo "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse; font-size: 14px; max-width: 100%;'>";
    echo "<thead style='background-color: #f9f9f9;'>";
    echo "<tr>
            <th>Email</th>
            <th>Genere</th>
            <th>Età</th>
            <th>Provenienza</th>
            <th>Token</th>
            <th>Data invio</th>
          </tr>";
    echo "</thead><tbody>";

    // Mappatura codici risposta
    $mapping_q1 = [
        'AO05' => [0, 'Surveillance not indicated'],
        'AO04' => [0.5, 'Reschedule ASAP'],
        'AO01' => [1, '1 year'],
        'AO02' => [2.5, '2/3 years'],
        'AO03' => [5, '5 years']
    ];

    // Inizializza array per grafico e statistiche
    $data = [];
    $medici_stats = [];

    // Unico ciclo per stampa e raccolta dati
    // Inizio ciclo while
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $val_q1 = $row['913884X32X47'] ?? null;
        $val_q2 = $row['913884X33X51'] ?? null;

        // Stampa riga della tabella
        echo "<tr>";
        echo "<td>" . htmlspecialchars($email) . "</td>";
        echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
        echo "<td>" . htmlspecialchars($row['age']) . "</td>";
        echo "<td>" . htmlspecialchars($row['provenienza']) . "</td>";
        echo "<td>" . htmlspecialchars($row['token']) . "</td>";
        echo "<td>" . htmlspecialchars($row['submitdate']) . "</td>";
        echo "</tr>";

        // Raccolta dati per grafico
        if (isset($mapping_q1[$val_q1]) && isset($mapping_q1[$val_q2])) {
            $recommended_value = null;
            $recommended_label = 'N/A';

            if ($row['913884X12X13'] === '') {
                $recommended_value = 1;
                $recommended_label = 'Survey: every 1 year';
            } elseif ($row['913884X12X15'] === '') {
                $recommended_value = 2.5;
                $recommended_label = 'Survey: every 2–3 years';
            } elseif ($row['913884X12X16'] === '') {
                $recommended_value = 5;
                $recommended_label = 'Survey: every 5 years';
            } elseif ($row['913884X12X17'] === '') {
                $recommended_value = 0;
                $recommended_label = 'Survey: not indicated';
            }

            if ($recommended_value !== null) {
                $data[] = [
                    'email' => $email,
                    'q1_value' => $mapping_q1[$val_q1][0],
                    'survey_value' => $recommended_value,
                    'q2_value' => $mapping_q1[$val_q2][0],
                    'q1_label' => $mapping_q1[$val_q1][1],
                    'survey_label' => $recommended_label,
                    'q2_label' => $mapping_q1[$val_q2][1]
                ];

                if (!isset($medici_stats[$email])) {
                    $medici_stats[$email] = ['tot' => 0, 'changed' => 0];
                }

                $medici_stats[$email]['tot']++;

                if ($mapping_q1[$val_q1][0] != $mapping_q1[$val_q2][0]) {
                    $medici_stats[$email]['changed']++;
                }
            }
        }
    } // CHIUSURA DEL WHILE

    echo "</tbody></table></div>";

    ?>

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    google.charts.load('current', {packages: ['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
['Medico', 'Periodo Iniziale post visita', 'Raccomandazione della Survey', 'Periodo Finale'],
<?php
foreach ($data as $entry) {
    $email = addslashes($entry['email']);
    $q1 = $entry['q1_value'];
    $q2 = $entry['q2_value'];
    $qSurvey = $entry['survey_value'];
    echo "['$email', $q1, $qSurvey, $q2],\n";
}
?>
        ]);

        var options = {
            title: 'Confronto tra tempistiche',
            vAxis: {
                title: 'Intervallo / Modifica (valore numerico)',
                minValue: 0
            },
            hAxis: {
                title: 'Medico'
            },
            legend: { position: 'top', textStyle: { fontSize: 12 } },
            bar: { groupWidth: '60%' },
			colors: ['#a2d729', '#ffe066', '#ff7f0e']


        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
    </script>

    <!-- Div per grafico -->
    <div id="chart_div" style="width: 100%; height: 500px; margin-top: 30px;"></div>

    <!-- Legenda valori -->
    <div style="margin-top: 40px;">
        <h4>Legenda valori del grafico</h4>
        <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-size: 14px; max-width: 600px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th>Valore</th>
                    <th>Significato</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>0</td><td>Surveillance not indicated</td></tr>
                <tr><td>0.5</td><td>Reschedule ASAP</td></tr>
                <tr><td>1</td><td>Surveillance colonoscopy every 1 year</td></tr>
                <tr><td>2.5</td><td>Surveillance colonoscopy every 2–3 years</td></tr>
                <tr><td>5</td><td>Surveillance colonoscopy every 5 years</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Tabella percentuali cambiamento -->
    <div style="margin-top: 30px;">
        <h4>Percentuale di cambiamento per medico</h4>
        <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th>Email</th>
                    <th>Totale risposte</th>
                    <th>Ha cambiato idea</th>
                    <th>% Cambio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medici_stats as $email => $stat): 
                    $percent = $stat['tot'] > 0 ? round(($stat['changed'] / $stat['tot']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($email) ?></td>
                    <td><?= $stat['tot'] ?></td>
                    <td><?= $stat['changed'] ?></td>
                    <td><?= $percent ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
} else {
    echo "<p>Nessun risultato trovato.</p>";
}

$stmt->close();

        }
        ?>
    </div>
</div>

<!-- Script per caricare dinamicamente i paesi -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch('https://restcountries.com/v3.1/all')
            .then(response => response.json())
            .then(data => {
                const select = document.querySelector('select[name="provenienza"]');
                data.sort((a, b) => a.name.common.localeCompare(b.name.common));
                data.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.name.common;
                    option.textContent = country.name.common;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento dei paesi:', error);
            });
    });
</script>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById("emailDropdown");
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
}

document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("emailDropdown");
    const button = document.querySelector(".dropdown-multi button");
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = "none";
    }
});
</script>

</body>
</html>
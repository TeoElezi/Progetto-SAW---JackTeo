<?php
// Funzione per recuperare i dati delle gare dal database
function getRaces($conn) {
    $sql = "SELECT id, name, date, location, circuit_img FROM races";
    $result = $conn->query($sql);
    return $result;
}
?>

<?php include '../includes/header.php'; ?>
<head>
    <style>
        
        tr {
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            background-color: #fff;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #c30000;
        }
        th {
            background-color: #c30000;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        td img {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
            filter: drop-shadow(0 4px 8px black);
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .no-races {
            text-align: center;
            padding: 20px;
            font-size: 1.2em;
            color: #555;
        }
    </style>
    <h1>Gare di Formula 1</h1>
    <table>
        <tr>
            <th>N°</th>
            <th>Nome</th>
            <th>Data</th>
            <th>Luogo</th>
            <th>Immagine del Circuito</th>
        </tr>
        <?php
        $races = getRaces($conn);
        if ($races->num_rows > 0) {
            while ($row = $races->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['circuit_img']) . "' alt='Circuito' class='circuit-img'></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='no-races'>Nessuna gara trovata</td></tr>";
        }
        ?>
    </table>
<?php include '../includes/footer.php'; ?>
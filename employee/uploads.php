<?php
session_start();

// Nur eingeloggte Mitarbeiter oder Admins dürfen zugreifen
if (!isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] !== 'employee' && $_SESSION['user']['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}

$employee = $_SESSION['user'];

// Datenbankverbindungsdaten anpassen
$dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
$dbUser = "praxisblockDB";
$dbPass = "kcntmXThr9y3XhCZwGA.";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

// Alle Uploads laden, die target_employee = aktuellem Mitarbeiter entsprechen
// Optional: Joins, um den Namen des hochladenden Bürgers (citizen) zu sehen
$stmt = $pdo->prepare("
    SELECT up.*, 
           c.username AS citizen_username, 
           c.first_name AS citizen_first, 
           c.last_name AS citizen_last
    FROM uploads up
    JOIN users c ON up.citizen_id = c.id
    WHERE up.target_employee = :empId
    ORDER BY up.upload_date DESC
");
$stmt->execute([':empId' => $employee['id']]);
$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bürger-Uploads verwalten</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Upload-Dateien von Bürgern</h2>

    <?php if (count($uploads) === 0): ?>
        <p>Keine Uploads vorhanden.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Bürger</th>
                <th>Dateiname</th>
                <th>Upload-Datum</th>
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($uploads as $up): ?>
                <tr>
                    <td><?php echo $up['id']; ?></td>
                    <td>
                        <?php
                        // Zeigt Username oder Vor- Nachname
                        echo htmlspecialchars($up['citizen_username']);
                        if ($up['citizen_first'] || $up['citizen_last']) {
                            echo " (" . htmlspecialchars($up['citizen_first'] . " " . $up['citizen_last']) . ")";
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($up['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($up['upload_date']); ?></td>
                    <td>
                        <!-- Pfad anpassen, z. B. ../files/uploads/ -->
                        <a href="../files/uploads/<?php echo htmlspecialchars($up['file_path']); ?>" download>
                            Herunterladen
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
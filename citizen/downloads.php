<?php
// Die Session sollte bereits in dashboard.php laufen.
// Optionaler Sicherheitscheck:
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'citizen') {
    header("Location: ../login.php");
    exit;
}

$citizen = $_SESSION['user'];

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

// Abfrage: Eigene Downloads (assigned_to = current user)
// + optional: Standarddownloads (is_standard=1), falls du willst,
//   dass Bürger auch die globalen Standarddateien sehen
$stmt = $pdo->prepare("
    SELECT * 
    FROM downloads
    WHERE (assigned_to = :userid OR is_standard = 1)
    ORDER BY upload_date DESC
");
$stmt->execute([':userid' => $citizen['id']]);
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Downloads</title>
    <link rel="stylesheet" href="../css/styles.css"><!-- Pfad ggf. anpassen -->
</head>
<body>
<div class="form-container">
    <h2>Meine Downloads</h2>
    <?php if (count($downloads) === 0): ?>
        <p>Keine Downloads verfügbar.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Dateiname</th>
                <th>Datum</th>
                <th>Download</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($downloads as $dl): ?>
                <tr>
                    <td><?php echo htmlspecialchars($dl['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($dl['upload_date']); ?></td>
                    <td>
                        <!-- Ordnerpfad anpassen, z. B. /files/downloads/ -->
                        <a href="../files/downloads/<?php echo htmlspecialchars($dl['file_path']); ?>" download>
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
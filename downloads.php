<?php
// downloads.php
session_start();

// Datenbankverbindungsdaten
$dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
$dbUser = "praxisblockDB";
$dbPass = "kcntmXThr9y3XhCZwGA.";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankfehler: " . $e->getMessage());
}

// Lade alle öffentlichen Downloads (is_standard = 1)
$stmt = $pdo->prepare("SELECT * FROM downloads WHERE is_standard = 1 ORDER BY upload_date DESC");
$stmt->execute();
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Öffentliche Downloads</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Öffentliche Downloads</h1>
    <div class="download-list">
        <?php if(count($downloads) > 0): ?>
            <ul>
                <?php foreach($downloads as $download): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($download['file_name']); ?></strong><br>
                        <a href="files/downloads/<?php echo htmlspecialchars($download['file_path']); ?>" download>
                            Herunterladen
                        </a>
                        <br>
                        <small>Hochgeladen am: <?php echo htmlspecialchars($download['upload_date']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Keine Downloads verfügbar.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Da dieses Script über dashboard.php?page=provide_download eingebunden wird,
// sollte session_start() und ein Rollencheck (employee/admin) bereits im Dashboard erfolgen.

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

// Aktuell eingeloggter Mitarbeiter oder Admin
$staff = $_SESSION['user'];

// Meldungen
$messageStandard = ''; // Meldung beim Zuweisen eines Standarddownloads
$errorStandard   = '';

$messageUpload = '';   // Meldung beim Hochladen einer neuen Datei
$errorUpload   = '';

// 1. POST-Verarbeitung: Standard-Download zuweisen
if (isset($_POST['action']) && $_POST['action'] === 'assign_standard') {
    $downloadId = (int)($_POST['download_id'] ?? 0);
    $citizenId  = (int)($_POST['citizen_id']  ?? 0);

    if ($downloadId <= 0 || $citizenId <= 0) {
        $errorStandard = "Bitte sowohl einen Standarddownload als auch einen Bürger auswählen.";
    } else {
        // Standard-Download-Datensatz prüfen
        $stmt = $pdo->prepare("SELECT file_name, file_path FROM downloads WHERE id = :id AND is_standard = 1");
        $stmt->execute([':id' => $downloadId]);
        $standardDownload = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$standardDownload) {
            $errorStandard = "Der ausgewählte Download ist nicht (mehr) verfügbar oder kein Standarddownload.";
        } else {
            // Neuen Eintrag für den Bürger anlegen
            $stmt2 = $pdo->prepare("
                INSERT INTO downloads (file_name, file_path, assigned_to, uploaded_by, is_standard)
                VALUES (:file_name, :file_path, :assigned_to, :uploaded_by, 0)
            ");
            $stmt2->execute([
                ':file_name'    => $standardDownload['file_name'],
                ':file_path'    => $standardDownload['file_path'],
                ':assigned_to'  => $citizenId,
                ':uploaded_by'  => $staff['id']
            ]);
            $messageStandard = "Standard-Download wurde erfolgreich zugewiesen.";
        }
    }
}

// 2. POST-Verarbeitung: Neue Datei hochladen und zuweisen
if (isset($_POST['action']) && $_POST['action'] === 'upload_new') {
    $citizenId = (int)($_POST['citizen_id'] ?? 0);

    // Datei-Check
    if ($citizenId <= 0) {
        $errorUpload = "Bitte wählen Sie einen Bürger aus.";
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorUpload = "Bitte wählen Sie eine Datei aus.";
    } else {
        // Optional: Dateityp checken
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $errorUpload = "Nur PDF, JPG oder PNG sind erlaubt.";
        } else {
            // Datei speichern
            $uploadDir = __DIR__ . '/../files/downloads/';
            // Passe diesen Pfad ggf. an!
            // Wenn dein 'dashboard.php' im Hauptverzeichnis liegt und 'employee' ein Unterordner ist,
            // dann ../files/downloads/ => /var/www/html/files/downloads/

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $origName   = basename($_FILES['file']['name']);
            $newName    = time() . '_' . $origName;  // Eindeutiger Dateiname
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // In DB eintragen -> assigned_to = $citizenId, is_standard=0, uploaded_by = staff
                $stmt = $pdo->prepare("
                    INSERT INTO downloads (file_name, file_path, assigned_to, uploaded_by, is_standard)
                    VALUES (:file_name, :file_path, :assigned_to, :uploaded_by, 0)
                ");
                $stmt->execute([
                    ':file_name'    => $origName,
                    ':file_path'    => $newName,
                    ':assigned_to'  => $citizenId,
                    ':uploaded_by'  => $staff['id']
                ]);
                $messageUpload = "Neue Datei wurde hochgeladen und zugewiesen.";
            } else {
                $errorUpload = "Fehler beim Hochladen der Datei.";
            }
        }
    }
}

// Standarddownloads laden
$stmt = $pdo->query("SELECT id, file_name FROM downloads WHERE is_standard = 1 ORDER BY file_name");
$standardDownloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alle Bürger laden (Rolle 'citizen')
$stmt = $pdo->query("SELECT id, username, first_name, last_name FROM users WHERE role = 'citizen' ORDER BY username");
$citizens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Download bereitstellen</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        // Filtern der Bürger (Select) in erstem Formular
        function filterCitizens1() {
            const searchInput = document.getElementById('citizenSearch').value.toLowerCase();
            const select = document.getElementById('citizenSelect1');
            for (let i = 0; i < select.options.length; i++) {
                const txt = select.options[i].text.toLowerCase();
                select.options[i].style.display = txt.includes(searchInput) ? '' : 'none';
            }
        }
        // Filtern der Bürger (Select) in zweitem Formular
        function filterCitizens2() {
            const searchInput = document.getElementById('citizenSearch2').value.toLowerCase();
            const select = document.getElementById('citizenSelect2');
            for (let i = 0; i < select.options.length; i++) {
                const txt = select.options[i].text.toLowerCase();
                select.options[i].style.display = txt.includes(searchInput) ? '' : 'none';
            }
        }
    </script>
</head>
<body>

<!-- Neue Datei hochladen und zuweisen -->
<div class="form-container">
    <h2>Neue Datei hochladen &amp; zuweisen</h2>
    <?php if ($messageUpload): ?>
        <p class="message"><?php echo htmlspecialchars($messageUpload); ?></p>
    <?php endif; ?>
    <?php if ($errorUpload): ?>
        <p class="error"><?php echo htmlspecialchars($errorUpload); ?></p>
    <?php endif; ?>

    <form action="dashboard.php?page=provide_download" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_new">

        <label for="citizenSearch2">Bürger suchen:</label>
        <input type="text" id="citizenSearch2" placeholder="Suchbegriff eingeben..." onkeyup="filterCitizens2()">

        <label for="citizenSelect2">Bürger auswählen:</label>
        <select name="citizen_id" id="citizenSelect2" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($citizens as $c): ?>
                <?php
                $displayText = $c['username'];
                if ($c['first_name'] || $c['last_name']) {
                    $displayText .= " - " . $c['first_name'] . " " . $c['last_name'];
                }
                ?>
                <option value="<?php echo $c['id']; ?>">
                    <?php echo htmlspecialchars($displayText); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="file">Datei (PDF, JPG, PNG):</label>
        <input type="file" name="file" id="file" accept=".pdf,image/jpeg,image/png" required>

        <button type="submit">Hochladen &amp; zuweisen</button>
    </form>
</div>

</body>
</html>
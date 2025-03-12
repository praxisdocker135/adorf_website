<?php
// Da dieses Script über dashboard.php?page=manage_standard_downloads eingebunden wird,
// sollte session_start() bereits im dashboard.php erfolgt sein.
// Prüfe, ob der User Admin ist:
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

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

$message = '';
$error   = '';

// Prüfen, ob ein Standarddownload gelöscht werden soll
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    // Löschvorgang nur bei Einträgen mit is_standard=1
    $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = :id AND is_standard = 1");
    if ($stmt->execute([':id' => $deleteId]) && $stmt->rowCount() > 0) {
        $message = "Standard-Download (ID: $deleteId) wurde entfernt.";
    } else {
        $error = "Konnte den Standard-Download nicht entfernen oder Eintrag existiert nicht.";
    }
}

// Prüfen, ob ein neuer Standarddownload hochgeladen werden soll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_standard') {
    // Datei-Upload prüfen
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Bitte wählen Sie eine Datei aus.";
    } else {
        // Optional: Dateityp-Check
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $error = "Nur PDF, JPG oder PNG sind als Standarddownload erlaubt.";
        } else {
            // Datei hochladen
            $uploadDir = __DIR__ . '/../files/downloads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $origName   = basename($_FILES['file']['name']);
            $newName    = time() . '_' . $origName;
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // Datenbankeintrag anlegen => is_standard=1, assigned_to=NULL
                $stmt = $pdo->prepare("
                    INSERT INTO downloads (file_name, file_path, assigned_to, uploaded_by, is_standard)
                    VALUES (:file_name, :file_path, NULL, :uploaded_by, 1)
                ");
                if ($stmt->execute([
                    ':file_name'   => $origName,
                    ':file_path'   => $newName,
                    ':uploaded_by' => $_SESSION['user']['id'] // admin
                ])) {
                    $message = "Standard-Download wurde erfolgreich hochgeladen.";
                } else {
                    $error = "Fehler beim Speichern in der Datenbank.";
                }
            } else {
                $error = "Fehler beim Hochladen der Datei.";
            }
        }
    }
}

// Alle Standarddownloads laden
$stmt = $pdo->query("SELECT * FROM downloads WHERE is_standard = 1 ORDER BY upload_date DESC");
$standardDownloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Standard-Downloads verwalten</title>
    <!-- Gleiche CSS-Datei einbinden wie bei den anderen Seiten -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Standard-Downloads verwalten</h2>

    <!-- Meldungen (Erfolg oder Fehler) -->
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Formular zum Hochladen neuer Standard-Downloads -->
    <form action="../dashboard.php?page=manage_standard_downloads" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_standard">

        <label for="file">Neue Datei hochladen (PDF, JPG, PNG):</label>
        <input type="file" name="file" id="file" accept=".pdf,image/jpeg,image/png" required>

        <button type="submit" class="btn">Hochladen</button>
    </form>

    <!-- Tabelle mit allen vorhandenen Standard-Downloads -->
    <?php if (count($standardDownloads) > 0): ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Dateiname</th>
                <th>Hochgeladen am</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($standardDownloads as $sd): ?>
                <tr>
                    <td><?php echo $sd['id']; ?></td>
                    <td><?php echo htmlspecialchars($sd['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($sd['upload_date']); ?></td>
                    <td>
                        <a class="delete-link"
                           href="../dashboard.php?page=manage_standard_downloads&amp;delete_id=<?php echo $sd['id']; ?>"
                           onclick="return confirm('Diesen Standard-Download wirklich löschen?');">
                            Löschen
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Keine Standard-Downloads vorhanden.</p>
    <?php endif; ?>
</div>
</body>
</html>
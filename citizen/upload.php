<?php

// Prüfe, ob der Benutzer eingeloggt und Bürger ist
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'citizen') {
    die("Zugriff verweigert. Bitte loggen Sie sich als Bürger ein.");
}

$citizen = $_SESSION['user'];

// Überprüfe, ob das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Prüfe, ob eine Datei hochgeladen wurde
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        die("Fehler beim Upload.");
    }

    // Erlaubte MIME-Typen
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($_FILES['file']['type'], $allowed_types)) {
        die("Nicht erlaubter Dateityp.");
    }

    // Hole den angegebenen Mitarbeiter-Benutzernamen aus dem Formular
    $employee_username = trim($_POST['employee']);

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

    // Überprüfe, ob der angegebene Mitarbeiter existiert (Mitarbeiter haben z. B. die Rolle 'employee' oder 'admin')
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND role != 'citizen'");
    $stmt->execute([':username' => $employee_username]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        die("Mitarbeiter nicht gefunden.");
    }

    // Verzeichnis für Uploads definieren
    $upload_dir = __DIR__ . '/files/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generiere einen neuen Dateinamen, um Kollisionen zu vermeiden
    $filename = time() . "_" . basename($_FILES['file']['name']);
    $target_file = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // Speichere den Upload in der Datenbank
        $stmt = $pdo->prepare("INSERT INTO uploads (file_name, file_path, citizen_id, target_employee) VALUES (:file_name, :file_path, :citizen_id, :target_employee)");
        $stmt->execute([
            ':file_name'      => basename($_FILES['file']['name']),
            ':file_path'      => $filename,
            ':citizen_id'     => $citizen['id'],
            ':target_employee'=> $employee['id']
        ]);

        echo "Datei erfolgreich hochgeladen.";
    } else {
        echo "Fehler beim Speichern der Datei.";
    }
} else {
    echo "Ungültige Anfrage.";
}
?>

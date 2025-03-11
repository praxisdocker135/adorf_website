<?php
session_start();


$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Felder aus dem Formular einlesen
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $address   = trim($_POST['address']);
    $birthdate = trim($_POST['birthdate']);  // Format: DD/MM/YYYY
    $email     = trim($_POST['email']);

    // Grundlegende Validierung
    if (empty($firstName) || empty($lastName) || empty($address) || empty($birthdate) || empty($email)) {
        $error = "Bitte füllen Sie alle Felder aus.";
    } else {
        // Datenbank-Verbindungsdaten anpassen!
        $dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
        $dbUser = "praxisblockDB";
        $dbPass = "kcntmXThr9y3XhCZwGA.";

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Datenbankfehler: " . $e->getMessage());
        }

        // Prüfen, ob es bereits einen Nutzer mit identischen Daten gibt
        $checkStmt = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE first_name = :fn
              AND last_name  = :ln
              AND address    = :adr
              AND birthdate  = :bd
              AND email      = :em
        ");
        $checkStmt->execute([
            ':fn'  => $firstName,
            ':ln'  => $lastName,
            ':adr' => $address,
            ':bd'  => $birthdate,
            ':em'  => $email
        ]);
        $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error = "Ein Benutzer mit diesen Daten existiert bereits.";
        } else {
            // Neuen Bürger anlegen, Passwort = NULL
            $insertStmt = $pdo->prepare("
                INSERT INTO users (username, password, role, first_name, last_name, address, birthdate, email)
                VALUES ('', NULL, 'citizen', :fn, :ln, :adr, :bd, :em)
            ");
            $success = $insertStmt->execute([
                ':fn' => $firstName,
                ':ln' => $lastName,
                ':adr'=> $address,
                ':bd' => $birthdate,
                ':em' => $email
            ]);
            if ($success) {
                $message = "Registrierung erfolgreich! Ihr Account wurde angelegt und erscheint in der Liste der Bürger ohne Passwort.";
                // Hier könntest du dem Nutzer noch mitteilen,
                // dass ein temporäres Passwort per Post geschickt wird o. Ä.
            } else {
                $error = "Fehler beim Anlegen des Kontos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bürger-Registrierung</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header>
    <div class="header-container">
        <img src="images/logo.jpg" alt="Landratsamt Ansbach Logo" class="logo">
        <nav>
            <ul>
                <li><a href="index.php">Startseite</a></li>
            </ul>
        </nav>
    </div>
</header>
<div class="form-container">
    <h2>Bürger-Registrierung</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="register_citizen.php" method="post">
        <label for="first_name">Vorname:</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Nachname:</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="address">Adresse:</label>
        <input type="text" name="address" id="address" required>

        <label for="birthdate">Geburtsdatum (DD-MM-YYYY):</label>
        <input type="date" name="birthdate" id="birthdate" required>

        <label for="email">E-Mail:</label>
        <input type="email" name="email" id="email" required>

        <button type="submit" class="btn">Registrieren</button>
    </form>
</div>
</body>
</html>
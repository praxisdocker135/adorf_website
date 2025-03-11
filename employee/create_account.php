<?php
// Da dieses Script aus dashboard.php inkludiert wird,
// ist die Session bereits gestartet und $user (Admin) schon geprüft.

if ($_SESSION['user']['role'] !== 'admin') {
    // Sicherheitshalber
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

// Formular-Daten verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzername und Rolle aus POST auslesen
    $username = trim($_POST['username']);
    $role     = $_POST['role'];  // z. B. 'employee' oder 'admin'

    // Validierung
    if (empty($username)) {
        $error = "Bitte geben Sie einen Benutzernamen ein.";
    } elseif (!in_array($role, ['employee','admin'], true)) {
        $error = "Ungültige Rolle ausgewählt.";
    } else {
        // Prüfen, ob der Benutzername bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);

        if ($stmt->fetch()) {
            $error = "Der Benutzername existiert bereits.";
        } else {
            // Generiere ein zufälliges Passwort (z. B. 8 Zeichen, hexadezimal)
            $randomPassword = bin2hex(random_bytes(4));
            $passwordHash   = password_hash($randomPassword, PASSWORD_DEFAULT);

            // Neuen Mitarbeiter/Admin in die Datenbank einfügen
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, email)
                VALUES (:username, :password, :role, '')
            ");
            if ($stmt->execute([
                ':username' => $username,
                ':password' => $passwordHash,
                ':role'     => $role
            ])) {
                // Erfolg
                $message  = "Konto für <strong>" . htmlspecialchars($username) . "</strong> wurde erstellt.<br>";
                $message .= "Rolle: <strong>" . htmlspecialchars($role) . "</strong><br>";
                $message .= "Temporäres Passwort: <strong>" . htmlspecialchars($randomPassword) . "</strong>";
            } else {
                $error = "Fehler beim Erstellen des Kontos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiterkonto erstellen</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Konto erstellen</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- WICHTIG: action zeigt auf dashboard.php?page=create_account -->
    <form action="dashboard.php?page=create_account" method="post">
        <label for="username">Benutzername:</label>
        <input type="text" name="username" id="username" required>

        <label for="role">Rolle:</label>
        <select name="role" id="role">
            <option value="employee">Mitarbeiter (employee)</option>
            <option value="admin">Admin (admin)</option>
        </select>

        <button type="submit">Konto erstellen</button>
    </form>

    <div class="back-link">
        <a href="../dashboard.php">Zurück zum Dashboard</a>
    </div>
</div>
</body>
</html>
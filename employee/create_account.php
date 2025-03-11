<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Nur Admins dürfen hier zugreifen
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
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        $error = "Bitte geben Sie einen Benutzernamen ein.";
    } else {
        // Prüfen, ob der Benutzername bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            $error = "Der Benutzername existiert bereits.";
        } else {
            // Generiere ein zufälliges Passwort (z. B. 8 Zeichen, hexadezimal)
            $randomPassword = bin2hex(random_bytes(4));
            $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
            
            // Neuen Mitarbeiter in die Datenbank einfügen, Rolle: employee
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (:username, :password, 'employee', '')");
            if ($stmt->execute([':username' => $username, ':password' => $passwordHash])) {
                $message = "Mitarbeiterkonto für <strong>" . htmlspecialchars($username) . "</strong> wurde erstellt.<br>";
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
    <!-- Externe CSS-Datei einbinden -->
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Falls in der externen CSS noch kein spezieller Style für Formulare definiert ist */
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin-bottom: 5px;
        }
        .form-container input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .form-container button {
            padding: 10px;
            background: #003366;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        .message {
            text-align: center;
            color: green;
            margin-bottom: 15px;
        }
        .error {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            color: #003366;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Mitarbeiterkonto erstellen</h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="create_account.php" method="post">
            <label for="username">Benutzername:</label>
            <input type="text" name="username" id="username" required>
            <button type="submit">Konto erstellen</button>
        </form>
        <div class="back-link">
            <a href="../dashboard.php">Zurück zum Dashboard</a>
        </div>
    </div>
</body>
</html>

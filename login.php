<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

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

    // Benutzer anhand des Benutzernamens suchen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Prüfen, ob ein Passwort gesetzt ist und ob das eingegebene Passwort korrekt ist
        if ($user['password'] && password_verify($password, $user['password'])) {
            // Login erfolgreich – Benutzer in Session speichern
            $_SESSION['user'] = $user;
            // Weiterleitung basierend auf der Benutzerrolle
            header("Location: dashboard.php");
        } else {
            $error = "Ungültige Zugangsdaten.";
        }
    } else {
        $error = "Benutzer nicht gefunden.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Externe CSS-Datei einbinden -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Falls in deiner styles.css kein spezieller Login-Bereich definiert ist */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container label {
            margin-bottom: 5px;
        }
        .login-container input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .login-container button {
            padding: 10px;
            border: none;
            background: #003366;
            color: #fff;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-container .reset-link {
            text-align: center;
            margin-top: 10px;
        }
        .login-container .reset-link a {
            color: #003366;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="username">Benutzername:</label>
            <input type="text" name="username" id="username" required>
            
            <label for="password">Passwort:</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">Einloggen</button>
        </form>
        <div class="reset-link">
            <a href="reset_request.php">Passwort zurücksetzen</a>
        </div>
    </div>
</body>
</html>

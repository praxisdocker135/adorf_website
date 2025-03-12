<?php


// Falls ein User schon eingeloggt ist, kann man ihn ggf. zum Dashboard leiten
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten: Benutzername & Geburtsdatum
    $username   = trim($_POST['username']);
    $birthdate  = trim($_POST['birthdate']); // Format gemäß browser sprache

    // Ggf. Validierung
    if (empty($username) || empty($birthdate)) {
        $error = "Bitte füllen Sie alle Felder aus.";
    } else {
        // DB-Verbindungsdaten anpassen
        $dsn    = "mysql:host=localhost;dbname=adorf_website;charset=utf8";
        $dbUser = "praxisblockDB";
        $dbPass = "kcntmXThr9y3XhCZwGA.";

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Datenbankfehler: " . $e->getMessage());
        }

        // Prüfen, ob ein Bürger mit diesem Benutzernamen & Geburtsdatum existiert
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = :uname
              AND birthdate = :bdate
              AND role = 'citizen'
              LIMIT 1
        ");
        $stmt->execute([
            ':uname' => $username,
            ':bdate' => $birthdate
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Kein Bürgerkonto mit diesen Daten gefunden.";
        } else {
            // Passwort auf NULL setzen => landet in pending_accounts (z. B. password IS NULL)
            $upd = $pdo->prepare("
                UPDATE users
                SET password = NULL
                WHERE id = :uid
            ");
            $upd->execute([':uid' => $user['id']]);

            // Optional: failed_logins hochsetzen, falls du es so handhabst
            // oder ein Flag force_password_change = 1 setzen – je nach Konzept.
            // Bsp.:
            // $pdo->prepare("UPDATE users SET failed_logins = 5 WHERE id=:uid")->execute([':uid'=>$user['id']]);

            $message = "Ihr Konto wurde zur Passwort-Rücksetzung markiert. 
                        Bitte wenden Sie sich an unser Team oder warten Sie, 
                        bis ein Admin Ihr Passwort zurücksetzt.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Passwort zurücksetzen</title>
    <link rel="stylesheet" href="../css/styles.css"><!-- Pfad anpassen -->
</head>
<body>
<!-- Optionaler Header, wie auf index.php etc. -->
<header>
    <div class="header-container">
        <img src="../images/logo.jpg" alt="Landratsamt Ansbach Logo" class="logo">
        <nav>
            <ul>
                <li><a href="../index.php">Startseite</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="form-container">
    <h2>Passwort zurücksetzen lassen</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="reset_request.php" method="post">
        <label for="username">Benutzername:</label>
        <input type="text" name="username" id="username" required>

        <label for="birthdate">Geburtsdatum:</label>
        <input type="date" name="birthdate" id="birthdate" required>

        <button type="submit" class="btn">Absenden</button>
    </form>
</div>
</body>
</html>
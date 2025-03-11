<?php
session_start();

// Nur Admins sollen Zugriff haben
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// DB-Verbindung anpassen
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

// 1) Passwort-Reset anstoßen?
if (isset($_GET['reset_id'])) {
    $userId = (int)$_GET['reset_id'];

    // Prüfen, ob der Nutzer existiert und ein Bürger ist (role='citizen')
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role='citizen'");
    $stmt->execute([':id' => $userId]);
    $citizen = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($citizen) {
        // Neues Passwort generieren
        $randomPass   = bin2hex(random_bytes(4));
        $hashedPass   = password_hash($randomPass, PASSWORD_DEFAULT);

        // Reset in DB: password = hashedPass, failed_logins = 0
        $upd = $pdo->prepare("
            UPDATE users
            SET password = :pw, failed_logins = 0
            WHERE id = :id
        ");
        $upd->execute([
            ':pw' => $hashedPass,
            ':id' => $userId
        ]);

        $message = "Passwort für Citizen mit ID {$userId} wurde zurückgesetzt. 
                    Neues Passwort: <strong>{$randomPass}</strong>";
    } else {
        $error = "Bürger-Konto nicht gefunden oder keine Berechtigung.";
    }
}

// 2) Alle Pending Accounts ermitteln
// Kriterium: password IS NULL (neuer Account) oder failed_logins >= 5
$query = "
    SELECT id, first_name, last_name, email, failed_logins, password, created_at
    FROM users
    WHERE role = 'citizen'
      AND (password IS NULL OR failed_logins >= 5)
    ORDER BY created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Pending Bürger-Accounts</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="form-container">
    <h2>Pending Bürger-Accounts</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (count($pendingUsers) === 0): ?>
        <p>Es gibt aktuell keine neuen oder gesperrten Bürgerkonten.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Fehlversuche</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pendingUsers as $user): ?>
                <?php
                // Statusbestimmung
                if (is_null($user['password'])) {
                    $status = "Neuer Account (kein Passwort)";
                } elseif ($user['failed_logins'] >= 5) {
                    $status = "Gesperrt";
                } else {
                    $status = "Unbekannt";
                }
                ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['failed_logins']; ?></td>
                    <td><?php echo $status; ?></td>
                    <td>
                        <!-- Reset-Button => ?reset_id=XYZ -->
                        <a class="btn" href="dashboard.php?page=pending_accounts&amp;reset_id=<?php echo $user['id']; ?>"
                           onclick="return confirm('Passwort wirklich zurücksetzen?');">
                            Passwort zurücksetzen
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
<?php
// Da dieses Skript vom Dashboard aus eingebunden wird, ist die Session bereits gestartet
// und $user in der Regel schon definiert.
// Falls du direktes Aufrufen blockieren willst, kannst du zusätzlich folgendes tun:
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'employee' && $_SESSION['user']['role'] !== 'admin')) {
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

// Prüfen, ob eine Löschanfrage vorliegt
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Kontaktformular-Eintrag löschen
    $stmt = $pdo->prepare("DELETE FROM contact_submissions WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);

    // Optional eine Erfolgsmeldung ausgeben oder via header-Redirect neu laden
    echo "<p style='color: green;'>Eintrag mit ID $deleteId wurde gelöscht.</p>";
}

// Alle Einträge abrufen
$stmt = $pdo->prepare("SELECT * FROM contact_submissions ORDER BY submitted_at DESC");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kontaktformulare verwalten</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<h2>Kontaktformulare verwalten</h2>
<?php if (count($contacts) > 0): ?>
    <table class="contact-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>E-Mail</th>
            <th>Nachricht</th>
            <th>Eingangsdatum</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($contacts as $contact): ?>
            <tr>
                <td><?php echo htmlspecialchars($contact['id']); ?></td>
                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($contact['message'])); ?></td>
                <td><?php echo htmlspecialchars($contact['submitted_at']); ?></td>
                <td>
                    <!-- Löschen-Link mit GET-Parameter ?delete_id=XYZ -->
                    <a class="delete-link" href="dashboard.php?page=manage_contacts&amp;delete_id=<?php echo $contact['id']; ?>"
                       onclick="return confirm('Diesen Eintrag wirklich löschen?');">
                        Löschen
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Keine Kontaktformulare vorhanden.</p>
<?php endif; ?>
</body>
</html>
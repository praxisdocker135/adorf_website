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
$message = '';
$error   = '';

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $downloadId = (int)($_POST['download_id'] ?? 0);
    $citizenId  = (int)($_POST['citizen_id']  ?? 0);

    if ($downloadId <= 0 || $citizenId <= 0) {
        $error = "Bitte sowohl einen Standarddownload als auch einen Bürger auswählen.";
    } else {
        // 1. Standard-Download-Datensatz abfragen
        $stmt = $pdo->prepare("SELECT file_name, file_path FROM downloads WHERE id = :id AND is_standard = 1");
        $stmt->execute([':id' => $downloadId]);
        $standardDownload = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$standardDownload) {
            $error = "Der ausgewählte Download ist nicht (mehr) verfügbar oder kein Standarddownload.";
        } else {
            // 2. Neuen Eintrag für den Bürger anlegen -> Download zuweisen
            //    (Dupliziert file_name und file_path, aber is_standard=0 und assigned_to = citizenId)
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
            $message = "Download wurde erfolgreich zugewiesen.";
        }
    }
}

// 3. Standarddownloads laden
$stmt = $pdo->query("SELECT id, file_name FROM downloads WHERE is_standard = 1 ORDER BY file_name");
$standardDownloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Alle Bürger laden (Rolle 'citizen')
$stmt = $pdo->query("SELECT id, username, first_name, last_name FROM users WHERE role = 'citizen' ORDER BY username");
$citizens = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Download bereitstellen</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
        }
        .form-container .message {
            text-align: center;
            color: green;
            margin-bottom: 15px;
        }
        .form-container .error {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button, input[type="text"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button {
            background: #003366;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        /* Suchfeld + Dropdown für Bürger */
        .citizen-search-wrapper {
            position: relative;
        }
        #citizenSearch {
            margin-bottom: 5px;
        }
        #citizenSelect {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
    <script>
        // Einfache JS-Lösung, um Bürger in der Select-Liste zu filtern
        function filterCitizens() {
            const searchInput = document.getElementById('citizenSearch').value.toLowerCase();
            const select = document.getElementById('citizenSelect');
            for (let i = 0; i < select.options.length; i++) {
                const txt = select.options[i].text.toLowerCase();
                select.options[i].style.display = txt.includes(searchInput) ? '' : 'none';
            }
        }
    </script>
</head>
<body>
<div class="form-container">
    <h2>Download bereitstellen</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="dashboard.php?page=provide_download" method="post">
        <label for="download_id">Standarddownload auswählen:</label>
        <select name="download_id" id="download_id" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($standardDownloads as $sd): ?>
                <option value="<?php echo $sd['id']; ?>">
                    <?php echo htmlspecialchars($sd['file_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="citizenSearch">Bürger suchen:</label>
        <input type="text" id="citizenSearch" placeholder="Suchbegriff eingeben..." onkeyup="filterCitizens()">

        <label for="citizenSelect">Bürger auswählen:</label>
        <select name="citizen_id" id="citizenSelect" required>
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

        <button type="submit">Zuweisen</button>
    </form>
</div>
</body>
</html>
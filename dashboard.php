<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Externe Stylesheet einbinden -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- Header-Banner -->
<header>
    <div class="header-container">
        <img src="images/logo.jpg" alt="Landratsamt Ansbach Logo" class="logo">
        <nav>
            <ul>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- Dashboard-Inhalt -->
<div class="container">
    <div class="sidebar">
        <h2>Navigation</h2>
        <?php
        // Navigation für Bürger
        if ($user['role'] === 'citizen') {
            echo '<a href="dashboard.php?page=downloads">Meine Downloads</a>';
            echo '<a href="dashboard.php?page=upload">Datei Upload</a>';
            echo '<a href="dashboard.php?page=change_password">Passwort ändern</a>';
        } else { // Navigation für Mitarbeiter (und Admin)
            echo '<a href="dashboard.php?page=manage_contacts">Kontaktformulare verwalten</a>';
            echo '<a href="dashboard.php?page=provide_download">Download bereitstellen</a>';
            echo '<a href="dashboard.php?page=uploads">Bürger-Uploads</a>';
            echo '<a href="dashboard.php?page=change_password">Passwort ändern</a>';
            // Zusätzliche Menüpunkte für Admins
            if ($user['role'] === 'admin') {
                echo '<a href="dashboard.php?page=reset_password">Passwörter zurücksetzen</a>';
                echo '<a href="dashboard.php?page=create_account">Mitarbeiterkonto erstellen</a>';
                echo '<a href="dashboard.php?page=pending_accounts">Pending Bürger Accounts</a>';
                echo '<a href="dashboard.php?page=manage_standard_downloads">Standard Downloads bearbeiten</a>';
            }
        }
        ?>
    </div>
    <div class="main-content">
        <?php
        // Inhalte anhand des Query-Parameters laden
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            // Sicherheitsüberprüfung: nur erlaubte Werte verarbeiten
            $allowed_pages = [
                'downloads',
                'manage_standard_downloads',
                'upload',
                'profile',
                'manage_contacts',
                'provide_download',
                'reset_password',
                'uploads',
                'create_account',
                'pending_accounts',
                'change_password'

            ];
            if (in_array($page, $allowed_pages)) {
                // Inhalte in Unterordnern (citizen/ oder employee/) laden
                switch ($page) {
                    case 'downloads':
                        include 'citizen/downloads.php';
                        break;
                    case 'upload':
                        include 'citizen/upload.php';
                        break;
                    case 'manage_contacts':
                        include 'employee/manage_contacts.php';
                        break;
                    case 'provide_download':
                        include 'employee/provide_download.php';
                        break;
                    case 'uploads':
                        include 'employee/uploads.php';
                        break;
                    case 'reset_password':
                        include 'employee/reset_password.php';
                        break;
                    case 'create_account':
                        include 'employee/create_account.php';
                        break;
                    case 'pending_accounts':
                        include 'employee/pending_accounts.php';
                        break;
                    case 'manage_standard_downloads':
                        include 'employee/manage_standard_downloads.php';
                        break;
                    case 'change_password':
                        include 'change_password.php';
                        break;
                    default:
                        echo "<h2>Willkommen, " . htmlspecialchars($user['username']) . "!</h2>";
                        break;
                }
            } else {
                echo "<h2>Seite nicht gefunden.</h2>";
            }
        } else {
            // Standard: Begrüßungsnachricht
            echo "<h2>Willkommen, " . htmlspecialchars($user['username']) . "!</h2>";
            echo "<p>Bitte wählen Sie eine Option aus dem Menü auf der linken Seite.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>

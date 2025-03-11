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
    <style>
        /* Zusätzliche Inline-Styles für das Dashboard-Layout */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        /* Header-Banner oben */
        header {
            background-color: #003366;
            color: #fff;
            padding: 10px 0;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-container img.logo {
            max-height: 50px;
        }
        .header-container nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .header-container nav ul li {
            margin-left: 20px;
        }
        .header-container nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            display: flex;
            height: calc(100vh - 80px); /* 80px als Beispiel für Header-Höhe */
        }
        .sidebar {
            width: 250px;
            background-color: #003366;
            color: #fff;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-top: 0;
            font-size: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        .sidebar a:hover {
            background-color: #0055aa;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
            background: #fff;
        }
    </style>
</head>
<body>
<!-- Header-Banner -->
<header>
    <div class="header-container">
        <img src="logo.png" alt="Landratsamt Ansbach Logo" class="logo">
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
            echo '<a href="dashboard.php?page=profile">Mein Profil</a>';
        } else { // Navigation für Mitarbeiter (und Admin)
            echo '<a href="dashboard.php?page=manage_contacts">Kontaktformulare verwalten</a>';
            echo '<a href="dashboard.php?page=provide_download">Download bereitstellen</a>';
            // Zusätzliche Menüpunkte für Admins
            if ($user['role'] === 'admin') {
                echo '<a href="dashboard.php?page=admin_reset_employees">Mitarbeiter-Passwörter zurücksetzen</a>';
                echo '<a href="dashboard.php?page=admin_reset_citizens">Bürger-Passwörter zurücksetzen</a>';
                echo '<a href="dashboard.php?page=create_account">Mitarbeiterkonto erstellen</a>';
                echo '<a href="dashboard.php?page=pending_accounts">Pending Bürger Accounts</a>';
                echo '<a href="dashboard.php?page=manage_standard_downloads">Pending Bürger Accounts</a>';
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
                'upload',
                'profile',
                'manage_contacts',
                'provide_download',
                'admin_reset_employees',
                'admin_reset_citizens',
                'create_account',
                'pending_accounts',
                'manage_standard_downloads'
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
                    case 'profile':
                        include 'citizen/profile.php';
                        break;
                    case 'manage_contacts':
                        include 'employee/manage_contacts.php';
                        break;
                    case 'provide_download':
                        include 'employee/provide_download.php';
                        break;
                    case 'admin_reset_employees':
                        include 'employee/admin_reset_employees.php';
                        break;
                    case 'admin_reset_citizens':
                        include 'employee/admin_reset_citizens.php';
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

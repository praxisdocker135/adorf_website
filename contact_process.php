<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Eingabedaten trimmen
  $name    = trim($_POST["name"]);
  $email   = trim($_POST["email"]);
  $message = trim($_POST["message"]);
  
  // Validierung
  $errors = array();
  if (empty($name)) {
    $errors[] = "Bitte geben Sie Ihren Namen ein.";
  }
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Bitte geben Sie eine gültige E-Mail-Adresse ein.";
  }
  if (empty($message)) {
    $errors[] = "Bitte geben Sie eine Nachricht ein.";
  }
  
  if (!empty($errors)) {
    echo "<h3>Fehler:</h3><ul>";
    foreach ($errors as $error) {
      echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo '<p><a href="index.php">Zurück zum Formular</a></p>';
    exit;
  }
  
  // Datenbankverbindung (Passe DSN, Benutzer und Passwort an!)
  $dsn    = "mysql:host=localhost;dbname=contact;charset=utf8";
  $dbUser = "praxisblockDB";
  $dbPass = "kcntmXThr9y3XhCZwGA.";
  
  try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Eintrag in die Tabelle einfügen
    $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, message) VALUES (:name, :email, :message)");
    $stmt->execute([
      ':name'    => $name,
      ':email'   => $email,
      ':message' => $message
    ]);
    
    echo "<h3>Vielen Dank für Ihre Nachricht!</h3>";
    echo '<p><a href="index.php">Zurück zur Startseite</a></p>';
  } catch (PDOException $e) {
    echo "Datenbankfehler: " . $e->getMessage();
  }
  
} else {
  header("Location: index.php");
  exit;
}
?>

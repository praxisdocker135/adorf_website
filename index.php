<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Landratsamt Ansbach</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <!-- Header mit Logo und Navigation -->
  <header>
    <div class="container header-container">
      <img src="logo.png" alt="Landratsamt Ansbach Logo" class="logo">
      <nav>
        <ul>
          <li><a href="#home">Startseite</a></li>
          <li><a href="#services">Leistungen</a></li>
          <li><a href="#news">Aktuelles</a></li>
          <li><a href="#contact">Kontakt</a></li>
          <!-- Zusätzliche Navigationselemente -->
          <li><a href="downloads.php">Öffentliche Downloads</a></li>
          <li><a href="login.php">Login</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Hero-Bereich -->
  <section id="hero">
    <div class="container">
      <h1>Willkommen beim Landratsamt Ansbach</h1>
      <p>Ihr zuverlässiger Partner in allen Verwaltungsangelegenheiten.</p>
      <a href="#services" class="btn">Mehr erfahren</a>
      <!-- Optional: ein zusätzlicher Login-Button -->
      <a href="login.php" class="btn">Login</a>
    </div>
  </section>

  <!-- Leistungs-Bereich -->
  <section id="services">
    <div class="container">
      <h2>Unsere Leistungen</h2>
      <div class="cards">
        <div class="card">
          <h3>Bürgerdienste</h3>
          <p>Informationen zu An- und Ummeldungen, Ausweisen und mehr.</p>
          <a href="#">Mehr erfahren</a>
        </div>
        <div class="card">
          <h3>Wirtschaftsförderung</h3>
          <p>Beratung und Unterstützung für Unternehmen in Ansbach.</p>
          <a href="#">Mehr erfahren</a>
        </div>
        <div class="card">
          <h3>Bau und Umwelt</h3>
          <p>Übersicht zu Planungs- und Genehmigungsverfahren.</p>
          <a href="#">Mehr erfahren</a>
        </div>
      </div>
    </div>
  </section>

  <!-- News-Bereich -->
  <section id="news">
    <div class="container">
      <h2>Aktuelles</h2>
      <article>
        <h3>Neues Bürgerportal gestartet</h3>
        <p>Erfahren Sie, wie unser neues digitales Portal den Service verbessert.</p>
        <a href="#">Zum Artikel</a>
      </article>
    </div>
  </section>

  <!-- Kontakt-Bereich -->
  <section id="contact">
    <div class="container">
      <h2>Kontakt</h2>
      <p>
        Landratsamt Ansbach<br>
        Musterstraße 1, 91522 Ansbach<br>
        Telefon: 0981 123456
      </p>
      <form action="#" method="post">
        <input type="text" name="name" placeholder="Ihr Name" required>
        <input type="email" name="email" placeholder="Ihre E-Mail" required>
        <textarea name="message" placeholder="Ihre Nachricht" required></textarea>
        <button type="submit">Senden</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>&copy; 2025 Landratsamt Ansbach. Alle Rechte vorbehalten.</p>
    </div>
  </footer>
</body>
</html>

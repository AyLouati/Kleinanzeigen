<?php
// Verbindung zur Datenbank mit PDO herstellen
try {
    $dsn = "mysql:host=localhost;dbname=bfw_kleinanzeigen;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $benutzername = $_POST['benutzername'];
    $passwort = $_POST['passwort'];

    // Passwort hashen
    $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO benutzer (benutzername, passwort) VALUES (:benutzername, :passwort)");
        $stmt->execute([
            ':benutzername' => $benutzername,
            ':passwort' => $passwort_hash
        ]);
        $message = "Registrierung erfolgreich!";
    } catch (PDOException $e) {
        $message = "Fehler bei der Registrierung: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Regestrieren</title>
</head>
<body>
    <div class="header">
        <div class="item1"><a href="kleinanzeigen.php"><img src="cropped-bfw-hamburg-Logo-4c.png" width="75%"></a></div> 
        <div class="item2">Willkommen auf BfW-Kleinanzeigen</div>
        <div class="navbar">
            
            <button class="menu-button">☰ Menü</button>
            <div class="dropdown-content">
                <a href="kleinanzeigen.php">Startseite</a>
                <a href="anzeigen.php">Anzeigen</a>
                <a href="kategorien.php">Kategorien</a>
                <a href="meine_anzeigen.php">Meine Anzeigen</a>
                <?php if (isset($_SESSION['rolle']) && $_SESSION['rolle'] == 'admin'): ?>
                    <a href="admin.php">Admin-Bereich</a>
                <?php endif; ?>
                <a href="login.php">Login</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>    
    </div> 
    <?php if (isset($message)): ?>
        <p class="<?= strpos($message, 'erfolgreich') !== false ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label for="benutzername">Benutzername:</label><br>
        <input type="text" id="benutzername" name="benutzername" required><br><br>
        <label for="passwort">Passwort:</label><br>
        <input type="password" id="passwort" name="passwort" required><br><br>
        <input type="submit" value="Registrieren">
    </form>
</body>
</html>
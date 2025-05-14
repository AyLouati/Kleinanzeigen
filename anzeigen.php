<?php
session_start();

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


$anzeigen = [];
try {
    $stmt = $pdo->query("SELECT * FROM anzeigen WHERE status = 'freigegeben' ORDER BY erstellungsdatum DESC");
    $anzeigen = $stmt->fetchAll();
} catch (PDOException $e) {
    $anzeigen = [];
    $message = "Fehler beim Abrufen der Anzeigen: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aufgeben'])) {
    if (!isset($_SESSION['benutzer_id'])) {
        $message = "Bitte melde dich an, um eine Anzeige aufzugeben.";
    } else {
        $titel = $_POST['titel'];
        $beschreibung = $_POST['beschreibung'];
        $preis = floatval($_POST['preis']);
        $nickname = $_POST['nickname'];
        $email = $_POST['email'];
        $benutzer_id = $_SESSION['benutzer_id'];
        $bild = null;

       
        if (isset($_FILES['bild']) && $_FILES['bild']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['bild']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $bild = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['bild']['tmp_name'], "Uploads/$bild");
            } else {
                $message = "Nur JPG, JPEG, PNG erlaubt.";
            }
        }

        try {
            $sql = "INSERT INTO anzeigen (titel, beschreibung, preis, nickname, email, benutzer_id, bild, status)
                    VALUES (:titel, :beschreibung, :preis, :nickname, :email, :benutzer_id, :bild, 'ausstehend')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titel' => $titel,
                ':beschreibung' => $beschreibung,
                ':preis' => $preis,
                ':nickname' => $nickname,
                ':email' => $email,
                ':benutzer_id' => $benutzer_id,
                ':bild' => $bild
            ]);
            $message = "Anzeige erfolgreich aufgegeben! Wird nach Freigabe sichtbar.";
        } catch (PDOException $e) {
            $message = "Fehler beim Erstellen der Anzeige: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Anzeigen</title>
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

    <div class="content">
        <h2>Anzeigen</h2>
        <?php if (!empty($anzeigen)): ?>
            <?php foreach ($anzeigen as $anzeige): ?>
                <div class="anzeige">
                    <h4><?= htmlspecialchars($anzeige['titel']) ?></h4>
                    <p><strong>Preis:</strong> €<?= number_format($anzeige['preis'], 2, ',', '.') ?></p>
                    <p><?= htmlspecialchars($anzeige['beschreibung']) ?></p>
                    <?php if ($anzeige['bild']): ?>
                        <img src="Uploads/<?= htmlspecialchars($anzeige['bild']) ?>" alt="Anzeigenbild" style="max-width: 200px;">
                    <?php endif; ?>
                    <p class="nickname">Eingestellt von: <?= htmlspecialchars($anzeige['nickname']) ?> am <?= htmlspecialchars($anzeige['erstellungsdatum']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Keine Anzeigen vorhanden.</p>
        <?php endif; ?>

       
        <div class="Anzeige_aufgeben">
            <h3>Anzeige aufgeben</h3>
            <form method="post" enctype="multipart/form-data">
                <label for="titel">Titel:</label><br>
                <input type="text" id="titel" name="titel" required><br><br>
                <label for="beschreibung">Beschreibung:</label><br>
                <textarea id="beschreibung" name="beschreibung" required></textarea><br><br>
                <label for="preis">Preis (€):</label><br>
                <input type="number" id="preis" name="preis" step="0.01" min="0" required><br><br>
                <label for="nickname">Nickname:</label><br>
                <input type="text" id="nickname" name="nickname" value="<?= isset($_SESSION['benutzername']) ? htmlspecialchars($_SESSION['benutzername']) : '' ?>" required><br><br>
                <label for="email">E-Mail:</label><br>
                <input type="email" id="email" name="email" required><br><br>
                <label for="bild">Bild (optional):</label><br>
                <input type="file" id="bild" name="bild" accept=".jpg,.jpeg,.png"><br><br>
                <button type="submit" name="aufgeben">Anzeige aufgeben</button>
            </form>
        </div>
    </div>
</body>
</html>
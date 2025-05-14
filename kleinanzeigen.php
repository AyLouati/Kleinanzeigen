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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aufgeben'])) {
    $titel = $_POST['titel'];
    $beschreibung = $_POST['beschreibung'];
    $rubrik_id = (int)$_POST['rubrik_id'];
    $nickname = $_POST['nickname'];
    $email = isset($_POST['email']) ? $_POST['email'] : ''; 

    try {
        $sql = "INSERT INTO anzeigen (titel, beschreibung, rubrik_id, nickname, email, status)
                VALUES (:titel, :beschreibung, :rubrik_id, :nickname, :email, 'freigegeben')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titel' => $titel,
            ':beschreibung' => $beschreibung,
            ':rubrik_id' => $rubrik_id,
            ':nickname' => $nickname,
            ':email' => $email
        ]);
        $message = "Anzeige erfolgreich aufgegeben!";
    } catch (PDOException $e) {
        $message = "Fehler beim Erstellen der Anzeige: " . $e->getMessage();
    }
}


try {
    $sql = "SELECT a.titel, a.beschreibung, r.name AS rubrik, a.nickname, a.erstellungsdatum
            FROM anzeigen a
            JOIN rubriken r ON a.rubrik_id = r.id
            WHERE a.status = 'freigegeben'
            ORDER BY r.name, a.erstellungsdatum DESC";
    $stmt = $pdo->query($sql);
    $anzeigen = $stmt->fetchAll();
} catch (PDOException $e) {
    $anzeigen = [];
    $message = "Fehler beim Abrufen der Anzeigen: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>BfW-Kleinanzeigen</title>
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
    <div class="startseite">
        
        <?php if (isset($message)): ?>
            <p class="<?= strpos($message, 'erfolgreich') !== false ? 'success' : 'error' ?>"><?= $message ?></p>
        <?php endif; ?>

        <div class="kategorien">
            <div class="item3">Kategorien<a href="kategorien.php" style="color: black;"></a></div>
            <div class="item4"><a href="kategorien.php" style="color: black;">Alle Kategorien</a></div>
            <div class="item5">Auto, Roller, Bikes<a href="kategorien.php" style="color: black;"></a></div>
            <div class="item6">Bücher<a href="kategorien.php" style="color: black;"></a></div>
            <div class="item7">Elektronik<a href="kategorien.php" style="color: black;"></a></div>
        </div>
        <div class="anzeigen">
            <div class="item8">Alle Anzeigen</div>
            <?php
            if (!empty($anzeigen)) {
                $aktuelle_rubrik = null;
                foreach ($anzeigen as $row) {
                    
                    if ($aktuelle_rubrik !== $row['rubrik']) {
                        $aktuelle_rubrik = $row['rubrik'];
                        echo "<h3 class='rubrik'>" . htmlspecialchars($aktuelle_rubrik) . "</h3>";
                    }

                    echo "<div class='anzeige'>";
                    echo "<h4>" . htmlspecialchars($row['titel']) . "</h4>";
                    echo "<p>" . htmlspecialchars($row['beschreibung']) . "</p>";
                    echo "<p class='nickname'>Eingestellt von: " . htmlspecialchars($row['nickname']) . " am " . htmlspecialchars($row['erstellungsdatum']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Keine Anzeigen gefunden.</p>";
            }
            ?>
        </div>
    </div>
    <div class="footer">
        <div class="item9">BFW Berufsförderungswerk Hamburg gGmbH - Marie-Bautz-Weg 16 - 22159 Hamburg - Telefon: 040 64581 - 1000 - E-Mail: info.bfw@cjd.de</div>
    </div>
</body>
</html>
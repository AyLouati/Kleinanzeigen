<?php
session_start();


try {
    $dsn = "mysql:host=localhost;dbname=bfw_kleinanzeigen;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}


if (!isset($_SESSION['rolle']) || $_SESSION['rolle'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_ad'])) {
    $anzeige_id = (int)$_POST['anzeige_id'];
    try {
        $stmt = $pdo->prepare("UPDATE anzeigen SET status = 'freigegeben' WHERE id = :anzeige_id");
        $stmt->execute([':anzeige_id' => $anzeige_id]);
       
        $stmt = $pdo->prepare("SELECT email FROM anzeigen WHERE id = :anzeige_id");
        $stmt->execute([':anzeige_id' => $anzeige_id]);
        $email = $stmt->fetchColumn();
       
        $message = "Anzeige freigegeben (E-Mail-Versand provisorisch).";
    } catch (PDOException $e) {
        $message = "Fehler beim Freigeben: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_ad'])) {
    $anzeige_id = (int)$_POST['anzeige_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM anzeigen WHERE id = :anzeige_id");
        $stmt->execute([':anzeige_id' => $anzeige_id]);
        $message = "Anzeige gelöscht.";
    } catch (PDOException $e) {
        $message = "Fehler beim Löschen: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $benutzername = $_POST['benutzername'];
    $email = $_POST['email'];
    $passwort = password_hash($_POST['passwort'], PASSWORD_DEFAULT);
    $rolle = $_POST['rolle'];
    try {
        $stmt = $pdo->prepare("INSERT INTO benutzer (benutzername, email, passwort, rolle) VALUES (:benutzername, :email, :passwort, :rolle)");
        $stmt->execute([
            ':benutzername' => $benutzername,
            ':email' => $email,
            ':passwort' => $passwort,
            ':rolle' => $rolle
        ]);
        $message = "Benutzer angelegt.";
    } catch (PDOException $e) {
        $message = "Fehler beim Anlegen: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $benutzer_id = (int)$_POST['benutzer_id'];
    $benutzername = $_POST['benutzername'];
    $email = $_POST['email'];
    $rolle = $_POST['rolle'];
    try {
        $stmt = $pdo->prepare("UPDATE benutzer SET benutzername = :benutzername, email = :email, rolle = :rolle WHERE id = :benutzer_id");
        $stmt->execute([
            ':benutzername' => $benutzername,
            ':email' => $email,
            ':rolle' => $rolle,
            ':benutzer_id' => $benutzer_id
        ]);
        $message = "Benutzer bearbeitet.";
    } catch (PDOException $e) {
        $message = "Fehler beim Bearbeiten: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $benutzer_id = (int)$_POST['benutzer_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM benutzer WHERE id = :benutzer_id");
        $stmt->execute([':benutzer_id' => $benutzer_id]);
        $message = "Benutzer gelöscht.";
    } catch (PDOException $e) {
        $message = "Fehler beim Löschen: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_rubrik'])) {
    $name = $_POST['name'];
    try {
        $stmt = $pdo->prepare("INSERT INTO rubriken (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        $message = "Rubrik angelegt.";
    } catch (PDOException $e) {
        $message = "Fehler beim Anlegen: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_rubrik'])) {
    $rubrik_id = (int)$_POST['rubrik_id'];
    $name = $_POST['name'];
    try {
        $stmt = $pdo->prepare("UPDATE rubriken SET name = :name WHERE id = :rubrik_id");
        $stmt->execute([':name' => $name, ':rubrik_id' => $rubrik_id]);
        $message = "Rubrik bearbeitet.";
    } catch (PDOException $e) {
        $message = "Fehler beim Bearbeiten: " . $e->getMessage();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_rubrik'])) {
    $rubrik_id = (int)$_POST['rubrik_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM rubriken WHERE id = :rubrik_id");
        $stmt->execute([':rubrik_id' => $rubrik_id]);
        $message = "Rubrik gelöscht.";
    } catch (PDOException $e) {
        $message = "Fehler beim Löschen: " . $e->getMessage();
    }
}


try {
    $stmt = $pdo->query("SELECT a.*, r.name AS rubrik_name FROM anzeigen a JOIN rubriken r ON a.rubrik_id = r.id ORDER BY a.erstellt_am DESC");
    $anzeigen = $stmt->fetchAll();
} catch (PDOException $e) {
    $anzeigen = [];
    $message = "Fehler beim Abrufen der Anzeigen: " . $e->getMessage();
}


try {
    $stmt = $pdo->query("SELECT * FROM benutzer ORDER BY benutzername ASC");
    $benutzer = $stmt->fetchAll();
} catch (PDOException $e) {
    $benutzer = [];
    $message = "Fehler beim Abrufen der Benutzer: " . $e->getMessage();
}


try {
    $stmt = $pdo->query("SELECT * FROM rubriken ORDER BY name ASC");
    $rubriken = $stmt->fetchAll();
} catch (PDOException $e) {
    $rubriken = [];
    $message = "Fehler beim Abrufen der Rubriken: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Admin-Bereich</title>
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
                <a href="admin.php">Admin-Bereich</a>
                <a href="login.php">Login</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <p class="<?= strpos($message, 'erfolgreich') !== false ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="content">
        <h2>Admin-Bereich</h2>
        <h3>Anzeigen verwalten</h3>
        <?php foreach ($anzeigen as $anzeige): ?>
            <div class="anzeige">
                <h4><?= htmlspecialchars($anzeige['titel']) ?></h4>
                <p><strong>Rubrik:</strong> <?= htmlspecialchars($anzeige['rubrik_name']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($anzeige['status']) ?></p>
                <p><?= htmlspecialchars($anzeige['beschreibung']) ?></p>
                <?php if ($anzeige['bild']): ?>
                    <img src="uploads/<?= htmlspecialchars($anzeige['bild']) ?>" alt="Anzeigenbild" style="max-width: 200px;">
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="anzeige_id" value="<?= $anzeige['id'] ?>">
                    <?php if ($anzeige['status'] != 'freigegeben'): ?>
                        <button type="submit" name="approve_ad">Freigeben</button>
                    <?php endif; ?>
                    <button type="submit" name="delete_ad" onclick="return confirm('Anzeige wirklich löschen?')">Löschen</button>
                </form>
            </div>
        <?php endforeach; ?>

        <h3>Benutzer verwalten</h3>
        <form method="post">
            <h4>Neuen Benutzer anlegen</h4>
            <label for="benutzername">Benutzername:</label>
            <input type="text" name="benutzername" required>
            <label for="email">E-Mail:</label>
            <input type="email" name="email" required>
            <label for="passwort">Passwort:</label>
            <input type="password" name="passwort" required>
            <label for="rolle">Rolle:</label>
            <select name="rolle" required>
                <option value="user">Benutzer</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="create_user">Anlegen</button>
        </form>
        <?php foreach ($benutzer as $b): ?>
            <div class="anzeige">
                <form method="post">
                    <input type="hidden" name="benutzer_id" value="<?= $b['id'] ?>">
                    <label for="benutzername">Benutzername:</label>
                    <input type="text" name="benutzername" value="<?= htmlspecialchars($b['benutzername']) ?>" required>
                    <label for="email">E-Mail:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($b['email']) ?>" required>
                    <label for="rolle">Rolle:</label>
                    <select name="rolle" required>
                        <option value="user" <?= $b['rolle'] == 'user' ? 'selected' : '' ?>>Benutzer</option>
                        <option value="admin" <?= $b['rolle'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit" name="edit_user">Bearbeiten</button>
                    <button type="submit" name="delete_user" onclick="return confirm('Benutzer wirklich löschen?')">Löschen</button>
                </form>
            </div>
        <?php endforeach; ?>

        <h3>Rubriken verwalten</h3>
        <form method="post">
            <h4>Neue Rubrik anlegen</h4>
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            <button type="submit" name="create_rubrik">Anlegen</button>
        </form>
        <?php foreach ($rubriken as $rubrik): ?>
            <div class="anzeige">
                <form method="post">
                    <input type="hidden" name="rubrik_id" value="<?= $rubrik['id'] ?>">
                    <label for="name">Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($rubrik['name']) ?>" required>
                    <button type="submit" name="edit_rubrik">Bearbeiten</button>
                    <button type="submit" name="delete_rubrik" onclick="return confirm('Rubrik wirklich löschen?')">Löschen</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
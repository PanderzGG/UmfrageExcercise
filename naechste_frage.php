<?php
session_start();
require_once 'db.php';

// Prüfen, ob die Session die Keys "fragenById" und "fragenindex" enthält
if (!isset($_SESSION['fragenById']) || !isset($_SESSION['fragenindex'])) {
    header('Location: index.php');
    exit();
}

// Prüfen, ob "ausgewaehlte_antwort" in $_POST gesetzt ist
if (!isset($_POST['ausgewaehlte_antwort'])) {
    header('Location: index.php');
    exit();
}

$neueAusgewaehlteAntwortID = (int)$_POST['ausgewaehlte_antwort'];

// Aktuelle Frage ermitteln
$fragenIndex = $_SESSION['fragenindex'];
$fragenIds = array_keys($_SESSION['fragenById']);
$aktuelleFrageId = $fragenIds[$fragenIndex];
$aktuelleFrage = &$_SESSION['fragenById'][$aktuelleFrageId];

$alteAusgewaehlteAntwortID = $aktuelleFrage['ausgewaehlteAntwortID'];


if ($alteAusgewaehlteAntwortID !== $neueAusgewaehlteAntwortID) {
    // Prüfen, ob der Nutzertoken im Cookie existiert
    if (!isset($_COOKIE['nutzertoken'])) {
        // Neuen Nutzertoken erstellen
        $result = $db->query("INSERT INTO nutzertoken VALUES (NULL)");
        if (!$result) {
            die("Fehler beim Erstellen des Nutzertokens: " . $db->error);
        }
        $nutzertoken = $db->insert_id;
        // Nutzertoken im Cookie speichern
        setcookie('nutzertoken', $nutzertoken, time() + (86400 * 30)); // Cookie gültig für 30 Tage
    } else {
        $nutzertoken = $_COOKIE['nutzertoken'];
    }
    $_SESSION['nutzertoken'] = $nutzertoken;

    $frageId = $aktuelleFrageId;

    if ($neueAusgewaehlteAntwortID == 0) {
        
        $stmt = $db->prepare("DELETE FROM abgegebeneantwort WHERE nutzertokenid = ? AND frageid = ? AND antwortid = ?");
        $stmt->bind_param("iii", $nutzertoken, $frageId, $alteAusgewaehlteAntwortID);
        $stmt->execute();
        $stmt->close();
    } elseif ($alteAusgewaehlteAntwortID == 0) {
        
        $stmt = $db->prepare("INSERT INTO abgegebeneantwort (nutzertokenid, frageid, antwortid) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $nutzertoken, $frageId, $neueAusgewaehlteAntwortID);
        $stmt->execute();
        $stmt->close();
    } else {
        
        $stmt = $db->prepare("UPDATE abgegebeneantwort SET antwortid = ? WHERE nutzertokenid = ? AND frageid = ? AND antwortid = ?");
        $stmt->bind_param("iiii", $neueAusgewaehlteAntwortID, $nutzertoken, $frageId, $alteAusgewaehlteAntwortID);
        $stmt->execute();
        $stmt->close();
    }

    // Neue ausgewählte Antwort speichern
    $aktuelleFrage['ausgewaehlteAntwortID'] = $neueAusgewaehlteAntwortID;
}


$_SESSION['fragenindex']++;


if ($_SESSION['fragenindex'] >= count($_SESSION['fragenById'])) {
    header('Location: danke.html');
    exit();
}


header('Location: index.php');
exit();
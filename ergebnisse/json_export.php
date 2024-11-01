<?php
require_once '../db.php';


$fragen = [];
$fragenById = [];

$result = $db->query("SELECT id, fragentext FROM frage");
while ($row = $result->fetch_assoc()) {
    $frageId = $row['id'];
    $frage = [
        'id' => $frageId,
        'fragentext' => $row['fragentext'],
        'antworten' => [],  // Wird später gefüllt
    ];
    $fragen[] = $frage;
    $fragenById[$frageId] = &$fragen[count($fragen) - 1];
}


$result = $db->query("
    SELECT 
        ma.id AS antwortid, 
        ma.frageid, 
        ma.antworttext, 
        COUNT(aa.id) AS anzahl
    FROM 
        moeglicheantwort ma
    LEFT JOIN 
        abgegebeneantwort aa ON ma.id = aa.antwortid
    GROUP BY 
        ma.id
");

while ($row = $result->fetch_assoc()) {
    $frageId = $row['frageid'];
    $antwort = [
        'id' => $row['antwortid'],
        'antworttext' => $row['antworttext'],
        'anzahl' => $row['anzahl'],
    ];
    $fragenById[$frageId]['antworten'][] = $antwort;
}

// Gesamte Anzahl Nutzertoken laden
$result = $db->query("SELECT COUNT(*) AS nutzeranzahl FROM nutzertoken");
$row = $result->fetch_assoc();
$gesamtNutzer = $row['nutzeranzahl'];

// Anzahl Antworten pro Frage und Anzahl "Keine Antwort" berechnen
foreach ($fragen as &$frage) {
    $frageId = $frage['id'];
    // Anzahl abgegebene Antworten für die Frage
    $result = $db->query("
        SELECT COUNT(DISTINCT nutzertokenid) AS anzahl 
        FROM abgegebeneantwort 
        WHERE frageid = $frageId AND antwortid != 0
    ");
    $row = $result->fetch_assoc();
    $antwortGesamt = $row['anzahl'];

    // Anzahl "Keine Antwort"
    $keineAntwortAnzahl = $gesamtNutzer - $antwortGesamt;

    // Speichern
    $frage['antwortGesamt'] = $antwortGesamt;
    $frage['keineAntwortAnzahl'] = $keineAntwortAnzahl;
}


$jsonData = [
    'gesamtNutzer' => $gesamtNutzer,
    'fragen' => $fragen,
];


$dateiname = 'umfrageergebnisse_' . date('Y-m-d') . '.json';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $dateiname . '"');


echo json_encode($jsonData, JSON_PRETTY_PRINT);
?>
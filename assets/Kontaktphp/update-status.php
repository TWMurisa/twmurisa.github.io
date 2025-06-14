<?php
// Verbindung zur DB
$db = new mysqli("localhost", "root", "", "auftragdb");

// Wenn Verbindung fehlschlägt → Abbruch mit Fehler
if ($db->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Datenbankfehler"]);
  exit;
}

// JSON-Daten einlesen und dekodieren
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Logging (zum Debuggen, optional)
// file_put_contents("debug_update.json", $input);

// Prüfung: Fehlen status oder id?
if (!isset($data['status']) || !isset($data['id'])) {
  http_response_code(400);
  echo json_encode(["error" => "Fehlende Daten"]);
  exit;
}

// SQL-Statement vorbereiten
$stmt = $db->prepare("UPDATE anfragen SET status = ?, status_geaendert_am = NOW() WHERE id = ?");
$stmt->bind_param("si", $data['status'], $data['id']);

// Ausführen und Statusmeldung
if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Update fehlgeschlagen"]);
}
?>

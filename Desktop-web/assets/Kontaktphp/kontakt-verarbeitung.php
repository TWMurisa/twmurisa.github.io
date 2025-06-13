<?php
$db = new mysqli("localhost", "root", "", "auftragdb");
if ($db->connect_error) die("DB Fehler");

$stmt = $db->prepare("INSERT INTO anfragen (vorname, nachname, email, telefon, budget, nachricht) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $_POST['vorname'], $_POST['nachname'], $_POST['email'], $_POST['telefon'], $_POST['budget'], $_POST['nachricht']);
$stmt->execute();

header("Location: danke.html");
?>

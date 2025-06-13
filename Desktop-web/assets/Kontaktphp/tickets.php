<?php
$conn = new mysqli("localhost", "root", "", "auftragdb");
$result = $conn->query("SELECT *, TIMESTAMPDIFF(MINUTE, status_geaendert_am, NOW()) AS dauer_min FROM anfragen");
$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
?>

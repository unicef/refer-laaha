<?php
$data = ["status" => 'active', 'code' => 200];
header('Content-Type: application/json');
echo json_encode($data);
?>
<?php
$data = $this->get('qDataTableBlock');
header('Content-type: application/json;  charset=UTF-8');
echo json_encode($data['ajax'], JSON_PRETTY_PRINT);

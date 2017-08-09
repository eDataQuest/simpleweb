<?php
$data = $this->get('qXeditBlock');
header('Content-type: application/json;  charset=UTF-8');
echo json_encode($data, JSON_PRETTY_PRINT);


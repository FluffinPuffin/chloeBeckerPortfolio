<?php
session_start();
header('Content-Type: application/json');

$id = (int)($_POST['project_id'] ?? 0);
if ($id) {
    $_SESSION['opened_packs']   = $_SESSION['opened_packs'] ?? [];
    $_SESSION['opened_packs'][] = $id;
    $_SESSION['opened_packs']   = array_values(array_unique($_SESSION['opened_packs']));
}

echo json_encode(['ok' => true]);

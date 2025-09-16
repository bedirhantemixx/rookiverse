<?php
session_start();
require_once 'config.php';

if (!isset($_POST['id'])) {
    echo 'invalid';
    exit;
}

$course_id = (int) $_POST['id'];

try {
    $db = get_db_connection();

    $check = $db->prepare("SELECT student FROM courses WHERE id = :id");
    $check->execute([':id' => $course_id]);
    $result = $check->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo 'course not found';
        exit;
    }

    $number = (int) $result['student'] + 1;

    $stmt = $db->prepare("UPDATE courses SET student = :student WHERE id = :id");
    $stmt->execute([
        ':student' => $number,
        ':id'      => $course_id,
    ]);

    echo 'success';
    echo $course_id; // gerekirse ID döndür
} catch (PDOException $e) {
    echo 'error: ' . $e->getMessage();
}

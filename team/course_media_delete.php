<?php
declare(strict_types=1);
session_start();

$projectRoot = dirname(__DIR__, 2);
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

function json_response($ok, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode($ok ? ['success'=>true] + $data : ['success'=>false] + $data);
    exit;
}

try {
    if (!isset($_SESSION['team_logged_in'], $_SESSION['team_db_id'])) {
        json_response(false, ['message'=>'Unauthorized'], 401);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, ['message'=>'Method Not Allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $course_id = isset($input['id']) ? (int)$input['id'] : 0;
    $type = $input['type'] ?? ''; // 'cover' | 'intro'

    if ($course_id <= 0 || !in_array($type, ['cover','intro'], true)) {
        json_response(false, ['message'=>'Geçersiz parametreler'], 422);
    }

    $pdo = get_db_connection();

    // sahiplik
    $s = $pdo->prepare('SELECT cover_image_url, intro_video_url FROM courses WHERE id=:id AND team_db_id=:team');
    $s->execute([':id'=>$course_id, ':team'=>$_SESSION['team_db_id']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        json_response(false, ['message'=>'Course not found or not owned'], 403);
    }

    // fiziki dosyayı sil
    $base = $projectRoot . '/uploads/courses/' . $course_id . '/';
    if ($type === 'cover') {
        foreach (glob($base . 'cover.*') as $p) { @unlink($p); }
        $u = $pdo->prepare('UPDATE courses SET cover_image_url=NULL, updated_at=NOW() WHERE id=:id AND team_db_id=:team');
        $u->execute([':id'=>$course_id, ':team'=>$_SESSION['team_db_id']]);
    } else {
        @unlink($base . 'intro.mp4');
        $u = $pdo->prepare('UPDATE courses SET intro_video_url=NULL, updated_at=NOW() WHERE id=:id AND team_db_id=:team');
        $u->execute([':id'=>$course_id, ':team'=>$_SESSION['team_db_id']]);
    }

    json_response(true, ['message'=>'Medya kaldırıldı.']);
} catch (Throwable $e) {
    json_response(false, ['message'=>'Server error','error'=>$e->getMessage()], 500);
}

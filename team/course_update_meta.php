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

    $pdo = get_db_connection();

    $course_id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title         = trim($_POST['title'] ?? '');
    $about_text    = trim($_POST['about_text'] ?? '');
    $goal_text     = trim($_POST['goal_text'] ?? '');
    $learnings     = trim($_POST['learnings_text'] ?? '');
    $category_id   = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $level         = trim($_POST['level'] ?? '');

    if ($course_id <= 0 || $title === '' || $about_text === '' || $goal_text === '' || $learnings === '' || $category_id <= 0 || $level === '') {
        json_response(false, ['message'=>'Eksik veya hatalı form verisi'], 422);
    }

    // Kurs bu takıma mı ait?
    $q = $pdo->prepare('SELECT id FROM courses WHERE id=:id AND team_db_id=:team');
    $q->execute([':id'=>$course_id, ':team'=>$_SESSION['team_db_id']]);
    if (!$q->fetch()) {
        json_response(false, ['message'=>'Course not found or not owned'], 403);
    }

    // Kategori exists?
    $qc = $pdo->prepare('SELECT id FROM categories WHERE id=:cid');
    $qc->execute([':cid'=>$category_id]);
    if (!$qc->fetch()) {
        json_response(false, ['message'=>'Kategori bulunamadı'], 422);
    }

    // Güncelle
    $u = $pdo->prepare('UPDATE courses
    SET title=:title, status=:status, about_text=:about, goal_text=:goal, learnings_text=:learn,
        category_id=:cat, level=:lvl, updated_at=NOW()
    WHERE id=:id AND team_db_id=:team');

    $u->execute([
        ':title'=>$title,
        ':about'=>$about_text,
        ':goal'=>$goal_text,
        ':learn'=>$learnings,
        ':cat'=>$category_id,
        ':lvl'=>$level,
        ':id'=>$course_id,
        ':team'=>$_SESSION['team_db_id'],
        ':status'=>'pending',
    ]);

    json_response(true, ['message'=>'Kurs bilgileri güncellendi.']);
    header('location: editCourse.php?id='.$course_id);
    exit();
} catch (Throwable $e) {
    json_response(false, ['message'=>'Server error','error'=>$e->getMessage()], 500);
    header('editCourse.php?id='.$course_id);
    exit();
}

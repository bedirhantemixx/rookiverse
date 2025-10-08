<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$debug = isset($_GET['debug']);
$type  = $_GET['type'] ?? '';

/** (Opsiyonel) config sabiti varsa kullanalım */
$projectRoot = null;
if (is_file(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
    if (defined('PROJECT_ROOT')) {
        $projectRoot = rtrim(PROJECT_ROOT, '/\\');
    }
}

/** Olası data dizinleri (öncelik sırasıyla) */
$candidates = [];
if ($projectRoot)          $candidates[] = $projectRoot . '/data';
$candidates[] = realpath(__DIR__ . '/../data') ?: (__DIR__ . '/../data');
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/data';
}

/** Hedef dosyayı eşle */
$filename = match ($type) {
    'games'      => 'games.json',
    'categories' => 'categories.json',
    default      => null
};

if (!$filename) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid type']); exit;
}

/** İlk erişilebilir yolu seç */
$foundPath = null;
foreach ($candidates as $dir) {
    $p = rtrim($dir, '/\\') . '/' . $filename;
    if (is_readable($p)) { $foundPath = $p; break; }
}

/** Hata durumunda teşhis ver */
if (!$foundPath) {
    http_response_code(404);
    if ($debug) {
        echo json_encode([
            'ok' => false,
            'error' => 'Not found',
            'checked_paths' => array_map(fn($d)=>rtrim($d, '/\\') . '/' . $filename, $candidates),
            'cwd' => getcwd(),
            'dir' => __DIR__
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok'=>false,'error'=>'Not found']);
    }
    exit;
}

/** JSON oku */
$raw  = file_get_contents($foundPath);
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'JSON parse error: '.json_last_error_msg()]); exit;
}

echo json_encode(['ok'=>true,'data'=>$data,'_path'=>$debug ? $foundPath : null], JSON_UNESCAPED_UNICODE);

<?php
/**
 * Secure Download Gateway
 * Handles verified, obfuscated token downloads with path traversal mitigation
 */

require_once __DIR__ . '/config.php';

// Check token parameter
$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    die("<h3>400 Bad Request</h3><p>Parameter token unduhan tidak valid atau telah kadaluarsa.</p>");
}

// Validate Token
$filename = validateFileToken($token);

if (!$filename) {
    http_response_code(403);
    die("<h3>403 Forbidden</h3><p>Akses ditolak: Token verifikasi berkas tidak valid, rusak, atau telah kadaluarsa. Silakan refresh halaman utama.</p>");
}

// Sanitize filename against Directory Traversal attacks
$cleanFilename = basename($filename);
if ($cleanFilename !== $filename || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
    http_response_code(403);
    die("<h3>403 Security Alert</h3><p>Percobaan manipulasi jalur direktori terdeteksi.</p>");
}

// Check allowed extensions
$ext = strtolower(pathinfo($cleanFilename, PATHINFO_EXTENSION));
if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
    http_response_code(403);
    die("<h3>403 Forbidden</h3><p>Ekstensi berkas tidak diizinkan untuk diunduh.</p>");
}

// Locate file in storage or fallback
$targetPath = STORAGE_PATH . DIRECTORY_SEPARATOR . $cleanFilename;
if (!file_exists($targetPath)) {
    $targetPath = FALLBACK_STORAGE_PATH . DIRECTORY_SEPARATOR . $cleanFilename;
}

if (!file_exists($targetPath) || !is_file($targetPath)) {
    http_response_code(404);
    die("<h3>404 Not Found</h3><p>Berkas formulir tidak ditemukan di server.</p>");
}

// Realpath verification to prevent symlink traversal outside allowed folders
$realTargetPath = realpath($targetPath);
$realStoragePath = realpath(STORAGE_PATH);
$realFallbackPath = realpath(FALLBACK_STORAGE_PATH);

$isInsideAllowed = false;
if ($realStoragePath && str_starts_with($realTargetPath, $realStoragePath)) {
    $isInsideAllowed = true;
} elseif ($realFallbackPath && str_starts_with($realTargetPath, $realFallbackPath)) {
    $isInsideAllowed = true;
}

if (!$isInsideAllowed) {
    http_response_code(403);
    die("<h3>403 Security Violation</h3><p>Akses di luar direktori aman ditolak.</p>");
}

// Content-Type mapping
$mimeTypes = [
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc'  => 'application/msword',
    'pdf'  => 'application/pdf',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls'  => 'application/vnd.ms-excel',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'zip'  => 'application/zip',
];

$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
$fileSize = filesize($realTargetPath);

// Clean output buffer to ensure binary file integrity
if (ob_get_level()) {
    ob_end_clean();
}

// Security & Streaming Headers
$mode = $_GET['mode'] ?? 'attachment';
$disposition = ($mode === 'view' || $mode === 'inline') ? 'inline' : 'attachment';

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);
header("Content-Type: " . $contentType);
header("Content-Disposition: " . $disposition . "; filename=\"" . rawurlencode($cleanFilename) . "\"; filename*=UTF-8''" . rawurlencode($cleanFilename));
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $fileSize);
header("X-Content-Type-Options: nosniff");
header("X-Robots-Tag: noindex, nofollow");

// Stream file in safe chunks to avoid memory bottlenecks
$handle = fopen($realTargetPath, 'rb');
if ($handle === false) {
    http_response_code(500);
    die("Gagal membaca berkas.");
}

while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}
fclose($handle);
exit;


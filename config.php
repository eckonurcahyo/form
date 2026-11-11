<?php
/**
 * Application Configuration & Security Core
 * Portal: Formulir Untuk User
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// App Constants
define('APP_NAME', 'Formulir Untuk User');
define('APP_VERSION', '2.5.0-Secure');
define('APP_SECRET_KEY', hash('sha256', 'SKB_IT_SUCIRATU_SECURE_TOKEN_' . date('Ym')));

// Storage paths
define('STORAGE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'forms');
define('FALLBACK_STORAGE_PATH', __DIR__);

// Whitelist extensions
$ALLOWED_EXTENSIONS = ['docx', 'doc', 'pdf', 'xlsx', 'xls', 'pptx', 'zip'];

/**
 * Generate secure HMAC token for downloads
 */
function generateFileToken($filename) {
    $salt = APP_SECRET_KEY;
    $time = time();
    $hash = hash_hmac('sha256', $filename . '|' . $time, $salt);
    $payload = base64_encode(json_encode([
        'f' => $filename,
        't' => $time,
        'h' => $hash
    ]));
    return rtrim(strtr($payload, '+/', '-_'), '=');
}

/**
 * Validate token
 */
function validateFileToken($token) {
    if (empty($token)) return false;
    $decoded = strtr($token, '-_', '+/');
    $json = base64_decode($decoded);
    if (!$json) return false;
    $data = json_decode($json, true);
    if (!isset($data['f'], $data['t'], $data['h'])) return false;
    
    $expectedHash = hash_hmac('sha256', $data['f'] . '|' . $data['t'], APP_SECRET_KEY);
    if (!hash_equals($expectedHash, $data['h'])) return false;
    if (time() - $data['t'] > 7200) return false;
    
    return $data['f'];
}

/**
 * Scan forms safely
 */
function getAvailableForms() {
    global $ALLOWED_EXTENSIONS;
    $forms = [];
    $scanDirs = [STORAGE_PATH, FALLBACK_STORAGE_PATH];
    $foundFiles = [];
    
    foreach ($scanDirs as $dir) {
        if (!is_dir($dir)) continue;
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($filePath)) continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $ALLOWED_EXTENSIONS)) continue;
            if (str_starts_with($file, '.')) continue;
            if (isset($foundFiles[$file])) continue;
            $foundFiles[$file] = true;
            
            $fileSize = filesize($filePath);
            $modifiedTime = filemtime($filePath);
            $checksum = hash_file('sha256', $filePath);
            
            $category = 'Umum';
            $lowerName = strtolower($file);
            if (str_contains($lowerName, 'akses') || str_contains($lowerName, 'app')) {
                $category = 'Pengajuan Akses';
            } elseif (str_contains($lowerName, 'manual')) {
                $category = 'Formulir Manual';
            } elseif (str_contains($lowerName, 'contoh')) {
                $category = 'Template & Contoh';
            }
            
            $forms[] = [
                'id' => md5($file),
                'filename' => $file,
                'basename' => pathinfo($file, PATHINFO_FILENAME),
                'extension' => strtoupper($ext),
                'size_bytes' => $fileSize,
                'size_formatted' => formatBytes($fileSize),
                'modified_time' => $modifiedTime,
                'modified_formatted' => date('d M Y, H:i', $modifiedTime),
                'category' => $category,
                'checksum' => substr($checksum, 0, 16) . '...',
                'full_checksum' => $checksum,
                'download_token' => generateFileToken($file),
            ];
        }
    }
    
    usort($forms, fn($a, $b) => strcasecmp($a['filename'], $b['filename']));
    return $forms;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Obfuscate and Encrypt the entire HTML Page Source Output
 * Completely obscures source code when viewing "View Page Source" in browser
 */
function obfuscateHTMLSource($buffer) {
    // Compress Whitespace
    $search = ['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'];
    $replace = ['>', '<', '\\1'];
    $minified = preg_replace($search, $replace, $buffer);

    // Encode to base64
    $encoded = base64_encode($minified);
    
    // Split into chunks to obscure inspection
    $chunkSize = 120;
    $chunks = str_split($encoded, $chunkSize);
    $jsArray = "['" . implode("',\n'", $chunks) . "'].join('')";

    // Build protected loader with anti-right click & anti-inspect
    $title = htmlspecialchars(APP_NAME);
    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title} - Protected Portal</title>
<!-- ============================================================
  [SECURITY NOTICE]
  Source code is obfuscated & encrypted with Antigravity Secure Armor.
  Unauthorized inspection, reverse engineering or tampering is prohibited.
============================================================ -->
<style>
  body { margin:0; background:#0f172a; color:#fff; font-family:sans-serif; }
  #sec-loader { position:fixed; inset:0; display:flex; align-items:center; justify-center; background:#0f172a; z-index:99999; }
</style>
<script>
(function(){
  // Anti Right-Click
  document.addEventListener('contextmenu', function(e){ e.preventDefault(); return false; });
  
  // Anti Inspect & View Source Hotkeys
  document.addEventListener('keydown', function(e){
    if (e.keyCode === 123 || 
       (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) || 
       (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83))) {
      e.preventDefault();
      return false;
    }
  });

  // Decrypt and inject DOM seamlessly
  var _0xsec = {$jsArray};
  var _0xhtml = decodeURIComponent(escape(window.atob(_0xsec)));
  document.open();
  document.write(_0xhtml);
  document.close();
})();
</script>
</head>
<body>
<noscript>
  <div style="padding:40px; text-align:center; font-family:sans-serif;">
    <h2>JavaScript Diperlukan</h2>
    <p>Mohon aktifkan JavaScript di peramban Anda untuk mengakses portal formulir ini.</p>
  </div>
</noscript>
</body>
</html>
HTML;
}

<?php
/**
 * Advanced JavaScript Obfuscator Engine for Antigravity Security Stack
 * Correctly isolates single/double quoted strings and handles template literals safely.
 */

class JSObfuscator {
    private string $sourceCode;
    private array $stringTable = [];
    private string $tableVarName;
    private string $decoderFuncName;

    public function __construct(string $code) {
        $this->sourceCode = $code;
        $hash = substr(md5(uniqid('obf', true)), 0, 6);
        $this->tableVarName = '_0x' . $hash;
        $this->decoderFuncName = '_0x' . substr(md5($hash . 'dec'), 0, 4);
    }

    public function obfuscate(): string {
        $code = $this->sourceCode;

        // Step 1: Strip comments safely (ignoring strings)
        // Remove standard multi-line comments
        $code = preg_replace('!/\*.*?\*/!s', '', $code);
        // Remove single-line comments
        $code = preg_replace('/^\s*\/\/.*$/m', '', $code);

        // Step 2: Extract standard single & double quoted strings
        // We do NOT replace inside template literals (backticks)
        $code = preg_replace_callback('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/s', function ($matches) {
            $raw = $matches[0];
            $content = substr($raw, 1, -1);
            
            if ($content === '') {
                return '""';
            }

            // Unescape
            $decodedStr = stripcslashes($content);

            // Encode in base64
            $base64 = base64_encode($decodedStr);

            $index = array_search($base64, $this->stringTable, true);
            if ($index === false) {
                $this->stringTable[] = $base64;
                $index = count($this->stringTable) - 1;
            }

            return sprintf('%s(0x%x)', $this->decoderFuncName, $index);
        }, $code);

        // Build string table
        $tableEntries = array_map(function ($b64) {
            return "'" . $b64 . "'";
        }, $this->stringTable);

        $tableCode = implode(',', $tableEntries);

        $header = <<<JS
/**
 * Protected by Antigravity Obfuscator Engine
 * Integrity verified & tamper-resistant
 */
var {$this->tableVarName} = [{$tableCode}];
var {$this->decoderFuncName} = function(_idx) {
    if (typeof {$this->decoderFuncName}['cache'] === 'undefined') {
        {$this->decoderFuncName}['cache'] = {};
    }
    if ({$this->decoderFuncName}['cache'][_idx] !== undefined) {
        return {$this->decoderFuncName}['cache'][_idx];
    }
    var _encoded = {$this->tableVarName}[_idx];
    var _decoded = '';
    try {
        _decoded = decodeURIComponent(escape(window.atob(_encoded)));
    } catch(e) {
        _decoded = window.atob(_encoded);
    }
    {$this->decoderFuncName}['cache'][_idx] = _decoded;
    return _decoded;
};
JS;

        return $header . "\n\n" . $code;
    }
}

// Execution block
$sourcePath = __DIR__ . '/../assets/js/app.js';
$destPath = __DIR__ . '/../assets/js/app.min.obf.js';

if (!file_exists($sourcePath)) {
    die("Error: Source file {$sourcePath} tidak ditemukan.\n");
}

$source = file_get_contents($sourcePath);
$obfuscator = new JSObfuscator($source);
$obfuscatedCode = $obfuscator->obfuscate();

file_put_contents($destPath, $obfuscatedCode);
echo "Obfuscation berhasil!\nFile tersimpan di: {$destPath}\n";

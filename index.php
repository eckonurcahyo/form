<?php
require_once __DIR__ . '/config.php';

// Enable Total Source Code Obfuscation & Encryption
ob_start('obfuscateHTMLSource');

$forms = getAvailableForms();
$totalForms = count($forms);
$totalSize = array_sum(array_column($forms, 'size_bytes'));
$formattedTotalSize = formatBytes($totalSize);
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - Portal Unduh Dokumen Resmi</title>
    
    <!-- Offline Tailwind CSS & Engine -->
    <link rel="stylesheet" href="assets/css/tailwind.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <script src="assets/js/tailwind.min.js"></script>
    <script>
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                50: '#eef2ff',
                                100: '#e0e7ff',
                                200: '#c7d2fe',
                                500: '#6366f1',
                                600: '#4f46e5',
                                700: '#4338ca',
                                900: '#312e81'
                            }
                        }
                    }
                }
            };
        }
    </script>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-300 relative overflow-x-hidden select-none">

    <!-- Ambient Glowing Gradient Orbs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-500/15 dark:bg-indigo-600/20 rounded-full blur-3xl animate-glow"></div>
        <div class="absolute top-1/3 -right-32 w-80 h-80 bg-cyan-500/15 dark:bg-cyan-600/15 rounded-full blur-3xl animate-glow" style="animation-delay: -3s;"></div>
        <div class="absolute -bottom-20 left-1/3 w-96 h-96 bg-purple-500/15 dark:bg-purple-600/15 rounded-full blur-3xl animate-glow" style="animation-delay: -5s;"></div>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-30 glass-panel border-b border-slate-200/80 dark:border-slate-800/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo & Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 p-0.5 shadow-lg shadow-indigo-500/25 flex items-center justify-center transform transition-transform hover:scale-105">
                    <div class="w-full h-full bg-indigo-600 rounded-[14px] flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            <?= htmlspecialchars(APP_NAME) ?>
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            v<?= APP_VERSION ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Portal Unduh Dokumen & Template Resmi</p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>100% Offline Ready</span>
                </div>

                <!-- Theme Switcher -->
                <button id="theme-toggle-btn" type="button" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-all border border-slate-200 dark:border-slate-700 focus:outline-none" aria-label="Ganti Tema">
                    <svg id="theme-icon-sun" class="w-5 h-5 hidden text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="theme-icon-moon" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span id="theme-label" class="text-xs font-semibold hidden md:inline">Mode</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
        
        <!-- Hero Section -->
        <div class="relative rounded-3xl p-6 sm:p-8 md:p-10 mb-8 overflow-hidden bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white shadow-2xl border border-indigo-500/20">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute right-12 top-10 opacity-10 hidden lg:block pointer-events-none">
                <svg class="w-64 h-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>

            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-4">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Aman & Terverifikasi
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3">
                    Unduh Berkas Formulir & Format Word Resmi
                </h2>
                <p class="text-indigo-200 text-sm sm:text-base leading-relaxed mb-6">
                    Pilih formulir yang Anda butuhkan di bawah ini. Semua dokumen diproteksi dengan verifikasi hash dan token unduhan aman untuk memastikan integritas data.
                </p>

                <!-- Quick Stats Badges -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                    <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                        <p class="text-xs text-indigo-200">Total Formulir</p>
                        <p class="text-2xl font-bold text-white"><?= $totalForms ?></p>
                    </div>
                    <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                        <p class="text-xs text-indigo-200">Format Didukung</p>
                        <p class="text-2xl font-bold text-white"><?= implode(', ', array_unique(array_column($forms, 'extension'))) ?></p>
                    </div>
                    <div class="col-span-2 sm:col-span-1 px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                        <p class="text-xs text-indigo-200">Total Ukuran</p>
                        <p class="text-2xl font-bold text-white"><?= $formattedTotalSize ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Grid View (Langsung Ditampilkan) -->
        <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <?php foreach ($forms as $doc): 
                $isPdf = (strtoupper($doc['extension']) === 'PDF');
            ?>
            <div 
                class="form-card-item glass-card rounded-2xl p-6 relative flex flex-col justify-between group"
                data-title="<?= strtolower(htmlspecialchars($doc['filename'])) ?>"
                data-category="<?= strtolower(htmlspecialchars($doc['category'])) ?>"
                data-ext="<?= strtolower($doc['extension']) ?>"
            >
                <!-- Top Card Info -->
                <div>
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <?php if ($isPdf): ?>
                        <!-- PDF Icon badge (Rose / Red) -->
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 flex items-center justify-center flex-shrink-0 text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9h4m-4 4h4m-4 4h2"/>
                            </svg>
                        </div>
                        <?php else: ?>
                        <!-- DOCX Icon badge (Blue) -->
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 flex items-center justify-center flex-shrink-0 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/>
                            </svg>
                        </div>
                        <?php endif; ?>

                        <!-- Category & Format Tag -->
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                                <?= htmlspecialchars($doc['category']) ?>
                            </span>
                            <?php if ($isPdf): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300">
                                .PDF
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">
                                .<?= htmlspecialchars($doc['extension']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- File Title -->
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        <?= htmlspecialchars($doc['basename']) ?>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 line-clamp-2">
                        <?php if ($isPdf): ?>
                        Contoh formulir referensi dalam format PDF. Klik untuk membuka dan melihat contoh langsung di tab baru peramban.
                        <?php else: ?>
                        Format berkas resmi Microsoft Word (.docx) siap pakai dan dapat diedit langsung oleh pengguna.
                        <?php endif; ?>
                    </p>

                    <!-- File Meta Specs -->
                    <div class="flex flex-wrap items-center gap-y-2 gap-x-4 py-3 px-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 mb-6">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
                            </svg>
                            <span><?= $doc['size_formatted'] ?></span>
                        </div>
                        <div class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><?= $doc['modified_formatted'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Buttons -->
                <div class="flex items-center gap-3 pt-2">
                    <?php if ($isPdf): ?>
                    <!-- PDF Action: Buka Tab Baru "Lihat Contoh" -->
                    <a 
                        href="download.php?token=<?= urlencode($doc['download_token']) ?>&mode=view" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all focus:outline-none"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Lihat Contoh</span>
                    </a>
                    <?php else: ?>
                    <!-- Word / DOCX Action: Unduh Formulir -->
                    <button 
                        type="button" 
                        onclick="triggerDownload('<?= $doc['download_token'] ?>', '<?= addslashes($doc['filename']) ?>', this)" 
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all focus:outline-none"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Unduh Formulir</span>
                    </button>
                    <?php endif; ?>

                    <!-- Details Secondary Button -->
                    <button 
                        type="button" 
                        onclick='openDetailModal(<?= json_encode($doc) ?>)'
                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold transition-all border border-slate-200 dark:border-slate-700" 
                        title="Lihat Detail & Checksum"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Security Footnote Banner -->
        <div class="mt-12 p-6 rounded-2xl glass-panel border border-slate-200/80 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Keamanan Dokumen Terjamin</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Download token HMAC SHA-256 terenkripsi, proteksi path traversal, dan Source Code Protection.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Portal Siap Digunakan Secara Offline</span>
            </div>
        </div>

    </main>

    <!-- Modal Detail Formulir -->
    <div id="doc-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div id="modal-backdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
        <div id="modal-content" class="relative z-10 w-full max-w-lg glass-panel bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 transform transition-all">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-200 dark:border-indigo-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span id="modal-doc-category" class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 mb-1">Kategori</span>
                        <h3 id="modal-doc-title" class="text-base font-bold text-slate-900 dark:text-white leading-tight">Nama Dokumen</h3>
                    </div>
                </div>
                <button id="modal-close-btn" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-3 mb-6">
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Format File</span>
                    <span id="modal-doc-ext" class="font-bold text-indigo-600 dark:text-indigo-400 uppercase">DOCX</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Ukuran Berkas</span>
                    <span id="modal-doc-size" class="font-semibold text-slate-800 dark:text-slate-200">0 KB</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Tanggal Modifikasi Terakhir</span>
                    <span id="modal-doc-date" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                </div>
                
                <div class="pt-2">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Integritas Checksum (SHA-256)</span>
                        <button type="button" onclick="copyChecksum()" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Salin Hash</span>
                        </button>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-700 dark:text-slate-300 break-all select-all" id="modal-doc-checksum">
                        -
                    </div>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 mb-6 text-xs text-indigo-900 dark:text-indigo-200 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Unduh berkas ini dan buka menggunakan Microsoft Word atau LibreOffice Writer.</span>
            </div>

            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    id="modal-download-btn"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-indigo-500/25 transition-all"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full"></div>

    <!-- Obfuscated Security Script -->
    <script src="assets/js/app.min.obf.js"></script>

</body>
</html>
<?php
ob_end_flush();
?>

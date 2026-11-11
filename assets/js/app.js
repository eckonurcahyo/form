/**
 * Formulir Untuk User - Interactive Client Engine
 * Features: Modal Detail, Theme Toggle, Sleek Compact Toast, Anti-Inspect Protection
 */

(function () {
    'use strict';

    // State
    const state = {
        theme: localStorage.getItem('portal_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
        activeModalDoc: null,
        activeToastTimer: null
    };

    // DOM Elements
    const elements = {
        html: document.documentElement,
        themeToggleBtn: document.getElementById('theme-toggle-btn'),
        themeIconSun: document.getElementById('theme-icon-sun'),
        themeIconMoon: document.getElementById('theme-icon-moon'),
        themeLabel: document.getElementById('theme-label'),
        cardsContainer: document.getElementById('cards-container'),
        docModal: document.getElementById('doc-modal'),
        modalBackdrop: document.getElementById('modal-backdrop'),
        modalContent: document.getElementById('modal-content'),
        modalCloseBtn: document.getElementById('modal-close-btn'),
        toastContainer: document.getElementById('toast-container')
    };

    // Initialize Theme
    function initTheme() {
        if (state.theme === 'dark') {
            elements.html.classList.add('dark');
            if (elements.themeIconSun) elements.themeIconSun.classList.remove('hidden');
            if (elements.themeIconMoon) elements.themeIconMoon.classList.add('hidden');
            if (elements.themeLabel) elements.themeLabel.textContent = 'Mode Gelap';
        } else {
            elements.html.classList.remove('dark');
            if (elements.themeIconSun) elements.themeIconSun.classList.add('hidden');
            if (elements.themeIconMoon) elements.themeIconMoon.classList.remove('hidden');
            if (elements.themeLabel) elements.themeLabel.textContent = 'Mode Terang';
        }
    }

    function toggleTheme() {
        state.theme = state.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('portal_theme', state.theme);
        initTheme();
        showToast('Tema diubah ke ' + (state.theme === 'dark' ? 'Mode Gelap' : 'Mode Terang'), 'info');
    }

    // Modal Details Handler
    window.openDetailModal = function (docData) {
        state.activeModalDoc = docData;
        const modal = elements.docModal;
        if (!modal) return;

        document.getElementById('modal-doc-title').textContent = docData.filename;
        document.getElementById('modal-doc-category').textContent = docData.category;
        document.getElementById('modal-doc-size').textContent = docData.size_formatted;
        document.getElementById('modal-doc-date').textContent = docData.modified_formatted;
        document.getElementById('modal-doc-ext').textContent = docData.extension;
        document.getElementById('modal-doc-checksum').textContent = docData.full_checksum;

        const downloadBtn = document.getElementById('modal-download-btn');
        if (downloadBtn) {
            const isPdf = (docData.extension && docData.extension.toUpperCase() === 'PDF');
            if (isPdf) {
                downloadBtn.className = 'flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-emerald-500/25 transition-all';
                downloadBtn.innerHTML = '<span class="text-base">👁️</span> <span>Lihat Contoh (Buka Tab Baru)</span>';
                downloadBtn.onclick = function () {
                    window.open('download.php?token=' + encodeURIComponent(docData.download_token) + '&mode=view', '_blank');
                };
            } else {
                downloadBtn.className = 'flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-indigo-500/25 transition-all';
                downloadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> <span>Unduh Sekarang</span>';
                downloadBtn.onclick = function () {
                    triggerDownload(docData.download_token, docData.filename, downloadBtn);
                };
            }
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function closeModal() {
        const modal = elements.docModal;
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Trigger Secure Download with Single Elegant Feedback
    window.triggerDownload = function (token, filename, btnElement) {
        if (!token) {
            showToast('Token unduhan tidak tersedia', 'error');
            return;
        }

        let originalContent = '';
        if (btnElement) {
            originalContent = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Menyiapkan...';
        }

        showToast('Mengunduh: ' + filename, 'success');

        const downloadUrl = 'download.php?token=' + encodeURIComponent(token);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();

        setTimeout(function () {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalContent;
            }
            link.remove();
        }, 1500);
    };

    // Copy to Clipboard
    window.copyChecksum = function () {
        if (!state.activeModalDoc || !state.activeModalDoc.full_checksum) return;
        navigator.clipboard.writeText(state.activeModalDoc.full_checksum).then(function () {
            showToast('Checksum SHA256 disalin!', 'success');
        }).catch(function () {
            showToast('Gagal menyalin checksum', 'error');
        });
    };

    // Compact Toast Notification
    function showToast(message, type) {
        type = type || 'info';
        if (!elements.toastContainer) return;

        elements.toastContainer.innerHTML = '';
        if (state.activeToastTimer) {
            clearTimeout(state.activeToastTimer);
        }

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-center justify-between gap-3 px-4 py-3 rounded-xl shadow-xl border text-xs font-semibold max-w-sm w-auto transition-all transform duration-200 translate-y-2 opacity-0 ' +
            (type === 'success' 
                ? 'bg-slate-900 text-white border-emerald-500/50 dark:bg-slate-800' 
                : type === 'error'
                ? 'bg-rose-900 text-white border-rose-500/50'
                : 'bg-slate-900 text-white border-indigo-500/50 dark:bg-slate-800');

        const iconSymbol = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';

        toast.innerHTML = 
            '<div class="flex items-center gap-2.5 overflow-hidden">' +
                '<span class="text-sm flex-shrink-0">' + iconSymbol + '</span>' +
                '<span class="truncate max-w-[240px]">' + message + '</span>' +
            '</div>' +
            '<button type="button" class="text-slate-400 hover:text-white ml-2 flex-shrink-0 text-base leading-none" onclick="this.parentElement.remove()">&times;</button>';

        elements.toastContainer.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.remove('translate-y-2', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        state.activeToastTimer = setTimeout(function () {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(function () {
                toast.remove();
            }, 250);
        }, 3200);
    }

    // Security Features: Disable Right Click & Developer Tools Hotkeys
    function initSecurityShield() {
        // Disable Right Click Menu
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            showToast('Aksi klik kanan dinonaktifkan', 'info');
            return false;
        });

        // Disable DevTools / View Source Keys
        document.addEventListener('keydown', function (e) {
            // F12
            if (e.keyCode === 123) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+I / J / C
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
                e.preventDefault();
                return false;
            }
            // Ctrl+U (View Source) or Ctrl+S (Save Page)
            if (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Bind Event Listeners
    function bindEvents() {
        if (elements.themeToggleBtn) {
            elements.themeToggleBtn.addEventListener('click', toggleTheme);
        }

        if (elements.modalCloseBtn) elements.modalCloseBtn.addEventListener('click', closeModal);
        if (elements.modalBackdrop) elements.modalBackdrop.addEventListener('click', closeModal);
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });
    }

    // Initialization
    document.addEventListener('DOMContentLoaded', function () {
        initSecurityShield();
        initTheme();
        bindEvents();
    });

})();

# Formulir Untuk User - Secure Document Portal

Portal web modern berbasis PHP & Tailwind CSS untuk mendistribusikan berkas formulir dan template resmi perusahaan secara aman, cepat, dan 100% offline.

---

## 🌟 Fitur Utama

1. **Desain Modern & Kekinian:**
   - **Glassmorphism & Ambient Glow:** Efek visual modern dengan blur backdrop dan aura glowing gradasi.
   - **Dark & Light Mode:** Dilengkapi toggle pengubah tema terang/gelap dengan penyimpanan otomatis di browser (`localStorage`).
   - **Dual View Layout:** Pilihan tampilan *Grid Cards* modern dan *Table List* rapi.
   - **Real-time Search & Filter:** Pencarian instan (shortcut `Ctrl + K`), tombol filter format (`.docx`), kategori formulir, dan reset filter.
   - **Detail Modal & Checksum SHA-256:** Popup detail file, ukuran presisi, tanggal pembaruan, dan tombol salin hash SHA-256 untuk memverifikasi integritas dokumen.

2. **100% Offline Asset:**
   - Semua aset CSS dan JS Tailwind disimpan secara lokal di folder `assets/`.
   - Tidak memerlukan koneksi internet sama sekali untuk rendering antarmuka pengguna maupun pengunduhan dokumen.

3. **Keamanan Berlapis (Security Hardened):**
   - **Obfuscated JavaScript Engine (`assets/js/app.min.obf.js`):** Script frontend dilindungi dengan teknik obfuscation (Base64 encoding, lookup table, cache decoding).
   - **Secure Token-Based Download Gateway (`download.php`):** Parameter unduhan dienkripsi menggunakan HMAC SHA-256 dengan batas waktu kedaluwarsa.
   - **Anti Path Traversal:** Proteksi ketat terhadap manipulasi direktori (`../`, null bytes, symlink breakout, dan ekstensi file yang dilarang).
   - **Direktori Terproteksi (`.htaccess`):** Menonaktifkan directory listing (`Options -Indexes`) dan memblokir akses langsung ke berkas sensitif atau kode internal.

---

## 📂 Struktur Direktori

```text
c:\xampp\htdocs\form\
│
├── index.php                      # Halaman utama portal
├── download.php                   # Gateway unduhan aman (token & anti-traversal)
├── config.php                     # Konfigurasi keamanan, secret key, & helper
├── .htaccess                      # Aturan keamanan Apache web server
│
├── storage/
│   └── forms/                     # Direktori penyimpanan berkas formulir resmi
│       ├── Form Pengajuan Akses (Manual).docx
│       └── Form Pengajuan Akses App (Contoh).docx
│
├── assets/
│   ├── css/
│   │   ├── tailwind.min.css       # Tailwind CSS offline
│   │   └── custom.css             # Glassmorphism, animations, & custom scrollbar
│   └── js/
│       ├── tailwind.min.js        # Engine Tailwind offline compiler
│       ├── app.js                 # Source code frontend JavaScript (unobfuscated)
│       └── app.min.obf.js         # Frontend JavaScript yang telah di-obfuscate
│
└── tools/
    └── obfuscator.php             # Script PHP untuk men-generate ulang app.min.obf.js
```

---

## 🚀 Cara Menjalankan di XAMPP

1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada modul **Apache**.
3. Buka browser (Chrome, Edge, Firefox) dan akses:
   ```text
   http://localhost/form/
   ```

---

## 🔒 Menjalankan Ulang JavaScript Obfuscator

Jika Anda melakukan perubahan logika pada file `assets/js/app.js`, Anda dapat meng-obfuscate ulang file tersebut dengan mudah:

```powershell
cd c:\xampp\htdocs\form
php tools\obfuscator.php
```

Output otomatis tersimpan dan terbarui di `assets/js/app.min.obf.js`.


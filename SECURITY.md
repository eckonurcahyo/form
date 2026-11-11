# Standard Security & Obfuscation Architecture (SSA-Framework)

Dokumen ini memuat arsitektur dan standar keamanan yang diterapkan pada proyek **"Formulir Untuk User"**. Konsep ini dirancang dengan prinsip **Defense-in-Depth (Keamanan Berlapis)** agar dapat direplikasi secara konsisten sebagai standar keamanan (*security boilerplate*) pada proyek-proyek web PHP berbasis intranet maupun publik selanjutnya.

---

## 🏛️ Arsitektur Keamanan Berlapis (Defense-in-Depth)

Sistem ini membagi proteksi ke dalam **4 Lapisan Keamanan (4 Layers of Protection)**:

```
┌────────────────────────────────────────────────────────┐
│  Layer 1: Server Hardening & Transport Headers        │  (.htaccess & HTTP Headers)
├────────────────────────────────────────────────────────┤
│  Layer 2: Cryptographic Token Gateway (Anti-Traversal) │  (config.php & download.php)
├────────────────────────────────────────────────────────┤
│  Layer 3: Dynamic HTML Output Buffer Obfuscation       │  (ob_start & Source Armor)
├────────────────────────────────────────────────────────┤
│  Layer 4: Client-Side Logic Obfuscator & Shield        │  (app.min.obf.js & Event Guard)
└────────────────────────────────────────────────────────┘
```

---

## 🛡️ Rincian 4 Lapisan Keamanan

### 1. Layer 1: Server Hardening & HTTP Security Headers
Tujuan: Mencegah serangan dasar seperti MIME-confusion, Clickjacking, dan Directory Snooping sebelum request diproses aplikasi.

- **Direktori Listing Dinonaktifkan:** Menggunakan `Options -Indexes` di `.htaccess` sehingga pengguna tidak dapat melihat isi folder web jika tidak ada file index.
- **Proteksi Berkas Sensitif:** Memblokir akses langsung browser ke file konfigurasi, environment, atau git repository:
  ```apache
  <FilesMatch "^\.(git|env|htaccess|htpasswd)|config\.php$|obfuscator\.php$">
      Require all denied
  </FilesMatch>
  ```
- **HTTP Security Headers:**
  - `X-Content-Type-Options: nosniff` (Mencegah sniffing MIME type).
  - `X-Frame-Options: SAMEORIGIN` (Mencegah embedding website di iframe situs asing / anti-Clickjacking).
  - `X-XSS-Protection: 1; mode=block` (Proteksi filter XSS bawaan browser).
  - `Referrer-Policy: strict-origin-when-cross-origin`.

---

### 2. Layer 2: Cryptographic Token Gateway (Zero-Path Disclosure & Anti-Traversal)
Tujuan: Menyembunyikan struktur folder fisik server dan mencegah serangan **Path Traversal / LFI** (`../../etc/passwd` atau arbitrary file download).

- **HMAC SHA-256 Token Generation:**
  Tautan unduhan tidak pernah mengekspos nama berkas atau path fisik secara mentah (`?file=form.docx`), melainkan ditukar dengan token ber-tanda tangan digital:
  $$\text{Token} = \text{Base64}\Big(\text{JSON}\big(\text{file}, \text{timestamp}, \text{HMAC-SHA256}(\text{file} \parallel \text{timestamp}, \text{SecretKey})\big)\Big)$$
- **Boundary Verification via `realpath()`:**
  Gateway memverifikasi apakah berkas target secara mutlak berada di dalam folder whitelist yang diizinkan menggunakan fungsi canonical PHP:
  ```php
  $realTargetPath = realpath($targetPath);
  $realStoragePath = realpath(STORAGE_PATH);
  if (!$realTargetPath || !str_starts_with($realTargetPath, $realStoragePath)) {
      http_response_code(403);
      die("Akses di luar direktori aman ditolak.");
  }
  ```
- **Whitelist Extension Enforcement:** Hanya berkas berekstensi yang diizinkan (`.docx`, `.pdf`, `.xlsx`, dll.) yang dapat dialirkan. Ekstensi skrip seperti `.php`, `.phtml`, `.exe` otomatis ditolak.
- **Token Expiry (Anti-Replay Attack):** Token memiliki masa kedaluwarsa maksimal 2 jam.

---

### 3. Layer 3: Dynamic HTML Output Buffer Obfuscation (Source Armor)
Tujuan: Mengenkripsi seluruh markup HTML halaman sehingga pihak luar tidak dapat melihat kode sumber (*View Page Source*) secara telanjang.

- **Mekanisme Output Buffering:**
  Pada awal file `index.php`, pasang fungsi callback ob_start:
  ```php
  ob_start('obfuscateHTMLSource');
  ```
- **Proses Enkripsi Output:**
  1. HTML dikompresi (*whitespace minification*).
  2. Hasilnya di-encode ke Base64 dan dipecah menjadi array potongan-potongan (*chunked array*).
  3. Server mengirimkan dokumen pembungkus (*loader wrapper*) kecil yang mengekstrak dan menulis ulang konten langsung di memori DOM peramban melalui `document.write(decodeURIComponent(escape(atob(...))))`.
- **Hasil bagi Pengguna/Penyerang:**
  Ketika pengguna menekan `Ctrl + U` (View Source) atau mengambil halaman via `curl`, yang terlihat hanyalah loader terenkripsi, bukan kode HTML formulir asli.

---

### 4. Layer 4: Client-Side Logic Obfuscation & Interaction Shield
Tujuan: Melindungi logika JavaScript dari pembacaan langsung dan memblokir akses ke fungsi inspeksi peramban.

- **String Literal Scrambling:**
  Menggunakan engine PHP (`tools/obfuscator.php`) untuk mengekstrak string, nama event, dan teks ke dalam array Base64 terproteksi dengan memoized decoder function `_0x...()`.
- **Anti-Context Menu (Disable Right Click):**
  ```javascript
  document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      return false;
  });
  ```
- **Developer Tools & Shortcut Hotkey Blocker:**
  Mencegah eksekusi shortcut inspeksi:
  - `F12` (DevTools shortcut)
  - `Ctrl + Shift + I` / `Ctrl + Shift + J` / `Ctrl + Shift + C` (Inspect element & Console)
  - `Ctrl + U` (View Page Source)
  - `Ctrl + S` (Save Entire Page)

---

## 📋 Checklist Implementasi untuk Next Project

Gunakan berkas-berkas berikut sebagai cetak biru (*blueprint*) pada proyek baru:

| No | Berkas Inti | Peran & Konfigurasi |
|:---|:---|:---|
| 1 | **`.htaccess`** | Letakkan di root direktori untuk mengaktifkan security headers dan mematikan directory browsing. |
| 2 | **`config.php`** | Salin fungsi `generateFileToken()`, `validateFileToken()`, dan `obfuscateHTMLSource()`. Ganti `APP_SECRET_KEY` sesuai nama proyek. |
| 3 | **`download.php`** | Gunakan sebagai gerbang satu pintu untuk semua pengunduhan berkas aman. |
| 4 | **`tools/obfuscator.php`** | Jalankan via CLI (`php tools/obfuscator.php`) setiap kali ada pembaruan script frontend. |
| 5 | **`index.php`** | Pastikan baris pertama memanggil `ob_start('obfuscateHTMLSource');` dan baris terakhir memanggil `ob_end_flush();`. |

---

## ⚖️ Kesimpulan Standar Industri

Sistem ini **sangat layak dijadikan standar internal** untuk:
1. Portal dokumen internal / intranet kantor.
2. Web aplikasi korporat yang membatasi pengunduhan berkas sembarangan.
3. Aplikasi web yang membutuhkan perlindungan terhadap pencurian aset antarmuka (*UI asset scraping*) dan manipulasi URL direktori.


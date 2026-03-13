# Custom Plugin

Plugin WordPress kustom modern yang menyediakan fitur-fitur inti, manajemen data melalui REST API, dan sistem template yang terorganisir.

## 🚀 Fitur Utama

- 📊 **Form Kontak & Submissions**: Form frontend menggunakan **Alpine.js** yang terintegrasi dengan **WordPress REST API**. Data disimpan ke tabel database kustom.
- ⚙️ **Dashboard Info Sistem**: Menampilkan informasi detail tentang server, PHP, WordPress, dan database.
- �️ **Core Features**:
  - Registrasi Custom Post Types & Taxonomies.
  - Dukungan upload file SVG.
  - Pengaturan ukuran gambar (Image Sizes).
- 🌐 **Internasionalisasi (i18n)**: Siap untuk diterjemahkan melalui folder `/languages/`.
- 🧩 **Struktur Modular**: Menggunakan arsitektur modern untuk kemudahan pengembangan.

## 🛠️ Arsitektur & Teknologi

- **Autoloading**: Mendukung standar **PSR-4** melalui Composer.
- **Template Engine**: Pemisahan logika (PHP) dan tampilan (HTML) menggunakan class `Template` kustom.
- **Repository Pattern**: Abstraksi database menggunakan `SubmissionModel` untuk manajemen data yang terpusat.
- **Frontend Reactive**: Menggunakan **Alpine.js v3** untuk interaktivitas tanpa jQuery.
- **Conflict Prevention**: Script Alpine.js hanya dimuat jika belum ada di sistem untuk menghindari konflik dengan tema/plugin lain.

## 📦 Instalasi & Pengembangan

### Prasyarat

- PHP 7.4+
- Node.js & npm (untuk build)
- Composer (opsional, untuk autoloading)

### Langkah Instalasi

1. Clone atau download folder plugin ini ke `/wp-content/plugins/`.
2. Jalankan `npm install` untuk menginstal dependensi build.
3. Aktifkan plugin melalui dashboard WordPress.

### Cara Build (Zip)

Untuk menghasilkan file plugin yang siap didistribusikan:

```bash
npm run build
```

File zip akan tersedia di folder `/dist/custom-plugin-1.0.0.zip`.

## 📝 Penggunaan Shortcodes

- `[custom_form]`: Menampilkan formulir kontak reaktif (Alpine.js).
- `[custom_data limit="5"]`: Menampilkan tabel data kiriman terbaru.
- `[custom_message text="Halo!" style="default"]`: Menampilkan pesan kustom dengan styling.

## 📁 Struktur Folder

```
custom-plugin/
├── custom-plugin.php          # Entry point utama
├── src/                       # Source code (PSR-4 Autoloaded)
│   ├── Admin/                 # Logika dashboard admin
│   ├── Api/                   # REST API Controllers
│   ├── Core/                  # Fitur Inti (Template, Model, CPT)
│   ├── Frontend/              # Logika Frontend & Shortcodes
├── templates/                 # File View (HTML/PHP)
│   ├── admin/                 # Template dashboard admin
│   └── frontend/              # Template untuk shortcode & form
├── assets/                    # Aset statis (CSS, JS)
├── dist/                      # Hasil build (Zip)
├── languages/                 # File terjemahan (.mo/.po)
├── composer.json              # Konfigurasi Composer
└── package.json               # Konfigurasi Build npm
```

## 📜 Lisensi

GPL v2 or later.

# Custom Plugin Starter Kit

Plugin WordPress kustom minimalis yang dirancang sebagai fondasi untuk pengembangan plugin modern.

## 🚀 Fitur Inti

- 🛠️ **Core Features**:
  - Kerangka kerja untuk Custom Post Types & Taxonomies.
  - Dukungan upload file SVG bawaan.
  - Manajemen ukuran gambar kustom.
- 🌐 **Internasionalisasi (i18n)**: Siap untuk diterjemahkan melalui folder `/languages/`.
- 🧩 **Struktur Modular**: Menggunakan arsitektur modern (PSR-4) untuk pengelolaan kode yang bersih.

## 🛠️ Teknologi & Arsitektur

- **Autoloading**: Mendukung standar **PSR-4** melalui Composer.
- **Template Engine**: Class `Template` kustom untuk pemisahan logika (PHP) dan tampilan (HTML).
- **Modern Build**: Mendukung otomatisasi pembuatan file distribusi (Zip) melalui npm.

## 📦 Instalasi & Pengembangan

### Prasyarat
- PHP 7.4+
- Node.js & npm (untuk build)
- Composer (untuk autoloading PSR-4)

### Langkah Instalasi
1. Clone atau download folder plugin ini ke `/wp-content/plugins/`.
2. Jalankan `npm install` untuk menginstal dependensi build.
3. Jalankan `composer install` (atau `composer dump-autoload`) untuk memuat class.
4. Aktifkan plugin melalui dashboard WordPress.

### Cara Build (Zip)
Untuk menghasilkan file plugin yang siap didistribusikan:
```bash
npm run build
```
File zip akan tersedia di folder `/dist/custom-plugin-1.0.0.zip`.

## 📁 Struktur Folder

```
custom-plugin/
├── custom-plugin.php          # Entry point utama
├── src/                       # Source code (PSR-4 Autoloaded)
│   ├── Core/                  # Fitur Inti (Template, CPT, Taxonomies)
├── templates/                 # File View (HTML/PHP)
├── assets/                    # Aset statis (CSS, JS)
├── dist/                      # Hasil build (Zip)
├── languages/                 # File terjemahan (.mo/.po)
├── composer.json              # Konfigurasi Composer
└── package.json               # Konfigurasi Build npm
```

## 📜 Lisensi
GPL v2 or later.

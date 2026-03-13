# Custom Plugin Starter Kit

Plugin WordPress kustom minimalis yang dirancang sebagai fondasi untuk pengembangan plugin modern dengan standar _best practice_ industri.

## 🚀 Fitur Inti

- 🛠️ **Core Features**:
  - Kerangka kerja untuk Custom Post Types & Taxonomies.
  - Dukungan upload file SVG bawaan.
  - Manajemen ukuran gambar kustom.
- 🌐 **Internasionalisasi (i18n)**: Siap untuk diterjemahkan melalui folder `/languages/`.
- 🧩 **Struktur Modular**: Menggunakan arsitektur modern (PSR-4) untuk pengelolaan kode yang bersih.

## 🛠️ Teknologi & Arsitektur (Best Practice)

Plugin ini menyediakan contoh implementasi yang dapat diaktifkan di `src/Core/Plugin.php`:

- **REST API Controller**: Contoh implementasi API yang aman dengan Schema, Permission Check, dan Validasi Argumen di `src/Api/ExampleController.php`.
- **Template Engine & Override**: Sistem render tampilan di `src/Core/Template.php` yang mendukung _Theme Override_ (kompatibilitas tema seperti WooCommerce).
- **Advanced Hooks**: Contoh penggunaan Action & Filter Hooks yang tepat di `src/Frontend/Frontend.php`.
- **Shortcode System**: Pemisahan logika dan tampilan pada shortcode di `src/Frontend/Shortcode.php`.
- **Autoloading**: Standar **PSR-4** melalui Composer.

## 📦 Instalasi & Pengembangan

### Prasyarat

- PHP 7.4+
- Node.js & npm (untuk build)
- Composer (untuk autoloading PSR-4)

### Langkah Instalasi

1. Clone atau download folder plugin ini ke `/wp-content/plugins/`.
2. Jalankan `npm install` untuk menginstal dependensi build.
3. Jalankan `composer install` untuk memuat class.
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
│   ├── Admin/                 # Contoh menu & assets admin
│   ├── Api/                   # Contoh REST API Controllers
│   ├── Core/                  # Fitur Inti (Template, CPT, Taxonomies)
│   ├── Frontend/              # Contoh Hooks & Shortcodes
├── templates/                 # File View (HTML/PHP)
├── assets/                    # Aset statis (CSS, JS)
├── dist/                      # Hasil build (Zip)
├── languages/                 # File terjemahan (.mo/.po)
├── composer.json              # Konfigurasi Composer
└── package.json               # Konfigurasi Build npm
```

## 📜 Lisensi

GPL v2 or later.

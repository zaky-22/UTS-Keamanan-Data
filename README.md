# UTS Kriptografi dan Keamanan Sistem Informasi

Aplikasi web sistem informasi admin sederhana yang mengimplementasikan **Kriptografi Hibrida** (Enkripsi Simetris AES-256-CBC dan Asimetris RSA-2048 Digital Signature) berbasis Native PHP, Tailwind CSS, dan MySQL (PDO).

## Struktur Direktori
- `config/database.php` : Konfigurasi koneksi database via PDO.
- `includes/crypto.php` : Kelas OpenSSL untuk enkripsi AES dan tanda tangan digital RSA.
- `index.php` : Antarmuka dashboard admin dan form interaktif.

## Cara Instalasi
1. Clone repository ini ke folder server lokal Anda (`htdocs` XAMPP atau `www` Laragon).
2. Buat database baru di phpMyAdmin dengan nama `db_kriptografi_uts`.
3. Jalankan query SQL berikut untuk membuat tabel:
   ```sql
   CREATE TABLE IF NOT EXISTS admin_users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL,
       secret_note TEXT NOT NULL,
       digital_signature TEXT NOT NULL
   );

   INSERT INTO admin_users (username, secret_note, digital_signature) 
   VALUES ('admin_utama', 'Confidential: Anggaran Proyek Kriptografi Tahun 2026 Rp 500.000.000', 'pending_sign');
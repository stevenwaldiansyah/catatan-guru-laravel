# EduJurnal

Sistem administrasi kegiatan belajar mengajar untuk jurnal mengajar, absensi, catatan perkembangan siswa, dan jadwal.

Aplikasi memakai Laravel 13, PHP 8.3+, MySQL/MariaDB, Blade, Tailwind CSS 4, Alpine.js, Lucide, Chart.js, dan DomPDF. Tampilan mengikuti design system TouchFlow yang disesuaikan untuk web.

## Instalasi lokal

1. Pasang PHP 8.3 atau lebih baru, Composer, Node.js, dan MySQL/MariaDB.
2. Salin lingkungan:

```bash
cp .env.example .env
php artisan key:generate
```

3. Isi koneksi database di `.env`. Jangan menaruh kata sandi produksi di repositori. Contoh XAMPP lokal memakai pengguna `root` dengan kata sandi kosong dan database `edujurnal_app`.

4. Buat database:

```sql
CREATE DATABASE edujurnal_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. Jalankan migrasi dan data demo:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Perintah `php artisan migrate:fresh --seed` menghapus seluruh tabel di database aplikasi lalu mengisinya kembali ke kondisi demo semula. Jangan menjalankannya pada data yang ingin dipertahankan.

## Akun demo

Seeder menolak berjalan saat `APP_ENV=production`. Akun berikut hanya untuk percobaan lokal:

| Peran | Nama pengguna | Email | Kata sandi |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@edujurnal.local` | `Admin#2026` |
| Guru | `siti` | `siti@edujurnal.local` | `Guru#2026` |
| Guru | `budi` | `budi@edujurnal.local` | `Guru#2026` |

## Uji otomatis

```bash
php artisan test
```

Pengujian memakai SQLite di memori dan tidak menyentuh database MySQL.

## Yang sudah berjalan

- Masuk, keluar, profil, ganti kata sandi, dan penolakan akun nonaktif.
- Peran admin dan guru ditegakkan di middleware serta policy.
- Master data guru, kelas, siswa, mata pelajaran, tahun ajaran, semester, penugasan, dan jadwal.
- Jurnal mengajar dengan status draf dan selesai.
- Absensi per sesi, pengisian massal, rekap, dan penolakan pencatatan ganda.
- Catatan perkembangan siswa yang hanya terlihat oleh penulis dan admin.
- Laporan jurnal, absensi, dan catatan dalam CSV serta PDF.
- Dasbor admin dan guru membaca data database.

## Batasan

- Database lama bernama `edujurnal` tidak diubah. Aplikasi ini memakai `edujurnal_app`.
- Modul buku nilai tidak tersedia.
- Pendaftaran umum tidak dibuka. Akun guru dibuat oleh admin.
- Tidak ada unggah berkas atau logo sekolah.
- Riwayat perubahan catatan dan jurnal hanya menyimpan waktu buat dan waktu ubah, bukan jejak tiap kolom.
- Gestur geser tidak dipakai. Tindakan penting tersedia sebagai tombol dan konfirmasi.
- Hari jadwal adalah Senin sampai Sabtu. Bentrok guru dan kelas dicegah secara bawaan. Bentrok ruang dimatikan sampai diaktifkan di Pengaturan.
- Satu siswa hanya memiliki satu kelas aktif pada satu tahun ajaran.
- Guru tidak dapat menghapus jurnal yang sudah selesai.
- Admin tidak membuat catatan siswa atas nama sendiri. Catatan dicatat dari akun guru.

## Produksi

Set `APP_ENV=production`, `APP_DEBUG=false`, dan kata sandi database yang kuat. Jangan menjalankan seeder demo di produksi.

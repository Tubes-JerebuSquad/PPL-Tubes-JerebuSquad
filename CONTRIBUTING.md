# Panduan Kontribusi RimbaWatch

Panduan singkat untuk anggota tim Jerebu Squad.

## 1. Menjalankan proyek

Prasyarat: Git dan Docker Desktop (Engine harus berstatus *running*).

```bash
git clone https://github.com/Tubes-JerebuSquad/PPL-Tubes-JerebuSquad.git
cd PPL-Tubes-JerebuSquad
docker compose up --build
```

- Aplikasi: http://localhost:8000
- PostgreSQL/PostGIS dari host: `localhost:5433` (DB `rimbawatch`, user `rimbawatch`)
- `.env` dibuat otomatis dari `.env.example` saat pertama kali jalan.
- Migration berjalan otomatis saat container start.

Menghentikan: `Ctrl+C`, atau `docker compose down`.

## 2. Alur kerja Git

1. Ambil `main` terbaru:
   ```bash
   git checkout main
   git pull
   ```
2. Buat branch dari tiket Jira:
   ```bash
   git checkout -b feature/TJ123-<nomor>-<nama-singkat>
   ```
   Contoh: `feature/TJ123-33-pengelolaan-pengguna`.
   Untuk pekerjaan non-fitur pakai awalan `chore/` atau `fix/`.
3. Commit kecil dan sering. Format pesan:
   ```
   feat(TJ123-33): tambah halaman daftar pengguna
   fix(TJ123-33): perbaiki validasi email
   chore: ...
   ```
   Tipe yang dipakai: `feat`, `fix`, `chore`, `docs`, `test`, `refactor`.
4. Push, lalu buka Pull Request ke `main`:
   ```bash
   git push -u origin feature/TJ123-<nomor>-<nama-singkat>
   ```

## 3. Aturan Pull Request

- Judul memuat kode tiket, contoh: `feat(TJ123-33): pengelolaan pengguna`.
- Deskripsi singkat: apa yang berubah dan cara mengujinya.
- Wajib **1 approval** dari anggota lain. Tidak boleh approve PR sendiri.
- Jangan push langsung ke `main`.
- Satu PR untuk satu tiket. Jangan campur banyak pekerjaan.
- Setelah merge, hapus branch-nya.

## 4. Database dan migration

- Skema dasar (23 tabel) sudah ada di `database/migrations`.
- Perubahan skema: **buat file migration baru**, jangan ubah migration lama yang sudah ada di `main`.
- `php artisan migrate:fresh` menghapus seluruh data. Jalankan hanya di database lokal sendiri.
- Menjalankan perintah artisan di Docker:
  ```bash
  docker compose exec app php artisan <perintah>
  ```

## 5. Keamanan

- Jangan commit `.env`, password, token, atau kredensial apa pun. Gunakan `.env.example` untuk contoh nilai non-rahasia.
- Jangan tulis alamat dan password server database eksternal di repo.

## 6. Laporan harian

Setiap hari isi di Jira dengan format:

```
Yang dikerjakan kemarin | Yang akan dikerjakan hari ini | Blocker kemarin
```

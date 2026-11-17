<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan tabel berkas_media dengan ERD (skema_sqlserver.sql). Migration lama tidak diubah.
 * path_file menjadi kunci_penyimpanan; kolom nama_file dihapus (sudah termuat di kunci_penyimpanan).
 * Gagal (dibatalkan otomatis) bila ada berkas dengan diunggah_oleh kosong.
 * SQLite (khusus test, database kosong) hanya rename/tambah/hapus kolom; rollback hanya didukung di PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
ALTER TABLE berkas_media RENAME COLUMN id TO berkas_media_id;
ALTER TABLE berkas_media RENAME COLUMN nama_asli TO nama_file_asli;
ALTER TABLE berkas_media RENAME COLUMN mime_type TO tipe_mime;
ALTER TABLE berkas_media RENAME COLUMN ukuran_byte TO ukuran_file_byte;
ALTER TABLE berkas_media RENAME COLUMN path_file TO kunci_penyimpanan;
ALTER TABLE berkas_media RENAME COLUMN created_at TO dibuat_pada;
ALTER TABLE berkas_media DROP COLUMN nama_file;
ALTER TABLE berkas_media DROP COLUMN updated_at;
ALTER TABLE berkas_media ADD COLUMN checksum varchar;
ALTER TABLE berkas_media ADD COLUMN tingkat_akses varchar NOT NULL DEFAULT 'PRIVATE';
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE berkas_media RENAME COLUMN id TO berkas_media_id;
ALTER TABLE berkas_media RENAME COLUMN nama_asli TO nama_file_asli;
ALTER TABLE berkas_media RENAME COLUMN mime_type TO tipe_mime;
ALTER TABLE berkas_media ALTER COLUMN tipe_mime TYPE varchar(100);
ALTER TABLE berkas_media RENAME COLUMN ukuran_byte TO ukuran_file_byte;

-- path_file dipakai sebagai kunci_penyimpanan; nama_file (nama berkas tersimpan) sudah termuat di dalamnya
ALTER TABLE berkas_media RENAME COLUMN path_file TO kunci_penyimpanan;
ALTER TABLE berkas_media ALTER COLUMN kunci_penyimpanan TYPE varchar(500);
ALTER TABLE berkas_media ADD CONSTRAINT berkas_media_kunci_penyimpanan_unique UNIQUE (kunci_penyimpanan);
ALTER TABLE berkas_media DROP COLUMN nama_file;

ALTER TABLE berkas_media ADD COLUMN checksum varchar(128);
ALTER TABLE berkas_media ADD COLUMN tingkat_akses varchar(30) NOT NULL DEFAULT 'PRIVATE';

ALTER TABLE berkas_media ALTER COLUMN diunggah_oleh SET NOT NULL;
ALTER TABLE berkas_media ADD CONSTRAINT berkas_media_diunggah_oleh_foreign FOREIGN KEY (diunggah_oleh) REFERENCES pengguna (pengguna_id);

ALTER TABLE berkas_media RENAME COLUMN created_at TO dibuat_pada;
UPDATE berkas_media SET dibuat_pada = CURRENT_TIMESTAMP WHERE dibuat_pada IS NULL;
ALTER TABLE berkas_media ALTER COLUMN dibuat_pada SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE berkas_media ALTER COLUMN dibuat_pada SET NOT NULL;
ALTER TABLE berkas_media DROP COLUMN updated_at;
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE berkas_media ADD COLUMN updated_at timestamp(0);
ALTER TABLE berkas_media ALTER COLUMN dibuat_pada DROP NOT NULL;
ALTER TABLE berkas_media ALTER COLUMN dibuat_pada DROP DEFAULT;
ALTER TABLE berkas_media RENAME COLUMN dibuat_pada TO created_at;

ALTER TABLE berkas_media DROP CONSTRAINT berkas_media_diunggah_oleh_foreign;
ALTER TABLE berkas_media ALTER COLUMN diunggah_oleh DROP NOT NULL;

ALTER TABLE berkas_media DROP COLUMN tingkat_akses;
ALTER TABLE berkas_media DROP COLUMN checksum;

ALTER TABLE berkas_media ADD COLUMN nama_file varchar(255);
UPDATE berkas_media SET nama_file = regexp_replace(kunci_penyimpanan, '^.*/', '');
ALTER TABLE berkas_media ALTER COLUMN nama_file SET NOT NULL;
ALTER TABLE berkas_media DROP CONSTRAINT berkas_media_kunci_penyimpanan_unique;
ALTER TABLE berkas_media ALTER COLUMN kunci_penyimpanan TYPE varchar(255);
ALTER TABLE berkas_media RENAME COLUMN kunci_penyimpanan TO path_file;

ALTER TABLE berkas_media RENAME COLUMN ukuran_file_byte TO ukuran_byte;
ALTER TABLE berkas_media ALTER COLUMN tipe_mime TYPE varchar(255);
ALTER TABLE berkas_media RENAME COLUMN tipe_mime TO mime_type;
ALTER TABLE berkas_media RENAME COLUMN nama_file_asli TO nama_asli;
ALTER TABLE berkas_media RENAME COLUMN berkas_media_id TO id;
SQL);
    }
};
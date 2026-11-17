<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan tabel peristiwa_laporan dengan ERD.
 * SQLite (khusus test, database kosong) hanya rename/tambah/hapus kolom; rollback hanya didukung di PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
ALTER TABLE peristiwa_laporan RENAME COLUMN id TO peristiwa_id;
ALTER TABLE peristiwa_laporan RENAME COLUMN deskripsi TO catatan_peristiwa;
ALTER TABLE peristiwa_laporan RENAME COLUMN waktu_peristiwa TO terjadi_pada;
ALTER TABLE peristiwa_laporan ADD COLUMN aktor_id integer;
ALTER TABLE peristiwa_laporan ADD COLUMN status_lama varchar;
ALTER TABLE peristiwa_laporan ADD COLUMN status_baru varchar;
ALTER TABLE peristiwa_laporan DROP COLUMN created_at;
ALTER TABLE peristiwa_laporan DROP COLUMN updated_at;
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE peristiwa_laporan RENAME COLUMN id TO peristiwa_id;
ALTER TABLE peristiwa_laporan ALTER COLUMN jenis_peristiwa TYPE varchar(50);
ALTER TABLE peristiwa_laporan RENAME COLUMN deskripsi TO catatan_peristiwa;
ALTER TABLE peristiwa_laporan RENAME COLUMN waktu_peristiwa TO terjadi_pada;
ALTER TABLE peristiwa_laporan ADD COLUMN aktor_id bigint;
ALTER TABLE peristiwa_laporan ADD COLUMN status_lama varchar(40);
ALTER TABLE peristiwa_laporan ADD COLUMN status_baru varchar(40);
ALTER TABLE peristiwa_laporan ADD CONSTRAINT peristiwa_laporan_aktor_id_foreign FOREIGN KEY (aktor_id) REFERENCES pengguna (pengguna_id);
ALTER TABLE peristiwa_laporan DROP COLUMN created_at;
ALTER TABLE peristiwa_laporan DROP COLUMN updated_at;
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE peristiwa_laporan ADD COLUMN created_at timestamp(0);
ALTER TABLE peristiwa_laporan ADD COLUMN updated_at timestamp(0);
ALTER TABLE peristiwa_laporan DROP CONSTRAINT peristiwa_laporan_aktor_id_foreign;
ALTER TABLE peristiwa_laporan DROP COLUMN status_baru;
ALTER TABLE peristiwa_laporan DROP COLUMN status_lama;
ALTER TABLE peristiwa_laporan DROP COLUMN aktor_id;
ALTER TABLE peristiwa_laporan RENAME COLUMN terjadi_pada TO waktu_peristiwa;
ALTER TABLE peristiwa_laporan RENAME COLUMN catatan_peristiwa TO deskripsi;
ALTER TABLE peristiwa_laporan ALTER COLUMN jenis_peristiwa TYPE varchar(255);
ALTER TABLE peristiwa_laporan RENAME COLUMN peristiwa_id TO id;
SQL);
    }
};
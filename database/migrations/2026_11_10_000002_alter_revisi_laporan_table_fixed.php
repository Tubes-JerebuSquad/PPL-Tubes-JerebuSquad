<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan tabel revisi_laporan dengan ERD. Kolom diedit_oleh untuk data lama diisi dari pelapor laporan,
 * jadi migration alter_laporan_table_fixed harus sudah jalan lebih dulu.
 * SQLite (khusus test, database kosong) hanya rename/tambah/hapus kolom; rollback hanya didukung di PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
ALTER TABLE revisi_laporan RENAME COLUMN id TO revisi_id;
ALTER TABLE revisi_laporan RENAME COLUMN waktu_kejadian TO insiden_pada;
ALTER TABLE revisi_laporan RENAME COLUMN catatan_revisi TO alasan_revisi;
ALTER TABLE revisi_laporan RENAME COLUMN created_at TO dibuat_pada;
ALTER TABLE revisi_laporan ADD COLUMN diedit_oleh integer;
ALTER TABLE revisi_laporan ADD COLUMN lokasi geometry;
ALTER TABLE revisi_laporan DROP COLUMN latitude;
ALTER TABLE revisi_laporan DROP COLUMN longitude;
ALTER TABLE revisi_laporan DROP COLUMN updated_at;
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE revisi_laporan RENAME COLUMN id TO revisi_id;
ALTER TABLE revisi_laporan ALTER COLUMN nomor_revisi DROP DEFAULT;
ALTER TABLE revisi_laporan ALTER COLUMN kategori TYPE varchar(50);
ALTER TABLE revisi_laporan RENAME COLUMN waktu_kejadian TO insiden_pada;
ALTER TABLE revisi_laporan RENAME COLUMN catatan_revisi TO alasan_revisi;

ALTER TABLE revisi_laporan ADD COLUMN diedit_oleh bigint;
UPDATE revisi_laporan r SET diedit_oleh = l.pelapor_id FROM laporan l WHERE l.laporan_id = r.laporan_id;
ALTER TABLE revisi_laporan ALTER COLUMN diedit_oleh SET NOT NULL;
ALTER TABLE revisi_laporan ADD CONSTRAINT revisi_laporan_diedit_oleh_foreign FOREIGN KEY (diedit_oleh) REFERENCES pengguna (pengguna_id);

ALTER TABLE revisi_laporan ADD COLUMN lokasi geometry(Geometry, 4326);
UPDATE revisi_laporan SET lokasi = ST_SetSRID(ST_MakePoint(longitude::float8, latitude::float8), 4326);
ALTER TABLE revisi_laporan ALTER COLUMN lokasi SET NOT NULL;
ALTER TABLE revisi_laporan DROP COLUMN latitude;
ALTER TABLE revisi_laporan DROP COLUMN longitude;

ALTER TABLE revisi_laporan RENAME COLUMN created_at TO dibuat_pada;
UPDATE revisi_laporan SET dibuat_pada = CURRENT_TIMESTAMP WHERE dibuat_pada IS NULL;
ALTER TABLE revisi_laporan ALTER COLUMN dibuat_pada SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE revisi_laporan ALTER COLUMN dibuat_pada SET NOT NULL;
ALTER TABLE revisi_laporan DROP COLUMN updated_at;
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE revisi_laporan ADD COLUMN updated_at timestamp(0);
ALTER TABLE revisi_laporan ALTER COLUMN dibuat_pada DROP NOT NULL;
ALTER TABLE revisi_laporan ALTER COLUMN dibuat_pada DROP DEFAULT;
ALTER TABLE revisi_laporan RENAME COLUMN dibuat_pada TO created_at;
ALTER TABLE revisi_laporan ADD COLUMN latitude numeric(10,7);
ALTER TABLE revisi_laporan ADD COLUMN longitude numeric(10,7);
UPDATE revisi_laporan SET latitude = ST_Y(lokasi)::numeric(10,7), longitude = ST_X(lokasi)::numeric(10,7);
ALTER TABLE revisi_laporan ALTER COLUMN latitude SET NOT NULL;
ALTER TABLE revisi_laporan ALTER COLUMN longitude SET NOT NULL;
ALTER TABLE revisi_laporan DROP COLUMN lokasi;
ALTER TABLE revisi_laporan DROP CONSTRAINT revisi_laporan_diedit_oleh_foreign;
ALTER TABLE revisi_laporan DROP COLUMN diedit_oleh;
ALTER TABLE revisi_laporan RENAME COLUMN alasan_revisi TO catatan_revisi;
ALTER TABLE revisi_laporan RENAME COLUMN insiden_pada TO waktu_kejadian;
ALTER TABLE revisi_laporan ALTER COLUMN kategori TYPE varchar(255);
ALTER TABLE revisi_laporan ALTER COLUMN nomor_revisi SET DEFAULT 1;
ALTER TABLE revisi_laporan RENAME COLUMN revisi_id TO id;
SQL);
    }
};
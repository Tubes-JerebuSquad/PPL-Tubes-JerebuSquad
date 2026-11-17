<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan tabel laporan dengan ERD (skema_sqlserver.sql). Migration lama tidak diubah.
 * Gagal (dibatalkan otomatis) bila ada laporan dengan pelapor_id kosong atau id
 * wilayah/kawasan/pelapor yang tidak ada di tabel induknya. Harus jalan SEBELUM migration revisi_laporan.
 * SQLite (khusus test, database kosong) hanya rename/tambah/hapus kolom; rollback hanya didukung di PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
ALTER TABLE laporan RENAME COLUMN id TO laporan_id;
ALTER TABLE laporan RENAME COLUMN nomor_laporan TO kode_laporan;
ALTER TABLE laporan RENAME COLUMN waktu_kejadian TO insiden_pada;
ALTER TABLE laporan RENAME COLUMN status TO status_laporan;
ALTER TABLE laporan RENAME COLUMN created_at TO dibuat_pada;
ALTER TABLE laporan RENAME COLUMN updated_at TO diperbarui_pada;
ALTER TABLE laporan ADD COLUMN lokasi geometry;
ALTER TABLE laporan ADD COLUMN diserahkan_pada datetime;
ALTER TABLE laporan DROP COLUMN latitude;
ALTER TABLE laporan DROP COLUMN longitude;
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE laporan RENAME COLUMN id TO laporan_id;
ALTER TABLE laporan RENAME COLUMN nomor_laporan TO kode_laporan;
ALTER TABLE laporan ALTER COLUMN kode_laporan TYPE varchar(50);
ALTER TABLE laporan ALTER COLUMN kategori TYPE varchar(50);
ALTER TABLE laporan RENAME COLUMN waktu_kejadian TO insiden_pada;
ALTER TABLE laporan RENAME COLUMN status TO status_laporan;
ALTER TABLE laporan ALTER COLUMN status_laporan TYPE varchar(40);
UPDATE laporan SET status_laporan = UPPER(status_laporan);
ALTER TABLE laporan ALTER COLUMN status_laporan SET DEFAULT 'MENUNGGU_VERIFIKASI';

ALTER TABLE laporan ADD COLUMN lokasi geometry(Geometry, 4326);
UPDATE laporan SET lokasi = ST_SetSRID(ST_MakePoint(longitude::float8, latitude::float8), 4326);
ALTER TABLE laporan ALTER COLUMN lokasi SET NOT NULL;
ALTER TABLE laporan DROP COLUMN latitude;
ALTER TABLE laporan DROP COLUMN longitude;
CREATE INDEX laporan_lokasi_spatialindex ON laporan USING GIST (lokasi);

ALTER TABLE laporan RENAME COLUMN created_at TO dibuat_pada;
ALTER TABLE laporan RENAME COLUMN updated_at TO diperbarui_pada;
UPDATE laporan SET dibuat_pada = CURRENT_TIMESTAMP WHERE dibuat_pada IS NULL;
ALTER TABLE laporan ALTER COLUMN dibuat_pada SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE laporan ALTER COLUMN dibuat_pada SET NOT NULL;
ALTER TABLE laporan ADD COLUMN diserahkan_pada timestamp(0);

ALTER INDEX IF EXISTS laporan_nomor_laporan_unique RENAME TO laporan_kode_laporan_unique;
ALTER INDEX IF EXISTS laporan_status_index RENAME TO laporan_status_laporan_index;

ALTER TABLE laporan ALTER COLUMN pelapor_id SET NOT NULL;
ALTER TABLE laporan ADD CONSTRAINT laporan_pelapor_id_foreign FOREIGN KEY (pelapor_id) REFERENCES pengguna (pengguna_id);
ALTER TABLE laporan ADD CONSTRAINT laporan_wilayah_id_foreign FOREIGN KEY (wilayah_id) REFERENCES wilayah (wilayah_id);
ALTER TABLE laporan ADD CONSTRAINT laporan_kawasan_id_foreign FOREIGN KEY (kawasan_id) REFERENCES kawasan (kawasan_id);
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE laporan DROP CONSTRAINT laporan_kawasan_id_foreign;
ALTER TABLE laporan DROP CONSTRAINT laporan_wilayah_id_foreign;
ALTER TABLE laporan DROP CONSTRAINT laporan_pelapor_id_foreign;
ALTER TABLE laporan ALTER COLUMN pelapor_id DROP NOT NULL;
ALTER INDEX IF EXISTS laporan_status_laporan_index RENAME TO laporan_status_index;
ALTER INDEX IF EXISTS laporan_kode_laporan_unique RENAME TO laporan_nomor_laporan_unique;
ALTER TABLE laporan DROP COLUMN diserahkan_pada;
ALTER TABLE laporan ALTER COLUMN dibuat_pada DROP NOT NULL;
ALTER TABLE laporan ALTER COLUMN dibuat_pada DROP DEFAULT;
ALTER TABLE laporan RENAME COLUMN diperbarui_pada TO updated_at;
ALTER TABLE laporan RENAME COLUMN dibuat_pada TO created_at;
DROP INDEX IF EXISTS laporan_lokasi_spatialindex;
ALTER TABLE laporan ADD COLUMN latitude numeric(10,7);
ALTER TABLE laporan ADD COLUMN longitude numeric(10,7);
UPDATE laporan SET latitude = ST_Y(lokasi)::numeric(10,7), longitude = ST_X(lokasi)::numeric(10,7);
ALTER TABLE laporan ALTER COLUMN latitude SET NOT NULL;
ALTER TABLE laporan ALTER COLUMN longitude SET NOT NULL;
ALTER TABLE laporan DROP COLUMN lokasi;
ALTER TABLE laporan ALTER COLUMN status_laporan DROP DEFAULT;
UPDATE laporan SET status_laporan = LOWER(status_laporan);
ALTER TABLE laporan ALTER COLUMN status_laporan TYPE varchar(255);
ALTER TABLE laporan ALTER COLUMN status_laporan SET DEFAULT 'menunggu_verifikasi';
ALTER TABLE laporan RENAME COLUMN status_laporan TO status;
ALTER TABLE laporan RENAME COLUMN insiden_pada TO waktu_kejadian;
ALTER TABLE laporan ALTER COLUMN kategori TYPE varchar(255);
ALTER TABLE laporan ALTER COLUMN kode_laporan TYPE varchar(255);
ALTER TABLE laporan RENAME COLUMN kode_laporan TO nomor_laporan;
ALTER TABLE laporan RENAME COLUMN laporan_id TO id;
SQL);
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan tabel foto_revisi_laporan dengan ERD (primary key jadi gabungan revisi_id + berkas_media_id).
 * SQLite (khusus test, database kosong) hanya rename/tambah/hapus kolom; rollback hanya didukung di PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
DROP TABLE foto_revisi_laporan;
CREATE TABLE foto_revisi_laporan (
    revisi_id integer NOT NULL REFERENCES revisi_laporan (revisi_id) ON DELETE CASCADE,
    berkas_media_id integer NOT NULL REFERENCES berkas_media (id) ON DELETE CASCADE,
    urutan_tampilan integer NOT NULL DEFAULT 1,
    PRIMARY KEY (revisi_id, berkas_media_id)
);
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE foto_revisi_laporan DROP CONSTRAINT IF EXISTS foto_revisi_laporan_revisi_laporan_id_berkas_media_id_unique;
ALTER TABLE foto_revisi_laporan DROP COLUMN id;
ALTER TABLE foto_revisi_laporan RENAME COLUMN revisi_laporan_id TO revisi_id;
ALTER TABLE foto_revisi_laporan ADD PRIMARY KEY (revisi_id, berkas_media_id);
ALTER TABLE foto_revisi_laporan DROP COLUMN created_at;
ALTER TABLE foto_revisi_laporan DROP COLUMN updated_at;
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
ALTER TABLE foto_revisi_laporan ADD COLUMN created_at timestamp(0);
ALTER TABLE foto_revisi_laporan ADD COLUMN updated_at timestamp(0);
ALTER TABLE foto_revisi_laporan DROP CONSTRAINT foto_revisi_laporan_pkey;
ALTER TABLE foto_revisi_laporan RENAME COLUMN revisi_id TO revisi_laporan_id;
ALTER TABLE foto_revisi_laporan ADD COLUMN id bigserial PRIMARY KEY;
ALTER TABLE foto_revisi_laporan ADD CONSTRAINT foto_revisi_laporan_revisi_laporan_id_berkas_media_id_unique UNIQUE (revisi_laporan_id, berkas_media_id);
SQL);
    }
};
<?php

/**
 * Menyiapkan database pengujian PostgreSQL.
 *
 * Dijalankan otomatis oleh docker/entrypoint.sh agar setiap anggota tim
 * punya database uji tanpa langkah manual. Aman dijalankan berulang kali.
 */

require __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$namaUji = 'rimbawatch_test';

$host = $_ENV['DB_HOST'] ?? 'db';
$port = $_ENV['DB_PORT'] ?? '5432';
$pengguna = $_ENV['DB_USERNAME'] ?? 'rimbawatch';
$sandi = $_ENV['DB_PASSWORD'] ?? '';

$opsi = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 5,
];

try {
    $pemeliharaan = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $pengguna, $sandi, $opsi);

    $cek = $pemeliharaan->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
    $cek->execute([$namaUji]);

    if ($cek->fetchColumn() === false) {
        $pemeliharaan->exec('CREATE DATABASE '.$namaUji);
        echo "Database uji {$namaUji} dibuat.".PHP_EOL;
    }

    $uji = new PDO("pgsql:host={$host};port={$port};dbname={$namaUji}", $pengguna, $sandi, $opsi);
    $uji->exec('CREATE EXTENSION IF NOT EXISTS postgis');
} catch (PDOException $exception) {
    fwrite(STDERR, 'Peringatan: database uji belum siap — '.$exception->getMessage().PHP_EOL);
}

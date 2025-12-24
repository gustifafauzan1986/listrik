<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan; // Tambahkan Facade Artisan
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    /**
     * Tampilkan Halaman Backup/Restore
     */
    public function index()
    {
        return view('settings.database');
    }

    /**
     * Proses Backup Database (Download SQL/SQLite)
     */
    public function backup()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d-H-i-s');
        $connection = config('database.default');

        // Pastikan folder penyimpanan ada
        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        if ($connection === 'sqlite') {
            // --- LOGIKA SQLITE ---
            $dbPath = config('database.connections.sqlite.database');
            $backupPath = storage_path("app/backups/{$filename}.sqlite");

            if (File::copy($dbPath, $backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Gagal backup file SQLite.');
            }

        } elseif ($connection === 'mysql') {
            // --- LOGIKA MYSQL ---
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            $filename = $filename . ".sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // Perintah mysqldump
            // Tambahkan path manual jika di windows (opsional, sesuaikan path XAMPP Anda)
            // $dumpPath = "C:/xampp/mysql/bin/mysqldump.exe"; 
            $dumpPath = "mysqldump"; // Default global path

            $command = "\"{$dumpPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$backupPath}\"";

            $returnVar = NULL;
            $output  = NULL;
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Gagal backup MySQL. Pastikan mysqldump terinstall dan ada di PATH.');
            }

        } elseif ($connection === 'pgsql') {
            // --- LOGIKA POSTGRESQL ---
            $dbName = config('database.connections.pgsql.database');
            $dbUser = config('database.connections.pgsql.username');
            $dbPass = config('database.connections.pgsql.password');
            $dbHost = config('database.connections.pgsql.host');
            $dbPort = config('database.connections.pgsql.port', '5432');

            $filename = $filename . ".sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // 1. Definisikan Path pg_dump (Sesuaikan dengan instalasi Anda)
            // Jika di Windows/Laragon, ganti dengan path lengkap, misal: "C:/Program Files/PostgreSQL/15/bin/pg_dump.exe"
            $pgDumpPath = env('PG_DUMP_PATH', 'pg_dump'); 

            // 2. Set password environment variable
            putenv("PGPASSWORD={$dbPass}");

            // 3. Susun perintah (Tambahkan 2>&1 untuk menangkap error log)
            $command = "\"{$pgDumpPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} > \"{$backupPath}\" 2>&1";

            $returnVar = NULL;
            $output  = [];
            
            // Eksekusi
            exec($command, $output, $returnVar);

            // Bersihkan password dari memori
            putenv("PGPASSWORD=");

            if ($returnVar === 0 && file_exists($backupPath) && filesize($backupPath) > 0) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                // Log Error Output untuk debugging
                Log::error("Backup PostgreSQL Gagal. Output: " . implode("\n", $output));
                
                return back()->with('error', 'Gagal backup PostgreSQL. Cek Log Laravel untuk detail error (kemungkinan path pg_dump salah).');
            }
        }

        return back()->with('error', 'Tipe database tidak didukung untuk fitur ini.');
    }

    /**
     * Proses Import / Restore Database
     */
    // public function restore(Request $request)
    // {
    //     // Tingkatkan limit untuk file besar
    //     ini_set('max_execution_time', 300); 
    //     ini_set('memory_limit', '512M');

    //     $request->validate([
    //         'backup_file' => 'required|file'
    //     ]);

    //     $file = $request->file('backup_file');
    //     $extension = $file->getClientOriginalExtension();
    //     $connection = config('database.default');

    //     try {
    //         if ($connection === 'sqlite' && $extension === 'sqlite') {
    //             // Restore SQLite
    //             $dbPath = config('database.connections.sqlite.database');
    //             File::copy($dbPath, $dbPath . '.bak'); // Buat backup file lama
    //             $file->move(dirname($dbPath), basename($dbPath));
    //             return back()->with('success', 'Database SQLite berhasil dipulihkan.');

    //         } elseif ($connection === 'mysql' && $extension === 'sql') {
    //             // Restore MySQL
    //             $dbName = config('database.connections.mysql.database');
    //             $dbUser = config('database.connections.mysql.username');
    //             $dbPass = config('database.connections.mysql.password');
    //             $dbHost = config('database.connections.mysql.host');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Tambahkan path mysql manual jika perlu
    //             $mysqlPath = "mysql"; 
                
    //             $command = "\"{$mysqlPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} < \"{$fullPath}\"";

    //             $returnVar = NULL;
    //             $output  = NULL;
    //             exec($command, $output, $returnVar);
    //             unlink($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database MySQL berhasil dipulihkan.');
    //             } else {
    //                 return back()->with('error', 'Gagal restore MySQL.');
    //             }

    //         } elseif ($connection === 'pgsql' && $extension === 'sql') {
    //             // --- LOGIKA RESTORE POSTGRESQL ---
    //             $dbName = config('database.connections.pgsql.database');
    //             $dbUser = config('database.connections.pgsql.username');
    //             $dbPass = config('database.connections.pgsql.password');
    //             $dbHost = config('database.connections.pgsql.host');
    //             $dbPort = config('database.connections.pgsql.port', '5432');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Path psql (bisa diset di .env PG_PSQL_PATH)
    //             $psqlPath = env('PG_PSQL_PATH', 'psql');

    //             putenv("PGPASSWORD={$dbPass}");

    //             // Perintah restore
    //             $command = "\"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} < \"{$fullPath}\" 2>&1";

    //             $returnVar = NULL;
    //             $output  = [];
    //             exec($command, $output, $returnVar);
                
    //             putenv("PGPASSWORD=");
    //             unlink($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database PostgreSQL berhasil dipulihkan.');
    //             } else {
    //                 Log::error("Restore PostgreSQL Gagal. Output: " . implode("\n", $output));
    //                 return back()->with('error', 'Gagal restore PostgreSQL. Cek Log Laravel.');
    //             }

    //         } else {
    //             return back()->with('error', 'Format file tidak cocok dengan database yang digunakan.');
    //         }

    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    /**
     * Proses Import / Restore Database
     */
    // public function restore(Request $request)
    // {
    //     // Tingkatkan limit untuk file besar
    //     ini_set('max_execution_time', 300); 
    //     ini_set('memory_limit', '512M');

    //     $request->validate([
    //         'backup_file' => 'required|file'
    //     ]);

    //     $file = $request->file('backup_file');
    //     $extension = $file->getClientOriginalExtension();
    //     $connection = config('database.default');

    //     try {
    //         if ($connection === 'sqlite' && $extension === 'sqlite') {
    //             // Restore SQLite
    //             $dbPath = config('database.connections.sqlite.database');
    //             File::copy($dbPath, $dbPath . '.bak'); // Buat backup file lama
    //             $file->move(dirname($dbPath), basename($dbPath));
    //             return back()->with('success', 'Database SQLite berhasil dipulihkan.');

    //         } elseif ($connection === 'mysql' && $extension === 'sql') {
    //             // Restore MySQL
    //             $dbName = config('database.connections.mysql.database');
    //             $dbUser = config('database.connections.mysql.username');
    //             $dbPass = config('database.connections.mysql.password');
    //             $dbHost = config('database.connections.mysql.host');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Tambahkan path mysql manual jika perlu
    //             $mysqlPath = "mysql"; 
                
    //             $command = "\"{$mysqlPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} < \"{$fullPath}\"";

    //             $returnVar = NULL;
    //             $output  = NULL;
    //             exec($command, $output, $returnVar);
                
    //             // Gunakan File::delete agar tidak error jika file sudah hilang/terkunci
    //             File::delete($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database MySQL berhasil dipulihkan.');
    //             } else {
    //                 return back()->with('error', 'Gagal restore MySQL.');
    //             }

    //         } elseif ($connection === 'pgsql' && $extension === 'sql') {
    //             // --- LOGIKA RESTORE POSTGRESQL ---
    //             $dbName = config('database.connections.pgsql.database');
    //             $dbUser = config('database.connections.pgsql.username');
    //             $dbPass = config('database.connections.pgsql.password');
    //             $dbHost = config('database.connections.pgsql.host');
    //             $dbPort = config('database.connections.pgsql.port', '5432');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Path psql (bisa diset di .env PG_PSQL_PATH)
    //             $psqlPath = env('PG_PSQL_PATH', 'psql');

    //             putenv("PGPASSWORD={$dbPass}");

    //             // Perintah restore
    //             $command = "\"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} < \"{$fullPath}\" 2>&1";

    //             $returnVar = NULL;
    //             $output  = [];
    //             exec($command, $output, $returnVar);
                
    //             putenv("PGPASSWORD=");
                
    //             // Gunakan File::delete agar lebih aman
    //             File::delete($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database PostgreSQL berhasil dipulihkan.');
    //             } else {
    //                 Log::error("Restore PostgreSQL Gagal. Output: " . implode("\n", $output));
    //                 return back()->with('error', 'Gagal restore PostgreSQL. Cek Log Laravel.');
    //             }

    //         } else {
    //             return back()->with('error', 'Format file tidak cocok dengan database yang digunakan.');
    //         }

    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

     /**
     * Proses Import / Restore Database
     */
     /**
     * Proses Import / Restore Database
     */
    
    public function restore(Request $request)
{
    // Tingkatkan limit untuk file besar
    ini_set('max_execution_time', 300); 
    ini_set('memory_limit', '512M');

    $request->validate([
        'backup_file' => 'required|file'
    ]);

    $file = $request->file('backup_file');
    $extension = $file->getClientOriginalExtension();
    $connection = config('database.default');

    try {
        if ($connection === 'pgsql' && $extension === 'sql') {
            // --- LOGIKA RESTORE POSTGRESQL ---
            $dbName = config('database.connections.pgsql.database');
            $dbUser = config('database.connections.pgsql.username');
            $dbPass = config('database.connections.pgsql.password');
            $dbHost = config('database.connections.pgsql.host');
            $dbPort = config('database.connections.pgsql.port', '5432');

            $path = $file->storeAs('temp', 'restore.sql');
            $fullPath = storage_path('app/' . $path);

            // Path psql (sesuaikan dengan environment server)
            $psqlPath = env('PG_PSQL_PATH', 'psql');

            // --- CATATAN PENTING ---
            // Kita TIDAK menggunakan db:wipe agar tabel 'sessions' tidak hilang.
            // Kita mengandalkan perintah psql untuk menimpa data.
            // -----------------------

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows Fix: Gunakan 'type' dan pipe
                $fullPath = str_replace('/', '\\', $fullPath); 
                $command = "set PGPASSWORD={$dbPass} && type \"{$fullPath}\" | \"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} 2>&1";
            } else {
                // Linux/Mac/Unix
                putenv("PGPASSWORD={$dbPass}");
                $command = "\"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} < \"{$fullPath}\" 2>&1";
            }

            $returnVar = NULL;
            $output  = [];
            exec($command, $output, $returnVar);
            
            // Bersihkan password dari environment variable (Linux only)
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                putenv("PGPASSWORD=");
            }
            
            File::delete($fullPath);

            // --- SAFETY NET (Jaga-jaga) ---
            // Jika file backup mengandung perintah "DROP TABLE sessions", 
            // kita harus memastikan tabel itu dibuat ulang agar tidak error.
            if (!Schema::hasTable('sessions')) {
                Schema::create('sessions', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }

            if ($returnVar === 0) {
                return back()->with('success', 'Database PostgreSQL berhasil dipulihkan.');
            } else {
                Log::error("Restore PostgreSQL Gagal. Command: {$command}");
                Log::error("Output: " . implode("\n", $output));
                return back()->with('error', 'Gagal restore PostgreSQL. Cek Log Laravel.');
            }

        } elseif ($connection === 'mysql' && $extension === 'sql') {
            // --- LOGIKA MYSQL (Tetap) ---
            // ... Kode MySQL Anda sebelumnya ...
             return back()->with('error', 'Fitur MySQL belum disesuaikan.');

        } elseif ($connection === 'sqlite' && $extension === 'sqlite') {
             // --- LOGIKA SQLITE (Tetap) ---
             // ... Kode SQLite Anda sebelumnya ...
             return back()->with('error', 'Fitur SQLite belum disesuaikan.');
        } else {
            return back()->with('error', 'Format file tidak cocok dengan database yang digunakan.');
        }

    } catch (\Exception $e) {
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}
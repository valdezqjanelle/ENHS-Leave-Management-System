<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupController extends Controller
{
    public function create(Request $request)
    {
        try {
            $user = $request->user();

            $backupDirectory = 'backups';

            Storage::disk('local')->makeDirectory($backupDirectory);

            $timestamp = now()->format('Y-m-d_H-i-s');

            $fileName = "els_database_backup_{$timestamp}.json";

            $filePath = "{$backupDirectory}/{$fileName}";

            $tables = Schema::getTables();

            $excludedTables = [
                'personal_access_tokens',
                'sessions',
            ];

            $backupData = [
                'system' => [
                    'name' => 'ENHS Leave Management System',
                    'backup_type' => 'Full Database',
                    'created_at' => now()->toISOString(),
                ],
                'tables' => [],
            ];

            foreach ($tables as $tableInfo) {
                $tableName = $tableInfo['name'];

                if (
                    in_array($tableName, $excludedTables) ||
                    str_starts_with($tableName, 'pg_') ||
                    str_starts_with($tableName, 'sql_') ||
                    $tableName === 'spatial_ref_sys'
                ) {
                    continue;
                }

                try {
                    $backupData['tables'][$tableName] = DB::table($tableName)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();
                } catch (Throwable $e) {
                    continue;
                }
            }

            $json = json_encode(
                $backupData,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            if ($json === false) {
                throw new \Exception('Failed to generate backup JSON.');
            }

            Storage::disk('local')->put($filePath, $json);

            $fileSize = Storage::disk('local')->size($filePath);

            $backup = Backup::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'backup_type' => 'Full Database',
                'file_size' => $fileSize,
                'status' => 'Successful',
                'created_by' => $user?->user_id,
            ]);

            return response()->json([
                'message' => 'Database backup created successfully.',
                'data' => $backup,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create database backup.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $backups = Backup::orderByDesc('created_at')->get();

            return response()->json([
                'data' => $backups,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to load backups.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function download($id)
    {
        try {
            $backup = Backup::findOrFail($id);

            if (!Storage::disk('local')->exists($backup->file_path)) {
                return response()->json([
                    'message' => 'Backup file not found.',
                ], 404);
            }

            return Storage::disk('local')->download(
                $backup->file_path,
                $backup->file_name
            );
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to download backup.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|mimes:json|max:51200',
            ]);

            $file = $request->file('backup_file');

            $contents = file_get_contents($file->getRealPath());

            $backupData = json_decode($contents, true);

            if (
                json_last_error() !== JSON_ERROR_NONE ||
                !isset($backupData['tables']) ||
                !is_array($backupData['tables'])
            ) {
                return response()->json([
                    'message' => 'Invalid backup file format.',
                ], 422);
            }

            DB::beginTransaction();

            try {
                $tables = $backupData['tables'];

                $excludedTables = [
                    'personal_access_tokens',
                    'sessions',
                ];

                $existingTables = array_keys($tables);

                $tableOrder = [];

                foreach ($existingTables as $tableName) {
                    if (
                        in_array($tableName, $excludedTables) ||
                        str_starts_with($tableName, 'pg_') ||
                        str_starts_with($tableName, 'sql_') ||
                        $tableName === 'spatial_ref_sys'
                    ) {
                        continue;
                    }

                    if (!Schema::hasTable($tableName)) {
                        continue;
                    }

                    $tableOrder[] = $tableName;
                }

                DB::statement('SET session_replication_role = replica');

                foreach ($tableOrder as $tableName) {
                    DB::table($tableName)->delete();
                }

                foreach ($tableOrder as $tableName) {
                    $rows = $tables[$tableName];

                    if (!empty($rows)) {
                        foreach (array_chunk($rows, 100) as $chunk) {
                            DB::table($tableName)->insert($chunk);
                        }
                    }
                }

                DB::statement('SET session_replication_role = origin');

                DB::commit();

                return response()->json([
                    'message' => 'Database restored successfully.',
                ]);
            } catch (Throwable $e) {
                DB::statement('SET session_replication_role = origin');

                DB::rollBack();

                throw $e;
            }
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to restore database.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
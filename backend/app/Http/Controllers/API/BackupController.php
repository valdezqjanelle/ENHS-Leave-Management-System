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
                    str_starts_with($tableName, 'pg_') ||
                    str_starts_with($tableName, 'sql_') ||
                    $tableName === 'spatial_ref_sys'
                ) {
                    continue;
                }

                try {
                    $backupData['tables'][$tableName] = DB::table($tableName)
                        ->get()
                        ->map(fn($row) => (array) $row)
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
                    'message' => 'Backup file not found.'
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
}

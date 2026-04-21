<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\AuditLogHelper;
use App\Helpers\HttpResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    use ApiResponse, AuditLogHelper;

    public function upload(Request $request)
    {
        $attributes = $request->validate([
            'file' => ['required', 'file', 'max:51200'], // max 50MB
            'prefix' => ['nullable', 'string', 'max:1024'],
            'filename' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'in:private,public'],
        ]);

        $file = $request->file('file');
        $prefix = isset($attributes['prefix']) ? trim($attributes['prefix'], '/') : '';
        $filename = $attributes['filename'] ?? $file->hashName();
        $path = $prefix !== '' ? $prefix . '/' . $filename : $filename;
        $visibility = $attributes['visibility'] ?? 'public';

        try {
            $disk = Storage::disk('s3');
            $stream = fopen($file->getRealPath(), 'r');

            $uploaded = $disk->put($path, $stream, [
                'visibility' => $visibility,
                'ContentType' => $file->getMimeType(),
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if (!$uploaded) {
                return $this->errorResponse('Unable to upload file', null, HttpResponseCode::INTERNAL_SERVER_ERROR);
            }

            $this->logAudit('create', 'storage_object', $path, $path, [
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $visibility,
            ], $request);

            $url = null;
            if ($visibility === 'public') {
                try {
                    $url = $disk->url($path);
                } catch (\Throwable $e) {
                    $url = null;
                }
            }

            return $this->successResponse([
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $visibility,
                'url' => $url,
            ], 'File uploaded successfully', HttpResponseCode::CREATED);
        } catch (\Throwable $e) {
            return $this->errorResponse('Unable to upload file', $e->getMessage());
        }
    }

    public function files(Request $request)
    {
        $attributes = $request->validate([
            'prefix' => ['nullable', 'string', 'max:1024'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $prefix = isset($attributes['prefix']) ? trim($attributes['prefix'], '/') : '';
        $limit = (int) ($attributes['limit'] ?? 100);

        try {
            $disk = Storage::disk('s3');
            $keys = array_slice($disk->allFiles($prefix), 0, $limit);

            $files = array_map(function (string $key) use ($disk) {
                return [
                    'path' => $key,
                    'size' => $disk->size($key),
                    'last_modified' => date('c', $disk->lastModified($key)),
                    'mime_type' => $disk->mimeType($key),
                ];
            }, $keys);

            return $this->successResponse([
                'prefix' => $prefix,
                'count' => count($files),
                'files' => $files,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse('Unable to list files', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $attributes = $request->validate([
            'path' => ['required', 'string', 'max:1024'],
        ]);

        $path = ltrim($attributes['path'], '/');

        try {
            $disk = Storage::disk('s3');

            if (!$disk->exists($path)) {
                return $this->errorResponse('File not found', null, HttpResponseCode::NOT_FOUND);
            }

            $deleted = $disk->delete($path);

            if (!$deleted) {
                return $this->errorResponse('Unable to delete file', null, HttpResponseCode::INTERNAL_SERVER_ERROR);
            }

            $this->logAudit('delete', 'storage_object', $path, $path, null, $request);

            return $this->successResponse([
                'path' => $path,
            ], 'File deleted successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Unable to delete file', $e->getMessage());
        }
    }
}
<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Images
{
    public static function save(Model $model, array $data, string $field, string $folder, ?Closure $check = null): void
    {
        $file = $data[$field] ?? null;
        unset($data[$field]);
        $path = $file instanceof UploadedFile ? $file->store($folder, 'public') : null;
        $oldPath = null;

        try {
            DB::transaction(function () use ($model, $data, $field, $path, $check, &$oldPath) {
                $record = $model->exists ? $model->newQuery()->lockForUpdate()->findOrFail($model->id) : $model;
                $oldPath = $record->{$field};
                if ($check) {
                    $check($record);
                }
                $record->fill($data);
                if ($path) {
                    $record->{$field} = $path;
                }
                $record->save();
            }, 3);
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $error;
        }

        if ($path && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}

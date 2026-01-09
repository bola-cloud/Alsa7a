<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Upload a file to the specified folder in public disk.
     * Returns the relative path including 'storage/' prefix.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string|null
     */
    public function upload(UploadedFile $file, string $folder): ?string
    {
        // Store the file in the public disk
        // store() returns the path relative to the disk root (e.g., 'clubs/logos/xyz.jpg')
        $path = $file->store($folder, 'public');

        if (!$path) {
            return null;
        }

        // Return the path with 'storage/' prefix to be consistent with database convention
        return 'storage/' . $path;
    }

    /**
     * Delete an old file and upload a new one.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $oldPath
     * @return string|null
     */
    public function replace(UploadedFile $file, string $folder, ?string $oldPath): ?string
    {
        $this->delete($oldPath);
        return $this->upload($file, $folder);
    }

    /**
     * Delete a file from the public disk.
     * Handles paths with or without 'storage/' prefix.
     *
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        // Check if it's an external URL (should not delete)
        // If it starts with http/https, we check if it matches our app URL
        if (preg_match('#^https?://#i', $path)) {
            $appUrl = config('app.url');
            // If app.url is not set or doesn't match, we assume external and return false
            // However, asset() might use the current host which matches.
            // Best approach: try to strip the domain and see if file exists.

            // Simple check: if it contains the storage path structure
            if (!str_contains($path, '/storage/')) {
                return false;
            }
        }

        // Remove 'storage/' prefix to get the path relative to public disk root
        // Also handling full URL case by extracting path after 'storage/'
        $pathWithoutStorage = strstr($path, 'storage/');
        if ($pathWithoutStorage) {
            $relativePath = str_replace('storage/', '', $pathWithoutStorage);
        } else {
            $relativePath = str_replace('storage/', '', $path);
        }

        // Also handle cases where it might start with just a slash
        $relativePath = ltrim($relativePath, '/');

        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }

        return false;
    }
}

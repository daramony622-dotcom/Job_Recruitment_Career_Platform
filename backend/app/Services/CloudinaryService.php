<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey    = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    /**
     * Upload file (image or document) to Cloudinary.
     */
    public function uploadFile(UploadedFile|string $file, string $folder = 'uploads', string $resourceType = 'auto'): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        $options = [
            'folder'        => $folder,
            'resource_type' => $resourceType,
        ];

        $result = $this->cloudinary->uploadApi()->upload($filePath, $options);

        return [
            'secure_url' => $result['secure_url'] ?? $result['url'] ?? null,
            'public_id'  => $result['public_id'] ?? null,
            'format'     => $result['format'] ?? null,
            'bytes'      => $result['bytes'] ?? null,
        ];
    }

    /**
     * Shortcut for uploading images.
     */
    public function uploadImage(UploadedFile|string $file, string $folder = 'images'): array
    {
        return $this->uploadFile($file, $folder, 'image');
    }

    /**
     * Shortcut for uploading documents (resumes, PDFs).
     */
    public function uploadDocument(UploadedFile|string $file, string $folder = 'documents'): array
    {
        return $this->uploadFile($file, $folder, 'raw');
    }

    /**
     * Delete file from Cloudinary by public ID.
     */
    public function deleteFile(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => $resourceType,
            ]);

            return ($result['result'] ?? '') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }
}


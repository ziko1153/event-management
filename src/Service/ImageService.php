<?php

namespace App\Service;

class ImageService
{
    private string $uploadPath;

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    public function uploadImage(array $file): string
    {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }



        $pathParts = explode('public/', $this->uploadPath);
        $fileUniqueName = explode("/", $pathParts[1]);
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($fileUniqueName[count($fileUniqueName) - 2] . '_') . '.' . $extension;
        $destination = $this->uploadPath . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Failed to upload image');
        }


        return $pathParts[1] . $filename;
    }

    public function deleteImage(string $path): void
    {
        $fullPath = __DIR__ . '/../../public/' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
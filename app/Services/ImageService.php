<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageService
{
    /**
     * Handle file upload and store it in the sepcified directory.
     * 
     * @param string $directory
     * @return string
     */
    public function uploadImage(UploadedFile $file, string $directory): string
    {
        $filename = $file->hashName();
        $file->storeAs('public/' . $directory, $filename);
        
        return 'products/' . $filename;
    }
}
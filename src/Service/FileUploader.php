<?php

// src/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $uploadFolder;

    public function __construct($uploadFolder)
    {
        $this->uploadFolder = $uploadFolder;
    }

    public function upload(UploadedFile $file, $exts = [])
    {
        $fileName = false;

        if (count($exts) > 0) 
        {
            foreach ($exts as $ext) 
            {
                if ($file->guessExtension() == $ext) 
                {
                    $fileName = md5(uniqid()).'.'.$file->guessExtension();
                    break;
                }
            }
        }
        else
        {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
        }

        if ($fileName !== false) 
        {
            try {
                $file->move($this->getUploadFolder(), $fileName);
            } catch (FileException $e) {
                return false;
            }
        }

        return $fileName;
    }

    public function removeFile($file)
    {
        $file_path = $this->getUploadFolder().$file;
        if(file_exists($file_path)) unlink($file_path);
    }

    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }
}
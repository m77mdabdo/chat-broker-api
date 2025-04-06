<?php

namespace App\Repositories\S3;

interface UploadFileRepositoryInterface
{
    public function upload($file);
    public function uploadVideo($file);

    public function uploadImage360($file);

}

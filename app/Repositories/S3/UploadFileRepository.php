<?php

namespace App\Repositories\S3;

use App\Http\Requests\Product\AddImageRequest;
use Illuminate\Support\Facades\Storage;

class UploadFileRepository implements UploadFileRepositoryInterface
{
    // public function upload($file,$path){
    //     $fileNameWithoutSpaces = str_replace(' ','_',$file->getClientOriginalName());
    //     $editedFileName = time().'_'.$fileNameWithoutSpaces;
    //     $fileContent = $file->getContent();
    //     $filenameForS3 = $path."/".$editedFileName;
    //     $response = Storage::disk('public')->put($filenameForS3, $fileContent, []);
    //     return $editedFileName;
    // }
    public function upload($file ){


        $imageName = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('images'),$imageName);

        return $imageName;

    }


    public function uploadVideo($file)
    {
        $videoName = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('videos'), $videoName);
        return $videoName;
    }

    public function uploadImage360($file)
    {
        $image360Name = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('image360'), $image360Name);
        return $image360Name;
    }
}

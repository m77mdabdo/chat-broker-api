<?php

namespace App\Repositories\Product;

use App\Models\Image;
use App\Models\image360;
use App\Models\video;
use App\Models\Price;
use App\Models\Product;
use App\Repositories\S3\UploadFileRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ProductRepository implements ProductRepositoryInterface
{
    private $UploadFileRepository;
    public function __construct(UploadFileRepositoryInterface $UploadFileRepository)
    {
        $this->UploadFileRepository = $UploadFileRepository;
    }

    public function addNewProduct($request, $product)
    {

        if ($request->images) {
            //----------- Upload Image ---------------//
            //$images -> file data forloop for in images
            foreach ($request->images as $image) {
                // upload here
                $imageName = $this->UploadFileRepository->upload($image);
                $image = Image::create([
                    'image' => $imageName,
                    'product_id' => $product->id,
                    'object_id' => $product->id,
                ]);
            }
        }
        if ($request->videos) {
            //----------- Upload video ---------------//
            //$videos -> file data forloop for in images
            foreach ($request->videos as $video) {
                // upload here
                $videoName = $this->UploadFileRepository->uploadVideo($video);
                $video = Video::create([
                    'video' => $videoName,
                    'product_id' => $product->id,
                    'object_id' => $product->id,
                ]);
            }
        }

        if ($request->images360) {
            //----------- Upload video ---------------//
            //$image360 -> file data forloop for in images
            foreach ($request->images360 as $image360) {
                // upload here
                $image360Name = $this->UploadFileRepository->uploadImage360($image360);
                $image360 = Image360::create([
                    'image360' => $image360Name,
                    'product_id' => $product->id,
                    'object_id' => $product->id,
                ]);
            }
        }

        return $product;
    }

    public function updateProduct($request)
    {

    }
}

<?php

namespace App\Repositories\Product;

interface ProductRepositoryInterface
{
    public function addNewProduct($request, $product);
    public function updateProduct($request);
}

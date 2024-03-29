<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ImageService;

class ImageController extends Controller
{
    private $image_service;

    public function __construct()
    {
        $this->image_service = new ImageService();
    }

    public function findUrl($url = null)
    {
        return $this->image_service->findUrl($url);
    }

    public function updateOrCreate(array $data)
    {
        return $this->image_service->updateOrCreate($data);
    }

    public function delete($id)
    {
        return $this->image_service->delete($id);
    }
}

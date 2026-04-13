<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use App\Models\ProductImage;

class ImageController extends Controller
{
    /**
     * @OA\Get(
     *      path="/images",
     *      operationId="getImages",
     *      tags={"Image"},
     *      summary="Retrieve all images",
     *      description="Retrieve all images",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          headers={
     *              @OA\Header(header="Cache-Control", description="public, max-age=120", @OA\Schema(type="string")),
     *              @OA\Header(header="ETag", @OA\Schema(type="string"))
     *          },
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ImageResponse")
     *          )
     *       ),
     *       @OA\Response(response=304, description="Not Modified"),
     *  )
     */
    public function index()
    {
        return $this->preferredFormat(ProductImage::all());
    }

}

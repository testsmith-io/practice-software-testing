<?php

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
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ImageResponse")
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Returns when the resource is not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Resource not found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Returns when the method is not allowed for the requested route",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Method is not allowed for the requested route"),
     *          )
     *      ),
     * )
     */
    public function index()
    {
        return $this->preferredFormat(ProductImage::all());
    }

}

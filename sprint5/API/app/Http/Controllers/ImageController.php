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
     *       @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *       @OA\Response(response="405", ref="#/components/responses/MethodNotAllowedResponse"),
     * )
     */
    public function index()
    {
        return $this->preferredFormat(ProductImage::all());
    }

}

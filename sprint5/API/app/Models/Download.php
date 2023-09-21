<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mehradsadeghi\FilterQueryString\FilterQueryString;

 /** @OA\Schema(
 *     schema="DownloadResponse",
 *     type="object",
 *     title="DownloadResponse",
 *     properties={
 *     @OA\Property(property="id", type="string"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="type", type="string"),
 *         @OA\Property(property="status", type="string"),
 *         @OA\Property(property="filename", type="string"),
 *
 *     }
 * )
 */
class Download extends BaseModel
{
    use HasFactory, FilterQueryString, HasUlids;

    protected $table = 'downloads';
    protected $fillable = ['name', 'type', 'status', 'filename'];
}

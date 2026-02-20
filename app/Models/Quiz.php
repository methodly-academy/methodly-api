<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Quiz",
 *     required={"chapter_id", "title"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="chapter_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Quiz Dasar Laravel"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['chapter_id', 'title'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}

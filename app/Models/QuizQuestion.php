<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @OA\Schema(
 *     schema="QuizQuestion",
 *     required={"quiz_id", "question_text", "question_type"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="quiz_id", type="integer", example=1),
 *     @OA\Property(property="question_text", type="string", example="Apa itu Laravel?"),
 *     @OA\Property(property="question_type", type="string", enum={"multiple_choice", "boolean", "short_answer", "essay"}),
 *     @OA\Property(property="points", type="integer", example=10),
 *     @OA\Property(property="explanation", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_id', 'question_text', 'question_type', 'points', 'explanation'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class);
    }

    // Pencarian Pertanyaan berdasarkan teks
    public function scopeSearch(Builder $query, ?string $keyword)
    {
        if ($keyword) {
            return $query->where('question_text', 'ILIKE', "%{$keyword}%");
        }
        
        return $query;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $guarded = ['id'];
    protected $fillable = ['title','slug','description','thumbnail'];

    // Setiap Series punya banyak Course
    public function courses(){
        return $this->belongsToMany(Course::class, 'course_series')->withPivot('sort_order')->orderByPivot('sort_oder','asc');
    }
}

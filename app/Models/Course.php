<?php

namespace App\Models;

use App\Models\Level;
use App\Models\Series;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = ['id'];
    protected $fillable = ['title','slug','description','thumbnail','type','price','isPublished'];

    // Tiap Course punya 1 level
    public function level(){
        return $this->belongsTo(Level::class);
    }

    // Tiap Course punya banyak chapter
    public function chapters(){
        return $this->hasMany(Chapter::class);
    }

    // Tiap course bisa masuk ke banyak series
    public function series(){
        return $this->belongsToMany(Series::class, 'course_series')->withPivot('sort_order');
    }
}

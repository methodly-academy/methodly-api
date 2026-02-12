<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $guarded = ['id'];
    protected $fillable = ['name', 'slug'];

    public function courses(){
        return $this->hasMany(Course::class);
    }
}

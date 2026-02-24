<?php

namespace App\Models;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = ['id'];
    protected $fillable = ['title','type','pdf_path','video_url','duration','is_downloadable'];

    public function chapter(){
        return $this->belongsTo(Chapter::class);
    }
}

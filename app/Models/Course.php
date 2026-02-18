<?php

namespace App\Models;

use App\Models\Level;
use App\Models\Series;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = ['name','slug','description','thumbnail','type','price', 'level_id','is_published'];

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

    // Pencarian Kelas berdasarkan nama
    public function scopeSearch(Builder $query, ?string $keyword)
    {
        if ($keyword) {
            // menggunakan ILIKE agar pencarian mengabaikan huruf besar/kecil di Postgres
            return $query->where('name', 'ILIKE', "%{$keyword}%");
        }
        
        return $query;
    }

    // Filter Kelas berdasarkan Tipe Kelas
    public function scopeByType(Builder $query, ?string $type)
    {
        if ($type && in_array($type, ['free', 'premium'])) {
            return $query->where('type', $type);
        }

        return $query;
    }

    public function scopePublished(Builder $query){
        return $query->where('is_published',true);
    }

}

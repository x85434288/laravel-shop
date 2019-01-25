<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //

    protected $fillable = [
        'name','is_directory','level','path'
    ];

    protected $casts = [
        'is_directory' => 'boolean'
    ];


    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class,'parent_id');
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function getPathIdsAttribute()
    {
        return array_filter(explode('-',trim($this->path,'-')));
    }

    public function getAncestorsAttribute()
    {
        return Category::query()
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->get();
    }

    public function getFullNamesAttribute()
    {
        return $this->ancestors
                    ->pluck('name')
                    ->push($this->name)
                    ->implode(' - ');
    }

}

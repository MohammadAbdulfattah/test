<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSector extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'parent_id'];

    public function children(): HasMany
    {
        return $this->hasMany(CustomerSector::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(CustomerSector::class, 'parent_id');
    }
}

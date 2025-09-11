<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Tag extends Model
{
    use HasFactory;

    protected $table = 'gbs_tags';
    protected $fillable = ['name','color'];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\TagFactory::new();
    // }
    public function contacts()
{
    return $this->belongsToMany(\APP\Contact::class, 'contact_tag');
}

}

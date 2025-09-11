<?php

namespace Modules\Goals\Entities;

use App\Brands;
use App\Category;
use App\Product;
use App\User;
use App\Variation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoalGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'name',
        'created_by',
        'start_date',
        'end_date'
    ];

    // Supervisor (user)
    public function supervisor()    
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Category group goals linked to this GoalGroup
    public function category_group_goals()    
    {
        return $this->hasMany(CategoryGroupGoal::class, 'group_id');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_group_goal', 'group_id', 'category_id')
           ;
    }

    // Brands (many-to-many with pivot data)
    public function brands()
    {
        return $this->belongsToMany(Brands::class, 'brands_group_goal', 'group_id', 'brand_id');
           
    }

    // Variations (products) via pivot
    public function variations()
    {
        return $this->belongsToMany(Variation::class, 'products_group_goal', 'group_id', 'variation_id')
            ->with('product'); // To also eager load product data
    }

    // Optional: Access products directly
    public function products()
    {
        return $this->hasManyThrough(Product::class, Variation::class, 'product_id', 'id', null, 'product_id');
    }
    // Brand group goals linked to this GoalGroup
    public function brand_group_goals()   
    {
        return $this->hasMany(BrandGroupGoal::class, 'group_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class, 'group_id');
    }
}

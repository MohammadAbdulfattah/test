<?php

namespace Modules\Goals\Http\Controllers;

use App\Brands;
use App\Category;
use App\Product;
use App\User;
use App\Utils\ProductUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Goals\Entities\BrandGroupGoal;
use Modules\Goals\Entities\CategoryGroupGoal;
use Modules\Goals\Entities\Goal;
use Modules\Goals\Entities\GoalGroup;
use Modules\Goals\Entities\ProductGroupGoal;

class GoalsController extends Controller
{
    /*
      * All Utils instance.
     */
    protected $productUtil;


    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index($id)
    {
        if (! auth()->user()->can('goals.view')) {
            abort(403, 'Unauthorized action.');
        }

        $typeTranslations = [
            'Brand' => __('product.brand'),
            'Category' => __('product.category'),
            'Product' => __('sale.product'),
        ];
        $groupId = $id;
        $group = GoalGroup::findOrFail($groupId);

        $goal_summary = Goal::with(['user', 'child_goals'])
            ->where('group_id', $groupId)
            ->whereNull('parent_id')
            ->get()
            ->map(function ($goal) use ($group,$typeTranslations) {
                // Determine type and fetch item name
                if ($goal->products_group_goal_id) {
                    $goalType = 'Product';
                    $groupGoal = ProductGroupGoal::find($goal->products_group_goal_id);
                    $itemId = $groupGoal->variation_id;
                $itemName = optional($groupGoal->variation)->name == "DUMMY"
                    ? optional($groupGoal->variation->product)->name 
                    : optional($groupGoal->variation->product)->name . " " . optional($groupGoal->variation)->name;
                } elseif ($goal->brand_group_id) {
                    $goalType = 'Brand';
                    $groupGoal = BrandGroupGoal::find($goal->brand_group_id);
                    $itemId = $groupGoal->brand_id;
                    $itemName = optional($groupGoal->brand)->name;
                } elseif ($goal->category_group_id) {
                    $goalType = 'Category';
                    $groupGoal = CategoryGroupGoal::find($goal->category_group_id);
                    $itemId = $groupGoal->category_id;
                    $itemName = optional($groupGoal->category)->name;
                } else {
                    return null; // No valid goal type
                }
            if ($goalType == 'Product') {
                $qty = false;
                if ($goal->product_amount) {
                    // AMOUNT-based goal
                    $actualSales = DB::table('transactions')
                        ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                        ->where('transactions.created_by', $goal->user_id)
                        ->where('transactions.type', 'sell')
                        ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                        ->where('transaction_sell_lines.variation_id', $itemId)
                        ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));
                   
                    $target = $goal->product_amount;
                } else {
                    // QUANTITY-based goal
                    $actualSales = DB::table('transactions')
                        ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                        ->where('transactions.created_by', $goal->user_id)
                        ->where('transactions.type', 'sell')
                        ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                        ->where('transaction_sell_lines.variation_id', $itemId)
                        ->value(DB::raw('SUM(transaction_sell_lines.quantity)'));
                    
                    $target = $this->productUtil->num_f($goal->product_quantity);
                    $qty = true;
                    
                }
                if($qty)
                {
                    $key ='target_qty';
                }else{
                    $key = 'target_amount';
                }
                $percentage = $target > 0 ? round(($actualSales / $target) * 100, 2) : 0;

                return [
                    'user_name' => $goal->user->first_name . " " . $goal->user->last_name,
                    'goal_type' => $typeTranslations[$goalType],
                    'item_name' => $itemName,
                    $key => $target,
                    'actual_sales' => $actualSales == 0 && $key == 'target_qty'? $this->productUtil->num_f(0): $actualSales  ,
                    'percentage' => $percentage,
                    'reward' => $goal->reward_amount,

                    'child_goals' => $goal->child_goals->map(function ($child) use ($group) {
                        // Same detection logic for child goal type
                        if ($child->products_group_goal_id) {
                            $goalType = 'Product';
                            $groupGoal = ProductGroupGoal::find($child->products_group_goal_id);
                            $itemId = $groupGoal->variation_id;
                        } elseif ($child->brand_group_id) {
                            $goalType = 'Brand';
                            $groupGoal = BrandGroupGoal::find($child->brand_group_id);
                            $itemId = $groupGoal->brand_id;
                        } elseif ($child->category_group_id) {
                            $goalType = 'Category';
                            $groupGoal = CategoryGroupGoal::find($child->category_group_id);
                            $itemId = $groupGoal->category_id;
                        } else {
                            return null;
                        }
                        $qty=false;

                        if ($child->product_amount) {
                            // AMOUNT-based child
                            $actualSales = DB::table('transactions')
                                ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                                ->where('transactions.created_by', $child->user_id)
                                ->where('transactions.type', 'sell')
                                ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                                ->where('transaction_sell_lines.variation_id', $itemId)
                                ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));

                            $target = $child->product_amount;
                        } else {
                            // QUANTITY-based child
                            $actualSales = DB::table('transactions')
                                ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                                ->where('transactions.created_by', $child->user_id)
                                ->where('transactions.type', 'sell')
                                ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                                ->where('transaction_sell_lines.variation_id', $itemId)
                                ->value(DB::raw('SUM(transaction_sell_lines.quantity)'));
                            
                            $target = $this->productUtil->num_f($child->product_quantity);
                            $qty=true;
                        }


                        $percentage = $target > 0 ? round(($actualSales / $target) * 100, 2) : 0;
                        if($qty){
                            return [
                                'target_qty' => $target,
                                'actual_sales' => $actualSales == 0 ? $this->productUtil->num_f(0) : $actualSales,
                                'percentage' => $percentage,
                                'reward' => $child->reward_amount,
                            ];
                        }else{
                            return [
                                'target_amount' => $target,
                                'actual_sales' => $actualSales,
                                'percentage' => $percentage,
                                'reward' => $child->reward_amount,
                            ];
                        }
                        
                    })->filter()->values()->toArray()
                ];
            }elseif ($goalType == 'Category') {
                    $actualSales = DB::table('transactions')
                        ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                        ->join('products', 'transaction_sell_lines.product_id', '=', 'products.id')
                        ->where('transactions.created_by', $goal->user_id)
                        ->where('transactions.type', 'sell')
                        ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                        ->where('products.category_id', $groupGoal->category_id)
                        ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));
                    $percentage = $goal->category_amount > 0 ? round(($actualSales / $goal->category_amount) * 100, 2) : 0;

                    return [
                        'user_name' => $goal->user->first_name . " " . $goal->user->last_name,
                        'goal_type' => $typeTranslations[$goalType],
                        'item_name' => $itemName,
                        'target_amount' => $goal->category_amount,
                        'actual_sales' => $actualSales,
                        'percentage' => $percentage,
                        'reward' => $goal->reward_amount,

                        'child_goals' => $goal->child_goals->map(function ($child) use ($group) {
                            // Same detection logic for child goal type
                            if ($child->products_group_goal_id) {
                                $goalType = 'Product';
                                $groupGoal = ProductGroupGoal::find($child->products_group_goal_id);
                                $itemId = $groupGoal->product_id;
                            } elseif ($child->brand_group_id) {
                                $goalType = 'Brand';
                                $groupGoal = BrandGroupGoal::find($child->brand_group_id);
                                $itemId = $groupGoal->brand_id;
                            } elseif ($child->category_group_id) {
                                $goalType = 'Category';
                                $groupGoal = CategoryGroupGoal::find($child->category_group_id);
                                $itemId = $groupGoal->category_id;
                            } else {
                                return null;
                            }

                          
                            $actualSales = DB::table('transactions')
                                ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                                ->join('products', 'transaction_sell_lines.product_id', '=', 'products.id')
                                ->where('transactions.created_by', $child->user_id)
                                ->where('transactions.type', 'sell')
                                ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                                ->where('products.category_id', $groupGoal->category_id)
                                ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));
                            

                            $percentage = $child->category_amount > 0 ? round(($actualSales / $child->category_amount) * 100, 2) : 0;

                            return [
                                'target_amount' => $child->category_amount,
                                'actual_sales' => $actualSales,
                                'percentage' => $percentage,
                                'reward' => $child->reward_amount,
                            ];
                        })->filter()->values()->toArray()
                    ];
                }else{
                    $actualSales = DB::table('transactions')
                        ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                        ->join('products', 'transaction_sell_lines.product_id', '=', 'products.id')
                        ->where('transactions.created_by', $goal->user_id)
                        ->where('transactions.type', 'sell')
                        ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                        ->where('products.brand_id', $groupGoal->brand_id)
                        ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));
                    $percentage = $goal->brand_amount > 0 ? round(($actualSales / $goal->brand_amount) * 100, 2) : 0;

                    return [
                        'user_name' => $goal->user->first_name . " " . $goal->user->last_name,
                        'goal_type' => $typeTranslations[$goalType],
                        'item_name' => $itemName,
                        'target_amount' => $goal->brand_amount,
                        'actual_sales' => $actualSales,
                        'percentage' => $percentage,
                        'reward' => $goal->reward_amount,

                        'child_goals' => $goal->child_goals->map(function ($child) use ($group) {
                            // Same detection logic for child goal type
                            if ($child->products_group_goal_id) {
                                $goalType = 'Product';
                                $groupGoal = ProductGroupGoal::find($child->products_group_goal_id);
                                $itemId = $groupGoal->product_id;
                            } elseif ($child->brand_group_id) {
                                $goalType = 'Brand';
                                $groupGoal = BrandGroupGoal::find($child->brand_group_id);
                                $itemId = $groupGoal->brand_id;
                            } elseif ($child->category_group_id) {
                                $goalType = 'Category';
                                $groupGoal = CategoryGroupGoal::find($child->category_group_id);
                                $itemId = $groupGoal->category_id;
                            } else {
                                return null;
                            }


                            $actualSales = DB::table('transactions')
                                ->join('transaction_sell_lines', 'transactions.id', '=', 'transaction_sell_lines.transaction_id')
                                ->join('products', 'transaction_sell_lines.product_id', '=', 'products.id')
                                ->where('transactions.created_by', $child->user_id)
                                ->where('transactions.type', 'sell')
                                ->whereBetween('transactions.transaction_date', [$group->start_date, $group->end_date])
                                ->where('products.brand_id', $groupGoal->brand_id)
                                ->value(DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price)'));
                            

                            $percentage = $child->brand_amount > 0 ? round(($actualSales / $child->brand_amount) * 100, 2) : 0;

                            return [
                                'target_amount' => $child->brand_amount,
                                'actual_sales' => $actualSales,
                                'percentage' => $percentage,
                                'reward' => $child->reward_amount,
                            ];
                        })->filter()->values()->toArray()
                    ];
                }
                
              

                
            })->filter()->values();
           
        return view('goals::goals.index',compact('goal_summary','id'));
    }

    public function create(Request $request,$id)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }
        $typeTranslations = [
            'brand' => __('product.brand'),
            'category' => __('product.category'),
            'product' => __('sale.product'),
        ];

        // Brand goals
        $brandGoals = DB::table('goal_groups')
            ->join('brands_group_goal as bgg', 'goal_groups.id', '=', 'bgg.group_id')
            ->join('brands as b', 'bgg.brand_id', '=', 'b.id')
            ->where('goal_groups.id', $id)
            ->select(
            'bgg.id as item_id',
                'goal_groups.name',
                'b.name as item_name',
                DB::raw("'brand' as goal_type")
            );

        // Category goals
        $categoryGoals = DB::table('goal_groups')
            ->join('categories_group_goal as cgg', 'goal_groups.id', '=', 'cgg.group_id')
            ->join('categories as c', 'cgg.category_id', '=', 'c.id')
            ->where('goal_groups.id', $id)
            ->select(
            'cgg.id as item_id',
                'goal_groups.name',
                'c.name as item_name',
                DB::raw("'category' as goal_type")
            );

        // Product goals
        $productGoals = DB::table('goal_groups')
            ->join('products_group_goal as pgg', 'goal_groups.id', '=', 'pgg.group_id')
            ->join('variations as v', 'pgg.variation_id', '=', 'v.id')
            ->join('products as p', 'v.product_id', '=', 'p.id')
            ->where('goal_groups.id', $id)
            ->select(
            'pgg.id as item_id',
                'goal_groups.name',
                DB::raw("CASE WHEN v.name != 'DUMMY' THEN CONCAT(p.name, ' - ', v.name) ELSE p.name END as item_name"),
                DB::raw("'product' as goal_type")
            );

        $goals = $brandGoals
            ->unionAll($categoryGoals)
            ->unionAll($productGoals)
            ->get();


        $goals = $goals->filter(fn($g) => !empty($g->item_name));

    
        $goals = $goals->map(function ($group) use ($typeTranslations) {
            $group->type_translated = $typeTranslations[$group->goal_type] ?? ucfirst($group->goal_type);
            return $group;
        });
        $business_id = request()->session()->get('user.business_id');
        $users = User::forDropdown($business_id, false, false, false, true);
        return view('goals::goals.create')->with(compact('id', 'goals','users'));
    }
    /**
     * Store a newly created goal without assign it to user.
     * @param Request $request
     * @return Renderable
     */
    public function storeGoal(Request $request,$id)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
           $group_id=$id;
           $user_id=$request->user_id;

            foreach($request->goals as $goal ){
              
                if($goal['goal_type']== 'brand'){
                    
                    $goalCreated=Goal::create([
                        'user_id'=>$user_id,
                        'group_id'=>$group_id,
                        'brand_amount'=> $goal['amount'],
                        'brand_group_id'=> $goal['item_id'],
                        'created_by' => auth()->user()->id,
                        'reward_amount' => $goal['reward_amount']
                    ]);
                    if(isset($goal['child_goals'])){
                        foreach($goal['child_goals'] as $child ){
                           
                            Goal::create([
                                'user_id' => $user_id,
                                'group_id' => $group_id,
                                'brand_amount' => $child['amount'],
                                'brand_group_id' => $goal['item_id'],
                                'parent_id'=> $goalCreated->id,
                                'created_by' => auth()->user()->id,
                                'reward_amount' => $child['reward_amount']
                            ]);
                        }
                    }
                }elseif($goal['goal_type'] == 'category'){
                    
                    $goalCreated = Goal::create([
                        'user_id' => $user_id,
                        'group_id' => $group_id,
                        'category_amount' => $goal['amount'],
                        'category_group_id' => $goal['item_id'],
                        'created_by' => auth()->user()->id,
                        'reward_amount' =>  $goal['reward_amount']
                    ]);
                    if (isset($goal['child_goals'])) {
                        foreach ($goal['child_goals'] as $child) {

                            Goal::create([
                                'user_id' => $user_id,
                                'group_id' => $group_id,
                                'category_amount' => $child['amount'],
                                'category_group_id' => $goal['item_id'],
                                'parent_id' => $goalCreated->id,
                                'created_by' => auth()->user()->id,
                                'reward_amount' => $child['reward_amount']
                            ]);
                        }
                    }
                }elseif($goal['goal_type'] == 'product'){
                    
                    if($goal['amount_type'] == 'quantity'){
                        
                        $goalCreated = Goal::create([
                            'user_id' => $user_id,
                            'group_id' => $group_id,
                            'product_quantity' => $goal['amount'],
                            'products_group_goal_id' => $goal['item_id'],
                            'created_by' => auth()->user()->id,
                            'reward_amount' =>  $goal['reward_amount']
                        ]);
                    }else{
                        $goalCreated = Goal::create([
                            'user_id' => $user_id,
                            'group_id' => $group_id,
                            'product_amount' => $goal['amount'],
                            'products_group_goal_id' => $goal['item_id'],
                            'created_by' => auth()->user()->id,
                            'reward_amount' =>  $goal['reward_amount']
                        ]);
                    }
                   
                    if (isset($goal['child_goals'])) {
                        foreach ($goal['child_goals'] as $child) {
                            $amount_type_key = 'product_amount';
                            if ($child['amount_type'] == 'quantity') {
                                Goal::create([
                                    'user_id' => $user_id,
                                    'group_id' => $group_id,
                                    'product_quantity' => $child['amount'],
                                    'products_group_goal_id' => $goal['item_id'],
                                    'parent_id' => $goalCreated->id,
                                    'created_by' => auth()->user()->id,
                                    'reward_amount' => $child['reward_amount']
                                ]);
                            }else{
                                Goal::create([
                                    'user_id' => $user_id,
                                    'group_id' => $group_id,
                                    'product_amount' => $child['amount'],
                                    'products_group_goal_id' => $goal['item_id'],
                                    'parent_id' => $goalCreated->id,
                                    'created_by' => auth()->user()->id,
                                    'reward_amount' => $child['reward_amount']
                                ]);
                            }
                            
                        }
                    }
                }
           }
           
           DB::commit();
            $output = [
                'success' => true,
                'msg' => __('contact.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
           
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }


    public function createProductGoal(Request $request)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }
        $id = $request->id;
        return view('goals::goals.create-product')->with(compact('id'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
            ->active()
            ->whereNull('variations.deleted_at')
            ->where(function ($query) use ($q) { 
                $query->where('products.name', 'like', '%'. $q.'%');
            })
       ->select(
            'variations.id as id',
            DB::raw("
            CASE 
                WHEN variations.name = 'Dummy' THEN products.name
                ELSE CONCAT(products.name, ' - ', variations.name)
            END as name
        ")
           
        )
            ->limit(10)
            ->get(['id', 'name']);
         
        return response()->json(['products' => $products]);
    }
    public function createBrandGoal(Request $request)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }
        $brands=Brands::pluck('name','id');
        $id=$request->id;
        return view('goals::goals.create-brand')->with(compact('id','brands'));
    }
    public function createCategoryGoal(Request $request)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }
        $categories = Category::pluck('name', 'id');
        $id = $request->id;
        return view('goals::goals.create-category')->with(compact('id', 'categories'));
        
    }
    /**
     * Store a newly created goal without assign it to user.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('goals.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $groupId = $request->id; // take existing group id from request
            $type = $request->type;

            if ($type == 'brand' && !empty($request->brand_id)) {
                $brandGoal = new BrandGroupGoal();  
                $brandGoal->brand_id = $request->brand_id;
                $brandGoal->group_id = $groupId;
                $brandGoal->save();
            }

            if ($type == 'category' && !empty($request->category_id)) {
                $categoryGoal = new CategoryGroupGoal();  
                $categoryGoal->category_id = $request->category_id;
                $categoryGoal->group_id = $groupId;
                $categoryGoal->save();
            }
            if ($type == 'product' && !empty($request->product_id)) {
                $productGoal = new ProductGroupGoal();
                $productGoal->variation_id = $request->product_id;
                $productGoal->group_id = $groupId;
                $productGoal->save();
            }
            
            $output = [
                'success' => true,
                'msg' => __('contact.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            
        }
        return redirect('goals/groups')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('goals::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('goals::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}

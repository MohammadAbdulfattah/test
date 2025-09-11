<?php

namespace Modules\Goals\Http\Controllers;

use Datatables;
use App\User;
use App\Utils\ProductUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Goals\Entities\GoalGroup;

class GroupController extends Controller
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
    public function index()
    {
        if (! auth()->user()->can('goal_group.view')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $user = auth()->user();


            $groups = GoalGroup::leftJoin('users as u', 'goal_groups.created_by', '=', 'u.id')
                ->leftJoin('users as s', 'goal_groups.supervisor_id', '=', 's.id')
                ->select(
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    DB::raw("CONCAT(COALESCE(s.surname, ''),' ',COALESCE(s.first_name, ''),' ',COALESCE(s.last_name,'')) as supervisor"),
                    'goal_groups.name',
                    'goal_groups.start_date',
                    'goal_groups.end_date',
                    'goal_groups.id'
                );


            if ($user->role_name != 'Admin') {
                $groups->where('supervisor_id', $user->id);
            }

            return DataTables::of($groups)

                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '<div class="btn-group">
                         <button type="button" class="btn-modal tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle" 
                             data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                             </span>
                         </button>
                         <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                        if (auth()->user()->can('group_details.view')) {
                            $html .= '<li><a href="' . route('group.show', ['id' => $row->id]) . '"  "><i class="glyphicon glyphicon-edit"></i> ' . __('goals::goals.group_details') . '</a> </li>';
                        }
                        if (auth()->user()->can('goals.view')) {
                            $html .= '<li><a href="' . route('goal.index', ['id' => $row->id]) . '"  "><i class="glyphicon glyphicon-edit"></i> ' . __('goals::goals.goal_group_details') . '</a> </li>';
                        }
                        if (auth()->user()->can('goals.create')) {
                            $html .= '<li><a href="' . route('product-goal.create', ['id' => $row->id]) . '"  class="goal_create_modal_button" data-toggle="modal" data-target="#goal_create_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('goals::goals.add_product_goal') . '</a> </li>';
                        }
                        if (auth()->user()->can('goals.create')) {
                            $html .= '<li><a href="' . route('brand-goal.create', ['id' => $row->id]) . '"  class="goal_create_modal_button" data-toggle="modal" data-target="#goal_create_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('goals::goals.add_brand_goal') . '</a></li> ';
                        }


                        if (auth()->user()->can('goals.create')) {
                            $html .= '<li><a href="' .  route('category-goal.create', ['id' => $row->id]) . '"  class="goal_create_modal_button" data-toggle="modal" data-target="#goal_create_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('goals::goals.add_category_goal') . '</a></li> ';
                            
                        }
                        if (auth()->user()->can('goal_group.update')) {
                            $html .= '<li><a href="' . route('goal_group.edit', ['id' => $row->id]) . '" ><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li> ';
                        }
                        if (auth()->user()->can('goal_group.delete')) {
                            $html .= '<li><a href="#" data-href="' . action([\Modules\Goals\Http\Controllers\GroupController::class, 'destroy'], [$row->id]) . '" class="delete_group"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</a></li>';
                        }



                        return $html;
                    }
                )
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('goals::groups.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (! auth()->user()->can('goal_group.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $users = User::forDropdown($business_id, false, false, false, true);
        return view('goals::groups.create', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('goal_group.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        try {
            $input = $request->only([
                'name',
                'supervisor_id',
                'start_date',
                'end_date',
            ]);
            $input['start_date'] = $this->productUtil->uf_date($input['start_date'], true);
            $input['end_date'] = $this->productUtil->uf_date($input['end_date'], true);
            $input['created_by'] = auth()->user()->id;

            GoalGroup::create($input);
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
        if (! auth()->user()->can('group_details.view')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
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
                    'goal_groups.id',
                    'goal_groups.name',
                    'b.name as item_name',
                    DB::raw("'brand' as type")
                );

            // Category goals
            $categoryGoals = DB::table('goal_groups')
                ->join('categories_group_goal as cgg', 'goal_groups.id', '=', 'cgg.group_id')
                ->join('categories as c', 'cgg.category_id', '=', 'c.id')
                ->where('goal_groups.id', $id)
                ->select(
                    'goal_groups.id',
                    'goal_groups.name',
                    'c.name as item_name',
                    DB::raw("'category' as type")
                );

            // Product goals
            $productGoals = DB::table('goal_groups')
                ->join('products_group_goal as pgg', 'goal_groups.id', '=', 'pgg.group_id')
                ->join('variations as v', 'pgg.variation_id', '=', 'v.id')
                ->join('products as p', 'v.product_id', '=', 'p.id')
                ->where('goal_groups.id', $id)
                ->select(
                    'goal_groups.id',
                    'goal_groups.name',
                    DB::raw("CASE WHEN v.name != 'DUMMY' THEN CONCAT(p.name, ' - ', v.name) ELSE p.name END as item_name"),
                    DB::raw("'product' as type")
                );

            $groups = $brandGoals
                ->unionAll($categoryGoals)
                ->unionAll($productGoals)
                ->get();

            
            $groups = $groups->filter(fn($g) => !empty($g->item_name));

        

            $groups = $groups->map(function ($group) use ($typeTranslations) {
                $group->type_translated = $typeTranslations[$group->type] ?? ucfirst($group->type);
                return $group;
            });
            return DataTables::of($groups)
                ->addColumn('brand_name', fn($row) => $row->type === 'brand' ? $row->item_name : null)
                ->addColumn('category_name', fn($row) => $row->type === 'category' ? $row->item_name : null)
                ->addColumn('product_name', fn($row) => $row->type === 'product' ? $row->item_name : null)
                ->rawColumns(['brand_name', 'category_name', 'product_name']) // only if needed (e.g. contains HTML)
                ->make(true);
        }
        return view('goals::groups.show')->with(compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (! auth()->user()->can('goal_group.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $users = User::forDropdown($business_id, false, false, false, true);
        $group=GoalGroup::findOrFail($id);
        return view('goals::groups.edit',compact('users','group'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('goal_group.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        try {
            $input = $request->only([
                'name',
                'supervisor_id',
                'start_date',
                'end_date',
            ]);
            $input['start_date'] = $this->productUtil->uf_date($input['start_date'], true);
            $input['end_date'] = $this->productUtil->uf_date($input['end_date'], true);
           

            $group= GoalGroup::find($id);
            $group->name= $input['name'];
            $group->start_date= $input['start_date'];
            $group->supervisor_id= $input['supervisor_id'];
            $group->end_date= $input['end_date'];
            $group->save();
            $output = [
                'success' => true,
                'msg' => __('contact.updated_success'),
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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('goal_group.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        try {
          
            $group = GoalGroup::findOrFail($id);
            $group->delete();
            $output = [
                'success' => true,
                'msg' => __('contact.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return response()->json($output);
    }
}

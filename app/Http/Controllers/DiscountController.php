<?php

namespace App\Http\Controllers;

use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Discount;
use App\SmartRootDiscount;
use App\TypeSmartRootDiscount;
use App\StatusOfSmartRootDiscount;
use App\DiscountStatus;
use App\Unit;
use App\Product;
use App\Variation;
use App\User;
use App\FinalDiscount;
use App\SubResult;
use App\SubCondition;
use App\SellingPriceGroup;
use App\Utils\Util;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DiscountController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;
    protected $businessUtil;
    protected $transactionUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil , BusinessUtil $businessUtil , TransactionUtil $transactionUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $discounts = Discount::where('discounts.business_id', $business_id)
                        ->leftjoin('brands as b', 'discounts.brand_id', '=', 'b.id')
                        ->leftjoin('categories as c', 'discounts.category_id', '=', 'c.id')
                        ->leftjoin('business_locations as l', 'discounts.location_id', '=', 'l.id')
                        ->select(['discounts.id', 'discounts.name', 'starts_at', 'ends_at',
                            'priority', 'b.name as brand', 'c.name as category', 'l.name as location', 'discounts.is_active', 'discounts.discount_amount', 'discount_type', ])
                        ->with(['variations', 'variations.product', 'variations.product_variation']);

            return Datatables::of($discounts)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\DiscountController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".discount_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\DiscountController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_discount_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        @if($is_active != 1)
                            &nbsp;
                            <button data-href="{{action(\'App\Http\Controllers\DiscountController@activate\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent activate-discount"><i class="fa fa-circle-o"></i> @lang("lang_v1.reactivate")</button>
                        @endif
                        '
                )
                ->addColumn('row_select', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="'.$row->id.'">';
                })
                ->addColumn('products', function ($row) {
                    $products = [];

                    foreach ($row->variations as $variation) {
                        $products[] = $variation->full_name;
                    }

                    return '<span class="label bg-primary">'.implode('</span>, <span class="label bg-primary">', $products).'</span>';
                })
                ->editColumn('name', function ($row) {
                    $name = $row->is_active != 1 ? $row->name.' <span class="label bg-yellow">'.__('lang_v1.inactive').'</sapn>' : $row->name;

                    return $name;
                })
                ->editColumn('starts_at', function ($row) {
                    $starts_at = ! empty($row->starts_at) ? $this->commonUtil->format_date($row->starts_at->toDateTimeString(), true) : '';

                    return $starts_at;
                })
                ->editColumn('ends_at', function ($row) {
                    $ends_at = ! empty($row->ends_at) ? $this->commonUtil->format_date($row->ends_at->toDateTimeString(), true) : '';

                    return $ends_at;
                })
                ->editColumn('discount_amount', '{{@num_format($discount_amount)}} @if($discount_type == "percentage") % @endif')
                ->rawColumns(['name', 'action', 'row_select', 'products'])
                ->make(true);
        }

        return view('discount.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');

        $brands = Brands::forDropdown($business_id);

        $locations = BusinessLocation::forDropdown($business_id);

        $price_groups = SellingPriceGroup::forDropdown($business_id);

        return view('discount.create')
                ->with(compact('categories', 'brands', 'locations', 'price_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'brand_id', 'category_id',
                'location_id', 'priority', 'discount_type', 'discount_amount', 'spg', ]);

            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $variation_ids = $request->input('variation_ids');

            if (! empty($variation_ids)) {
                unset($input['brand_id']);
                unset($input['category_id']);
            }

            $input['starts_at'] = $request->has('starts_at') ? $this->commonUtil->uf_date($request->input('starts_at'), true) : null;
            $input['ends_at'] = $request->has('ends_at') ? $this->commonUtil->uf_date($request->input('ends_at'), true) : null;
            $checkboxes = ['is_active', 'applicable_in_cg'];

            foreach ($checkboxes as $checkbox) {
                $input[$checkbox] = $request->has($checkbox) ? 1 : 0;
            }

            $discount = Discount::create($input);

            if (! empty($variation_ids)) {
                $discount->variations()->sync($variation_ids);
            }

            $output = ['success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $discount = Discount::where('business_id', $business_id)
                            ->with(['variations', 'variations.product', 'variations.product_variation'])
                            ->find($id);

            $starts_at = $this->commonUtil->format_date($discount->starts_at->toDateTimeString(), true);
            $ends_at = $this->commonUtil->format_date($discount->ends_at->toDateTimeString(), true);

            $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');

            $brands = Brands::forDropdown($business_id);

            $locations = BusinessLocation::forDropdown($business_id);

            $variations = [];

            foreach ($discount->variations as $variation) {
                $variations[$variation->id] = $variation->full_name;
            }
            //dd($variations);
            $price_groups = SellingPriceGroup::forDropdown($business_id);

            return view('discount.edit')
                ->with(compact('discount', 'starts_at', 'ends_at', 'brands', 'categories', 'locations', 'variations', 'price_groups'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'brand_id', 'category_id',
                    'location_id', 'priority', 'discount_type', 'discount_amount', 'spg', ]);

                $business_id = $request->session()->get('user.business_id');

                $input['starts_at'] = $request->has('starts_at') ? $this->commonUtil->uf_date($request->input('starts_at'), true) : null;
                $input['ends_at'] = $request->has('ends_at') ? $this->commonUtil->uf_date($request->input('ends_at'), true) : null;
                $checkboxes = ['is_active', 'applicable_in_cg'];

                foreach ($checkboxes as $checkbox) {
                    $input[$checkbox] = $request->has($checkbox) ? 1 : 0;
                }

                $variation_ids = $request->input('variation_ids');

                if (! empty($variation_ids)) {
                    unset($input['brand_id']);
                    unset($input['category_id']);
                }

                $discount = Discount::where('business_id', $business_id)
                            ->find($id);

                $discount->update($input);

                $discount->variations()->sync($variation_ids);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $discount = Discount::where('business_id', $business_id)->findOrFail($id);
                $discount->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Mass deactivates discounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDeactivate(Request $request)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (! empty($request->input('selected_discounts'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_discounts = explode(',', $request->input('selected_discounts'));

                DB::beginTransaction();

                Discount::where('business_id', $business_id)
                            ->whereIn('id', $selected_discounts)
                            ->update(['is_active' => 0]);

                DB::commit();
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.deactivated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Activates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        if (! auth()->user()->can('discount.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                Discount::where('id', $id)
                    ->where('business_id', $business_id)
                    ->update(['is_active' => 1]);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }   
    
    
    public function smart_root_discounts(){
       if (!auth()->user()->can('smart_root_discount.access')) {
                    abort(403, 'Unauthorized action.');
                }
        $business_id = request()->session()->get('user.business_id');
        $type_smart_root_discounts = TypeSmartRootDiscount::get();
         $locations = BusinessLocation::forDropdown($business_id);
        $users = User::forDropdown($business_id);

       if (request()->ajax()) {
            
    
             $userLogedIn = auth()->user();
            $query = SmartRootDiscount::where('created_by', $userLogedIn->id)
                           ->with(['type_smart_root_discount', 'business', 'user']);

             if (request()->has('type_smart_root_discount_id') && !empty(request('type_smart_root_discount_id'))) {
                    $query->where('type_smart_root_discount_id', request('type_smart_root_discount_id'));
                }
             if (request()->has('business_id') && !empty(request('business_id'))) {
                    $query->where('business_id', request('business_id'));
                }
             if (request()->has('user_id') && !empty(request('user_id'))) {
                    $query->where('user_id', request('user_id'));
                }
            if (request()->has('start_date') && request()->has('end_date')) {
                try {
                    $start_date = Carbon::createFromFormat('Y-m-d', request('start_date'))->startOfDay();
                    $end_date = Carbon::createFromFormat('Y-m-d', request('end_date'))->endOfDay();
                    
                    $query->where(function($q) use ($start_date, $end_date) {
                        $q->whereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date])
                        ->orWhere(function($q) use ($start_date, $end_date) {
                            $q->where('start_date', '<=', $start_date)
                                ->where('end_date', '>=', $end_date);
                        });
                    });
                } catch (\Exception $e) {
                    \Log::error("Date parsing error: " . $e->getMessage());
                }
            }

            $smartRootDiscounts = $query->get();
   
             return Datatables::of($smartRootDiscounts)
             ->addColumn('action', function ($row) {
                $editUrl = route('editSmartRootDiscount', $row->id);
                $showUrl = route('showSmartRootDiscount', $row->id);
                $deleteUrl = route('deleteSmartRootDiscount', $row->id);
                $activeUrl = route('active_smart_discount', $row->id);
                $messActiveUrl = route('messActive_smart_discount', $row->id);

                $editButton = $showButton = $deleteButton = '';

                if (auth()->user()->can('smart_root_discount.edit')) {
                    $editButton = '<a href="'.$editUrl.'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary edit_discount_button">'.__('messages.edit').'</a>';
                }

                if (auth()->user()->can('smart_root_discount.access')) {
                    $showButton = '<a href="'.$showUrl.'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-success show_discount_button">'.__('messages.show').'</a>';
                }

                if (auth()->user()->can('smart_root_discount.destroy')) {
$deleteButton = '<a href="#" data-href="'.$deleteUrl.'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error delete_discount_button" data-toggle="modal" data-target="#deleteDiscountModal">'.__('messages.delete').'</a>';                }

                if($row->is_active == 1) {
                    $messActiveButton = '<button data-url="'.$messActiveUrl.'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error toggle-discount-status">'.__('messages.messActive').'</button>';
                    return $editButton.' '.$deleteButton.' '.$showButton.' '.$messActiveButton;
                } else {
                    $activeButton = '<button data-url="'.$activeUrl.'" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error toggle-discount-status">'.__('messages.active').'</button>';
                    return $editButton.' '.$deleteButton.' '.$showButton.' '.$activeButton;
                }
            })
             ->addColumn('row_select', function ($row) {
                 return '<input type="checkbox" class="row-select" value="'.$row->id.'">';
             })
             ->addColumn('discount_name', function ($row) {
                 return $row->name;
             })
             ->addColumn('discount_type', function ($row) {
                 return $row->type_smart_root_discount ? $row->type_smart_root_discount->name : 'N/A';
             })
             ->addColumn('discount_start_date', function ($row) {
                 return $row->start_date;
             })
             ->addColumn('discount_end_date', function ($row) {
                 return $row->end_date;
             })
            ->addColumn('created_by', function ($row) {
                 return $row->created_by_user->username;
             })
             ->rawColumns(['action','created_by', 'row_select', 'discount_name', 'discount_type', 'discount_start_date', 'discount_end_date'])
             ->make(true);
       }

        return view('smart_root_discount.index',compact('type_smart_root_discounts','locations','users'));
    }

    public function active_smart_discount($id){
           if (request()->ajax()) {
            try {
                SmartRootDiscount::where('id', $id)
                    ->update(['is_active' => 1]);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

      public function messActive_smart_discount($id){
           if (request()->ajax()) {
            try {
                SmartRootDiscount::where('id', $id)
                    ->update(['is_active' => 0]);

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function create_smart_root_discount(){
        if (!auth()->user()->can('dsmart_root_discount.create')) {
                    abort(403, 'Unauthorized action.');
         }
        $business_id = request()->session()->get('user.business_id');
        $locations = BusinessLocation::forDropdown($business_id);
        $users = User::where('business_id',$business_id)->get();
        //$users = $users->prepend('All', '0');
        $discountTypes = TypeSmartRootDiscount::get();
        $discountStatus = DiscountStatus::get();
        $default_datetime = $this->businessUtil->format_date('now', true);
        $units = Unit::where('business_id', $business_id)->get(); 
        //dd($number);
        //dd($default_datetime);
        return view('smart_root_discount.create',compact('locations','users','discountTypes','discountStatus','default_datetime','units'));
    }

            public function store_smart_discount(Request $request)
            {
                
                $userLoggedIn = auth()->user();
                
                try {
                    // Validate the main fields
                    $validatedData = $request->validate([
                        'name' => 'required|string|max:255',
                        'business_id' => 'required|array',
                        'business_id.*' => 'exists:business_locations,id',
                        'user_id' => 'required|array',
                           'user_id.*' => function ($attribute, $value, $fail) {
                // Allow 0 (for "All Users") or check if user exists
                if ($value != 0 && !User::where('id', $value)->exists()) {
                    $fail('The selected user is invalid.');
                }
            },
                        'type_smart_root_discount_id' => 'required|exists:type_smart_root_discount,id',
                    ], [
                        'name.required' => 'The name field is required.',
                        'start_date.required' => 'The start date field is required.',
                        'end_date.required' => 'The end date field is required.',
                        'business_id.required' => 'The business id field is required.',
                        'user_id.required' => 'The user id field is required.',
                        'type_smart_root_discount_id.required' => 'The type smart root discount id field is required.',
                    ]);
                    
                    // Get the arrays from the request
                    $businessIds = $request->input('business_id');
                    $userIds = $request->input('user_id');
                    
                    // Handle "All Users" case (0 means all users)
                    if (in_array(0, $userIds)) {
                        $userIds = User::where('business_id', request()->session()->get('user.business_id'))
                            ->pluck('id')
                            ->toArray();
                    }
                    
                    $typeDiscount = $request->input('type_smart_root_discount_id');
                    
                    // Create a single SmartRootDiscount record with JSON arrays
                    $smartRootDiscount = SmartRootDiscount::create([
                        'name' => $validatedData['name'],
                        'start_date' => Carbon::parse($request->input('start_date'))->format('Y-m-d H:i:s'),
                        'end_date' => Carbon::parse($request->input('end_date'))->format('Y-m-d H:i:s'),
                        'business_id' => json_encode($businessIds), // Store as JSON array
                        'user_id' => json_encode($userIds), // Store as JSON array
                        'created_by' => $userLoggedIn->id,
                        'type_smart_root_discount_id' => $validatedData['type_smart_root_discount_id'],
                    ]);
                    
                    // Handle type-specific discount data
                    if ($typeDiscount == 1) {
                        $additionalData = $request->validate([
                            'invoice_amount' => 'required|array',
                            'invoice_amount.*' => 'numeric',
                            'discount_amount' => 'required|array',
                            'discount_amount.*' => 'numeric',
                            'discount_status_id' => 'required|array',
                            'discount_status_id.*' => 'exists:discount_status,id',
                        ]);
                        
                        foreach ($additionalData['invoice_amount'] as $key => $invoiceAmount) {
                            StatusOfSmartRootDiscount::create([
                                'smart_root_discount_id' => $smartRootDiscount->id,
                                'invoice_amount' => $invoiceAmount,
                                'discount_amount' => $additionalData['discount_amount'][$key],
                                'discount_status_id' => $additionalData['discount_status_id'][$key],
                            ]);
                        }
                    }
                    
                    if ($typeDiscount == 2 || $typeDiscount == 3) {
                        $additionalData = $request->validate([
                            'final_discount_ids' => 'required|array',
                            'final_discount_ids.*' => 'required|exists:final_discount,id',
                        ]);
                        
                        // Handle both array and comma-separated string input
                        $finalDiscountIds = $additionalData['final_discount_ids'];
                        if (is_string($finalDiscountIds[0])) {
                            $finalDiscountIds = array_unique(array_filter(
                                array_map('intval', explode(',', $finalDiscountIds[0]))
                            ));
                        }
                        
                        foreach ($finalDiscountIds as $finalDiscountId) {
                            StatusOfSmartRootDiscount::create([
                                'smart_root_discount_id' => $smartRootDiscount->id,
                                'final_discount_id' => $finalDiscountId,
                            ]);
                        }
                    }
       
              $output = [
            'success' => true,
            'msg' => __('lang_v1.added_success'),
            'redirect_url' => route('smart_root_discounts') // Add this line
        ];
    } catch (\Exception $e) {
        \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

        $output = [
            'success' => false,
            'msg' => __('messages.something_went_wrong'),
        ];
    }
             return $output;
            }

        public function editSmartRootDiscount($id)
        {
            if (!auth()->user()->can('smart_root_discount.edit')) {
                    abort(403, 'Unauthorized action.');
                }
            $discount = SmartRootDiscount::with(['business', 'user'])->findOrFail($id);
            $statusDiscount = StatusOfSmartRootDiscount::where('smart_root_discount_id', $discount->id)->get();
            
            // Initialize collections with relationship preservation
           // $finalDiscountsWithRelations = collect();
            $finalDiscountsWithRelations3 = collect();

            // Get all final discounts with their related conditions and results
            $discounts = collect();
            foreach($statusDiscount as $statusOfSmartRootDiscount) {
                $finalDiscountsWithRelations = FinalDiscount::whereHas('status_of_smart_root_discounts', function($q) use($statusOfSmartRootDiscount) {
                        $q->where('id', $statusOfSmartRootDiscount->id);
                    })->get();
            }

            //dd($statusDiscount);
           // dd($smartDiscount->type_smart_root_discount_id);
            // foreach ($discounts as $discountItem) {
               
            //     if($discount->type_smart_root_discount_id == 2) {
                   
            //         $discountItem->subConditions = !empty($discountItem->sub_condition_ids) 
            //             ? SubCondition::with(['varition.product', 'unit'])
            //             ->whereIn('id', json_decode($discountItem->sub_condition_ids, true))
            //             ->get()
            //             : collect();
            //         //dd($discountItem->subConditions->varition);
            //         $discountItem->subResults = !empty($discountItem->sub_result_ids) 
            //             ? SubResult::with(['varition.product', 'unit'])
            //             ->whereIn('id', json_decode($discountItem->sub_result_ids, true))
            //             ->get()
            //             : collect();
                    
            //         $finalDiscountsWithRelations->push($discountItem);
            //     }
            //     // For type 3: include only conditions
            //     elseif($discount->type_smart_root_discount_id == 3) {
            //         $discountItem->subConditions = !empty($discountItem->sub_condition_ids) 
            //             ? SubCondition::with(['variation.product', 'unit'])
            //             ->whereIn('id', json_decode($discountItem->sub_condition_ids, true))
            //             ->get()
            //             : collect();
                    
            //         $finalDiscountsWithRelations3->push($discountItem);
            //     }
            // }

            //dd($finalDiscountsWithRelations);
            
            $business_id = request()->session()->get('user.business_id');
            
            $locations = BusinessLocation::forDropdown($business_id);
            $users = User::forDropdown($business_id, false);
            $discountTypes = TypeSmartRootDiscount::pluck('name', 'id');
            $discountStatus = DiscountStatus::get();
            $units = Unit::forDropdown($business_id);

            $start_date = $this->commonUtil->format_date($discount->start_date, true);
            $end_date = $this->commonUtil->format_date($discount->end_date, true);
            // Get selected business locations (assuming many-to-many relationship)
           $selectedBusinessLocationIds = json_decode($discount->business_id, true) ?? [];
            $selectedUserIds = json_decode($discount->user_id, true) ?? [];

            // Fetch models for pre-selection
            $businessLocations = BusinessLocation::whereIn('id', $selectedBusinessLocationIds)->get();
            $selectedUserModels = User::whereIn('id', $selectedUserIds)->get();

            $locations = BusinessLocation::pluck('name', 'id')->toArray();
            $users = User::pluck('username', 'id')->toArray();

            return view('smart_root_discount.edit', compact(
                'discount',
                'start_date',
                'end_date',
                'statusDiscount',
                'finalDiscountsWithRelations3',
                'finalDiscountsWithRelations',
                'locations',
                'users',
                'discountTypes',
                'discountStatus',
                'units',
                'businessLocations',
                'selectedUserModels'
            ));
        }
public function update_smart_discount(Request $request, $id)
{
    try {

        $discount = SmartRootDiscount::findOrFail($id);

        $input = $request->only(['name']);

        $business_id = $request->session()->get('user.business_id');

        $input['start_date'] = $request->has('start_date') ? $this->commonUtil->uf_date($request->input('start_date'), true) : null;
        $input['end_date'] = $request->has('end_date') ? $this->commonUtil->uf_date($request->input('end_date'), true) : null;
        
        //dd($input);
       

      
        if ($request->has('business_id')) {
            $input['business_id'] =  $request->input('business_id');
        }

        // Update users if needed
        if ($request->has('user_id')) {
             $input['user_id'] =  $request->input('user_id');
        }
          $discount->update($input);

            // 5. Call updateType1Discount() conditionally
        if ($discount->type_smart_root_discount_id == 1) {
            $this->updateType1Discount($request, $discount);
        }
             
            $output = ['success' => true,
            'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
             return $output;
     
}

            protected function updateType1Discount($request, $discount)
            {
                $detailIds = $request->input('smart_discount_ids', []);
                $invoiceAmounts = $request->input('invoice_amount', []);
                $discountAmounts = $request->input('discount_amount', []);
                $discountStatusIds = $request->input('discount_status_id', []);
                //dd($detailIds);
                foreach ($detailIds as $index => $detailId) {
                    if (
                        isset($invoiceAmounts[$index]) &&
                        isset($discountAmounts[$index]) &&
                        isset($discountStatusIds[$index])
                    ) {
                        StatusOfSmartRootDiscount::where('id', $detailId)
                            ->where('smart_root_discount_id', $discount->id) // ✅ Corrected here
                            ->update([
                                'invoice_amount' => $this->commonUtil->num_uf($invoiceAmounts[$index]),
                                'discount_amount' => $this->commonUtil->num_uf($discountAmounts[$index]),
                                'discount_status_id' => $discountStatusIds[$index],
                            ]);
                    }
                }
            }

    /**
     * Update Type 2 Discount (Buy X Get Y)
     */
    protected function updateType2Discount($request, $discount)
    {
        $conditionIds = $request->input('condition_ids', []);
        
        foreach ($conditionIds as $conditionId) {
            $condition = DiscountCondition::findOrFail($conditionId);
            
            // Update condition details
            if ($request->has("condition_detail_ids.$conditionId")) {
                $conditionDetailIds = $request->input("condition_detail_ids.$conditionId", []);
                $conditionProducts = $request->input("condition_product_search.$conditionId", []);
                $conditionQuantities = $request->input("condition_quantity.$conditionId", []);
                $conditionUnitIds = $request->input("condition_unit_id.$conditionId", []);
                
                foreach ($conditionDetailIds as $index => $detailId) {
                    DiscountConditionDetail::where('id', $detailId)
                        ->where('discount_condition_id', $conditionId)
                        ->update([
                            'variation_id' => $conditionProducts[$index],
                            'quantity' => $conditionQuantities[$index],
                            'unit_id' => $conditionUnitIds[$index],
                        ]);
                }
            }
            
            // Update result details
            if ($request->has("result_detail_ids.$conditionId")) {
                $resultDetailIds = $request->input("result_detail_ids.$conditionId", []);
                $resultProducts = $request->input("result_product_search.$conditionId", []);
                $resultQuantities = $request->input("result_quantity.$conditionId", []);
                $resultUnitIds = $request->input("result_unit_id.$conditionId", []);
                
                foreach ($resultDetailIds as $index => $detailId) {
                    DiscountResultDetail::where('id', $detailId)
                        ->where('discount_condition_id', $conditionId)
                        ->update([
                            'variation_id' => $resultProducts[$index],
                            'quantity' => $resultQuantities[$index],
                            'unit_id' => $resultUnitIds[$index],
                        ]);
                }
            }
        }
    }

    /**
     * Update Type 3 Discount (Product based fixed amount)
     */
    protected function updateType3Discount($request, $discount)
    {
        $conditionIds = $request->input('condition_ids', []);
        $discountAmounts = $request->input('discount_amount', []);
        $discountStatusIds = $request->input('discount_status_id', []);
        
        foreach ($conditionIds as $index => $conditionId) {
            $condition = DiscountCondition::findOrFail($conditionId);
            
            // Update condition details
            if ($request->has("condition_detail_ids.$conditionId")) {
                $conditionDetailIds = $request->input("condition_detail_ids.$conditionId", []);
                $conditionProducts = $request->input("condition_product_search.$conditionId", []);
                $conditionQuantities = $request->input("condition_quantity.$conditionId", []);
                $conditionUnitIds = $request->input("condition_unit_id.$conditionId", []);
                
                foreach ($conditionDetailIds as $detailIndex => $detailId) {
                    DiscountConditionDetail::where('id', $detailId)
                        ->where('discount_condition_id', $conditionId)
                        ->update([
                            'variation_id' => $conditionProducts[$detailIndex],
                            'quantity' => $conditionQuantities[$detailIndex],
                            'unit_id' => $conditionUnitIds[$detailIndex],
                        ]);
                }
            }
            
            // Update discount amount and status
            $condition->update([
                'discount_amount' => $this->commonUtil->num_uf($discountAmounts[$index]),
                'discount_status_id' => $discountStatusIds[$index],
            ]);
        }
    }

    public function deleteSmartRootDiscount($id)
    {
        if (!auth()->user()->can('smart_root_discount.destroy')) {
                    abort(403, 'Unauthorized action.');
                }
    
        $smartRootDiscount = SmartRootDiscount::findOrFail($id);
 
        StatusOfSmartRootDiscount::where('smart_root_discount_id', $id)->delete();
    
       
        $smartRootDiscount->delete();
    
        return redirect()->back()->with('success', __('messages.smart_root_discount_deleted_successfully'));
    }

       public function showSmartRootDiscount($id)
        {
            $smartRootDiscount = SmartRootDiscount::findOrFail($id);
            $statusOfSmartRootDiscounts = StatusOfSmartRootDiscount::where('smart_root_discount_id', $id)->get();
            
            // Initialize collections
            $finalDiscountsWithRelations = collect();
            $finalDiscountsWithRelations3 = collect();

            // Get all final discounts with their related conditions and results
            foreach($statusOfSmartRootDiscounts as $statusOfSmartRootDiscount) {
                $discounts = FinalDiscount::whereHas('status_of_smart_root_discounts', function($q) use($statusOfSmartRootDiscount) {
                    $q->where('id', $statusOfSmartRootDiscount->id);
                })->get();
                
                foreach ($discounts as $discount) {
                    if($smartRootDiscount->type_smart_root_discount_id == 2) {
                        $discount->subConditions = !empty($discount->sub_condition_ids) 
                            ? SubCondition::whereIn('id', json_decode($discount->sub_condition_ids, true))->get()
                            : collect();
                        
                        $discount->subResults = !empty($discount->sub_result_ids) 
                            ? SubResult::whereIn('id', json_decode($discount->sub_result_ids, true))->get()
                            : collect();
                        
                        $finalDiscountsWithRelations->push($discount);
                    }
                    elseif($smartRootDiscount->type_smart_root_discount_id == 3) {
                        $discount->subConditions = !empty($discount->sub_condition_ids) 
                            ? SubCondition::whereIn('id', json_decode($discount->sub_condition_ids, true))->get()
                            : collect();
                        
                        $finalDiscountsWithRelations3->push($discount);
                    }
                }
            }

            // Always return collections, never null
            $selectedBusinessLocations = collect(
                !empty($smartRootDiscount->business_id) 
                    ? json_decode($smartRootDiscount->business_id, true) 
                    : []
            );
            
            $selectedUsers = collect(
                !empty($smartRootDiscount->user_id) 
                    ? json_decode($smartRootDiscount->user_id, true) 
                    : []
            );

            $locations = BusinessLocation::pluck('name', 'id')->toArray();
            $users = User::pluck('username', 'id')->toArray();

            // Get models - returns empty collection if no matches
            $businessLocations = BusinessLocation::whereIn('id', $selectedBusinessLocations)->get();
            $selectedUserModels = User::whereIn('id', $selectedUsers)->get();

            return view('smart_root_discount.show', compact(
                'smartRootDiscount',
                'finalDiscountsWithRelations',
                'finalDiscountsWithRelations3',
                'statusOfSmartRootDiscounts',
                'selectedBusinessLocations',
                'selectedUsers',
                'locations',
                'users',
                'businessLocations',
                'selectedUserModels'
            ));
        }

        public function add_sub_conditions(Request $request)
        {
                
                $validatedData = $request->validate([
                    'product_id' => 'required|array',
                    'quantity' => 'required|array',
                    'unit_id' => 'required|array', 
                ]);

                $product_ids = $validatedData['product_id'];
                $quantities = $validatedData['quantity'];
                $unit_ids = $validatedData['unit_id'];

                $createdIds = [];

                foreach ($product_ids as $key => $product_id) {
                    $quantity = $quantities[$key] ?? null; 
                    $unit_id = $unit_ids[$key] ?? null;   

                    if ($product_id && $quantity && $unit_id) {
                        $sub_condition = SubCondition::create([
                            'product_id' => $product_id,
                            'quantity' => $quantity,
                            'unit_id' => $unit_id,
                        ]);

                    
                        $createdIds[] = $sub_condition->id;
                    }
                }

                return response()->json(['ids' => $createdIds]);
        }

        public function add_sub_results(Request $request)
        {
            $validatedData = $request->validate([
                'product_id' => 'required|array',
                'quantity' => 'required|array',
                'unit_id' => 'required|array', 
            ]);

            $product_ids = $validatedData['product_id'];
            $quantities = $validatedData['quantity'];
            $unit_ids = $validatedData['unit_id'];

            $createdIds = [];

            foreach ($product_ids as $key => $product_id) {
                $quantity = $quantities[$key] ?? null; 
                $unit_id = $unit_ids[$key] ?? null;   

                if ($product_id && $quantity && $unit_id) {
                    $sub_condition = SubResult::create([
                        'product_id' => $product_id,
                        'quantity' => $quantity,
                        'unit_id' => $unit_id,
                    ]);

                
                    $createdIds[] = $sub_condition->id;
                }
            }

            return response()->json(['ids' => $createdIds]);
        }

        public function add_final_discount(Request $request)
        {
            
            $validatedData = $request->validate([
                'condition_id' => 'required|array',
                'condition_id.*' => 'exists:sub_condition,id', 
                'result_id' => 'required|array',
                'result_id.*' => 'exists:sub_result,id', 
            ], [
                'condition_id.required' => 'Condition IDs are required.',
                'result_id.required' => 'Result IDs are required.',
            ]);

            try {
        
                $finalDiscount = FinalDiscount::create([
                    'sub_condition_ids' => json_encode($validatedData['condition_id']), 
                    'sub_result_ids' => json_encode($validatedData['result_id']), 
                ]);

                return response()->json([
                    'message' => 'Final discount saved successfully',
                    'id' => $finalDiscount->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error saving final discount: ' . $e->getMessage());
                return response()->json([
                    'message' => 'An error occurred while saving the final discount.',
                ], 500);
            }
        }

        public function add_final_discount3(Request $request)
        {
            
            $validatedData = $request->validate([
                'condition_id' => 'required|array',
                'condition_id.*' => 'exists:sub_condition,id', 
                'discount_amount' => 'required|numeric',
                'discount_status_id' => 'exists:discount_status,id',
            ], [
                'condition_id.required' => 'Condition IDs are required.',
                'discount_amount.required' => 'Result IDs are required.',
                'discount_status_id.required' => 'Discount Status are required.',
            ]);

            try {
        
                $finalDiscount = FinalDiscount::create([
                    'sub_condition_ids' => json_encode($validatedData['condition_id']), 
                    'discount_amount' => $validatedData['discount_amount'], 
                    'discount_status_id' => $validatedData['discount_status_id'], 
                ]);

                return response()->json([
                    'message' => 'Final discount saved successfully',
                    'id' => $finalDiscount->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error saving final discount: ' . $e->getMessage());
                return response()->json([
                    'message' => 'An error occurred while saving the final discount.',
                ], 500);
            }
        }

public function getSmartRootDiscountByUser($id) {

    if (!auth()->user()->can('show_smart_root_discounts_user')) {
                    abort(403, 'Unauthorized action.');
                }
    $smartRootDiscounts = SmartRootDiscount::whereJsonContains('user_id', (string)$id)
        ->with(['status_of_smart_root_discounts.final_discount', 'status_of_smart_root_discounts.discount_status'])
        ->get();
        
    $data = [];
    
    foreach ($smartRootDiscounts as $smartRootDiscount) {
        // For each status discount, create a separate row
        foreach ($smartRootDiscount->status_of_smart_root_discounts as $statusDiscount) {
            $item = [
                'smartRootDiscount' => $smartRootDiscount,
                'statusDiscount' => $statusDiscount,
                'subConditions' => collect(),
                'subResults' => collect()
            ];
            
            // Load sub conditions and results for type 2 and 3 discounts
            if (in_array($smartRootDiscount->type_smart_root_discount_id, [2, 3]) && $statusDiscount->final_discount) {
                // For type 2 and 3, load sub conditions
                if (!empty($statusDiscount->final_discount->sub_condition_ids)) {
                    $subConditions = SubCondition::whereIn('id', json_decode($statusDiscount->final_discount->sub_condition_ids, true))
                        ->with(['varition.product', 'unit'])
                        ->get();
                    $item['subConditions'] = $item['subConditions']->merge($subConditions);
                }
                
                // For type 2, load sub results
                if ($smartRootDiscount->type_smart_root_discount_id == 2 && !empty($statusDiscount->final_discount->sub_result_ids)) {
                    $subResults = SubResult::whereIn('id', json_decode($statusDiscount->final_discount->sub_result_ids, true))
                        ->with(['varition.product', 'unit'])
                        ->get();
                    $item['subResults'] = $item['subResults']->merge($subResults);
                }
            }
            
            $data[] = $item;
        }
    }
    
    return view('smart_root_discount.show_discounts_by_user', compact('data'));
}

    public function check_smart_root_discount(Request $request)
{
    try {
        $user_id = auth()->user()->id;
        $total_amount = $request->input('total_amount');
        $dateToday = Carbon::today();
        $business_id = request()->session()->get('user.business_id');


        $applicableDiscounts = StatusOfSmartRootDiscount::with(['smart_root_discount'])
            ->whereHas('smart_root_discount', function($q) use ($user_id, $dateToday, $business_id) {
                $q->whereJsonContains('user_id', (string)$user_id)
                  ->whereJsonContains('business_id', (string)$business_id)
                  ->where('type_smart_root_discount_id', 1)
                  ->whereDate('start_date', '<=', $dateToday)
                  ->whereDate('end_date', '>=', $dateToday)
                  ->where('is_active',1);
            })
            ->where('invoice_amount', '<=', $total_amount)
            ->orderBy('invoice_amount', 'desc')
            ->get();

     
        $appliedDiscount = null;

        foreach ($applicableDiscounts as $discount) {
            $appliedDiscount = $discount;
            break; 
        }

        if (!$appliedDiscount) {
            return response()->json([
                'success' => true,
                'has_discount' => false,
                'total_discount' => 0,
                'original_amount' => $total_amount,
                'applied_discount' => null
            ]);
        }

       
        $cumulative_discount = 0;
        if ($appliedDiscount->discount_status_id == 1) {
          
            $cumulative_discount += $appliedDiscount->discount_amount;
        } elseif ($appliedDiscount->discount_status_id == 2) {
           
            $cumulative_discount += ($total_amount * $appliedDiscount->discount_amount) / 100;
        }

        return response()->json([
            'success' => true,
            'has_discount' => true,
            'total_discount' => $cumulative_discount,
            'applied_discount' => [
                'id' => $appliedDiscount->id,
                'discount_amount' => $appliedDiscount->discount_amount,
                'discount_type' => $appliedDiscount->discount_status_id == 1 ? 'fixed' : 'percentage',
                'invoice_amount' => $appliedDiscount->invoice_amount,
                'smart_root_discount_name' => optional($appliedDiscount->smart_root_discount)->name,
                'smart_root_discount_id' => optional($appliedDiscount->smart_root_discount)->id
            ],
            'original_amount' => $total_amount
        ]);

    } catch (\Exception $e) {
        \Log::error("Smart Root Discount Error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error processing discount',
            'error' => $e->getMessage()
        ], 500);
    }
}

        public function check_smart_root_discount2(Request $request)
        {
            try {
                
                $user_id = auth()->user()->id;
                 $business_id = request()->session()->get('user.business_id');
                $variation_id = $request->input('variation_id');
                $quantity = $request->input('quantity');
                $unit_id = $request->input('unit_id');
                $applied_discount_ids = $request->input('applied_discount_ids', []);
                $pos_unit_price_inc_tax = $request->input('pos_unit_price_inc_tax');
                // Get the true original price from database
                $variation = Variation::with('product')->find($variation_id);
                $piecePrice = $variation->sell_price_inc_tax;
                $unit = Unit::find($unit_id);
                $original_price = $unit->base_unit_multiplier ? $piecePrice * $unit->base_unit_multiplier : $piecePrice;
        
                // Convert string to array if needed
                if (is_string($applied_discount_ids)) {
                    $applied_discount_ids = json_decode($applied_discount_ids, true) ?? [];
                }
                 $baseUnit = Unit::where('id',$unit_id)->first();
                    $matchingConditions2 = SubCondition::where('product_id', $variation_id)
                        ->where('quantity', '<=', $quantity)
                        ->where('unit_id', $unit_id)
                        ->get();
                //dd($baseUnit);
                 if($baseUnit->base_unit_id != null && $baseUnit->base_unit_multiplier != null && $matchingConditions2->isEmpty()){
                      $matchingConditions = SubCondition::where('product_id', $variation_id)
                        ->where('quantity', '=', $baseUnit->base_unit_multiplier)
                        ->where('unit_id', $baseUnit->base_unit_id)
                        ->get();
                 }else{
                        $matchingConditions = SubCondition::where('product_id', $variation_id)
                        ->where('quantity', '<=', $quantity)
                        ->where('unit_id', $unit_id)
                        ->get();
                 }
                //    $conditions = SubCondition::where('product_id', $variation_id)
                //     ->where('quantity', '<=', $baseUnit->base_unit_multiplier)
                //     ->where('unit_id', $baseUnit->base_unit_id)
                //     ->first();
                // if($conditions){
                //     $matchingConditions = SubCondition::where('product_id', $variation_id)
                //     ->where('quantity', '<=', $baseUnit->base_unit_multiplier)
                //     ->where('unit_id', $baseUnit->base_unit_id)
                //     ->get();
                // }else{
                  
                

                //}
        
              
                //dd($matchingConditions);
                if ($matchingConditions->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'has_discount' => false,
                        'discounted_unit_price' => $pos_unit_price_inc_tax,
                        'original_unit_price' => $original_price,
                        'message' => 'No matching conditions found'
                    ]);
                }
                $dateToday = Carbon::today(); 
                // Get all applicable discounts (not already applied)
                $discounts = StatusOfSmartRootDiscount::with(['final_discount', 'smart_root_discount'])
                    ->whereHas('smart_root_discount', function($q) use ($user_id ,$dateToday,$business_id) {
                        $q->whereJsonContains('user_id', (string)$user_id)
                           ->whereJsonContains('business_id', (string)$business_id)
                          ->where('type_smart_root_discount_id', 3)
                          ->whereDate('start_date', '<=', $dateToday)
                          ->whereDate('end_date', '>=', $dateToday)
                           ->where('is_active',1);
                    })
                    ->whereHas('final_discount', function($q) use ($matchingConditions) {
                        $q->where(function($query) use ($matchingConditions) {
                            foreach ($matchingConditions as $condition) {
                                $query->orWhereJsonContains('sub_condition_ids', (string)$condition->id);
                            }
                        });
                    })
                    ->whereNotIn('id', $applied_discount_ids)
                    ->get();
                    //dd($discounts);
                 $applied_discounts = [];
        $current_discounted_price = $original_price;
        $applied_discounts_ids = [];
        $lastDiscount = null;
        $lastDiscountId = null;
        
        foreach ($discounts as $discount) {
            if (!$discount->final_discount) continue;

            $finalDiscount = $discount->final_discount;
            
            // التحقق من تطابق الشرط
            $applies = false;
            $matchedCondition = null;
            
            $sub_condition_ids = is_array($finalDiscount->sub_condition_ids) 
                ? $finalDiscount->sub_condition_ids 
                : (json_decode($finalDiscount->sub_condition_ids, true) ?? []);
            
            foreach ($matchingConditions as $condition) {
                if (in_array((string)$condition->id, $sub_condition_ids)) {
                    $applies = true;
                    $matchedCondition = $condition;
                    break;
                }
            }
            
            if (!$applies) continue;

            // حساب مقدار الخصم
            $discount_amount = 0;
            if ($finalDiscount->discount_status_id == 1) {
                $discount_amount = $finalDiscount->discount_amount;
                $discount_type = 'fixed';
            } elseif ($finalDiscount->discount_status_id == 2) {
                $discount_amount = ($original_price * $finalDiscount->discount_amount) / 100;
                $discount_type = 'percentage';
            } else {
                continue;
            }
            
            // نحتفظ بآخر خصم فقط (بدلاً من تطبيق جميع الخصومات)
            $lastDiscount = [
                'condition_discount_amount' => $finalDiscount->discount_amount,
                'discount_amount' => $discount_amount,
                'discount_type' => $discount_type,
                'matched_condition' => [
                    'product_id' => $variation_id,
                    'unit_id' => $unit_id,
                    'quantity' => $matchedCondition->quantity
                ],
                'smart_root_discount_name' => $discount->smart_root_discount->name ?? null,
                'smart_root_discount_id' => $discount->smart_root_discount->id ?? null
            ];
            $lastDiscountId = $discount->id;
        }
        
        // نطبق آخر خصم فقط إذا وجد
        if ($lastDiscount) {
            $current_discounted_price = $original_price - $lastDiscount['discount_amount'];
            $applied_discounts = [$lastDiscount];
            $applied_discounts_ids = [$lastDiscountId];
        }
        
        // التأكد من أن السعر لا يقل عن الصفر
        $current_discounted_price = max($current_discounted_price, 0);
        
        return response()->json([
            'success' => true,
            'has_discount' => !empty($applied_discounts),
            'discounted_unit_price' => $current_discounted_price,
            'applied_discounts' => $applied_discounts,
            'applied_discounts_ids' => $applied_discounts_ids,
            'original_unit_price' => $original_price,
        ]);
    } catch (\Exception $e) {
        \Log::error("Smart Root Discount Error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error processing discount',
            'error' => $e->getMessage()
        ], 500);
    }
        }

        public function check_smart_root_discount3(Request $request)
        {
            try {
                $user_id = auth()->user()->id;
                $location_id = $request->input('location_id');
                 $business_id = request()->session()->get('user.business_id');
                $cart_items = array_filter($request->input('cart_items', []), function($item) {
                    return empty($item['is_free_product']);
                });
                //$cart_items = $request->input('cart_items');
                $dateToday = Carbon::today(); 
                // Get all active discounts for this user with smart_root_discount relationship
                $discounts = StatusOfSmartRootDiscount::with(['final_discount', 'smart_root_discount'])
                    ->whereHas('smart_root_discount', function($q) use ($user_id , $business_id,$dateToday) {
                        $q->whereJsonContains('user_id', (string)$user_id)
                          ->whereJsonContains('business_id', (string)$business_id)
                        ->where('type_smart_root_discount_id', 2)
                        ->whereDate('start_date', '<=', $dateToday)
                        ->whereDate('end_date', '>=', $dateToday)
                        ->where('is_active',1);
                    })
                    ->whereHas('final_discount')
                    ->get();
            
                $products_to_add = [];
                $applied_discounts_info = [];
                $applied_discounts_ids = [];
                
                foreach ($discounts as $discount) {
                    $final_discount = $discount->final_discount;
                    
                    // Handle both array and JSON string formats for sub_condition_ids
                    $sub_condition_ids = is_array($final_discount->sub_condition_ids) 
                        ? $final_discount->sub_condition_ids 
                        : (json_decode($final_discount->sub_condition_ids, true) ?? []);
                    //dd($sub_condition_ids);
                    // Check if all conditions for this discount are met in the cart
                    $conditions_met = $this->checkCartConditions($cart_items, $sub_condition_ids);
                        //dd($conditions_met);
                    if ($conditions_met) {
                        // Handle both array and JSON string formats for sub_result_ids
                        $sub_result_ids = is_array($final_discount->sub_result_ids) 
                            ? $final_discount->sub_result_ids 
                            : (json_decode($final_discount->sub_result_ids, true) ?? []);
                        
                        $sub_results = SubResult::whereIn('id', $sub_result_ids)->get();
                        //dd($sub_results);
                        foreach ($sub_results as $sub_result) {
                            $products_to_add[] = [
                                'variation_id' => $sub_result->product_id,
                                'quantity' => $sub_result->quantity,
                                'unit_id' => $sub_result->unit_id,
                                'unit_name' => $sub_result->unit->actual_name ?? null,
                                'discount_id' => $final_discount->id,
                                'is_free' => 1,
                                'is_free_product' => true,
                                'discount_name' => $discount->smart_root_discount->name ?? null,
                            ];
                        }

                        // Add discount information to applied discounts
                        $applied_discounts_info[] = [
                            'discount_id' => $discount->id,
                            'name' => $discount->smart_root_discount->name ?? null,
                            'type' => 'free_product',
                            'conditions_met' => $sub_condition_ids,
                            'products_added' => $sub_result_ids,
                            'smart_root_discount_name' => $discount->smart_root_discount->name ?? null
                        ];
                   
                    }
                    $applied_discounts_ids[] = $discount->id;
                }

                $productsWithDiscounts = [];
                foreach ($request->cart_items as $item) {
                    // Combine existing product discounts with any new cart-level discounts
                    $combinedDiscounts = array_unique(array_merge(
                        $item['applied_discount_ids'] ?? [],
                        $cartLevelDiscountIds ?? []
                    ));
                    
                    $productsWithDiscounts[] = [
                        //'row_id' => $item['row_id'],
                        'applied_discount_ids' => $combinedDiscounts
                    ];
                }
                //dd($applied_discounts_ids)
                return response()->json([
                    'success' => true,
                    'has_discount' => !empty($products_to_add),
                    'products_to_add' => $products_to_add,
                    'applied_discounts_ids' => $applied_discounts_ids,
                    'applied_discounts_info' => $applied_discounts_info,
                    'total_free_products' => count($products_to_add),
                    'is_free_product' => true
                ]);
                
            } catch (\Exception $e) {
                \Log::error("Smart Root Discount Error: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing discount',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        protected function checkCartConditions($cart_items, $condition_ids)
        {
            if (empty($condition_ids)) return false;
            
            $conditions = SubCondition::whereIn('id', $condition_ids)->get();
            //dd($cart_items);
            $condition_map = [];
            //dd($conditions);
            // Group conditions by product
            foreach ($conditions as $condition) {
                $key = $condition->product_id . '_' . $condition->unit_id;
                if (!isset($condition_map[$key])) {
                    $condition_map[$key] = 0;
                }
                $condition_map[$key] += $condition->quantity;
            }
            
            // Check each condition against the cart
            foreach ($condition_map as $key => $required_quantity) {
                list($product_id, $unit_id) = explode('_', $key);
                $cart_quantity = 0;
                
                // Sum quantities for this product in the cart
                foreach ($cart_items as $item) {
                    if ($item['variation_id'] == $product_id && $item['unit_id'] == $unit_id) {
                        $cart_quantity += $item['quantity'];
                    }
                }
                
                if ($cart_quantity < $required_quantity) {
                    return false;
                }
            }
            
            return true;
        }

        public function get_sub_units_by_products($id) {
            $variation = Variation::with(['product'])->find($id);
            
            if ($variation && $variation->product) {
                $subUnitIds = $variation->product->sub_unit_ids ?? $variation->product->unit_id;
                
       
                $subUnitIds = array_map('intval', (array)$subUnitIds);
     
                $subUnitIds = array_filter($subUnitIds);
                
                if (!empty($subUnitIds)) {
                    $unit = Unit::whereIn('id', $subUnitIds)->get();
                    return $unit;
                }
            }
            
            return [];
        }

           public function get_pecies_by_products($id) {
            $product = Product::whereHas('variations', function($q) use($id){
                                  $q->where('id',$id);
                                     })->first();
            
            //if ($variation && $variation->product) {
                // $subUnitIds = $variation->product->sub_unit_ids ?? $variation->product->unit_id;
                
       
                // $subUnitIds = array_map('intval', (array)$subUnitIds);
     
                // $subUnitIds = array_filter($subUnitIds);
                
               // if (!empty($subUnitIds)) {
                    $unit = Unit::where('id',$product->unit_id)
                                  ->whereNull('base_unit_id')
                                  ->whereNull('base_unit_multiplier')
                                  ->get();
                    return $unit;
                //}
            //}
            
            return [];
        }
}

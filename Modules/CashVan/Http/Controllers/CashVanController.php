<?php

namespace Modules\CashVan\Http\Controllers;

use App\BusinessLocation;
use App\Transaction;
use App\User;
use App\Utils\ProductUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Datatables;
use DB;
use Modules\CashVan\Entities\Van;
use Modules\CashVan\Entities\VanStock;

class CashVanController extends Controller
{

  
    protected $productUtil;
    protected $vanStockController;



    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil,VanStockController $vanStockController)
    {
        $this->productUtil = $productUtil;
        $this->vanStockController=$vanStockController;
        
    }
    /**
     * Display a listing of the cash vans.
     * @return Renderable
     */
    public function index()
    {
        if (! auth()->user()->can('cashvan.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
           $user= auth()->user();

            $permitted_locations = auth()->user()->permitted_locations();
            $vans =Van::
                join('users as u', 'vans.driver_id', '=', 'u.id')
                ->join('business as b','vans.business_id','=','b.id')
                ->select(
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as driver_name"),
                    'vans.name',
                    'vans.color',
                    'vans.license_plate',
                    'vans.id',
                    'vans.is_add_stock_order'
                )->where('vans.business_id',$business_id);
                if ($permitted_locations != 'all') {
                    $vans->whereHas('van_locations', function ($vans) use ($permitted_locations) {
                    $vans->whereIn('van_locations.business_location_id', $permitted_locations);
                    });
                } else {
                    $vans->with('van_locations');
                }
            $is_driver=false;
            if($user->role_name != 'Admin'){
                $exists=Van::where('driver_id',$user->id)->exists();
                if($exists){
                    $vans->where('vans.driver_id',$user->id);
                    $is_driver = true;
                }
            }
            $status_colors = [  
                'completed' => 'bg-green',
                'pending' => 'bg-red',
            ];
            $statuses = $this->stockTransferStatuses();
            return DataTables::of($vans)
            
                ->addColumn('name', function ($row) {
                    // Add a truck icon beside the name
                    $icon = '<i class="fas fa-truck inline-block mr-2"></i>'; // Truck icon
                    return $icon ." ". $row->name;
                })
                ->addColumn(
                    'van_locations',
                    function ($row) {
                        return $row->van_locations->implode('name', ', ');
                    }
                )
                ->addColumn('status', function ($row) use($statuses,$status_colors) {
                    $row->is_add_stock_order = $row->is_add_stock_order == true ? 'pending' : 'completed';
                   
                    $status_color = !empty($status_colors[$row->is_add_stock_order]) ? $status_colors[$row->is_add_stock_order] : 'bg-gray';
                    $status = '<span class="label ' . $status_color . '">' . $statuses[$row->is_add_stock_order] . '</span>';

                    return $status;
                })
                ->addColumn(
                    'action',
                    function ($row) use($is_driver) {

                    $html = '<div class="btn-group">
                        <button type="button" class="btn-modal tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle" 
                            data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                   
                    if (auth()->user()->can('van_stock.create')) {
                            $html .= '<li><a href="' . route('van_stock.update', ['id' => $row->id]) . '"  > <i class="glyphicon glyphicon-edit"></i>' . __('cashvan::stock.add_van_stock') . '</a> </li>';
                        }
                        if (auth()->user()->can('van_stock.delete')) {
                            $html .= '<li><a href="#" data-href="' . action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'destroy'], [$row->id]) . '" class="delete_van_stock" > <i class="glyphicon glyphicon-edit"></i>' . __('cashvan::stock.delete_van_stock') . '</a></li>';
                        }
                        if (auth()->user()->can('van_stock.view_history')) {
                            $html .= '<li><a href="' . action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'getVanHistory'], [$row->id]) . '"  > <i class="glyphicon glyphicon-edit"></i>' . __('cashvan::stock.view_history') . '</a></li>';
                        }
                        if (auth()->user()->can('cashvan.update')) {
                            $html .= '<li><a href="' . route('cashvan.edit',['id'=>$row->id]) . '" class="edit_cashvan_button" data-toggle="modal" data-target="#cashvan_edit_modal"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li> ';
                        }
                        if (auth()->user()->can('van_stock.view')) {
                            $html .= '<li><a href="#"  class="btn-modal" data-container=".view_modal" data-href="' . action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';                        
                        }
                        if (auth()->user()->can('cashvan.delete')) {
                            $html .= '<li><a href="#" data-href="' . action([\Modules\CashVan\Http\Controllers\CashVanController::class, 'destroy'], [$row->id]) . '" class="delete_van"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</a></li>';
                        }
                        if ($row->is_add_stock_order=="pending" && auth()->user()->can('stock.accept_on_order')  ) {
                            $html .= '<li><a href="' . action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'showStockOrder'], [$row->id])  . '"  ><i class="glyphicon glyphicon-edit"></i> ' . __('cashvan::stock.show_stock_order') . '</a></li>';
                        }
                      

                        return $html;
                }
                )
                ->removeColumn('id')
                ->rawColumns(['action', 'name','status'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $users=User::forDropdown($business_id, false, false, false, true);
        return view('cashvan::cashvan.index')->with(compact(['business_locations','users']));
    }

    private function stockTransferStatuses()
    {
        return [
            'pending' => __('cashvan::stock.pending_for_approve'),
            'completed' => __('restaurant.completed'),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('cashvan.create') ) {
            abort(403, 'Unauthorized action.');
        }

        return view('cashvan::cashvan.create') ;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('cashvan.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        try {
            $input = $request->only([
                'name',
                'color',
                'license_plate',
                'driver_id',  
            ]);

            $exist=Van::where('license_plate', $input['license_plate'])->exists();
            $has_van=Van::where("driver_id", $input['driver_id'])->exists();
            if($has_van){
                $output = [
                    'success' => false,
                    'msg' => __('cashvan::messages.user_exist'),
                ];

                return redirect('/cashvan')->with('status', $output);
            }
            if($exist){
                $output = [
                    'success' => false,
                    'msg' => __('cashvan::messages.license_exist'),
                ];

                return redirect('/cashvan')->with('status', $output);
            }
            
            $input['business_id']=$business_id;
            $van=Van::create($input);
            if($request->van_locations){
                $van->van_locations()->sync($request->van_locations);
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
        return redirect('cashvan')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('cashvan::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (! auth()->user()->can('cashvan.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $cashvan=Van::where('id',$id)->with('van_locations')->first();
        $business_locations = BusinessLocation::forDropdown($business_id);
        $users = User::forDropdown($business_id, false, false, false, true);
        return view('cashvan::cashvan.edit')->with(compact(['business_locations', 'users','cashvan']));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('cashvan.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        try {
            $input = $request->only([
                'name',
                'color',
                'license_plate',
                'driver_id',
            ]);

            $exist = Van::where('license_plate', $input['license_plate'])->where('id','!=',$id)->exists();
            $has_van = Van::where("driver_id", $input['driver_id'])->where('id', '!=', $id)->exists();
            if ($has_van) {
                $output = [
                    'success' => false,
                    'msg' => __('cashvan::messages.user_exist'),
                ];

                return redirect('cashvan')->with('status', $output);
            }
            if ($exist) {
                $output = [
                    'success' => false,
                    'msg' => __('cashvan::messages.license_exist'),
                ];

                return redirect('cashvan')->with('status', $output);
            }

            $input['business_id'] = $business_id;
            $van = Van::findOrFail($id);
            $van->update($input);

            if ($request->van_locations) {
                $van->van_locations()->sync($request->van_locations ?? []);
            }
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
        return redirect('cashvan')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('cashvan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');
                $data = [];
                $sell_transfer = Transaction::where('business_id', $business_id)
                    ->where('type', 'sell_transfer')
                    ->where('van_id', $id)
                    ->first();

                if (!$sell_transfer) {
                    Van::where('id', $id)->delete();
                    return
                         [
                            'success' => 1,
                            'msg' => __('lang_v1.deleted_success'),
                        ]
                    ;
                }
                $van_stock = VanStock::where('van_id', $id)->get();

                $products = [];

                foreach ($van_stock as $sell_line) {
                    $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, false, $id);
                    $product->quantity = 0;
                    $product->base_unit_multiplier = 1;
                    $products[] = $product;
                }

                if (!empty($products)) {
                    $data['products'] = $products;
                    $delete = $this->vanStockController->deleteStock($id, $data, $sell_transfer->location_id);

                    if ($delete) {
                        VanStock::where('van_id', $id)->delete();
                        Van::where('id', $id)->delete();
                        return
                            [
                                'success' => 1,
                                'msg' => __('lang_v1.deleted_success'),
                            ];
                    }
                }

                DB::commit();
            }

            
            
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        
    }
}

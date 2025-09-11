<?php

namespace App\Http\Controllers;

use App\CustomerSector;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Contact;

class CustomerSectorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $customer_sectors = CustomerSector::select('name','parent_id','id');
     
            return Datatables::of($customer_sectors)
                ->addColumn(
                    'action', function ($row) {
                        $html = '';
                 
                        $html .= '<button data-href="' . action([\App\Http\Controllers\CustomerSectorController::class, 'edit'], [$row->id]) . '" 
                        class="btn btn-xs btn-primary btn-modal" 
                        data-container=".sector_modal">
                        <i class="fa fa-edit"></i> ' . __("messages.edit") . '
                      </button> ';
        
                        $html .= '<a href="'.action([\App\Http\Controllers\CustomerSectorController::class, 'destroy'], [$row->id]).'" class="btn btn-xs btn-danger delete-sector"><i class="fas fa-trash"></i>'.__('messages.delete').'</a>';
                    

                        return $html;
                    }
                )
                ->editColumn('name', function ($row) {
                    if ($row->parent_id != 0) {
                        return '--'.$row->name;
                    } else {
                        return $row->name;
                    }
                })
          
           
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('sectors.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
  
        
        $customerSectors = CustomerSector::where('parent_id', null)
                        
                        ->select(['name', 'id'])
                        ->get();
                        $parent_sectors = [];
                        if (! empty(  $customerSectors)) {
                            foreach (  $customerSectors as   $customerSector) {
                                $parent_sectors[$customerSector->id] = $customerSector->name;
                            }
                        }
        return view('sectors.create')
                    ->with(compact('parent_sectors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->only(['name']);
            if (! empty($request->input('add_as_sub_sec')) && $request->input('add_as_sub_sec') == 1 && ! empty($request->input('parent_id'))) {
                $input['parent_id'] = $request->input('parent_id');
            } else {
                $input['parent_id'] = null;
            }
          

            $category = CustomerSector::create($input);
            $output = ['success' => true,
              
                'msg' => __('messages.added_success'),
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
     * Display the specified resource.
     *
     * @param  \App\CustomerSector  $customerSector
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerSector $customerSector)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomerSector  $customerSector
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
{
    $sector = CustomerSector::findOrFail($id);

    $customerSectors = CustomerSector::where('parent_id', null)
                        ->where('id', '!=', $id)
                        ->select(['name', 'id'])
                        ->get();

    $parent_sectors = [];
    foreach ($customerSectors as $customerSector) {
        $parent_sectors[$customerSector->id] = $customerSector->name;
    }

    return view('sectors.edit')->with(compact('sector', 'parent_sectors'));
}


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CustomerSector  $customerSector
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:customer_sectors,id',
        ]);
    
        try {
            $sector = CustomerSector::findOrFail($id);
            $sector->name = $request->name;
    
            if ($request->has('add_as_sub_sec') && $request->add_as_sub_sec == 1 && !empty($request->parent_id)) {
                $sector->parent_id = $request->parent_id;
            } else {
                $sector->parent_id = null;
            }
    
            $sector->save();
    
            return response()->json([
                'success' => true,
                'msg' => __('messages.updated_success')
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
    
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CustomerSector  $customerSector
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $sector = CustomerSector::findOrFail($id);
    

            CustomerSector::where('parent_id', $sector->id)
                ->update(['parent_id' => null]);
    
           
            Contact::where('sector_id', $sector->id)
                ->update(['sector_id' => null]);
    
            $sector->delete();
            return response()->json([
                'success' => true,
                'msg' => __('messages.deleted_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }
    

    public function getChildren($parent_id)
{
    $children = CustomerSector::where('parent_id', $parent_id)->select('id', 'name')->get();
    $children->prepend((object)[
        'id' => '',
        'name' => __('messages.none') 
    ]);


    return response()->json($children);
}

}

<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Gbs\Entities\Tag;
use App\Contact;
use App\User;
use App\TransactionPayment;
use App\Transaction;
use App\Utils\TransactionUtil;
use App\CustomerSector;
use DB;
use Yajra\DataTables\Facades\DataTables;
use App\CustomerGroup;
class GbsController extends Controller
{
    protected $transactionUtil;

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil)
    {


        $this->transactionUtil = $transactionUtil;
     
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        return view('gbs::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('gbs::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('gbs::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('gbs::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    public function tags()
    {
        if (request()->ajax()) {
            $tag = Tag::get();
            return \DataTables::of($tag)
            ->addColumn('color', function ($tag) {
                return '<div style="width: 30px; height: 20px; background-color: ' . e($tag->color) . '; border: 1px solid #ccc; border-radius: 4px;"></div>';
            })
            ->addColumn('action', function ($tag) {
                $actions = '';
            
                if (auth()->user()->can('gbs.edit_tags')) {
                    $actions .= '
                        <button class="btn btn-xs btn-primary edit-tag-btn" 
                                data-id="' . $tag->id . '" 
                                data-name="' . e($tag->name) . '" 
                                data-color="' . e($tag->color) . '">
                            <i class="fa fa-edit"></i> تعديل
                        </button>
                    ';
                }
                return $actions;
            })            
                ->rawColumns(['action', 'color'])
                ->make(true);
        }
     
        return view('gbs::tags.index');
    }
    public function tagStore(Request $request)
    {
       
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        $tag = new Tag();
        $tag->name = $request->name;
        $tag->color = $request->color;
        $tag->save();
   
        return response()->json([
            'success' => true,
            'message' =>  __('gbs::lang.tag_stored'),
            'tag' => $tag
        ]);
    }
    public function updateTag(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);
    
        $tag = Tag::findOrFail($id);
        $tag->name = $request->name;
        $tag->color = $request->color;
        $tag->save();
    
        return response()->json(['success' => true, 'message' => __('gbs::lang.tag_updated')]);

    }
  

    public function contactsMap()
    {
        $business_id = request()->session()->get('user.business_id');

        $customers = Contact::where('type', 'customer')
        ->where('latitude','!=','')
        ->where('longitude','!=','')
        ->select(['id', 'name', 'mobile', 'latitude', 'longitude','coordinates', 'sector_id'])
        ->with(['tags:id,name,color']) 
        ->limit(5000)->active()
        ->get();
        foreach ($customers as $customer) {
            $output = $this->transactionUtil->getLedgerDetails($customer->id, null, null); 
            $customer->total_invoice = $output['total_invoice'] ?? 0;
            $customer->balance_due = $output['all_balance_due'] ?? 0;
        }
            $customer_groups = CustomerGroup::forDropdown($business_id);
            $tags = Tag::pluck('name','id');
            $tags->prepend(__('gbs::lang.no_tag'), '');
            $users = User::forDropdown($business_id);
            $parent_sectors = CustomerSector::where('parent_id', null)->pluck('name', 'id')->prepend(__('messages.none'), '');
        return view('gbs::maps.index')->with(compact('customers','customer_groups','tags','users','parent_sectors'));     
    }
    //  search for contact on map
    public function search(Request $request)
    {
        $query = $request->get('q');
        $groupId = $request->get('group_id');
        $tagId = $request->get('tag_id');
        $assignedTo = $request->get('assigned_to');
        $isActive = $request->get('status_filter');
        $parentSectorId = $request->get('parent_sector_id');
        $sectorId = $request->get('sector_id');
    
        if ($isActive == 'active') {
            $customerQuery = Contact::where('type', 'customer')
                ->where('latitude', '!=', '')
                ->where('longitude', '!=', '')
                ->active();
        } else {
            $customerQuery = Contact::where('type', 'customer')
                ->where('latitude', '!=', '')
                ->where('longitude', '!=', '')
                ->where('contact_status', $isActive);
        }
    
        if ($query) {
            $customerQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%$query%")
                  ->orWhere('mobile', 'LIKE', "%$query%")
                  ->orWhere('contact_id', 'LIKE', "%$query%");
            });
        }
        
    
        if ($groupId) {
            $customerQuery->where('customer_group_id', $groupId);
        }
    
        if ($tagId) {
            $customerQuery->whereHas('tags', function ($q) use ($tagId) {
                $q->where('gbs_tags.id', $tagId);
            });
        }
    
        if ($assignedTo) {
            $customerQuery->join('user_contact_access AS uc', 'contacts.id', 'uc.contact_id')
                ->where('uc.user_id', $assignedTo);
        }
     
      if ($parentSectorId) {
        $customerQuery->whereHas('sector', function ($q) use ($parentSectorId) {
            $q->where('parent_id', $parentSectorId)
              ->orWhere('id', $parentSectorId);
        });
    }


    if ($sectorId) {
        $customerQuery->where('sector_id', $sectorId);
    }

        // $customerQuery->with(['tags:id,name,color']);
        $customerQuery->with(['tags:id,name,color', 'sector:id,parent_id,name']);
        $results = $customerQuery->select('contacts.*')->get();
    
        foreach ($results as $customer) {
            $output = $this->transactionUtil->getLedgerDetails($customer->id, null, null); 
            $customer->total_invoice = $output['total_invoice'] ?? 0;
            $customer->balance_due = $output['all_balance_due'] ?? 0;
        }
    
        return response()->json($results);
    }
    public function getSectorStats()
{
    $sectorStats = DB::table('customer_sectors as cs')
        ->leftJoin('contacts', 'contacts.sector_id', '=', 'cs.id')
        ->leftJoin('customer_sectors as parent', 'cs.parent_id', '=', 'parent.id')
        ->select(
            'cs.id',
            'cs.name',
            'parent.name as parent_name',
            DB::raw('COUNT(contacts.id) as customer_count')
        )
        ->groupBy('cs.id', 'cs.name', 'parent.name')
        ->get();
        
$uncategorizedCount = DB::table('contacts')
->whereNull('sector_id')
->count();
        $sectorStats->push((object)[
            'id' => null,
            'name' => __('gbs::lang.none_sector'),
            'parent_name' => '',
            'customer_count' => $uncategorizedCount,
            'is_uncategorized' => true 
        ]);
        return \DataTables::of($sectorStats)
        ->addColumn('parent_name', function ($row) {
            return $row->parent_name ?? '-';
        })
        ->editColumn('name', function ($row) {
            return $row->name;
        })
        ->editColumn('customer_count', function ($row) {
            return $row->customer_count;
        })
        ->setRowClass(function ($row) {
            return isset($row->is_uncategorized) && $row->is_uncategorized ? 'table-warning' : '';
        })
        ->make(true);
    
}

public function getTagStats()
{
    $data = DB::table('gbs_contact_tags')
        ->join('gbs_tags', 'gbs_contact_tags.tag_id', '=', 'gbs_tags.id')
        ->select(
            'gbs_tags.color',
            'gbs_tags.name',
            DB::raw('COUNT(DISTINCT  gbs_contact_tags.contact_id) as customer_count')
        )
        ->groupBy('gbs_tags.id', 'gbs_tags.name')
        ->get();

        return datatables()->of($data)
        ->editColumn('color', function ($row) {
            return '<span style="display:inline-block;width:20px;height:20px;border-radius:50%;background-color:' . $row->color . ';"></span>';
        })
        ->rawColumns(['color']) 
        ->make(true);
    
}
public function getCustomerGroups()
{
 

    $groups = DB::table('customer_groups as cg')
        ->leftJoin('contacts', function ($join) {
            $join->on('contacts.customer_group_id', '=', 'cg.id')
                ->where('contacts.type', 'customer');
        })
        ->select(
            'cg.id',
            'cg.name',
            DB::raw('COUNT(contacts.id) as customers_count')
        )
        ->groupBy('cg.id', 'cg.name')
        ->get();

    $ungroupedCount = DB::table('contacts')
        ->whereNull('customer_group_id')
        ->where('type', 'customer')
        ->count();

    $ungroupedRow = (object)[
        'id' => null,
        'name' => __('gbs::lang.none_group'),
        'customers_count' => $ungroupedCount,
    ];


    $allGroups = $groups->push($ungroupedRow);

    return DataTables::of($allGroups)
        ->addColumn('customers_count', function ($row) {
            return $row->customers_count ?? 0;
        })
        ->make(true);

}



}

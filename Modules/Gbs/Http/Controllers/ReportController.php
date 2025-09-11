<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use App\User;
use App\CustomerGroup;
use Modules\Gbs\Entities\Tag;
use App\Contact;
use App\Utils\TransactionUtil;
use App\BusinessLocation;
use App\CustomerSector;
use Carbon\Carbon;


class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {

        $this->transactionUtil = $transactionUtil;
    }
    public function index()
    {
        return view('gbs::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('gbs::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('gbs::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('gbs::edit');
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

    public function delegatePerformanceReport(Request $request)
    {
        if (! auth()->user()->can('gbs.show_reports')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {

            $visitsGrouped = DB::table('gbs_daily_visits as dv')
                ->join('users as u', 'dv.user_id', '=', 'u.id')
                ->select(
                    DB::raw('DATE(dv.started_at) as visit_date'),
                    'dv.user_id',
                    'u.username as user_name',
                    DB::raw('DAYNAME(dv.started_at) as day_of_week'),
                    DB::raw('COUNT(DISTINCT dv.id) as actual_visits'),

                    DB::raw('(SELECT COUNT(*) 
                          FROM gbs_route_clients rc
                          WHERE rc.route_day_id = dv.route_day_id
                         ) as expected_visits'),


                    DB::raw('
                    (SELECT COUNT(*) 
                     FROM gbs_route_clients rc 
                     WHERE rc.route_day_id = dv.route_day_id
                    ) - COUNT(DISTINCT dv.id) as missed_visits'),

                    DB::raw('(
                    SELECT COUNT(*)
                    FROM gbs_visit_transactions vt2
                    JOIN transactions t2 ON vt2.transaction_id = t2.id
                    WHERE vt2.visit_id IN (
                        SELECT id FROM gbs_daily_visits 
                        WHERE DATE(started_at) = DATE(dv.started_at) AND user_id = dv.user_id
                    )
                ) as sales_count'),

                    DB::raw('(
                    SELECT SUM(t2.final_total)
                    FROM gbs_visit_transactions vt2
                    JOIN transactions t2 ON vt2.transaction_id = t2.id
                    WHERE vt2.visit_id IN (
                        SELECT id FROM gbs_daily_visits 
                        WHERE DATE(started_at) = DATE(dv.started_at) AND user_id = dv.user_id
                    )
                ) as total_sales'),


                    DB::raw('(
                    SELECT SUM(tp2.amount)
                    FROM gbs_visit_transactions vt2
                    JOIN transaction_payments tp2 ON vt2.transaction_id = tp2.transaction_id
                    WHERE vt2.visit_id IN (
                        SELECT id 
                        FROM gbs_daily_visits 
                        WHERE DATE(started_at) = DATE(dv.started_at) 
                          AND user_id = dv.user_id
                    )
                    AND tp2.parent_id IS NULL
                ) as total_paid')
                )
                ->groupBy(
                    DB::raw('DATE(dv.started_at)'),
                    'dv.user_id',
                    'u.username',
                    'dv.route_day_id'
                )
                ->orderBy('visit_date', 'desc');

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {

                $start = request()->start_date;
                $end = request()->end_date;

                $visitsGrouped->where(DB::raw('DATE(dv.started_at)'), '>=', $start)
                    ->where(DB::raw('DATE(dv.started_at)'), '<=',   $end);
            }
            if (!empty($request->user_id)) {
                $visitsGrouped->where('dv.user_id', $request->user_id);
            }
            return \DataTables::of($visitsGrouped)
                ->editColumn('total_paid', function ($row) {

                    $total_paid_html = '<span class="payment_due" data-orig-value="' . $row->total_paid . '">' . $this->transactionUtil->num_f($row->total_paid, true) . '</span>';

                    return $total_paid_html;
                })
                ->editColumn('total_sales', function ($row) {

                    $total_sales_html = '<span class="payment_due" data-orig-value="' . $row->total_sales . '">' . $this->transactionUtil->num_f($row->total_sales, true) . '</span>';

                    return   $total_sales_html;
                })
                ->rawColumns(['total_paid', 'total_sales'])
                ->make(true);
        }
        $business_id = $request->session()->get('user.business_id');
        $users = User::forDropdown($business_id,  true);
        $customers = Contact::customersDropdown($business_id);

        return view('gbs::reports.user_performance')->with(compact('users', 'customers'));
    }
    public function visitReport(Request $request)
    {
        if (! auth()->user()->can('gbs.show_reports')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {

            // $visits = DB::table('gbs_daily_visits as dv')
            //     ->join('users as u', 'dv.user_id', '=', 'u.id')
            //     ->join('contacts as c', 'dv.contact_id', '=', 'c.id')
            //     ->leftJoin('gbs_contact_tags as ct', 'c.id', '=', 'ct.contact_id')
            //     ->leftjoin('customer_groups as cg', 'cg.id', '=', 'c.customer_group_id')
            //     ->leftJoin('gbs_visit_transactions as vt', 'dv.id', '=', 'vt.visit_id')
            //     ->leftJoin('transactions as t', 'vt.transaction_id', '=', 't.id')
            //     ->leftJoin('contact_location as cl', 'c.id', '=', 'cl.contact_id')
            //     ->select(
            //         'dv.id',
            //         't.id as transaction_id',
            //         'u.username as user_name',
            //         'c.latitude',
            //         'c.longitude',
            //         'c.name as client_name',
            //         'dv.started_at',
            //         'dv.ended_at',
            //         DB::raw('DATE(dv.started_at) as visit_date'),
            //         DB::raw('CASE WHEN vt.transaction_id IS NOT NULL THEN "true" ELSE "false" END as visit_status'),
            //         't.invoice_no'
            //     )
            //     ->groupBy('dv.id', 'dv.started_at', 'dv.ended_at');
            $visits = DB::table('gbs_expected_visits as ev')
            ->join('users as u', 'ev.user_id', '=', 'u.id')
            ->join('contacts as c', 'ev.contact_id', '=', 'c.id')
            ->leftJoin('gbs_daily_visits as dv', function($join) {
                $join->on('dv.user_id', '=', 'ev.user_id')
                     ->on('dv.contact_id', '=', 'ev.contact_id')
                     ->whereDate('dv.started_at', DB::raw('ev.visit_date'));
            })
            ->leftJoin('gbs_visit_transactions as vt', 'dv.id', '=', 'vt.visit_id')
            ->leftJoin('transactions as t', 'vt.transaction_id', '=', 't.id')
            ->leftJoin('gbs_contact_tags as ct', 'c.id', '=', 'ct.contact_id')
            ->leftJoin('contact_location as cl', 'c.id', '=', 'cl.contact_id')
            ->leftJoin('customer_sectors as cs', 'c.sector_id', '=', 'cs.id')
            ->select(
                't.id as transaction_id',
                'ev.visit_date',
                'u.username as user_name',
                'c.name as client_name',
                'c.latitude',
                    'c.longitude',
                'dv.started_at',
                'dv.ended_at',
                't.invoice_no',
                'cs.id as sector_id',         
                'cs.parent_id as parent_id',
                DB::raw("
                    CASE
                        WHEN dv.id IS NULL THEN 'false'
                        WHEN t.id IS NOT NULL THEN 'true with sell'
                        ELSE 'true without sell'
                    END as visit_status
                ")
            );
       

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
            
                $visits->whereBetween('visit_date', [$start, $end]);
            }
            if (!empty($request->parent_sector_id)) {
                $parent_sector_id = $request->parent_sector_id;
            
                $visits->where(function ($query) use ($parent_sector_id) {
                    $query->where('cs.id', $parent_sector_id)
                          ->orWhere('cs.parent_id', $parent_sector_id);
                });
            }
            if (!empty($request->sector_id)) {
                $visits->where('c.sector_id', $request->sector_id);
            }
            
            
            if (!empty($request->user_id)) {
                $visits->where('ev.user_id', $request->user_id);
            }
            
            if (!empty($request->customer_id)) {
                $visits->where('ev.contact_id', $request->customer_id);
            }
            
            if (!empty($request->customer_group_id)) {
                $visits->where('c.customer_group_id', $request->customer_group_id);
            }
            
            if (!empty($request->tag_id)) {
                $visits->where('ct.tag_id', $request->tag_id);
            }
            
            if (!empty($request->location_id)) {
                $visits->where('cl.location_id', $request->location_id);
            }
            
            if (!empty(request()->visit_status)) {
                if (request()->visit_status === 'true_with_sell') {
                    $visits->whereNotNull('dv.id')
                           ->whereNotNull('t.id');
                } elseif (request()->visit_status === 'true_without_sell') {
                    $visits->whereNotNull('dv.id')
                           ->whereNull('t.id');
                } elseif (request()->visit_status === 'false') {
                    $visits->whereNull('dv.id');
                }
            }
            
            
            return \DataTables::of($visits)
            ->editColumn('visit_status', function ($row) {
                if ($row->visit_status === 'true with sell') {
                    return '<span style="color:green;">✔ بيع</span>';
                } elseif ($row->visit_status === 'true without sell') {
                    return '<span style="color:orange;">⚠ تمت الزيارة بدون بيع</span>';
                } else {
                    return '<span style="color:red;">✖ لم تتم الزيارة</span>';
                }
            })
            
                ->editColumn('client_name', function ($row) {
                    if (!empty($row->latitude) && !empty($row->longitude)) {
                        $mapUrl = "https://www.google.com/maps?q={$row->latitude},{$row->longitude}";
                        return '<a href="' . $mapUrl . '" target="_blank" style="color:blue;">' . e($row->client_name) . '</a>';
                    } else {
                        return '<span style="color:blue;">' . e($row->client_name) . '</span>';
                    }
                })
                ->editColumn('invoice_no', function ($row) {
                    if (!empty($row->transaction_id)) {
                        return '<a data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]) . '" 
                    href="#" 
                    data-container=".view_modal" 
                    class="btn-modal" 
                    style="color:#007bff;">' . e($row->invoice_no) . '</a>';
                    } else {
                        return '';
                    }
                })->editColumn('visit_date', function ($row) {
                    return $this->transactionUtil->format_date($row->visit_date, true);
                })
                ->editColumn('started_at', function ($row) {
                    return $this->transactionUtil->format_date($row->started_at, true);
                })
                ->editColumn('ended_at', function ($row) {
                    return $this->transactionUtil->format_date($row->ended_at, true);
                })
                ->rawColumns(['visit_status', 'client_name', 'invoice_no'])
                ->make(true);
        }
        $business_id = $request->session()->get('user.business_id');
        $users = User::forDropdown($business_id,  true);
        $customers = Contact::customersDropdown($business_id);
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $tag = Tag::get();
        $tags = $tag->pluck('name', 'id');
        $tags->prepend(__('gbs::lang.no_tag'), '');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $parent_sectors = CustomerSector::where('parent_id', null)->pluck('name', 'id')->prepend(__('messages.none'), '');
        return view('gbs::reports.visits_report')->with(compact('parent_sectors','users', 'customers', 'customer_groups', 'tags','business_locations'));
    }
    public function getShiftReport(Request $request)
{
    if (! auth()->user()->can('gbs.show_reports')) {
        abort(403, 'Unauthorized action.');
    }
    if (request()->ajax()) {
        


        $shifts = DB::table('gbs_shifts')
            ->join('users', 'gbs_shifts.user_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT user_id, visit_date, COUNT(*) as expected_count
                                 FROM gbs_expected_visits
                                 GROUP BY user_id, visit_date) as expected'),
                       function($join) {
                           $join->on('gbs_shifts.user_id', '=', 'expected.user_id')
                                ->on('gbs_shifts.shift_date', '=', 'expected.visit_date');
                       })
            ->leftJoin(DB::raw('(SELECT user_id, DATE(created_at) as visit_date, COUNT(*) as actual_count
                                 FROM gbs_daily_visits
                                 GROUP BY user_id, DATE(created_at)) as actual'),
                       function($join) {
                           $join->on('gbs_shifts.user_id', '=', 'actual.user_id')
                                ->on('gbs_shifts.shift_date', '=', 'actual.visit_date');
                       })
            ->select(
                'gbs_shifts.*',
                'users.username as user_name',
                DB::raw('COALESCE(expected.expected_count, 0) as expected_visits'),
                DB::raw('COALESCE(actual.actual_count, 0) as successful_visits'),
                DB::raw('COALESCE(expected.expected_count, 0) - COALESCE(actual.actual_count, 0) as missed_visits')
            );
        

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end = request()->end_date;
        
            $shifts->whereBetween('gbs_shifts.created_at', [$start, $end]);
        }
        if (!empty($request->user_id)) {
            $shifts->where('gbs_shifts.user_id', $request->user_id);
        }
        
    return \DataTables::of($shifts)
    ->editColumn('created_at', function ($row) {
       
        return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d');
    })
    ->addColumn('day_of_week', function ($row) {
    
        return \Carbon\Carbon::parse($row->created_at)->translatedFormat('l'); 
    })
    
    ->editColumn('start_time', function ($row) {
        return $row->start_time ?? '-';
    })
    ->editColumn('end_time', function ($row) {
        return $row->end_time ?? '-';
    })
    ->editColumn('start_location', function ($row) {
        if ($row->start_latitude && $row->start_longitude) {
            $url = "https://www.google.com/maps?q={$row->start_latitude},{$row->start_longitude}";
            return '<a href="' . $url . '" target="_blank" style="color:blue;">موقع البداية</a>';
        }
        return '-';
    })
   
    ->editColumn('end_location', function ($row) {
        if ($row->end_latitude && $row->end_longitude) {
            $url = "https://www.google.com/maps?q={$row->end_latitude},{$row->end_longitude}";
            return '<a href="' . $url . '" target="_blank" style="color:green;">موقع النهاية</a>';
        }
        return '-';
    })
    ->addColumn('user_name', function ($row) {
        return e($row->user_name);
    })
    ->addColumn('working_hours', function ($row) {
        if ($row->start_time && $row->end_time) {
            $start = \Carbon\Carbon::parse($row->start_time);
            $end = \Carbon\Carbon::parse($row->end_time);
            $diffInHours = $start->diffInMinutes($end) / 60;
    
 
            return number_format($diffInHours, 2) . ' ساعة';
        } else {
            return '—';
        }
    })
    
    ->rawColumns(['start_location', 'end_location'])
    ->make(true);
    }
    $business_id = $request->session()->get('user.business_id');
       $users = User::forDropdown($business_id,  true);
    return view('gbs::reports.shifts')->with(compact('users'));
}
}

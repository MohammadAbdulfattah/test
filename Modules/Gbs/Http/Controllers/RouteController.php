<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\BusinessUtil;
use App\Contact;
use App\User;
use Modules\Gbs\Entities\Route;
use Modules\Gbs\Entities\RouteDay;
use Modules\Gbs\Entities\RouteClient;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RouteController extends Controller
{

    protected $businessUtil;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }
    public function index()
    {
        if (! auth()->user()->can('gbs.show_roots')) {
            abort(403, 'Unauthorized action.');
        }
        $routes = Route::with('user')->where('is_active', '1');
        if (request()->ajax()) {
            return \DataTables::of($routes)
                ->addColumn('name', function ($row) {
                    return '<a href="#" class="text-primary view-route-details" data-id="' . $row->id . '">' . $row->name . '</a>';
                })
                ->addColumn('action', function ($route) {
                    $editUrl = action([\Modules\Gbs\Http\Controllers\RouteController::class, 'edit'], [$route->id]);
                    $deleteUrl = action([\Modules\Gbs\Http\Controllers\RouteController::class, 'destroy'], [$route->id]);

                    return '
                 <a href="' . $editUrl . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> تعديل</a>
                 
                     <button data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_route_button"><i class="fa fa-trash"></i> حذف</button>';
                })
                ->rawColumns(['action', 'name'])
                ->make(true);
        }
        return view('gbs::routes.index', compact('routes'));
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (! auth()->user()->can('gbs.create_roots')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $clients = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id);
        $default_datetime = $this->businessUtil->format_date('now', true);
        return view('gbs::routes.create')->with(compact('clients', 'users', 'default_datetime'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $route = new Route();
            $route->name = $request->input('name');
            $route->user_id = $request->input('user_id');
            $route->save();

            $contacts = $request->input('contacts');
            $intervalDaysInput = $request->input('interval_days', []);

            foreach ($contacts as $day => $clients) {
                if (empty($clients)) {
                    continue;
                }

                $routeDay = new RouteDay();
                $routeDay->route_id = $route->id;
                $routeDay->day_of_week = $day;
                $routeDay->interval_days = isset($intervalDaysInput[$day]) ? (int)$intervalDaysInput[$day] : 7;
                $routeDay->save();

                foreach ($clients as $contact_id) {
                    $routeClient = new RouteClient();
                    $routeClient->route_day_id = $routeDay->id;
                    $routeClient->contact_id = $contact_id;
                    $routeClient->save();
                }
            }

            DB::commit();

            $output = ['success' => true, 'msg' => __('gbs::lang.route_created_successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Route Store Error: ' . $e->getMessage());

            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()->action([\Modules\Gbs\Http\Controllers\RouteController::class, 'index'])
            ->with('status', $output);
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
        if (! auth()->user()->can('gbs.edit_roots')) {
            abort(403, 'Unauthorized action.');
        }
        $route = Route::findOrFail($id);
        $users = User::forDropdown(auth()->user()->business_id);
        $clients = Contact::customersDropdown(auth()->user()->business_id);


        $selectedContacts = [];

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday', 'friday'];
        foreach ($days as $day) {
            $routeDay = $route->days()->where('day_of_week', $day)->first();
            if ($routeDay) {

                $clientIds = $routeDay->clients->pluck('contact_id')->toArray();
                $selectedContacts[$day] = $clientIds;


                $clientModels = \App\Contact::whereIn('id', $clientIds)->get();
                foreach ($clientModels as $client) {
                    $clients[$client->id] = $client->name;
                }
            }
        }

        // اجلب قيم التكرار لكل يوم
        $selectedIntervals = [];
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday', 'friday'];
        foreach ($days as $day) {
            $routeDay = $route->days()->where('day_of_week', $day)->first();
            if ($routeDay) {
                $selectedIntervals[$day] = (int)($routeDay->interval_days ?: 7);
            }
        }

        return view('gbs::routes.edit', compact('route', 'users', 'clients', 'selectedContacts', 'selectedIntervals'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            $route = \Modules\Gbs\Entities\Route::findOrFail($id);
            $route->name = $request->input('name');
            $route->user_id = $request->input('user_id');
            $route->save();

            $contacts = $request->input('contacts', []);
            $intervalDaysInput = $request->input('interval_days', []);

            foreach ($contacts as $dayName => $clientIds) {
                // ابحث عن اليوم الحالي
                $routeDay = $route->days()->where('day_of_week', $dayName)->first();

                if ($routeDay) {
                    // حدث قيمة التكرار
                    $routeDay->interval_days = isset($intervalDaysInput[$dayName]) ? (int)$intervalDaysInput[$dayName] : ($routeDay->interval_days ?: 7);
                    $routeDay->save();
                    // حدث العملاء المرتبطين بهذا اليوم: نحذف القديم ونضيف الجديد فقط
                    $routeDay->clients()->delete();

                    foreach ($clientIds as $contactId) {
                        $routeDay->clients()->create([
                            'contact_id' => $contactId,
                        ]);
                    }
                } else {
                    // إذا لم يكن اليوم موجودًا، ننشئه ونربط العملاء به
                    if (!empty($clientIds)) {
                        $routeDay = $route->days()->create([
                            'day_of_week' => $dayName,
                            'interval_days' => isset($intervalDaysInput[$dayName]) ? (int)$intervalDaysInput[$dayName] : 7,
                        ]);

                        foreach ($clientIds as $contactId) {
                            $routeDay->clients()->create([
                                'contact_id' => $contactId,
                            ]);
                        }
                    }
                }
            }

            $output = [
                'success' => true,
                'msg' => __('gbs::lang.updated_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('routes.index')->with('status', $output);
    }




    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('gbs.delete_roots')) {
            abort(403, 'Unauthorized action.');
        }


        if (request()->ajax()) {
            try {
                $route = Route::find($id);
                $route->is_active = false;
                $route->save();


                $output = [
                    'success' => true,
                    'msg' => __('gbs::lang.inactive_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }

        $route = Route::find($id);
        $route->is_active = false;
        $route->save();
    }
    public function search(Request $request)
    {
        $term = $request->q;



        $clients = Contact::where('type', 'customer')
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%$term%")
                    ->orWhere('mobile', 'LIKE', "%$term%")
                    ->orWhere('contact_id', 'LIKE', "%$term%");
            })
            ->select('id', 'name')
            ->limit(20)
            ->get();

        return response()->json($clients);
    }
    public function getWeeklyRoutes(Request $request)
    {

        $userId = $request->input('user_id');

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday', 'friday'];

        $data = [];

        foreach ($days as $day) {

            $route = Route::where('user_id', $userId)
                ->where('day_of_week', $day)
                ->with('clients:id,name')
                ->first();

            $data[$day] = $route ? $route->clients->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name];
            }) : [];
        }

        return response()->json($data);
    }
    public function details($id)
    {

        $route = Route::with(['days.clients', 'days.clients.contact'])->findOrFail($id);

        $days_ar = [
            'sunday' => __('gbs::lang.sunday'),
            'monday' => __('gbs::lang.monday'),
            'tuesday' => __('gbs::lang.tuesday'),
            'wednesday' => __('gbs::lang.wednesday'),
            'thursday' => __('gbs::lang.thursday'),
            'saturday' => __('gbs::lang.saturday'),
            'friday' => __('gbs::lang.friday'),
        ];

        return view('gbs::routes.route_details', compact('route', 'days_ar'));
    }
    /// api for today  contacts
    public function getTodayClients(Request $request)
    {


        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $day = strtolower(Carbon::now()->locale('en')->dayName); // sunday, monday, etc.

        // $route = $user->routes()->where('is_active', true)->with(['days.clients.contact'])->first();
        $route = $user->routes()
            ->where('is_active', true)
            ->whereHas('days', function ($q) use ($day) {
                $q->where('day_of_week', $day);
            })
            ->with(['days.clients.contact'])
            ->first();

        if (!$route) {
            return response()->json(['clients' => []]);
        }

        $todayClientsArray = $route->days->firstWhere('day_of_week', $day)?->clients ?? [];

        $clients = collect($todayClientsArray)->map(function ($routeClient) {
            $contact = $routeClient->contact;

            return [
                'name' => $contact->name,
                'id' => $contact->id,
                'phone' => $contact->mobile ?? $contact->phone ?? '',
                'latitude' => $contact->latitude,
                'longitude' => $contact->longitude,
                'tags' => $contact->tags->map(function ($tag) {
                    return [
                        'name' => $tag->name,
                        'color' => $tag->color,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json(['clients' => $clients]);
    }
}

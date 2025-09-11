<?php

namespace Modules\Gbs\Http\Controllers;

use App\Contact;
use Modules\Gbs\Entities\Visit;
use Modules\Gbs\Entities\Reason;
use App\User;
use App\Business;
use App\Transaction;
use App\TransactionPayment;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Gbs\Entities\VisitTransaction;


class VisitController extends Controller
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

        return view('gbs::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $clients = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id);
        $default_datetime = $this->businessUtil->format_date('now', true);
        return view('gbs::visites.create')->with(compact('clients', 'users', 'default_datetime'));;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(Request $request)
    {
        //  $visit = Visites::get();

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


    // public function startVisit(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     $request->validate([
    //         'contact_id' => 'required|exists:contacts,id',
    //         'latitude' => 'nullable|numeric',
    //         'longitude' => 'nullable|numeric',
    //     ]);

    //     $contact = Contact::find($request->contact_id);

    //     $canVisitAnywhere = auth()->user()->can('gbs.can_visit_anywhere');


    //     if (!$contact || (!$canVisitAnywhere && (!$contact->latitude || !$contact->longitude))) {
    //         return response()->json([
    //             'success' => false,
    //             'code' => 400,
    //             'message' => 'لا يوجد موقع مسجل ل العميل س يتم تسجيل موقعك الحالي ك موقع ل العميل'
    //         ], 422);
    //     }


    //     $today = strtolower(Carbon::now()->locale('en')->dayName);

    //     $isScheduled = $user->routes()->where('is_active', true)
    //         ->whereHas('days', function ($q) use ($today, $request) {
    //             $q->where('day_of_week', $today)
    //                 ->whereHas('clients', function ($q2) use ($request) {
    //                     $q2->where('contact_id', $request->contact_id);
    //                 });
    //         })->exists();

    //     if (!$isScheduled) {
    //         return response()->json([
    //             'success' => false,
    //             'code' => 400,
    //             'message' => 'العميل غير مجدول للزيارة اليوم'
    //         ], 403);
    //     }

    //     $existingVisit = Visit::where('user_id', $user->id)
    //         ->where('contact_id', $contact->id)
    //         ->whereNull('ended_at')
    //         ->first();

    //     if ($existingVisit) {
    //         return response()->json([
    //             'success' => false,
    //             'code' => 400,
    //             'message' => 'هناك زيارة سارية بالفعل لهذا العميل. يرجى إنهاؤها أولاً.'
    //         ], 409);
    //     }



    //     if ($canVisitAnywhere) {

    //         if (!$request->filled('user_latitude') || !$request->filled('user_longitude')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'code' => 400,
    //                 'message' => 'يجب إرسال الموقع الجغرافي الخاص بك لبدء الزيارة'
    //             ], 422);
    //         }
    //     } else {

    //         $distance = $this->calculateDistance(
    //             $request->latitude,
    //             $request->longitude,
    //             $contact->latitude,
    //             $contact->longitude
    //         );

    //         $business_id = auth()->user()->business_id;
    //         $business = Business::find($business_id);

    //         $allowed_distance = $business->allowed_distance ?? 0.3;

    //         if ($distance > $allowed_distance) {
    //             return response()->json([
    //                 'success' => false,
    //                 'code' => 400,
    //                 'message' => 'أنت خارج نطاق موقع العميل'
    //             ], 403);
    //         }
    //     }

    //     $routeDayId = $user->routes()->where('is_active', true)
    //         ->whereHas('days', function ($q) use ($today, $request) {
    //             $q->where('day_of_week', $today)
    //                 ->whereHas('clients', function ($q2) use ($request) {
    //                     $q2->where('contact_id', $request->contact_id);
    //                 });
    //         })
    //         ->with(['days' => function ($q) use ($today, $request) {
    //             $q->where('day_of_week', $today)
    //                 ->whereHas('clients', function ($q2) use ($request) {
    //                     $q2->where('contact_id', $request->contact_id);
    //                 });
    //         }])
    //         ->first()
    //         ?->days
    //         ->first()
    //         ?->id;


    //     $visitData = [
    //         'user_id' => $user->id,
    //         'contact_id' => $contact->id,
    //         'route_day_id' => $routeDayId,
    //         'started_at' => Carbon::now(),
    //     ];

    //     if ($canVisitAnywhere) {

    //         if (!$request->filled('user_latitude') || !$request->filled('user_longitude')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'code' => 400,
    //                 'message' => 'يجب إرسال الموقع الجغرافي (خط الطول وخط العرض) لبدء الزيارة'
    //             ], 422);
    //         }


    //         $visitData['user_latitude'] = $request->user_latitude;
    //         $visitData['user_longitude'] = $request->user_longitude;
    //     }


    //     $visit = Visit::create($visitData);

    //     $invoiceUrl = route('pos.create.custom');
    //     $visit_id = $visit->id;
    //     $contact_id = $visit->contact_id;
    //     return response()->json([
    //         'success' => true,
    //         'code' => 200,
    //         'message' => 'تم بدء الزيارة بنجاح',
    //         'visit_id' => $visit_id,
    //         'contact_id' => $contact_id,
    //         'invoice_url' => $invoiceUrl,

    //     ]);
    // }


    public function startVisit(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $request->validate([
                'contact_id' => 'required|exists:contacts,id',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'user_latitude' => 'nullable|numeric',
                'user_longitude' => 'nullable|numeric',
            ]);

            $contact = Contact::find($request->contact_id);

            $canVisitAnywhere = $user->can('gbs.can_visit_anywhere');

            if (!$contact || (!$canVisitAnywhere && (!$contact->latitude || !$contact->longitude))) {
                return response()->json([
                    'success' => false,
                    'code' => 400,
                    'message' => 'لا يوجد موقع مسجل للعميل، سيتم تسجيل موقعك الحالي كموقع للعميل.'
                ], 422);
            }

            $today = strtolower(Carbon::now()->locale('en')->dayName);

            $isScheduled = $user->routes()->where('is_active', true)
                ->whereHas('days', function ($q) use ($today, $request) {
                    $q->where('day_of_week', $today)
                        ->whereHas('clients', function ($q2) use ($request) {
                            $q2->where('contact_id', $request->contact_id);
                        });
                })->exists();

            if (!$isScheduled) {
                return response()->json([
                    'success' => false,
                    'code' => 400,
                    'message' => 'العميل غير مجدول للزيارة اليوم'
                ], 403);
            }

            $existingVisit = Visit::where('user_id', $user->id)
                ->where('contact_id', $contact->id)
                ->whereNull('ended_at')
                ->first();

            if ($existingVisit) {
                return response()->json([
                    'success' => false,
                    'code' => 400,
                    'message' => 'هناك زيارة سارية بالفعل لهذا العميل. يرجى إنهاؤها أولاً.'
                ], 409);
            }

            $todayDate = Carbon::now()->toDateString();
            $existingTodayVisit = Visit::where('user_id', $user->id)
                ->where('contact_id', $contact->id)
                ->whereDate('started_at', $todayDate)
                ->whereNotNull('ended_at')
                ->latest()
                ->first();

            if ($existingTodayVisit) {
                if ($existingTodayVisit->reason_id == 1) {
                    $existingTodayVisit->update([
                        'started_at' => now(),
                        'ended_at' => null,
                        'reason_id' => null,
                        'user_latitude' => $request->user_latitude ?? $existingTodayVisit->user_latitude,
                        'user_longitude' => $request->user_longitude ?? $existingTodayVisit->user_longitude,
                    ]);

                    return response()->json([
                        'success' => true,
                        'code' => 200,
                        'message' => 'تم استئناف الزيارة السابقة.',
                        'visit_id' => $existingTodayVisit->id,
                        'contact_id' => $existingTodayVisit->contact_id,
                        'invoice_url' => route('pos.create.custom'),
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'code' => 400,
                        'message' => 'لا يمكن زيارة هذا العميل أكثر من مرة في نفس اليوم.'
                    ], 403);
                }
            }

            if ($canVisitAnywhere) {
                if (!$request->filled('user_latitude') || !$request->filled('user_longitude')) {
                    return response()->json([
                        'success' => false,
                        'code' => 400,
                        'message' => 'يجب إرسال الموقع الجغرافي الخاص بك لبدء الزيارة'
                    ], 422);
                }
            } else {
                $distance = $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $contact->latitude,
                    $contact->longitude
                );

                $business = Business::find($user->business_id);
                $allowed_distance = $business->allowed_distance ?? 0.3;

                if ($distance > $allowed_distance) {
                    return response()->json([
                        'success' => false,
                        'code' => 400,
                        'message' => 'أنت خارج نطاق موقع العميل'
                    ], 403);
                }
            }

            $routeDayId = $user->routes()->where('is_active', true)
                ->whereHas('days', function ($q) use ($today, $request) {
                    $q->where('day_of_week', $today)
                        ->whereHas('clients', function ($q2) use ($request) {
                            $q2->where('contact_id', $request->contact_id);
                        });
                })
                ->with(['days' => function ($q) use ($today, $request) {
                    $q->where('day_of_week', $today)
                        ->whereHas('clients', function ($q2) use ($request) {
                            $q2->where('contact_id', $request->contact_id);
                        });
                }])
                ->first()
                ?->days
                ->first()
                ?->id;

            $visitData = [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'route_day_id' => $routeDayId,
                'started_at' => now(),
            ];

            if ($canVisitAnywhere) {
                $visitData['user_latitude'] = $request->user_latitude;
                $visitData['user_longitude'] = $request->user_longitude;
            }

            $visit = Visit::create($visitData);

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'تم بدء الزيارة بنجاح',
                'visit_id' => $visit->id,
                'contact_id' => $visit->contact_id,
                'invoice_url' => route('pos.create.custom'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'خطأ في التحقق من البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Visit start failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.'
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $earthRadius * $angle;
    }


    public function endVisit(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $request->validate([
                'visit_id' => 'required|numeric',
            ]);

            $visit = Visit::where('id', $request->visit_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$visit) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'الزيارة غير موجودة أو غير تابعة لك.'
                ], 404);
            }

            if ($visit->status === 'sale_made') {
                $visit->ended_at = Carbon::now();
                $visit->save();

                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'message' => 'تم إنهاء الزيارة بنجاح بعد إتمام البيع',
                ]);
            }

            if (!$visit->failure_reason_id && !$request->filled('reason_id')) {
                $reasons = Reason::select('id', 'reason')->get();

                return response()->json([
                    'success' => true,
                    'code' => 206,
                    'message' => 'الرجاء اختيار سبب عدم إتمام البيع',
                    'reasons' => $reasons,
                ], 206);
            }

            $request->validate([
                'reason_id' => 'required|exists:gbs_failure_reasons,id',
            ]);

            $visit->reason_id = $request->reason_id;
            $visit->ended_at = Carbon::now();
            $visit->save();

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'تم إنهاء الزيارة مع حفظ سبب الفشل',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'خطأ في التحقق من البيانات.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Visit end failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'حدث خطأ غير متوقع أثناء إنهاء الزيارة. يرجى المحاولة لاحقًا.',
            ], 500);
        }
    }



    public function addContactLocation(Request $request, $contactId)
    {
        try {
            $user = Auth::guard('api')->user();

            $contact = Contact::find($contactId);

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'العميل غير موجود'
                ], 404);
            }

            if ((!$contact->latitude || !$contact->longitude) &&
                (!$request->filled('latitude') || !$request->filled('longitude'))
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب إرسال خط الطول والعرض '
                ], 422);
            }

            $today = strtolower(Carbon::now()->locale('en')->dayName);

            $isScheduledToday = $user->routes()
                ->where('is_active', true)
                ->whereHas('days', function ($q) use ($today, $contactId) {
                    $q->where('day_of_week', $today)
                        ->whereHas('clients', function ($q2) use ($contactId) {
                            $q2->where('contact_id', $contactId);
                        });
                })
                ->exists();

            if (!$isScheduledToday) {
                return response()->json([
                    'success' => false,
                    'message' => 'العميل غير مجدول لزيارة اليوم'
                ], 403);
            }

            if ($contact->latitude && $contact->longitude) {
                return response()->json([
                    'success' => false,
                    'message' => 'العميل لديه إحداثيات بالفعل'
                ], 400);
            }

            $contact->latitude = $request->latitude;
            $contact->longitude = $request->longitude;
            $contact->save();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ موقع العميل بنجاح',
                'location' => [
                    'latitude' => $contact->latitude,
                    'longitude' => $contact->longitude,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Add Contact Location Error', [
                'user_id' => auth()->id(),
                'contact_id' => $contactId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ موقع العميل، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }



    public function visitStats()
    {
        try {
            $user = auth()->user();
            $today = strtolower(\Carbon\Carbon::now()->locale('en')->dayName);

            $route = $user->routes()->where('is_active', true)
                ->with(['days.clients.contact'])
                ->whereHas('days', fn($q) => $q->where('day_of_week', $today))
                ->first();

            if (!$route) {
                return response()->json([
                    'total_clients' => 0,
                    'visited_clients' => 0,
                    'not_visited_clients' => 0,
                    'successful_visits' => 0,
                    'failed_visits' => 0,
                    'total_sales' => 0,
                    'total_invoice_amount' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                ]);
            }

            $todayRouteDay = $route->days->firstWhere('day_of_week', $today);
            $todayClients = $todayRouteDay?->clients ?? [];

            $clientIds = collect($todayClients)->pluck('contact_id')->toArray();

            $visits = Visit::where('user_id', $user->id)
                ->whereIn('contact_id', $clientIds)
                ->whereDate('started_at', \Carbon\Carbon::today())
                ->get();

            $visitedClientIds = $visits->pluck('contact_id')->unique();
            $visitedClientsCount = $visitedClientIds->count();

            $totalClients = count($clientIds);
            $notVisitedClients = $totalClients - $visitedClientsCount;

            $successfulVisits = $visits->where('status', 'sale_made')->count();
            $failedVisits = $visits->where('status', '!=', 'sale_made')->count();

            $visitIds = $visits->pluck('id');
            $salesCount = VisitTransaction::whereIn('visit_id', $visitIds)->count();

            $transactionIds = VisitTransaction::whereIn('visit_id', $visitIds)->pluck('transaction_id');

            $totalInvoicesAmount = Transaction::whereIn('id', $transactionIds)->sum('final_total');
            $totalPaid = TransactionPayment::whereIn('transaction_id', $transactionIds)->sum('amount');
            $totalDue = $totalInvoicesAmount - $totalPaid;

            return response()->json([
                'total_clients' => $totalClients,
                'visited_clients' => $visitedClientsCount,
                'not_visited_clients' => $notVisitedClients,
                'successful_visits' => $successfulVisits,
                'failed_visits' => $failedVisits,
                'total_sales' => $salesCount,
                'total_invoice_amount' => $totalInvoicesAmount,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
            ]);
        } catch (\Exception $e) {
            Log::error('Visit Stats Error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'حدث خطأ أثناء حساب الإحصائيات، يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }

    public function invoiceUrl()
    {
        try {
            $user = Auth::guard('api')->user();
            $today = Carbon::today();
            $business_id = $user->business_id;

            $visitsToday = Visit::where('user_id', $user->id)
                ->whereDate('started_at', $today)
                ->get();

            $visitIds = $visitsToday->pluck('id');
            $contactIds = $visitsToday->pluck('contact_id')->unique();

            $visitTransactions = VisitTransaction::whereIn('visit_id', $visitIds)
                ->whereDate('created_at', $today)
                ->get();

            $transactionsByContact = [];

            foreach ($contactIds as $contactId) {
                $visitIdsForContact = $visitsToday->where('contact_id', $contactId)->pluck('id');
                $transactions = $visitTransactions->whereIn('visit_id', $visitIdsForContact)->pluck('transaction_id')->toArray();

                $urls = [];
                foreach ($transactions as $tid) {
                    $url = app('App\Utils\TransactionUtil')->getInvoiceUrl($tid, $business_id);
                    $urls[] = [
                        'transaction_id' => $tid,
                        'invoice_url' => $url,
                    ];
                }

                $transactionsByContact[$contactId] = $urls;
            }

            return response()->json([
                'success' => true,
                'data' => $transactionsByContact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب روابط الفواتير.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

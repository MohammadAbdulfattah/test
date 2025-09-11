<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gbs\Entities\Shift;
use Illuminate\Validation\ValidationException;
use DB;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
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
   


    public function startShift(Request $request)
    {
    
    $user = $request->user(); 
        if (!$user) {
            return response()->json([
                'error_code' => 1001,
                'message' => __('User ID not found'), 
                'status' => 'error',
            ], 401);
        }
   
        try {

            $validatedData = $request->validate([
                'shift_date' => 'required|date',
                'start_time' => 'required|date_format:H:i:s',
                'start_latitude' => 'required|numeric',
                'start_longitude' => 'required|numeric',
            ]);
            $visitExists = DB::table('gbs_expected_visits')
                ->where('user_id', $user->id)
                ->whereDate('visit_date', $validatedData['shift_date'])
                ->exists();


            if (!$visitExists) {

                $dayOfWeek = strtolower(\Carbon::parse($validatedData['shift_date'])->format('l'));
                $routeDay = DB::table('gbs_route_days as rd')
                    ->join('gbs_routes as r', 'rd.route_id', '=', 'r.id')->where('r.is_active', '1')
                    ->where('r.user_id', $user->id)
                    ->where('rd.day_of_week', $dayOfWeek)
                    ->select('rd.id as route_day_id')
                    ->first();
                if ($routeDay) {
                    $clients = DB::table('gbs_route_clients')
                        ->where('route_day_id', $routeDay->route_day_id)
                        ->pluck('contact_id');
                    foreach ($clients as $contactId) {
                        DB::table('gbs_expected_visits')->updateOrInsert(
                            [
                                'user_id' => $user->id,
                                'contact_id' => $contactId,
                                'visit_date' => $validatedData['shift_date'],
                            ],
                            [
                                'day_of_week' => $dayOfWeek,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

          
            // fo open shift
            $existingShift = Shift::where('user_id', $user->id)
                ->where('shift_date', $validatedData['shift_date'])
                ->whereNull('end_time')
                ->whereNull('end_latitude') 
                ->first();

            if ($existingShift) {
                return response()->json([
                    'error_code' => 1003,
                    'message' => __('يجب انهاء الشيفت المفتوحة اولا'),
                    'status' => 'error',
                   
                ], 409); 
            }

            $shift = Shift::create([
                'user_id' => $user->id,
                'shift_date' => $validatedData['shift_date'],
                'start_time' => $validatedData['start_time'],
                'start_latitude' => $validatedData['start_latitude'],
                'start_longitude' => $validatedData['start_longitude'],
            ]);
      

            return response()->json([
                'success_code' => 2001,
                'message' => __('Shift recorded successfully!'),
                'status' => 'success',
                'data' => [
                'shift_date' => $shift->shift_date,      
                'start_time' => $shift->start_time,      
                'end_time' => $shift->end_time,   
                'can_visit_anywhere' => $user->can('gbs.can_visit_anywhere')      
    ],
            ], 201);

        } catch (ValidationException $e) {
            
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = [
                    'field' => $field,
                    'message' => $messages[0],
                    'error_code' => 401, 
                ];
            }

            return response()->json([
                'error_code' => 1002,
                'message' => __('Validation errors occurred'),
                'status' => 'error',
                'errors' => $errors,
            ], 422); 
        }
    }

    public function endShift(Request $request)
    {
        $user = $request->user(); 
    
        if (!$user) {
            return response()->json([
                'error_code' => 1001,
                'message' => __('User ID not found'), 
                'status' => 'error',
            ], 401);
        }

        try {
           
            $validatedData = $request->validate([
                'end_time' => 'required|date_format:H:i:s', 
                'end_latitude' => 'required|string',    
                'end_longitude' => 'required|string',   
               // 'shift_date' => 'required|date', 

            ]);

            
    
            $openShift = Shift::where('user_id', $user->id)
            //   ->where('shift_date', 'shift_date')
                ->whereNull('end_time') 
                ->whereNull('end_latitude')
                ->first();

            if (!$openShift) {
                return response()->json([
                    'error_code' => 1004,
                    'message' => __('No open shift found for today.'),
                    'status' => 'error',
                ], 404); 
            }

    
            $openShift->update([
                'end_time' => $validatedData['end_time'],
                'end_latitude' => $validatedData['end_latitude'],
                'end_longitude' => $validatedData['end_longitude'],
            ]);

            return response()->json([
                'success_code' => 2002,
                'message' => __('Shift ended successfully!'),
                'status' => 'success',
                'data' => ['end_time'=>$openShift->end_time], 
            ], 200); 

        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = [
                    'field' => $field,
                    'message' => $messages[0],
                    'error_code' => 401,
                ];
            }

            return response()->json([
                'error_code' => 1002,
                'message' => __('Validation errors occurred'),
                'status' => 'error',
                'errors' => $errors,
            ], 422); 
        }
    }
    
}




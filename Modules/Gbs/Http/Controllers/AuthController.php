<?php

namespace Modules\Gbs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
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
    public function login(Request $request)
{
    $validator = \Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required',
    ], [
        'username.required' => 'الرجاء إدخال اسم المستخدم',
        'password.required' => 'الرجاء إدخال كلمة المرور',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
        return response()->json([
            'status' => false,
            'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        ], 401);
    }

    $user = Auth::user();
    $user_name = $user->username;
    $token = $user->createToken('MobileApp')->accessToken;

    return response()->json([
        'status' => true,
        'message' => __('gbs::lang.success'),
        'user' => $user_name,
        'token' => $token,
    ], 200);
}
      
}

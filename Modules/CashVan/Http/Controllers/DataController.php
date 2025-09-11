<?php

namespace Modules\CashVan\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Adding cashvan module to navbar
     *
     * @return \Illuminate\Http\Response
     */
    public function modifyAdminMenu() 
    {
        if (auth()->user()->can('cashvan.view')) {
            return Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(action([\Modules\CashVan\Http\Controllers\CashVanController::class, 'index']), __('cashvan::cashvan.cashvan'),  ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                    <path d="M3 9l4 0"></path>
                </svg>', 'active' => request()->segment(1) == 'cashvan'])->order(40);
                //     $menu->dropdown(
                //             __('cashvan::cashvan.cashvan'),
                //         function ($sub) {
                //             if (auth()->user()->can('cashvan.view')) {
                //                 $sub->url(
                //                     action([\Modules\CashVan\Http\Controllers\CashVanController::class, 'index']),
                //                     __('cashvan::cashvan.cashvan'),
                //                     ['icon' => '', 'active' => request()->segment(1) == 'cashvan']
                //                 );
                //             }
                //             // if (auth()->user()->can('van_stock.create')) {
                //             //     $sub->url(
                //             //         action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'createMainStock']),
                //             //         __('cashvan::cashvan.main_stock'),
                //             //         ['icon' => '', 'active' => request()->segment(1) == 'cashvan']
                //             //     );
                //             // }
                //             // if (auth()->user()->can('van_report.view')) {
                //             //     $sub->url(
                //             //         action([\Modules\CashVan\Http\Controllers\VanStockController::class, 'getStockReport']),
                //             //         __('cashvan::cashvan.report'),
                //             //         ['icon' => '', 'active' => request()->segment(1) == 'cashvan']
                //             //     );
                //             // }
                           
                //         },  ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                //         <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                //         <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                //         <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                //         <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                //         <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                //         <path d="M3 9l4 0"></path>
                //     </svg>', 'active' => request()->segment(1) == 'cashvan'])->order(40);
             });
        }else{
            return ;
        }
    }

    /**
     * return the permissions of the module
     * @return Renderable
     */
    public function user_permissions()
    {
        $module_permissions = [];

      
        $module_permissions = [
            [
                'label' => __('cashvan::role.cashvan.view'),
                'value' => 'cashvan.view',
                'default' => false,
            ],
            [
                'label' => __('cashvan::role.cashvan.create'),
                'value' => 'cashvan.create',
                'default' => false
            ],
            [
                'label' => __('cashvan::role.cashvan.update'),
                'value' => 'cashvan.update',
                'default' => false,
            ],
            [
                'label' => __('cashvan::stock.cashvan.delete'),
                'value' => 'cashvan.delete',
                'default' => false,
            ],
            [
                'label' => __('cashvan::role.van_stock.create'),
                'value' => 'van_stock.create',
                'default' => false,
            ],[
                'label' => __('cashvan::stock.van_stock.view'),
                'value' => 'van_stock.view',
                'default' => false,
            ],
            [
                'label' => __('cashvan::stock.van_stock.view_history'),
                'value' => 'van_stock.view_history',
                'default' => false,
            ],
            [
                'label' => __('cashvan::stock.van_stock.delete'),
                'value' => 'van_stock.delete',
                'default' => false,
            ],
           
        ];

       

        return $module_permissions;
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('cashvan::create');
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
        return view('cashvan::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('cashvan::edit');
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
}

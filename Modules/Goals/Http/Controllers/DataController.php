<?php

namespace Modules\Goals\Http\Controllers;

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
        if (auth()->user()->can('goals.view')) {
            return Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->dropdown(
                        __('goals::goals.goals'),
                        function ($sub) {
                            if (auth()->user()->can('goals.groups.view')) {
                                $sub->url(
                                    action([\Modules\Goals\Http\Controllers\GroupController::class, 'index']),
                                    __('goals::goals.groups'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'goals']
                                );
                            }
                        },
                        ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="12" cy="12" r="9" />
                                <circle cx="12" cy="12" r="5" />
                                <circle cx="12" cy="12" r="1" />
                                </svg>', 'active' => request()->segment(1) == 'goals']
                    )->order('40');
                }
            );
        } else {
            return;
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
                'label' => __('goals::goals.goal_group.create'),
                'value' => 'goal_group.create',
                'default' => false,
            ],
            [
                'label' => __('goals::goals.goal_group.view'),
                'value' => 'goal_group.view',
                'default' => false
            ],
            [
                'label' => __('goals::goals.goals.create'),
                'value' => 'goals.create',
                'default' => false,
            ],
            [
                'label' => __('goals::goals.group_details.view'),
                'value' => 'group_details.view',
                'default' => false,
            ],
            [
                'label' => __('goals::goals.goals.view'),
                'value' => 'goals.view',
                'default' => false,
            ],
            [
                'label' => __('goals::goals.goal_group.update'),
                'value' => 'goal_group.update',
                'default' => false,
            ],
            [
                'label' => __('goals::goals.goal_group.delete'),
                'value' => 'goal_group.delete',
                'default' => false,
            ],


        ];



        return $module_permissions;
    }
}

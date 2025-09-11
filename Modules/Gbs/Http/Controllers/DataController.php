<?php


namespace Modules\Gbs\Http\Controllers;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Menu;

class DataController extends Controller
{
    /**
     * Defines module as a superadmin package.
     * @return Array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'gbs_module',
                'label' => 'gbs',
                'default' => false
            ]
        ];
    }

    /**
     * Adds Catalogue QR menus
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        $is_gbs_enabled = (boolean)$module_util->hasThePermissionInSubscription($business_id, 'gbs_module', 'superadmin_package');

        if ($is_gbs_enabled) {
         
    
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->dropdown(
                        __('gbs::lang.gbs'),
                        function ($sub) {
                            if (auth()->user()->can('gbs.show_roots')) {
                            $sub->url(
                                action('\Modules\Gbs\Http\Controllers\RouteController@index'),
                                __('gbs::lang.rootes'),
                                ['icon' => 'fa fa-route', 'active' => request()->segment(1) == 'gbs', 'style' => config('app.env') == 'demo' ? 'background-color: #ff851b;' : '']
                            );
                        }
                            if (auth()->user()->can('gbs.create_tags')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\GbsController@tags'),
                                    __('gbs::lang.tags'),
                                    ['icon' => 'fa fa-tag','active' => request()->segment(2) == 'tags']
                                );
                            }
                            if (auth()->user()->can('gbs::gbs.show_reports')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\ReportController@delegatePerformanceReport'),
                                    __('gbs::lang.user_performance'),
                                    ['icon' => 'fa fa-chart-line', 'active' => request()->segment(3) == 'user_performance']
                                );
                            }
                            if (auth()->user()->can('gbs::gbs.show_reports')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\ReportController@visitReport'),
                                    __('gbs::lang.visit_report'),
                                    ['icon' => 'fa fa-map-marker', 'active' => request()->segment(3) == 'visit_report']
                                );
                            }
                            if (auth()->user()->can('gbs::gbs.show_reports')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\ReportController@getShiftReport'),
                                    __('gbs::lang.shifts_report'),
                                    ['icon' =>'fa fa-user-clock', 'active' => request()->segment(3) == 'shifts_report']
                                );
                            }
                            if (auth()->user()->can('gbs::gbs.show_map')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\GbsController@contactsMap'),
                                    __('gbs::lang.Map'),
                                    ['icon' => 'fa fa-map-marker', 'active' => request()->segment(3) == 'map']
                                );
                            }
                            if (auth()->user()->can('gbs::gbs.show_reasons')) {
                                $sub->url(
                                    action('\Modules\Gbs\Http\Controllers\GbsFailureReasonController@index'),
                                    __('gbs::lang.reasons'),
                                    ['icon' => 'fa fa-times-circle', 'active' => request()->segment(3) == 'reasons']
                                );
                            }

              
                         },
                        ['icon' => 'fa fa-home'] 
                    )->order(86);
                }
            );
            
        }
    }
    public function user_permissions()
    {
        return [
            [
                'value' => 'gbs.can_visit_anywhere',
                'label' => __('gbs::lang.can_visit_anywhere'),
                'default' => false
            ],
            [
                'value' => 'gbs.create_tags',
                'label' => __('gbs::lang.create_tags'),
                'default' => false
            ],
            [
                'value' => 'gbs.edit_tags',
                'label' => __('gbs::lang.edit_tags'),
                'default' => false
            ],
            [
                'value' => 'gbs.show_reports',
                'label' => __('gbs::lang.show_reports'),
                'default' => false
            ],
            [
                'value' => 'gbs.delete_roots',
                'label' => __('gbs::lang.delete_roots'),
                'default' => false
            ],
            [
                'value' => 'gbs.create_roots',
                'label' => __('gbs::lang.create_roots'),
                'default' => false
            ],
            [
                'value' => 'gbs.edit_roots',
                'label' => __('gbs::lang.edit_roots'),
                'default' => false
            ],
            [
                'value' => 'gbs.show_map',
                'label' => __('gbs::lang.show_map'),
                'default' => false
            ],
            [
                'value' => 'gbs.edit_reasons',
                'label' => __('gbs::lang.edit_reasons'),
                'default' => false
            ],
            [
                'value' => 'gbs.delete_reasons',
                'label' => __('gbs::lang.delete_reasons'),
                'default' => false
            ],
            [
                'value' => 'gbs.show_reasons',
                'label' => __('gbs::lang.show_reasons'),
                'default' => false
            ],
            [
                'value' => 'gbs.add_reasons',
                'label' => __('gbs::lang.add_reasons'),
                'default' => false
            ],
        ];
    }
}
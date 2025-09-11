<?php

namespace App\Utils;

use App\Account;
use App\BusinessLocation;
use App\Product;
use App\System;
use App\Transaction;
use App\User;
use Composer\Semver\Comparator;
use Module;

class ModuleUtil extends Util
{
    /**
     * This function check if a module is installed or not.
     *
     * @param  string  $module_name (Exact module name, with first letter capital)
     * @return bool
     */
    public function isModuleInstalled($module_name)
    {
        $is_available = Module::has($module_name);

        if ($is_available) {
            //Check if installed by checking the system table {module_name}_version
            $module_version = System::getProperty(strtolower($module_name).'_version');
            if (empty($module_version)) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * This function check if superadmin module is installed or not.
     *
     * @return bool
     */
    public function isSuperadminInstalled()
    {
        return $this->isModuleInstalled('Superadmin');
    }

    /**
     * This function check if a function provided exist in all modules
     * DataController, merges the data and returned it.
     *
     * @param  string  $function_name
     * @return array
     */
    public function getModuleData($function_name, $arguments = null, $get_data_from_modules = [])
    {
        $modules = Module::toCollection()->toArray();

        $installed_modules = [];
        foreach ($modules as $module => $details) {
            if ($this->isModuleInstalled($details['name'])) {
                $installed_modules[] = $details;
            }
        }

         // If specific module names are provided, filter the installed modules
        if (!empty($get_data_from_modules) && is_array($get_data_from_modules)) {
            $installed_modules = array_filter($installed_modules, function ($module) use ($get_data_from_modules) {
                return in_array($module['name'], $get_data_from_modules);
            });
        }

        $data = [];
        if (! empty($installed_modules)) {
            foreach ($installed_modules as $module) {
                $class = 'Modules\\'.$module['name'].'\Http\Controllers\DataController';

                if (class_exists($class)) {
                    $class_object = new $class();
                    if (method_exists($class_object, $function_name)) {
                        if (! empty($arguments)) {
                            $data[$module['name']] = call_user_func([$class_object, $function_name], $arguments);
                        } else {
                            $data[$module['name']] = call_user_func([$class_object, $function_name]);
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Checks if a module is defined
     *
     * @param  string  $module_name
     * @return bool
     */
    public function isModuleDefined($module_name)
    {
        $is_installed = $this->isModuleInstalled($module_name);

        $check_for_enable = [];

        $output = ! empty($is_installed) ? true : false;

        if (in_array($module_name, $check_for_enable) &&
            ! $this->isModuleEnabled(strtolower($module_name))) {
            $output = false;
        }

        return $output;
    }

    /**
     * This function check if a business has active subscription packages
     *
     * @param  int  $business_id
     * @return bool
     */
    public function isSubscribed($business_id)
    {
        if ($this->isSuperadminInstalled()) {
            $package = \Modules\Superadmin\Entities\Subscription::active_subscription($business_id);
            if (empty($package)) {
                return false;
            }
        }

        return true;
    }

    /**
     * This function checks if a business has
     *
     * @param  int  $business_id
     * @param  string  $permission
     * @param  string  $callback_function = null
     * @return bool
     */
    public function hasThePermissionInSubscription($business_id, $permission, $callback_function = null)
    {
        if ($this->isSuperadminInstalled()) {
            if (auth()->user()->can('superadmin')) {
                return true;
            }

            $package = \Modules\Superadmin\Entities\Subscription::active_subscription($business_id);

            if (empty($package)) {
                return false;
            } elseif (isset($package['package_details'][$permission])) {
                if (! is_null($callback_function)) {
                    $obj = new ModuleUtil();
                    $permissions = $obj->getModuleData($callback_function);

                    $permission_formatted = [];
                    foreach ($permissions as $per) {
                        foreach ($per as $details) {
                            $permission_formatted[$details['name']] = $details['label'];
                        }
                    }

                    if (isset($permission_formatted[$permission])) {
                        return $package['package_details'][$permission];
                    } else {
                        return false;
                    }
                } else {
                    return $package['package_details'][$permission];
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the name of view used to display for subscription expired.
     *
     * @return string
     */
    public static function expiredResponse($redirect_url = null)
    {
        $response_array = ['success' => 0,
            'msg' => __(
                'superadmin::lang.subscription_expired_toastr',
                ['app_name' => config('app.name'),
                    'subscribe_url' => action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index']),
                ]
            ),
        ];

        if (request()->ajax()) {
            if (request()->wantsJson()) {
                return $response_array;
            } else {
                return view('superadmin::subscription.subscription_expired_modal');
            }
        } else {
            if (is_null($redirect_url)) {
                return back()
                    ->with('status', $response_array);
            } else {
                return redirect($redirect_url)
                    ->with('status', $response_array);
            }
        }
    }

    public function countBusinessLocation($business_id)
    {
        $count = BusinessLocation::where('business_id', $business_id)
                                ->count();

        return $count;
    }

    public function countUsers($business_id)
    {
        $count = User::where('business_id', $business_id)
                                    ->where('allow_login', 1)
                                    ->count();

        return $count;
    }

    public function countProducts($business_id, $start_dt, $end_dt)
    {
        $query = Product::where('business_id', $business_id);

        if (! empty($start_dt) && ! empty($start_dt)) {
            $query->whereBetween('created_at', [$start_dt, $end_dt]);
        }

        $count = $query->count();

        return $count;
    }

    public function countInvoice($business_id, $start_dt, $end_dt)
    {
        $query = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->where('status', 'final');

        if (! empty($start_dt) && ! empty($start_dt)) {
            $query->whereBetween('created_at', [$start_dt, $end_dt]);
        }

        $count = $query->count();

        return $count;
    }

    public function getResourceCount($business_id, $package)
    {
        $is_available = $this->isSuperadminInstalled();

        $start_dt = null;
        $end_dt = null;

        if (! empty($package)) {
            $start_dt = $package->start_date->toDateTimeString();
            $end_dt = $package->end_date->endOfDay()->toDateTimeString();
        }
        $output = [
            'locations_created' => $this->countBusinessLocation($business_id),
            'users_created' => $this->countUsers($business_id),
            'products_created' => $this->countProducts($business_id, $start_dt, $end_dt),
            'invoices_created' => $this->countInvoice($business_id, $start_dt, $end_dt),
        ];

        return $output;
    }

    /**
     * This function check if a business has available quota for various types.
     *
     * @param  string  $type
     * @param  int  $business_id
     * @param  int  $total_rows default 0
     * @return bool
     */
    public function isQuotaAvailable($type, $business_id, $total_rows = 0)
    {
        $is_available = $this->isSuperadminInstalled();

        if ($is_available) {
            $package = \Modules\Superadmin\Entities\Subscription::active_subscription($business_id);

            if (empty($package)) {
                return false;
            }

            //Start
            $start_dt = $package->start_date->toDateTimeString();
            $end_dt = $package->end_date->endOfDay()->toDateTimeString();

            if ($type == 'locations') {
                //Check for available location and max number allowed.
                $max_allowed = isset($package->package_details['location_count']) ? $package->package_details['location_count'] : 0;
                if ($max_allowed == 0) {
                    return true;
                } else {
                    $count = $this->countBusinessLocation($business_id);
                    if ($count >= $max_allowed) {
                        return false;
                    }
                }
            } elseif ($type == 'users') {
                //Check for available location and max number allowed.
                $max_allowed = isset($package->package_details['user_count']) ? $package->package_details['user_count'] : 0;
                if ($max_allowed == 0) {
                    return true;
                } else {
                    $count = $this->countUsers($business_id);
                    if ($count >= $max_allowed) {
                        return false;
                    }
                }
            } elseif ($type == 'products') {
                $max_allowed = isset($package->package_details['product_count']) ? $package->package_details['product_count'] : 0;
                if ($max_allowed == 0) {
                    return true;
                } else {
                    $count = $this->countProducts($business_id, $start_dt, $end_dt);

                    $total_products = $count + $total_rows;
                    if ($total_products >= $max_allowed) {
                        return false;
                    }
                }
            } elseif ($type == 'invoices') {
                $max_allowed = isset($package->package_details['invoice_count']) ? $package->package_details['invoice_count'] : 0;

                if ($max_allowed == 0) {
                    return true;
                } else {
                    $count = $this->countInvoice($business_id, $start_dt, $end_dt);
                    if ($count >= $max_allowed) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * This function returns the response for expired quota
     *
     * @param  string  $type
     * @param  int  $business_id
     * @param  string  $redirect_url = null
     * @return \Illuminate\Http\Response
     */
    public function quotaExpiredResponse($type, $business_id, $redirect_url = null)
    {
        if ($type == 'locations') {
            if (request()->ajax()) {
                if (request()->wantsJson()) {
                    $response_array = ['success' => 0,
                        'msg' => __('superadmin::lang.max_locations'),
                    ];

                    return $response_array;
                } else {
                    return view('superadmin::subscription.max_location_modal');
                }
            }
        } elseif ($type == 'users') {
            $response_array = ['success' => 0,
                'msg' => __('superadmin::lang.max_users'),
            ];

            return redirect($redirect_url)
                    ->with('status', $response_array);
        } elseif ($type == 'products') {
            $response_array = ['success' => 0,
                'msg' => __('superadmin::lang.max_products'),
            ];

            return redirect($redirect_url)
                    ->with('status', $response_array);
        } elseif ($type == 'invoices') {
            $response_array = ['success' => 0,
                'msg' => __('superadmin::lang.max_invoices'),
            ];

            if (request()->wantsJson()) {
                return $response_array;
            } else {
                return redirect($redirect_url)
                    ->with('status', $response_array);
            }
        }
    }

    public function accountsDropdown($business_id, $prepend_none = false, $closed = false, $show_balance = false)
    {
        $dropdown = [];

        if ($this->isModuleEnabled('account')) {
            $dropdown = Account::forDropdown($business_id, $prepend_none, $closed, $show_balance);
        }

        return $dropdown;
    }

    /**
     * This function returns the extra form fields in array format
     * required by any module which will be included during adding
     * or updating a resource
     *
     * @param  string  $function_name function name to be called to get data from
     * @return array
     */
    public function getModuleFormField($function_name)
    {
        $form_fields = [];
        $module_form_fields = $this->getModuleData($function_name);
        if (! empty($module_form_fields)) {
            foreach ($module_form_fields as $key => $value) {
                if (! empty($value) && is_array($value)) {
                    $form_fields = array_merge($form_fields, $value);
                }
            }
        }

        return $form_fields;
    }

    public function getApiSettings($api_token)
    {
        $settings = \Modules\Ecommerce\Entities\EcomApiSetting::where('api_token', $api_token)
                                ->first();

        return $settings;
    }

    /**
     * This function returns the installed version, available version
     * and uses comparator to check if update is available or not.
     *
     * @param  string  $module_name (Exact module name, with first letter capital)
     * @return array
     */
    public function getModuleVersionInfo($module_name)
    {
        $output = ['installed_version' => null,
            'available_version' => null,
            'is_update_available' => null,
        ];

        $is_available = Module::has($module_name);

        if ($is_available) {
            //Check if installed by checking the system table {module_name}_version
            $module_version = System::getProperty(strtolower($module_name).'_version');

            $output['installed_version'] = $module_version;
            $output['available_version'] = config(strtolower($module_name).'.module_version');

            $output['is_update_available'] = Comparator::greaterThan($output['available_version'], $output['installed_version']);
        }

        return $output;
    }

    public function availableModules()
    {
        return [
            'purchases' => ['name' => __('purchase.purchases')],
            'add_sale' => ['name' => __('sale.add_sale')],
            'pos_sale' => ['name' => __('sale.pos_sale')],
            'stock_transfers' => ['name' => __('lang_v1.stock_transfers')],
            'stock_adjustment' => ['name' => __('stock_adjustment.stock_adjustment')],
            'expenses' => ['name' => __('expense.expenses')],
            'account' => ['name' => __('lang_v1.account')],
            'drivers' => ['name' => __('lang_v1.drivers')],
            'tables' => ['name' => __('restaurant.tables'),
                'tooltip' => __('restaurant.tooltip_tables'),
            ],
            'modifiers' => ['name' => __('restaurant.modifiers'),
                'tooltip' => __('restaurant.tooltip_modifiers'),
            ],
            'service_staff' => [
                'name' => __('restaurant.service_staff'),
                'tooltip' => __('restaurant.tooltip_service_staff'),
            ],
            'booking' => ['name' => __('lang_v1.enable_booking')],
            'kitchen' => [
                'name' => __('restaurant.kitchen_for_restaurant'),
            ],
            'subscription' => ['name' => __('lang_v1.enable_subscription')],
            'types_of_service' => ['name' => __('lang_v1.types_of_service'),
                'tooltip' => __('lang_v1.types_of_service_help_long'),
            ],
        ];
    }
    public function availablePurchasesModules()
    {
        return [
            'enable_sale_period' => ['name' => __('sale.enable_sale_period')],
            'enable_purchase_date' => ['name' => __('purchase.enable_purchase_date')],
            'enable_reference_number' => ['name' => __('purchase.enable_reference_number')],
            'enable_document_attachment' => ['name' => __('sale.enable_document_attachment')],
            'enable_discount' => ['name' => __('sale.enable_discount')],
            'enable_tax' => ['name' => __('sale.enable_tax')],
            'enable_purchase_note' => ['name' => __('purchase.enable_purchase_note')],
            'enable_shipping' => ['name' => __('sale.enable_shipping')],
        ];
    }
    public function availableSaleModules()
    {
        return [
            'enable_sale_period' => ['name' => __('sale.enable_sale_period')],
            'enable_sale_date' => ['name' => __('sale.enable_sale_date')],
            'enable_invoice_design' => ['name' => __('sale.enable_invoice_design')],
            'enable_invoice_number' => ['name' => __('sale.enable_invoice_number')],
            'enable_document_attachment' => ['name' => __('sale.enable_document_attachment')],
            'enable_discount' => ['name' => __('sale.enable_discount')],
            'enable_tax' => ['name' => __('sale.enable_tax')],
            'enable_sale_note' => ['name' => __('sale.enable_sale_note')],
            'enable_shipping' => ['name' => __('sale.enable_shipping')],
        ];
    }
    public function availableProductsModules()
    {
        return [
            'enable_sku_symbol' => ['name' => __('product.enable_sku_symbol')],
            'enable_product_location' => ['name' => __('product.enable_product_location')],
            'enable_stock_management' => ['name' => __('product.enable_stock_management')],
            'enable_description' => ['name' => __('product.enable_description')],
            'enable_product_image' => ['name' => __('product.enable_product_image')],
            'enable_product_brochure' => ['name' => __('product.enable_product_brochure')],
            'enable_serial_number' => ['name' => __('product.enable_serial_number')],
            'enable_not_for_sale_button' => ['name' => __('product.enable_not_for_sale_button')],
            'enable_weight' => ['name' => __('product.enable_weight')],
            'enable_customized_fields' => ['name' => __('product.enable_customized_fields')],
            'enable_type' => ['name' => __('product.enable_type')],
            'enable_barcode_type' => ['name' => __('product.enable_barcode_type')],
            'enable_brand' => ['name' => __('product.enable_brand')],
            'enable_categories' => ['name' => __('product.enable_categories')],
            
        ];
    }
    public function availableUserModules()
    {
        return [
            'enable_surname' => ['name' => __('business.enable_surname')],
            'enable_is_active' => ['name' => __('business.enable_is_active')],
            'enable_service_staff_pin' => ['name' => __('business.enable_service_staff_pin')],
            'enable_user_location' => ['name' => __('business.enable_user_location')],
            'enable_sales_section' => ['name' => __('business.enable_sales_section')],
            'enable_more_info_section' => ['name' => __('business.enable_more_info_section')],
        ];
    }
    public function availableExpensesModules()
    {
        return [
            'enable_user_location' => ['name' => __('business.enable_user_location')],
            'enable_categories' => ['name' => __('product.enable_categories')],
            'enable_reference_number' => ['name' => __('purchase.enable_reference_number')],
            'enable_expense_date' => ['name' => __('expense.enable_expense_date')],
            'enable_expense_for' => ['name' => __('expense.enable_expense_for')],
            'enable_document_attachment' => ['name' => __('sale.enable_document_attachment')],
            'enable_tax' => ['name' => __('sale.enable_tax')],
            'enable_expense_note' => ['name' => __('expense.enable_expense_note')],
            'enable_payment' => ['name' => __('expense.enable_payment')],
            
        ];
    }
    public function availableContactsModules()
    {
        return [
            'enable_contact_type' => ['name' => __('contact.enable_contact_type')],
            'enable_contact_id' => ['name' => __('contact.enable_contact_id')],
            'enable_second_mobile_number' => ['name' => __('contact.enable_second_mobile_number')],
            'enable_phone' => ['name' => __('contact.enable_phone')],
            'enable_email' => ['name' => __('contact.enable_email')],
            'enable_custom_for' => ['name' => __('contact.enable_custom_for')],
            'enable_payment_period' => ['name' => __('contact.enable_payment_period')],
            'enable_opening_balance' => ['name' => __('contact.enable_opening_balance')],
            'enable_tax_number' => ['name' => __('contact.enable_tax_number')],
            'enable_credit_limit' => ['name' => __('contact.enable_credit_limit')],
            'enable_address' => ['name' => __('contact.enable_address')],
            'enable_customized_fields' => ['name' => __('product.enable_customized_fields')],
            'enable_shipping_address' => ['name' => __('contact.enable_shipping_address')],

        ];
    }

    public function availableQuickList()
    {
        return [
            'enable_add_user' => ['name' => __('lang_v1.enable_add_user')],
            'enable_add_purchase' => ['name' => __('lang_v1.enable_add_purchase')],
            'enable_add_product' => ['name' => __('lang_v1.enable_add_product')],
            'enable_add_sale' => ['name' => __('lang_v1.enable_add_sale')],
            'enable_sale_point' => ['name' => __('lang_v1.enable_sale_point')],
            'enable_add_stock_transfer' => ['name' => __('lang_v1.enable_add_stock_transfer')],
            'enable_stock_adjustment' => ['name' => __('lang_v1.enable_stock_adjustment')],
            'enable_add_expense' => ['name' => __('lang_v1.enable_add_expense')],
            'enable_profit_loss_report' => ['name' => __('lang_v1.enable_profit_loss_report')],
            'enable_purchase_sell_report' => ['name' => __('lang_v1.enable_purchase_sell_report')],
            'enable_taxes_report' => ['name' => __('lang_v1.enable_taxes_report')],
            'enable_customer_supplier_report' => ['name' => __('lang_v1.enable_customer_supplier_report')],
            'enable_customer_group_report' => ['name' => __('lang_v1.enable_customer_group_report')],
            'enable_stock_report' => ['name' => __('lang_v1.enable_stock_report')],
            'enable_stock_adjustment_report' => ['name' => __('lang_v1.enable_stock_adjustment_report')],
            'enable_items_report' => ['name' => __('lang_v1.enable_items_report')],
            'enable_product_purchase_report' => ['name' => __('lang_v1.enable_product_purchase_report')],
            'enable_product_sell_report' => ['name' => __('lang_v1.enable_product_sell_report')],
            'enable_purchase_payment_report' => ['name' => __('lang_v1.enable_purchase_payment_report')],
            'enable_sell_payment_report' => ['name' => __('lang_v1.enable_sell_payment_report')],
            'enable_expense_report' => ['name' => __('lang_v1.enable_expense_report')],
            'enable_register_report' => ['name' => __('lang_v1.enable_register_report')],
            'enable_sales_representative_report' => ['name' => __('lang_v1.enable_sales_representative_report')],
            'enable_table_report' => ['name' => __('lang_v1.enable_table_report')],
            'enable_service_staff_report' => ['name' => __('lang_v1.enable_service_staff_report')],

        ];
    }
    /**
     * Validate module category types and
     * return module category data if validates
     *
     * @param  string  $category_type
     * @return array
     */
    public function getTaxonomyData($category_type)
    {
        $category_types = ['product'];

        $modules_data = $this->getModuleData('addTaxonomies');

        // print_r($modules_data);

        // exit;

        $module_data = [];
        foreach ($modules_data as $module => $data) {
            foreach ($data  as $key => $value) {
                //key is category type
                //check if category type is duplicate
                if (! in_array($key, $category_types)) {
                    $category_types[] = $key;
                } else {
                    echo __('lang_v1.duplicate_taxonomy_type_found');
                    exit;
                }

                if ($category_type == $key) {
                    $module_data = $value;
                }
            }
        }

        if (! in_array($category_type, $category_types)) {
            echo __('lang_v1.taxonomy_type_not_found');
            exit;
        }

        return $module_data;
    }
}

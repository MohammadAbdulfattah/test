<?php

namespace App;

use App\Utils\Util;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'account_details' => 'array',
    ];

    public static function forDropdown($business_id, $prepend_none, $closed = false, $show_balance = false)
    {
        $query = Account::where('business_id', $business_id);
    
        // Get user's permitted accounts if specified
        $user = auth()->user();
        $user_accounts = $user->accounts ? json_decode($user->accounts, true) : null;
        //dd($user_accounts);
        // If user has specific accounts, filter by them
        if (!empty($user_accounts)) {
            $query->whereIn('accounts.id', $user_accounts);
        } else {
            // Apply location-based filtering as before
            $permitted_locations = $user->permitted_locations();
            $account_ids = [];
    
            if ($permitted_locations != 'all') {
                $locations = BusinessLocation::where('business_id', $business_id)
                                ->whereIn('id', $permitted_locations)
                                ->get();
    
                foreach ($locations as $location) {
                    if (!empty($location->default_payment_accounts)) {
                        $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                        foreach ($default_payment_accounts as $key => $account) {
                            if (!empty($account['is_enabled']) && !empty($account['account'])) {
                                $account_ids[] = $account['account'];
                            }
                        }
                    }
                }
    
                $account_ids = array_unique($account_ids);
                $query->whereIn('accounts.id', $account_ids);
            }
        }
    
        $can_access_account = $user->can('account.access');
        if ($can_access_account && $show_balance) {
            $query->select('accounts.name',
                    'accounts.id',
                    DB::raw("(SELECT SUM(IF(account_transactions.type='credit', amount, -1*amount)) as balance 
                            from account_transactions 
                            where account_transactions.account_id = accounts.id 
                            AND deleted_at is NULL) as balance")
                );
        }
    
        if (!$closed) {
            $query->where('is_closed', 0);
        }
    
        $accounts = $query->get();
    
        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }
    
        $default_account = $user->defult_account;
        $default_account_data = null;
    
        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;
    
            if ($can_access_account && $show_balance) {
                $name .= ' ('.__('lang_v1.balance').': '.$commonUtil->num_f($account->balance).')';
            }
    
            // Store default account separately if found
            if ($default_account && $account->id == $default_account) {
                $default_account_data = [$account->id => $name];
                continue;
            }
    
            $dropdown[$account->id] = $name;
        }
    
        // If default account exists, prepend it to the dropdown
        if ($default_account_data) {
            $dropdown = $default_account_data + $dropdown;
        }
    
        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Scope a query to only include non capital accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeNotCapital($query)
    // {
    //     return $query->where(function ($q) {
    //         $q->where('account_type', '!=', 'capital');
    //         $q->orWhereNull('account_type');
    //     });
    // }

    public static function accountTypes()
    {
        return [
            '' => __('account.not_applicable'),
            'saving_current' => __('account.saving_current'),
            'capital' => __('account.capital'),
        ];
    }

    public function account_type()
    {
        return $this->belongsTo(\App\AccountType::class, 'account_type_id');
    }
}

<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class EndTimeAfterStartTime implements Rule
{
    protected $startTime;

    public function __construct($startTime)
    {
        $this->startTime = $startTime;
    }

    public function passes($attribute, $value)
    {
        try {
            $start = Carbon::createFromFormat('h:i A', $this->startTime);
            $end = Carbon::createFromFormat('h:i A', $value);
            return $end->gt($start); // end must be > start
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return __('lang_v1.the_end_time');
    }
}
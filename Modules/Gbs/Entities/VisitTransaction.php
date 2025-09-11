<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitTransaction extends Model
{
    use HasFactory;

    protected $table = 'gbs_visit_transactions';
    protected $fillable = [
        'visit_id',
        'transaction_id',
        'invoice_no',
      
    ];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\VisitTransactionFactory::new();
    // }
    public function visit()
    {
        return $this->belongsTo(\Modules\Gbs\Entities\Visit::class);
    }

    public function transaction()
    {
        return $this->belongsTo(\APP\Transaction::class);
    }
}

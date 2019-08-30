<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    public function purchase_lines()
    {
        return $this->hasMany('App\PurchaseLine');
    }

    public function sell_lines()
    {
        return $this->hasMany('App\TransactionSellLine');
    }

    public function contact()
    {
        return $this->belongsTo('App\Contact', 'contact_id');
    }

    public function payment_lines()
    {
        return $this->hasMany('App\TransactionPayment');
    }

    public function location()
    {
        return $this->belongsTo('App\BusinessLocation', 'location_id');
    }

    public function business()
    {
        return $this->belongsTo('App\Business', 'business_id');
    }

    public function tax()
    {
        return $this->belongsTo('App\TaxRate', 'tax_id');
    }

    public function stock_adjustment_lines()
    {
        return $this->hasMany('App\StockAdjustmentLine');
    }
}

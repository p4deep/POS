<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionSellLine extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    public function transaction()
    {
        return $this->belongsTo('App\Transaction');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function variations()
    {
        return $this->belongsTo('App\Variation', 'variation_id');
    }

    public function modifiers()
    {
        return $this->hasMany('App\TransactionSellLine', 'parent_sell_line_id');
    }
}

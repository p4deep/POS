<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    public function product_variation()
    {
        return $this->belongsTo('App\ProductVariation');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    /**
     * Get the sell lines associated with the variation.
     */
    public function sell_lines()
    {
        return $this->hasMany('App\TransactionSellLine');
    }

    /**
     * Get the location wise details of the the variation.
     */
    public function variation_location_details()
    {
        return $this->hasMany('App\VariationLocationDetails');
    }
}

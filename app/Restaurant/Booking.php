<?php

namespace App\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo('App\Contact', 'contact_id');
    }

    public function table()
    {
        return $this->belongsTo('App\Restaurant\ResTable', 'table_id');
    }

    public function correspondent()
    {
        return $this->belongsTo('App\User', 'correspondent_id');
    }

    public function waiter()
    {
        return $this->belongsTo('App\User', 'waiter_id');
    }

    public function location()
    {
        return $this->belongsTo('App\BusinessLocation', 'location_id');
    }

    public function business()
    {
        return $this->belongsTo('App\Business', 'business_id');
    }
}

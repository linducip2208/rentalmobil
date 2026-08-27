<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CashFlowSnapshot extends Model
{
    protected $fillable = [
        'as_of_date',
        'horizon_days',
        'projected_inflow',
        'projected_outflow',
        'net_projection',
        'breakdown',
    ];


    protected function casts(): array
    {
        return [
            'as_of_date' => 'date',
            'horizon_days' => 'integer',
            'breakdown' => 'array',
        ];
    }


}

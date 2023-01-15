<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{

    protected $table = "suppliers";
    protected $fillable = [
        'owner_id', 'request_title', 'approval_status','request_message'
    ];

}

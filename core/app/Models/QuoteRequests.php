<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{

    protected $table = "quote_requests";
    protected $fillable = [
        'created_by_user_id', 'quote_title', 'quote_deadline','quote_document'
    ];

}

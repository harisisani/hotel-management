<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerDocuments extends Model
{

    protected $table = "busines_documents";
    protected $fillable = [
        'owner_id', 'document_title', 'document_name'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteProposals extends Model
{

    protected $table = "quote_proposals";
    protected $fillable = [
        'owner_id', 'quote_id', 'proposal_message','proposal_document'
    ];

}

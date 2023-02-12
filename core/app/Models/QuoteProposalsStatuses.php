<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteProposalsStatuses extends Model
{

    protected $table = "quote_proposals_statuses";
    protected $fillable = [
        'quote_proposal_id', 'status_message', 'status_type','status_document','deleted'
    ];

}
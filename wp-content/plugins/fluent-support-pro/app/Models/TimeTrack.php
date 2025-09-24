<?php

namespace FluentSupportPro\App\Models;

use FluentSupport\App\Models\Model;
use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Models\Ticket;

class TimeTrack extends Model
{
    protected $table = 'fs_time_tracks';

    protected $guarded = ['id'];

    protected $casts = [
        'working_minutes'  => 'int',
        'billable_minutes' => 'int',
        'is_manual'        => 'bool',
    ];

    protected $fillable = [
        'agent_id',
        'ticket_id',
        'customer_id',
        'mailbox_id',
        'started_at',
        'completed_at',
        'status',
        'working_minutes',
        'billable_minutes',
        'is_manual',
        'message'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function mailBox()
    {
        return $this->belongsTo(MailBox::class, 'mailbox_id', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}

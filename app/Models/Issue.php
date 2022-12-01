<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'index',
        'proj_id',
        'issue',
        'category',
        'action',
        'priority',
        'close_date',
        'initiator',
        'issue_owners',
        'sheet_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // protected $appends = ['action'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'open_date' => 'date',
        'close_date' => 'date',
    ];

    // public function getActionAttribute()
    // {
    //     $action = $this->issue_actions()->first();
    //     return isset($action) ? $action->action : null;
    // }

    // public function issue_actions()
    // {
    //     return $this->hasMany(IssueAction::class)->orderBy('action_date', 'desc');
    // }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }
}

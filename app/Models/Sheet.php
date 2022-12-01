<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $primaryKey = 'proj_id';

    protected $fillable = [
        'proj_id',
        'name',
        'order',
        'visible_columns',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'visible_columns' => 'array',
    ];

    public function issue()
    {
        return $this->hasMany(Issue::class);
    }
}

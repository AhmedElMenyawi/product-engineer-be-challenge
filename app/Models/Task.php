<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_token',
        'title',
        'description',
        'status',
        'priority',
        'team_id',
        'start_time',
        'end_time',
        'created_by',
        'assigned_to',
    ];

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }

    public static function updatableFields(): array
    {
        return [
            'title',
            'description',
            'status',
            'assigned_to',
            'priority',
            'team_id',
            'start_time',
            'end_time'
        ];
    }
}

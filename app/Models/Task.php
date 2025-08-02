<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_token',
        'title',
        'description',
        'status',
        'priority',
        'start_time',
        'end_time',
        'created_by_user_id',
        'assigned_to_user_id',
    ];

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TaskHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'changed_at'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}

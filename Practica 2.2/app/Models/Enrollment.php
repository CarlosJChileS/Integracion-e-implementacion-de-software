<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['user_id', 'course_id', 'cohort_id', 'enrolled_at'];

    protected function casts(): array
    {
        return ['enrolled_at' => 'immutable_datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $table = 'office';

    protected $primaryKey = 'office_id';

    public $timestamps = false;

    protected $fillable = ['office_name', 'room_number', 'floor', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

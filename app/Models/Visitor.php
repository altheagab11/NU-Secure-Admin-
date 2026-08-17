<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $table = 'visitor';

    protected $primaryKey = 'visitor_id';

    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'visitor_id', 'visitor_id');
    }

    public function displayName(): string
    {
        $name = trim(((string) ($this->first_name ?? '')).' '.((string) ($this->last_name ?? '')));

        return $name !== '' ? $name : 'Unknown Visitor';
    }
}

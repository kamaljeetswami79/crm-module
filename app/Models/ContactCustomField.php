<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'options',
        'required',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}

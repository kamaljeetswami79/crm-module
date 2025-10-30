<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergedCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'merged_contact_id',
        'contact_custom_field_id',
        'combined_value',
        'merge_details'
    ];

    protected $casts = [
        'merge_details' => 'array',
    ];

    public function mergedContact()
    {
        return $this->belongsTo(MergedContact::class);
    }

    public function customField()
    {
        return $this->belongsTo(ContactCustomField::class, 'contact_custom_field_id');
    }
}

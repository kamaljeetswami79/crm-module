<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergedContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_contact_id',
        'merged_contact_id',
        'merged_combined_name',
        'merged_combined_email',
        'merged_combined_phone',
        'merged_combined_gender',
        'merged_combined_profile_image',
        'merged_combined_additional_file',
        'merge_summary',
        'merged_at'
    ];

    protected $casts = [
        'merge_summary' => 'array',
        'merged_at' => 'datetime',
    ];

    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    public function mergedContact()
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }

    public function mergedCustomFieldValues()
    {
        return $this->hasMany(MergedCustomFieldValue::class);
    }

    public function scopeLatestForMaster($query, $masterId)
    {
        return $query->where('master_contact_id', $masterId)
                    ->orderBy('merged_at', 'desc')
                    ->first();
    }
}

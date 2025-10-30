<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'merged_into_id',
    ];

    protected $casts = [
        'gender' => 'string',
    ];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function getCustomFieldValue($fieldName)
    {
        $customField = ContactCustomField::where('name', $fieldName)->first();
        if (!$customField) {
            return null;
        }

        $value = $this->customFieldValues()
            ->where('contact_custom_field_id', $customField->id)
            ->first();

        return $value ? $value->value : null;
    }

    public function setCustomFieldValue($fieldName, $value)
    {
        $customField = ContactCustomField::where('name', $fieldName)->first();
        if (!$customField) {
            return false;
        }

        $this->customFieldValues()->updateOrCreate(
            ['contact_custom_field_id' => $customField->id],
            ['value' => $value]
        );

        return true;
    }

    public function mergedInto()
    {
        return $this->belongsTo(Contact::class, 'merged_into_id');
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into_id');
    }

    public function mergedContactRecords()
    {
        return $this->hasMany(MergedContact::class, 'master_contact_id');
    }

    public function mergedIntoRecord()
    {
        return $this->hasOne(MergedContact::class, 'merged_contact_id');
    }

    public function getLatestMergedData()
    {
        return $this->mergedContactRecords()->latest('merged_at')->first();
    }
}

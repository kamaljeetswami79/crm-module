<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'contact_custom_field_id',
        'value'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function customField()
    {
        return $this->belongsTo(ContactCustomField::class, 'contact_custom_field_id');
    }
}

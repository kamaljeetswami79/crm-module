<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomField;
use App\Models\ContactCustomFieldValue;
use App\Models\MergedContact;
use App\Models\MergedCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('customFieldValues')->whereNull('merged_into_id');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('gender') && $request->gender !== '') {
            $query->where('gender', $request->gender);
        }

        if ($request->has('custom_field') && $request->custom_field !== '' && $request->has('custom_value') && $request->custom_value !== '') {
            $fieldName = $request->custom_field;
            $fieldValue = $request->custom_value;
            
            $customField = ContactCustomField::where('name', $fieldName)->first();
            if ($customField) {
                $query->whereHas('customFieldValues', function($q) use ($customField, $fieldValue) {
                    $q->where('contact_custom_field_id', $customField->id)
                      ->where('value', 'like', "%{$fieldValue}%");
                });
            }
        }

        $contacts = $query->orderBy('created_at', 'asc')->paginate(10);
        
        $contacts->appends($request->except('page'));
        $customFields = ContactCustomField::active()->ordered()->get();

        if ($request->ajax()) {
            return view('contacts._table', compact('contacts', 'customFields'))->render();
        }

        return view('contacts.index', compact('contacts', 'customFields'));
    }

    public function create()
    {
        $customFields = ContactCustomField::active()->ordered()->get();
        return view('contacts.create', compact('customFields'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'email', 'phone', 'gender']);

        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $profileImageName = time() . '_' . $profileImage->getClientOriginalName();
            $profileImage->storeAs('profile_images', $profileImageName, 'public');
            $data['profile_image'] = $profileImageName;
        }

        if ($request->hasFile('additional_file')) {
            $additionalFile = $request->file('additional_file');
            $additionalFileName = time() . '_' . $additionalFile->getClientOriginalName();
            $additionalFile->storeAs('additional_files', $additionalFileName, 'public');
            $data['additional_file'] = $additionalFileName;
        }

        $contact = Contact::create($data);
        $this->handleCustomFields($contact, $request);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully!',
                'contact' => $contact->load('customFieldValues.customField')
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully!');
    }
    public function show(Contact $contact)
    {
       
        $contact->load('customFieldValues.customField');
        
        $mergedContacts = Contact::where('merged_into_id', $contact->id)->get();
       
        $masterContact = null;
        if ($contact->merged_into_id) {
            $masterContact = Contact::find($contact->merged_into_id);
        }
        
        $mergedData = null;
        if ($contact->merged_into_id) {
            $mergedData = $contact->mergedIntoRecord;
            
        } elseif ($contact->mergedContacts()->exists()) {
            $mergedData = $contact->getLatestMergedData();           
        }      
        
        return view('contacts.show', compact('contact', 'mergedContacts', 'masterContact', 'mergedData'));
    }


    public function edit(Contact $contact)
    {
        if ($contact->merged_into_id) {
            return redirect()->route('contacts.merged.view', $contact)
                ->with('error', 'This contact has been merged and cannot be edited.');
        }

        $contact->load('customFieldValues.customField');
        $customFields = ContactCustomField::active()->ordered()->get();
        return view('contacts.edit', compact('contact', 'customFields'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'email', 'phone', 'gender']);

        if ($request->hasFile('profile_image')) {
            if ($contact->profile_image) {
                Storage::disk('public')->delete('profile_images/' . $contact->profile_image);
            }
            
            $profileImage = $request->file('profile_image');
            $profileImageName = time() . '_' . $profileImage->getClientOriginalName();
            $profileImage->storeAs('profile_images', $profileImageName, 'public');
            $data['profile_image'] = $profileImageName;
        }

        if ($request->hasFile('additional_file')) {
            if ($contact->additional_file) {
                Storage::disk('public')->delete('additional_files/' . $contact->additional_file);
            }
            
            $additionalFile = $request->file('additional_file');
            $additionalFileName = time() . '_' . $additionalFile->getClientOriginalName();
            $additionalFile->storeAs('additional_files', $additionalFileName, 'public');
            $data['additional_file'] = $additionalFileName;
        }

        $contact->update($data);

        $this->handleCustomFields($contact, $request);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully!',
                'contact' => $contact->load('customFieldValues.customField')
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully!');
    }

    public function destroy(Contact $contact)
    {
        if ($contact->profile_image) {
            Storage::disk('public')->delete('profile_images/' . $contact->profile_image);
        }
        if ($contact->additional_file) {
            Storage::disk('public')->delete('additional_files/' . $contact->additional_file);
        }

        $contact->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully!'
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully!');
    }

    private function handleCustomFields($contact, $request)
    {
        $customFields = ContactCustomField::active()->get();
        
        foreach ($customFields as $customField) {
            $fieldName = 'custom_field_' . $customField->name;
            
            if ($request->has($fieldName)) {
                $value = $request->input($fieldName);
                
                if ($customField->required && empty($value)) {
                    continue; 
                }
                
                $contact->customFieldValues()->updateOrCreate(
                    ['contact_custom_field_id' => $customField->id],
                    ['value' => $value]
                );
            }
        }
    }

    public function getContactsByIds(Request $request)
    {
        $request->validate([
            'ids' => 'required|string'
        ]);

        $ids = explode(',', $request->ids);
        $contacts = Contact::whereIn('id', $ids)->whereNull('merged_into_id')->get();

        return response()->json([
            'success' => true,
            'contacts' => $contacts
        ]);
    }

    public function mergeContactsWithMaster(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'merge_id' => 'required|exists:contacts,id|different:master_id',
        ]);

        $master = Contact::with('customFieldValues')->findOrFail($request->master_id);
        $merge = Contact::with('customFieldValues')->findOrFail($request->merge_id);

        if ($master->merged_into_id || $merge->merged_into_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot merge contacts that have already been merged.',
            ], 422);
        }

        $mergeSummary = [
            'emails_merged' => false,
            'phones_merged' => false,
            'custom_fields_added' => [],
            'custom_fields_merged' => [],
            'custom_fields_conflicted' => [],
            'files_merged' => []
        ];

        $mergedCombinedData = [
            'merged_combined_email' => null,
            'merged_combined_phone' => null,
            'merged_combined_profile_image' => null,
            'merged_combined_additional_file' => null,
            'merged_combined_name' => null,
            'merged_combined_gender' => null,
        ];

        $masterUpdates = [];
        
        if (!$master->email && $merge->email) {
            $masterUpdates['email'] = $merge->email;
            $mergedCombinedData['merged_combined_email'] = $master->email ?: $merge->email;
            $mergeSummary['emails_merged'] = true;
        } else {
            $mergedCombinedData['merged_combined_email'] = $master->email;
        }

        if (!$master->phone && $merge->phone) {
            $masterUpdates['phone'] = $merge->phone;
            $mergedCombinedData['merged_combined_phone'] = $master->phone ?: $merge->phone;
            $mergeSummary['phones_merged'] = true;
        } else {
            $mergedCombinedData['merged_combined_phone'] = $master->phone;
        }

        if (!$master->profile_image && $merge->profile_image) {
            $masterUpdates['profile_image'] = $merge->profile_image;
            $mergedCombinedData['merged_combined_profile_image'] = $merge->profile_image;
            $mergeSummary['files_merged'][] = 'profile_image';
        } else {
            $mergedCombinedData['merged_combined_profile_image'] = $master->profile_image;
        }
        if (!$master->additional_file && $merge->additional_file) {
            $masterUpdates['additional_file'] = $merge->additional_file;
            $mergedCombinedData['merged_combined_additional_file'] = $merge->additional_file;
            $mergeSummary['files_merged'][] = 'additional_file';
        } else {
            $mergedCombinedData['merged_combined_additional_file'] = $master->additional_file;
        }
        $mergedCombinedData['merged_combined_name'] = $master->name;
        $mergedCombinedData['merged_combined_gender'] = $master->gender;

        if (!empty($masterUpdates)) {
            $master->update($masterUpdates);
        }

        $mergedCombinedData['master_contact_id'] = $master->id;
        $mergedCombinedData['merged_contact_id'] = $merge->id;
        $mergedCombinedData['merge_summary'] = $mergeSummary;
        $mergedCombinedData['merged_at'] = now();
        $mergedContactRecord = MergedContact::create($mergedCombinedData);

        $mergeFieldValues = $merge->customFieldValues->keyBy('contact_custom_field_id');
        foreach ($mergeFieldValues as $fieldId => $mergeValue) {
            $masterValue = $master->customFieldValues->where('contact_custom_field_id', $fieldId)->first();
            $customField = ContactCustomField::find($fieldId);
            
            $combinedValue = null;
            $mergeDetails = [
                'strategy_used' => 'keep_master',
                'master_value' => $masterValue ? $masterValue->value : null,
                'merged_value' => $mergeValue->value,
                'resolution' => 'Master value kept'
            ];
            
            if (!$masterValue) {
                $combinedValue = $mergeValue->value;
                $mergeDetails['strategy_used'] = 'add_missing';
                $mergeDetails['resolution'] = 'Field added from secondary contact (master had no value)';
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'contact_custom_field_id' => $fieldId,
                    'value' => $mergeValue->value
                ]);
                $mergeSummary['custom_fields_added'][] = [
                    'field_id' => $fieldId,
                    'field_name' => $customField->name ?? 'Unknown Field',
                    'field_type' => $customField->type ?? 'text',
                    'value' => $mergeValue->value
                ];
            } elseif (!$masterValue->value && $mergeValue->value) {
                $combinedValue = $mergeValue->value;
                $mergeDetails['strategy_used'] = 'fill_gap';
                $mergeDetails['resolution'] = 'Secondary value added (master had empty field)';
                $masterValue->update(['value' => $mergeValue->value]);
                $mergeSummary['custom_fields_added'][] = [
                    'field_id' => $fieldId,
                    'field_name' => $customField->name ?? 'Unknown Field',
                    'field_type' => $customField->type ?? 'text',
                    'value' => $mergeValue->value
                ];
            } elseif ($masterValue->value && $mergeValue->value && $masterValue->value !== $mergeValue->value) {
                $combinedValue = $masterValue->value;
                $mergeDetails['strategy_used'] = 'keep_master';
                $mergeDetails['resolution'] = 'Master value kept, both values stored in merged table';
                $mergeSummary['custom_fields_conflicted'][] = [
                    'field_id' => $fieldId,
                    'field_name' => $customField->name ?? 'Unknown Field',
                    'field_type' => $customField->type ?? 'text',
                    'master_value' => $masterValue->value,
                    'merged_value' => $mergeValue->value,
                    'resolution' => $mergeDetails['resolution']
                ];
            } else {
                $combinedValue = $masterValue->value;
            }

            MergedCustomFieldValue::create([
                'merged_contact_id' => $mergedContactRecord->id,
                'contact_custom_field_id' => $fieldId,
                'combined_value' => $combinedValue,
                'merge_details' => $mergeDetails
            ]);
        }

        $mergedContactRecord->update(['merge_summary' => $mergeSummary]);

        $merge->merged_into_id = $master->id;
        $merge->save();

        return response()->json([
            'success' => true,
            'message' => 'Contacts merged successfully using fill gaps strategy. Master contact data preserved, missing information added from secondary contact.',
            'master_contact' => $master->fresh('customFieldValues'),
            'merged_contact' => $merge,
            'merge_summary' => $mergeSummary
        ]);
    }

    public function showMergedContacts()
    {
        $mergedContacts = Contact::whereNotNull('merged_into_id')
            ->with('mergedInto')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('contacts.merged', compact('mergedContacts'));
    }

    public function showMergedContactView(Contact $contact)
    {
        if (!$contact->merged_into_id) {
            return redirect()->route('contacts.show', $contact)
                ->with('error', 'This contact has not been merged.');
        }
        
        $contact->load('customFieldValues.customField');
        $masterContact = Contact::find($contact->merged_into_id);
        if (!$masterContact) {
            return redirect()->route('contacts.show', $contact)
                ->with('error', 'This contact has not been merged.');
        }
        $mergedContacts = Contact::where('merged_into_id', $masterContact->id)->get();
        $mergedData = $contact->mergedIntoRecord;
        
        if ($mergedData) {
            $mergedData->load('mergedCustomFieldValues.customField');
        }
        
        return view('contacts.merged_view', compact('contact', 'masterContact', 'mergedContacts', 'mergedData'));
    }
}

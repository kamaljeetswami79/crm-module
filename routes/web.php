<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactCustomFieldController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/contacts/merge-with-master', [ContactController::class, 'mergeContactsWithMaster'])->name('contacts.merge-with-master');
    Route::get('/contacts/by-ids', [ContactController::class, 'getContactsByIds'])->name('contacts.by-ids');
    Route::get('/contacts/merged', [ContactController::class, 'showMergedContacts'])->name('contacts.merged');
    Route::get('/contacts/{contact}/merged-view', [ContactController::class, 'showMergedContactView'])->name('contacts.merged.view');
    Route::resource('contacts', ContactController::class);
    Route::resource('custom-fields', ContactCustomFieldController::class)->parameters([
        'custom-fields' => 'custom_field'
    ]);
    Route::patch('custom-fields/{custom_field}/toggle-status', [ContactCustomFieldController::class, 'toggleStatus'])->name('custom-fields.toggle-status');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

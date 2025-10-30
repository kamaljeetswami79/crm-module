<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_page_loads()
    {
        $response = $this->get('/contacts');
        $response->assertStatus(200);
    }

    public function test_can_create_contact()
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'gender' => 'male'
        ];

        $response = $this->post('/contacts', $contactData);
        $response->assertRedirect('/contacts');

        $this->assertDatabaseHas('contacts', $contactData);
    }
}

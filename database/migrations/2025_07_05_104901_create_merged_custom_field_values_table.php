<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('merged_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merged_contact_id'); // Reference to merged_contacts table
            $table->unsignedBigInteger('contact_custom_field_id');
            $table->text('combined_value')->nullable(); // Store the combined/merged value
            $table->json('merge_details')->nullable(); // Store details about how the merge was done
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('merged_contact_id')->references('id')->on('merged_contacts')->onDelete('cascade');
            $table->foreign('contact_custom_field_id')->references('id')->on('contact_custom_fields')->onDelete('cascade');
            
            // Indexes
            $table->index('merged_contact_id');
            $table->index('contact_custom_field_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merged_custom_field_values');
    }
};

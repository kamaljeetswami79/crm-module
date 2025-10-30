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
        Schema::create('merged_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_contact_id');
            $table->unsignedBigInteger('merged_contact_id');
            
            // Merged combined snapshot fields (result of merge, for historical reference)
            $table->string('merged_combined_name')->nullable();
            $table->text('merged_combined_email')->nullable(); // Store multiple emails
            $table->text('merged_combined_phone')->nullable(); // Store multiple phones
            $table->string('merged_combined_gender')->nullable();
            $table->string('merged_combined_profile_image')->nullable();
            $table->string('merged_combined_additional_file')->nullable();
            
            // Merge metadata
            $table->json('merge_summary')->nullable(); // Store detailed merge information
            $table->timestamp('merged_at');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('master_contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('merged_contact_id')->references('id')->on('contacts')->onDelete('cascade');
            
            // Indexes
            $table->index('master_contact_id');
            $table->index('merged_contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merged_contacts');
    }
};

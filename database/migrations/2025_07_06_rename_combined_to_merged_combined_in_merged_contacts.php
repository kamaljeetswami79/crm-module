<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merged_contacts', function (Blueprint $table) {
            $table->renameColumn('combined_name', 'merged_combined_name');
            $table->renameColumn('combined_email', 'merged_combined_email');
            $table->renameColumn('combined_phone', 'merged_combined_phone');
            $table->renameColumn('combined_gender', 'merged_combined_gender');
            $table->renameColumn('combined_profile_image', 'merged_combined_profile_image');
            $table->renameColumn('combined_additional_file', 'merged_combined_additional_file');
        });
    }

    public function down(): void
    {
        Schema::table('merged_contacts', function (Blueprint $table) {
            $table->renameColumn('merged_combined_name', 'combined_name');
            $table->renameColumn('merged_combined_email', 'combined_email');
            $table->renameColumn('merged_combined_phone', 'combined_phone');
            $table->renameColumn('merged_combined_gender', 'combined_gender');
            $table->renameColumn('merged_combined_profile_image', 'combined_profile_image');
            $table->renameColumn('merged_combined_additional_file', 'combined_additional_file');
        });
    }
};
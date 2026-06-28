<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statuses = [
            1 => ['name' => 'Pending', 'description' => 'Request is pending'],
            2 => ['name' => 'Accepted', 'description' => 'Request was accepted'],
            3 => ['name' => 'Completed', 'description' => 'Work is done'],
            4 => ['name' => 'Cancelled', 'description' => 'Request was cancelled'],
        ];

        foreach ($statuses as $id => $status) {
            $existing = DB::table('customization_statuses')->where('id', $id)->first();
            if ($existing) {
                // Ensure the name and description match exactly.
                DB::table('customization_statuses')
                    ->where('id', $id)
                    ->update([
                        'name' => $status['name'],
                        'description' => $status['description'],
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('customization_statuses')->insert([
                    'id' => $id,
                    'name' => $status['name'],
                    'description' => $status['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: we don't want to delete statuses that may be referenced.
    }
};

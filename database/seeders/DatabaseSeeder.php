<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create basic roles
        \App\Models\Role::firstOrCreate(['id' => 1], ['name' => 'Super Admin', 'description' => 'Top level admin']);
        \App\Models\Role::firstOrCreate(['id' => 2], ['name' => 'User', 'description' => 'Regular user']);

        // Create basic types required by the User model
        \App\Models\UserType::firstOrCreate(['id' => 1], ['name' => 'Admin', 'description' => 'Administrator']);
        \App\Models\UserType::firstOrCreate(['id' => 2], ['name' => 'User', 'description' => 'Regular User']);
        
        \App\Models\AccountType::firstOrCreate(['id' => 1], ['name' => 'Personal', 'description' => 'Personal Account']);
        \App\Models\OfferType::firstOrCreate(['id' => 1], ['name' => 'Sell Designs', 'description' => 'User sells design templates']);
        \App\Models\OfferType::firstOrCreate(['id' => 2], ['name' => 'Customization', 'description' => 'User provides customization services']);
        \App\Models\OfferType::firstOrCreate(['id' => 3], ['name' => 'Both', 'description' => 'User does both']);
        \App\Models\OfferType::firstOrCreate(['id' => 4], ['name' => 'None', 'description' => 'Regular client']);
        
        // Essential statuses
        \App\Models\CustomizationStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'description' => 'Request is pending']);
        \App\Models\CustomizationStatus::firstOrCreate(['id' => 2], ['name' => 'Accepted', 'description' => 'Request was accepted']);
        \App\Models\CustomizationStatus::firstOrCreate(['id' => 3], ['name' => 'Completed', 'description' => 'Work is done']);
        \App\Models\CustomizationStatus::firstOrCreate(['id' => 4], ['name' => 'Cancelled', 'description' => 'Request was cancelled']);

        // Asset and Template statuses
        \App\Models\AssetStatus::firstOrCreate(['id' => 1], ['name' => 'Published', 'description' => 'Visible to public']);
        \App\Models\TemplateStatus::firstOrCreate(['id' => 1], ['name' => 'Published', 'description' => 'Visible to public']);

        // Wallet history reference data
        \App\Models\WalletHistoryType::firstOrCreate(['id' => 1], ['name' => 'Credit', 'description' => 'Money added to wallet']);
        \App\Models\WalletHistoryType::firstOrCreate(['id' => 2], ['name' => 'Debit', 'description' => 'Money removed from wallet']);

        \App\Models\WalletHistoryStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'description' => 'Transaction is pending']);
        \App\Models\WalletHistoryStatus::firstOrCreate(['id' => 2], ['name' => 'Success', 'description' => 'Transaction completed successfully']);
        \App\Models\WalletHistoryStatus::firstOrCreate(['id' => 3], ['name' => 'Failed', 'description' => 'Transaction failed']);

        // Withdrawal statuses
        \App\Models\WithdrawalStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'description' => 'Withdrawal is pending manual processing', 'color' => 'warning']);
        \App\Models\WithdrawalStatus::firstOrCreate(['id' => 2], ['name' => 'Processing', 'description' => 'Withdrawal is being processed', 'color' => 'info']);
        \App\Models\WithdrawalStatus::firstOrCreate(['id' => 3], ['name' => 'Completed', 'description' => 'Withdrawal completed successfully', 'color' => 'success']);
        \App\Models\WithdrawalStatus::firstOrCreate(['id' => 4], ['name' => 'Failed', 'description' => 'Withdrawal failed', 'color' => 'danger']);

        // Notification types
        \App\Models\NotificationType::firstOrCreate(['id' => 1], ['name' => 'Email']);
        \App\Models\NotificationType::firstOrCreate(['id' => 2], ['name' => 'Push']);
        \App\Models\NotificationType::firstOrCreate(['id' => 3], ['name' => 'SMS']);

        // Test user creation removed for production deployments.
        // Reference data above is the only required seeding.
    }
}

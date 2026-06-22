<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // Subscription Types
        $pro  = SubscriptionType::firstOrCreate(['name' => 'Pro'],  ['description' => 'Pro subscription – unlimited uploads, 20 customization requests/month, pro badge, social links, advanced tools.']);
        $firm = SubscriptionType::firstOrCreate(['name' => 'Firm'], ['description' => 'Firm subscription – unlimited everything, firm badge, top-firms slideshare, social links, advanced tools.']);

        // Pro Plan
        SubscriptionPlan::updateOrCreate(
            ['name' => 'Pro', 'subscription_type_id' => $pro->id],
            [
                'description'   => 'Pro plan with unlimited asset uploads, groups, templates, 20 customisation requests/month, pro badge, and advanced tools.',
                'price'         => 0.99,
                'monthly_price' => 0.99,
                'annual_price'  => 10.90,
                'active'        => true,
            ]
        );

        // Firm Plan
        SubscriptionPlan::updateOrCreate(
            ['name' => 'Firm', 'subscription_type_id' => $firm->id],
            [
                'description'   => 'Firm plan with unlimited everything, firm badge, top-firms landing page slideshare, and advanced tools.',
                'price'         => 2.99,
                'monthly_price' => 2.99,
                'annual_price'  => 30.90,
                'active'        => true,
            ]
        );
    }
}

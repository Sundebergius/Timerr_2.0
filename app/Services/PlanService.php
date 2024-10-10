<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class PlanService
{
    // Define both product and price IDs in one place
    // The key values have been stored in config/services.php
    protected $plans = [
        'free' => [
            'limits' => [
                'clients' => 5,
                'projects' => 3,
                'products' => 5,
                'teams' => 1,
            ]
        ],
        'freelancer' => [
            'limits' => [
                'clients' => 25,
                'projects' => 10,
                'products' => 20,
                'teams' => 1,
            ]
        ],
        'freelancer_pro' => [
            'limits' => [
                'clients' => 75,
                'projects' => 15,
                'products' => 50,
                'teams' => 2,
                'team_members' => 10,
            ]
        ]
    ];

    // Determine if the app is in test mode
    protected function isTestMode()
    {
        return str_starts_with(Config::get('services.stripe.key'), 'pk_test_');
    }

    // Retrieve the product ID for a given plan from config/services.php
    public function getProductId($planName)
    {
        $env = $this->isTestMode() ? 'test' : 'live';
        return Config::get("services.stripe.plans.$env.$planName.product_id") ?? null;
    }

    // Retrieve the price ID for a given plan from config/services.php
    public function getPriceId($planName)
    {
        $env = $this->isTestMode() ? 'test' : 'live';
        return Config::get("services.stripe.plans.$env.$planName.price_id") ?? null;
    }

    // Method to get the plan name from a Stripe price ID
    public function getPlanNameByPriceId($priceId)
    {
        if (is_null($priceId)) {
            return 'free';
        }

        $env = $this->isTestMode() ? 'test' : 'live';
        $plans = Config::get("services.stripe.plans.$env");

        foreach ($plans as $plan => $details) {
            if (isset($details['price_id']) && $details['price_id'] === $priceId) {
                return $plan;
            }
        }

        return 'unknown';
    }

    public function getPlanLimits($planName)
    {
        // Return the limits array for the given plan
        return $this->plans[$planName]['limits'] ?? [];
    }

    // Return all plans (optional if needed)
    public function getAllPlans()
    {
        return $this->plans;
    }

    // Method to get the plan name from a Stripe product ID
    public function getPlanNameByProductId($productId)
    {
        foreach ($this->plans as $plan => $details) {
            // Check if the 'product' key exists before comparing
            if (isset($details['product']) && $details['product'] === $productId) {
                return $plan;
            }
        }
        return 'unknown';
    }
}

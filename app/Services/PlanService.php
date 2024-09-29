<?php

namespace App\Services;

class PlanService
{
    // Define both product and price IDs in one place
    protected $plans = [
        'free' => [
            'limits' => [
                'clients' => 5,
                'projects' => 3,
                'products' => 5,
                'teams' => 1, // Free plan can only have 1 team (personal team)
            ]
        ],
        'freelancer' => [
            'product' => 'prod_QuFyGzwZRxDsqV',
            'price' => 'price_1Q2RSpEEh64CES4EjOr0VQvr',
            'limits' => [
                'clients' => 25,
                'projects' => 10,
                'products' => 20,
                'teams' => 1, // Freelancer plan can only have 1 team (personal team)
            ]
        ],
        'freelancer_pro' => [
            'product' => 'prod_ProPlanID', // Example product ID for future plan
            'price' => 'price_ProPlanPriceID',
            'limits' => [
                'clients' => 75,
                'projects' => 15,
                'products' => 50,
                'teams' => 2, // Freelancer Pro can create up to 10 teams
                'team_members' => 10 // Freelancer Pro can have up to 10 team members
            ]
        ],
                // Add more plans here as needed
    ];

    // Method to get the Stripe price ID for a given plan
    public function getPriceId($planName)
    {
        return $this->plans[$planName]['price'] ?? null;
    }

    // Method to get the Stripe product ID for a given plan
    public function getProductId($planName)
    {
        return $this->plans[$planName]['product'] ?? null;
    }

    // Method to get the plan name from a Stripe price ID
    public function getPlanNameByPriceId($priceId)
    {
        // If there's no priceId (new user without a subscription), default to 'free'
        if (is_null($priceId)) {
            return 'free';
        }

        // Iterate through the plans and match the price ID
        foreach ($this->plans as $plan => $details) {
            if (isset($details['price']) && $details['price'] === $priceId) {
                return $plan;
            }
        }

        return 'unknown';
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
}

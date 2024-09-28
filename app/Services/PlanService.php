<?php

namespace App\Services;

class PlanService
{
    // Define both product and price IDs in one place
    protected $plans = [
        'free' => [
            'product' => 'prod_QuGyOeNHPgpG9w',         // Free plan product ID
            'price' => 'price_1Q3yTZEEh64CES4Ebu3507de' // Free plan price ID
        ],
        'freelancer' => [
            'product' => 'prod_QuFyGzwZRxDsqV',         // Freelancer plan product ID
            'price' => 'price_1Q2RSpEEh64CES4EjOr0VQvr' // Freelancer plan price ID
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
        foreach ($this->plans as $plan => $details) {
            if ($details['price'] === $priceId) {
                return $plan;
            }
        }
        return 'unknown';
    }

    // Method to get the plan name from a Stripe product ID
    public function getPlanNameByProductId($productId)
    {
        foreach ($this->plans as $plan => $details) {
            if ($details['product'] === $productId) {
                return $plan;
            }
        }
        return 'unknown';
    }

    // Return all plans (optional if needed)
    public function getAllPlans()
    {
        return $this->plans;
    }
}

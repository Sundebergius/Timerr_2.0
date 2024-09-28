<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;  // Your User model that uses Billable trait
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check for subscriptions that have passed their ends_at date and mark them as expired';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Fetch all users with canceled subscriptions that are not yet expired and have passed their `ends_at` date
        $usersWithSubscriptions = User::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'canceled')
                  ->whereNotNull('ends_at')
                  ->where('ends_at', '<', Carbon::now());
        })->get();

        foreach ($usersWithSubscriptions as $user) {
            $subscriptions = $user->subscriptions()->where('stripe_status', 'canceled')
                            ->whereNotNull('ends_at')
                            ->where('ends_at', '<', Carbon::now())
                            ->get();

            foreach ($subscriptions as $subscription) {
                $subscription->update([
                    'stripe_status' => 'expired', // Update to expired status
                    'updated_at' => now(),
                ]);

                \Log::info("Subscription ID {$subscription->stripe_id} for user {$user->id} marked as expired.");
            }
        }

        $this->info('Expired subscriptions have been processed.');
    }
}

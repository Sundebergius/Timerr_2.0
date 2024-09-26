<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Events\AddingTeam;
use Laravel\Jetstream\Jetstream;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  User  $user
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Team
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        // Dispatch event for adding team (if needed)
        AddingTeam::dispatch($user);

        // Create the team
        $team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => false,
        ]);

        // Assign the user as the owner of the new team
        $user->teams()->attach($team, ['role' => 'owner']);

        // Optionally switch to the new team
        $user->switchTeam($team);

        return $team;
    }
}
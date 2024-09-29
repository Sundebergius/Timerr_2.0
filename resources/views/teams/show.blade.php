<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            <!-- Update Team Name Section -->
            @livewire('teams.update-team-name-form', ['team' => $team])

            <!-- Team Member Management: Only show for non-personal teams -->
            @if (! $team->personal_team)
                @livewire('teams.team-member-manager', ['team' => $team])
            @endif

            <!-- Delete Team Section: Only show for non-personal teams -->
            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

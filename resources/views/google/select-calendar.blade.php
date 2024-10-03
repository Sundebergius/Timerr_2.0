<!-- resources/views/google/select-calendar.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Google Calendar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('google.save-selected-calendar') }}" method="POST">
                    @csrf
                    <label for="calendar">Choose a Google Calendar:</label>
                    <select id="calendar" name="google_calendar_id" class="form-control">
                        @foreach($calendars as $calendar)
                            <option value="{{ $calendar['id'] }}">{{ $calendar['summary'] }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary mt-4">Save Calendar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

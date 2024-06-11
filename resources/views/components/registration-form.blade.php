<form id="registration-form" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
    @csrf
    @if($task->task_type == 'hourly')
        <div class="mb-4">
            <label for="hours_worked" class="block text-gray-700 text-sm font-bold mb-2">Hours Worked:</label>
            <input type="number" id="hours_worked" name="hours_worked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
            <label for="minutes_worked" class="block text-gray-700 text-sm font-bold mb-2">Minutes Worked:</label>
            <input type="number" id="minutes_worked" name="minutes_worked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
    @endif
    @if($task->task_type == 'distance')
        <div class="mb-4">
            <label for="distance" class="block text-gray-700 text-sm font-bold mb-2">Distance Traveled:</label>
            <input type="number" id="distance" name="distance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <button type="button" onclick="submitRegistrationForm('{{ $task->task_type }}')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Create Registration
        </button>
    </div>
</form>
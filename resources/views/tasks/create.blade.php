<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Card Header -->
            <div class="bg-blue-600 text-white p-6">
                <h1 class="text-2xl font-bold">Create New Task</h1>
                <p class="text-sm mt-1">Fill in the details to create a new task for the project.</p>
            </div>

            <!-- Form Container -->
            <div id="app" class="p-6">
                <form id="taskForm" action="{{ route('projects.tasks.store', $project->id) }}" method="POST" @submit.prevent="submitForm">
                    @csrf

                    <!-- Task Creator Component -->
                    <task-creator 
                        :product-url="'{{ route('products.index') }}'" 
                        :project="{{ json_encode($project) }}" 
                        :user-id="{{ auth()->id() }}" 
                        @formSubmitted="submitForm">
                    </task-creator>

                    {{-- <!-- Submit Button -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-full shadow-lg transition duration-300 ease-in-out">
                            Save Task
                        </button>
                    </div> --}}
                </form>
            </div>
        </div>

        <!-- Floating Action Button (FAB) for Mobile -->
        <button class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white font-bold p-4 rounded-full shadow-lg transition duration-300 ease-in-out md:hidden" @click="submitForm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7v14" />
            </svg>
        </button>
    </div>
</x-app-layout>


{{-- <script>
    window.project = @json($project);
    window.userId = @json(auth()->id());
</script>

<script>
    new Vue({
      el: '#app',
      data: {
        project: window.project,
        userId: window.userId,
        },
      methods: {
            handleFormSubmission({ route, data }) {
            console.log('Form data:', data);
            // Check if 'type' key exists in data
            if (!data.hasOwnProperty('task_type')) {
                console.error('Type key is missing in form data');
                return;
            }

            // Submit the form data using Axios
            console.log('About to make axios.post request with url:', route, 'and formData:', data);
            axios.post(route, data)
            .then(response => {
            // Handle success
            console.log('Request was successful', response.data);
            })
            .catch(error => {
            // Handle error
            console.log('An error occurred', error);
            });
        },
      },
      mounted() {
            console.log('Project:', this.project);
            console.log('Project:', JSON.parse(JSON.stringify(this.project)));
            console.log('Type of project:', typeof this.project);
            console.log('User ID:', this.userId);
            },
    });
  </script> --}}
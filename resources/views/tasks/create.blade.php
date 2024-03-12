<x-app-layout>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Add New Task</h1>
        <div id="app">
            <form id="taskForm" action="{{ route('projects.tasks.store', json_decode($project)->id) }}" method="POST" @submit.prevent="$refs.taskCreator.handleFormSubmission">
                @csrf
                {{-- <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                    <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div> --}}

                <task-creator ref="taskCreator" v-bind:project="JSON.stringify(project)" @formSubmitted></task-creator>
                {{-- @if(isset($project))
                    <task-creator ref="taskCreator" project="{{ json_encode($project) }}"></task-creator>
                    <p>Project is defined.</p>
                @else
                    <p>$project is not defined.</p>
                @endif --}}
                
                <input type="hidden" id="hiddenInput" name="formData">
                <div class="flex items-center justify-between">
                    {{-- <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-96">
                        Add Task
                    </button> --}}
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<script>
    window.project = @json($project);
</script>

<script>
    new Vue({
      el: '#app',
      data: {
        project: window.project
        },
      methods: {
        // old handleFormSubmission method
        // handleFormSubmission(formData) {
        //     console.log('Form data:', formData);
        //   // Populate a hidden input field with the form data
        //   document.getElementById('hiddenInput').value = JSON.stringify(formData);

        //     // Set the form's action attribute based on the selected type
        //     var form = document.getElementById('taskForm');
        //     if (formData.type === 'project_based') {
        //         form.action = "/projects/" + this.project.id + "/tasks/storeProject";
        //     } else if (formData.type === 'hourly') {
        //         form.action = "/projects/" + this.project.id + "/tasks/storeHourly";
        //     } else {
        //         // Handle the case where the type key doesn't exist
        //         console.error('Error: The formData object does not contain a type key.');
        //         return;
        //     }
        //     // Add more conditions as needed

        //     // Submit the form data using Axios
        //     console.log('About to make axios.post request with url:', form.action, 'and formData:', formData);
        //     axios.post(form.action, formData)
        //     .then(response => {
        //         // Handle success
        //         console.log('Request was successful', response.data);
        //     })
        //     .catch(error => {
        //         // Handle error
        //         console.log('An error occurred', error);
        //     });
            
        // },
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
            },
    });
  </script>
  
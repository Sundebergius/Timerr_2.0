<x-app-layout>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Add New Task</h1>
        <div id="app">
            <form id="taskForm" action="{{ route('projects.tasks.store', json_decode($project)->id) }}" method="POST" @submit.prevent="$refs.taskCreator.handleFormSubmission">
                @csrf
                <task-creator :product-url="'{{ route('products.index') }}'" ref="taskCreator" v-bind:project="JSON.stringify(project)" v-bind:user-id="userId" @formSubmitted></task-creator>
                <input type="hidden" id="hiddenInput" name="formData">
                <div class="flex items-center justify-between">
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<script>
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
  </script>
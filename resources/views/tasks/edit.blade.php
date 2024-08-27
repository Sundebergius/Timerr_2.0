<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Task
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form id="task-update-form" method="POST"
                        action="{{ route('projects.tasks.update', [$project, $task]) }}">
                        @csrf
                        @method('PUT')

                        <!-- Hidden input for user_id -->
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                        <div class="mb-6">
                            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                            <input type="text" id="title" name="title" value="{{ $task->title }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Conditional blocks for each task type -->
                        @if ($task->task_type == 'project_based')
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg shadow-inner">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Project-Based Task</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="price"
                                            class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                                        <input type="text" id="price" name="price"
                                            value="{{ $task->taskable->price }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div>
                                        <label for="currency"
                                            class="block text-gray-700 text-sm font-bold mb-2">Currency:</label>
                                        <select id="currency" name="currency"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                            <option value="DKK"
                                                {{ $task->taskable->currency == 'DKK' ? 'selected' : '' }}>DKK </option>
                                            <option value="EUR"
                                                {{ $task->taskable->currency == 'EUR' ? 'selected' : '' }}>EUR </option>
                                            <option value="USD"
                                                {{ $task->taskable->currency == 'USD' ? 'selected' : '' }}>USD </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="startDate" class="block text-gray-700 text-sm font-bold mb-2">Start
                                            Date:</label>
                                        <input type="date" id="startDate" name="startDate"
                                            value="{{ date('Y-m-d', strtotime($task->taskable->start_date)) }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div>
                                        <label for="endDate" class="block text-gray-700 text-sm font-bold mb-2">End
                                            Date:</label>
                                        <input type="date" id="endDate" name="endDate"
                                            value="{{ date('Y-m-d', strtotime($task->taskable->end_date)) }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div class="col-span-2">
                                        <label for="location"
                                            class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
                                        <input type="text" id="location" name="location"
                                            value="{{ $task->taskable->project_location }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($task->task_type == 'hourly')
                            <div class="mb-6">
                                <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Hourly Task</h3>
                                    <div class="mb-4">
                                        <label for="hourly_wage"
                                            class="block text-gray-700 text-sm font-bold mb-2">Hourly Wage:</label>
                                        <input type="text" id="hourly_wage" name="hourly_wage"
                                            value="{{ $task->taskable->rate_per_hour }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg shadow-inner mt-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Registrations</h3>
                                    @foreach ($task->taskable->registrationHourly as $registration)
                                        @php
                                            $hours = floor($registration->minutes_worked / 60);
                                            $minutes = $registration->minutes_worked % 60;
                                        @endphp
                                        <div class="mb-4 item" data-task-type="hourly"
                                            data-registration-id="{{ $registration->id }}">
                                            <label for="registration_{{ $registration->id }}"
                                                class="block text-gray-700 text-sm font-bold mb-2">Registration
                                                #{{ $loop->iteration }}:</label>
                                            <div class="flex items-center space-x-4">
                                                <div class="w-1/2">
                                                    <label for="registration_hours_{{ $registration->id }}"
                                                        class="block text-gray-600 text-sm mb-1">Hours:</label>
                                                    <input type="number"
                                                        id="registration_hours_{{ $registration->id }}"
                                                        name="registrations[{{ $registration->id }}][hours]"
                                                        value="{{ $hours }}"
                                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                </div>
                                                <div class="w-1/2">
                                                    <label for="registration_minutes_{{ $registration->id }}"
                                                        class="block text-gray-600 text-sm mb-1">Minutes:</label>
                                                    <input type="number"
                                                        id="registration_minutes_{{ $registration->id }}"
                                                        name="registrations[{{ $registration->id }}][minutes]"
                                                        value="{{ $minutes }}"
                                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                </div>
                                                <button type="button"
                                                    class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                            </div>
                                            <div class="mt-4">
                                                <label for="registration_comment_{{ $registration->id }}" class="block text-gray-700 text-sm font-bold mb-2">
                                                    Comment:
                                                </label>
                                                <textarea id="registration_comment_{{ $registration->id }}" name="registrations[{{ $registration->id }}][comment]" rows="4"
                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ $registration->comment }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="button" onclick="openRegistrationModal()"
                                        class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Add Registration
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($task->task_type == 'product')
                            <div class="mb-4">
                                <h2 class="text-xl font-bold mb-2">Existing Products</h2>
                                <div id="existing-product-container" class="p-4">
                                    @foreach ($task->taskProduct as $taskProduct)
                                        <div class="item mb-4" data-task-type="product"
                                            data-product-id="{{ $taskProduct->product->id }}">
                                            <label class="block text-gray-700 text-sm font-bold mb-2">Product
                                                #{{ $loop->iteration }}: {{ $taskProduct->product->title }}</label>
                                            <div class="flex space-x-2">
                                                <input type="hidden"
                                                    name="items[{{ $taskProduct->product->id }}][product_id]"
                                                    value="{{ $taskProduct->product->id }}">
                                                <input type="number" id="product_quantity_{{ $taskProduct->id }}"
                                                    name="items[{{ $taskProduct->product->id }}][total_sold]"
                                                    value="{{ $taskProduct->total_sold }}"
                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                <button type="button"
                                                    class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mb-4" id="new-products-section" style="display: none;">
                                <h2 class="text-xl font-bold mb-2">Add New Products</h2>
                                <div id="new-product-container" class="p-4"></div>
                            </div>
                            <button type="button" id="add-product"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Add
                                Product</button>
                        @endif

                        @if ($task->task_type == 'distance')
                            <div class="mb-6">
                                <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Distance Task</h3>
                                    <div class="mb-4">
                                        <label for="price_per_km" class="block text-gray-700 text-sm font-bold mb-2">Price per KM:</label>
                                        <input type="text" id="price_per_km" name="price_per_km"
                                            value="{{ $task->taskable->price_per_km }}"
                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg shadow-inner mt-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Registrations</h3>
                                    @if ($task->taskable->registrationDistances)
                                        @foreach ($task->taskable->registrationDistances as $registration)
                                            <div class="mb-4 item" data-task-type="distance"
                                                data-registration-id="{{ $registration->id }}">
                                                <label for="registration_{{ $registration->id }}"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Registration
                                                    #{{ $loop->iteration }}:</label>
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-full">
                                                        <label for="registration_distance_{{ $registration->id }}"
                                                            class="block text-gray-600 text-sm mb-1">Distance:</label>
                                                        <input type="number" id="registration_distance_{{ $registration->id }}"
                                                            name="registrations[{{ $registration->id }}][distance]"
                                                            value="{{ $registration->distance }}"
                                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                    </div>
                                                    <button type="button"
                                                        class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <button type="button" onclick="openRegistrationModal()"
                                        class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Add Registration
                                    </button>
                                </div>
                            </div>
                        @endif 

                        @if ($task->task_type == 'other')
                            <div class="mb-4">
                                <h2 class="text-xl font-bold mb-2">Other Task</h2>
                                <label for="description"
                                    class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                                <textarea id="description" name="description"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ $task->taskable->description }}</textarea>
                            </div>

                            <div class="mb-4">
                                <h2 class="text-xl font-bold mb-2">Custom Fields</h2>
                                <div id="customFieldsContainer">
                                    @foreach ($task->customFields as $field)
                                        <div class="item mb-4 bg-gray-100 p-4 rounded shadow"
                                            data-custom-field-id="{{ $field->id }}" data-task-type="other">
                                            <label for="custom_field_{{ $field->id }}"
                                                class="block text-gray-700 text-sm font-bold mb-2">Custom Field
                                                #{{ $loop->iteration }}:</label>
                                            <div class="flex space-x-2">
                                                <input type="text" id="custom_field_{{ $field->id }}"
                                                    name="customFields[{{ $field->id }}][field]"
                                                    value="{{ $field->field }}"
                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                <button type="button"
                                                    class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="addCustomField" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Add Custom Field
                                </button>
                            </div>

                            <div id="checklistSectionsContainer">
                                @foreach ($task->checklistSections as $section)
                                    <div class="item mb-4 bg-gray-100 p-4 rounded shadow" data-checklist-section-id="{{ $section->id }}" data-task-type="other">
                                        <label for="checklist_section_{{ $section->id }}" class="block text-gray-700 text-sm font-bold mb-2">Checklist Section #{{ $loop->iteration }}:</label>
                                        <div class="flex space-x-2">
                                            <input type="text" id="checklist_section_{{ $section->id }}" name="checklistSections[{{ $section->id }}][title]" value="{{ $section->title }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                            <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                        </div>
                                        <div class="checklistItemsContainer">
                                            @foreach ($section->checklistItems as $item)
                                                <div class="item mb-4 bg-white p-2 rounded shadow-inner" data-checklist-item-id="{{ $item->id }}" data-task-type="other">
                                                    <label for="checklist_item_{{ $item->id }}" class="block text-gray-700 text-sm font-bold mb-2">Checklist Item #{{ $loop->iteration }}:</label>
                                                    <div class="flex space-x-2">
                                                        <input type="text" id="checklist_item_{{ $item->id }}" name="checklistSections[{{ $section->id }}][items][{{ $item->id }}][item]" value="{{ $item->item }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                        <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <!-- Add Checklist Item Button -->
                                            <button type="button" class="add-checklist-item bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                Add Checklist Item
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Add Checklist Section Button -->
                            <button type="button" id="addChecklistSection" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Checklist Section
                            </button>
                        @endif

                        <div class="mt-4">
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Registration Modal -->
    <div id="registration-modal"
        class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-md">
            <div class="px-6 py-4">
                <div class="text-lg font-medium text-gray-900">Add Registration</div>
                <div class="mt-4">
                    @include('components.registration-form', ['project' => $project, 'task' => $task])
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-100 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeRegistrationModal()"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    // Define the URLs in Blade syntax and assign them to JavaScript variables
    var storeHourlyUrl =
        "{{ route('projects.tasks.registrations.storeHourly', ['project' => $project->id, 'task' => $task->id]) }}";
    var storeDistanceUrl =
        "{{ route('projects.tasks.registrations.storeDistance', ['project' => $project->id, 'task' => $task->id]) }}";

    // document.getElementById('task-update-form').addEventListener('submit', function(event) {
    //     console.log('Form element:', event.target);
    //     console.log('Submit event listener called.');
    //     event.preventDefault(); // Prevent the form from submitting normally
    //     console.log('Form submitted with data:', Object.fromEntries(new FormData(event.target)));
    // });

    document.addEventListener('DOMContentLoaded', function() {
        var products = @json($products);
        var newProductCounter = 0;

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-item')) {
                console.log('Remove button clicked');
                var parentElement = event.target.closest('.item');
                console.log('Parent Element:', parentElement);

                var taskType = parentElement.dataset.taskType;
                console.log('Task Type:', taskType);

                var input;
                var id;
                var inputName;

                switch (taskType) {
                    case 'product':
                        input = parentElement.querySelector('input[name^="items["]');
                        break;
                    case 'distance':
                    case 'hourly':
                        input = parentElement.querySelector('input[name^="registrations["]');
                        break;
                    case 'other':
                        input = parentElement.querySelector(
                            'input[name^="customFields["], input[name^="checklistSections["], input[name^="checklistItems["]'
                        );
                        break;
                }

                if (input) {
                    var match = input.name.match(/\[(\d+)\]/);
                    if (match) {
                        id = match[1];

                        // Handle removal of newly created sections, items, and custom fields
                        if (id.startsWith('new_')) {
                            parentElement.remove();
                            return;
                        }
                    }
                

                    switch (taskType) {
                        case 'product':
                            inputName = 'items[' + id + '][_delete]';
                            break;
                        case 'distance':
                        case 'hourly':
                            inputName = 'registrations[' + id + '][_delete]';
                            break;
                            case 'other':
                            if (input.name.startsWith('customFields')) {
                                inputName = 'customFields[' + id + '][_delete]';
                            } else if (input.name.startsWith('checklistSections')) {
                                if (parentElement.dataset.checklistItemId) {
                                    var sectionElement = parentElement.closest('.item[data-checklist-section-id]');
                                    if (sectionElement) {
                                        var sectionId = sectionElement.dataset.checklistSectionId;
                                        var itemId = parentElement.dataset.checklistItemId;
                                        inputName = 'checklistSections[' + sectionId + '][items][' + itemId + '][_delete]';
                                    }
                                } else {
                                    inputName = 'checklistSections[' + id + '][_delete]';
                                }
                            }
                            break;
                    }

                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = inputName;
                    hiddenInput.value = 'true';
                    parentElement.appendChild(hiddenInput);
                }

                parentElement.classList.add('bg-red-200', 'transition', 'duration-500', 'ease-in-out');
                event.target.textContent = 'Cancel';
                event.target.classList.remove('remove-item', 'bg-red-500', 'hover:bg-red-700');
                event.target.classList.add('cancel-removal', 'bg-red-600', 'hover:bg-red-800');
            } else if (event.target.classList.contains('cancel-removal')) {
                var parentElement = event.target.closest('.item');
                var input = parentElement.querySelector('input[name*="_delete"]');
                if (input) {
                    input.remove();
                }
                parentElement.classList.remove('bg-red-200', 'transition', 'duration-500', 'ease-in-out');
                event.target.textContent = 'Remove';
                event.target.classList.remove('cancel-removal', 'bg-red-600', 'hover:bg-red-800');
                event.target.classList.add('remove-item', 'bg-red-500', 'hover:bg-red-700');
            }
        });

        var addProductButton = document.getElementById('add-product');

        if (addProductButton) {
            addProductButton.addEventListener('click', function() {
                var productContainer = document.getElementById('new-product-container');

                var div = document.createElement('div');
                div.className = 'product mb-4 flex items-center space-x-4';

                var select = document.createElement('select');
                select.className =
                    'block appearance-none w-1/2 bg-white border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500';
                select.name = 'new_products[' + newProductCounter + '][product_id]';

                products.forEach(function(product) {
                    var option = document.createElement('option');
                    option.value = product.id;
                    option.text = product.title;
                    select.appendChild(option);
                });

                var totalSoldInput = document.createElement('input');
                totalSoldInput.type = 'number';
                totalSoldInput.value = 1;
                totalSoldInput.className =
                    'block appearance-none w-1/2 bg-white border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500';
                totalSoldInput.name = 'new_products[' + newProductCounter + '][total_sold]';

                var removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.textContent = 'Remove';
                removeButton.className =
                    'remove-product ml-4 py-2 px-4 bg-red-500 text-white rounded hover:bg-red-700';

                div.appendChild(select);
                div.appendChild(totalSoldInput);
                div.appendChild(removeButton);

                productContainer.appendChild(div);

                document.getElementById('new-products-section').style.display = 'block';

                newProductCounter++;
            });
        }
    });

    document.getElementById('addCustomField').addEventListener('click', function() {
        var customFieldsContainer = document.getElementById('customFieldsContainer');
        var newFieldId = 'new_' + Date.now();

        var newFieldDiv = document.createElement('div');
        newFieldDiv.classList.add('item', 'mb-4', 'bg-gray-100', 'p-4', 'rounded', 'shadow');
        newFieldDiv.dataset.customFieldId = newFieldId;
        newFieldDiv.dataset.taskType = "other";

        newFieldDiv.innerHTML = `
            <label for="custom_field_${newFieldId}" class="block text-gray-700 text-sm font-bold mb-2">New Custom Field:</label>
            <div class="flex space-x-2">
                <input type="text" id="custom_field_${newFieldId}" name="customFields[new_${newFieldId}][field]" value="" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
            </div>
        `;

        customFieldsContainer.appendChild(newFieldDiv);
    });

    document.getElementById('addChecklistSection').addEventListener('click', function() {
        var checklistSectionsContainer = document.getElementById('checklistSectionsContainer');
        var newSectionId = 'new_' + Date.now();

        var newSectionDiv = document.createElement('div');
        newSectionDiv.classList.add('item', 'mb-4', 'bg-gray-100', 'p-4', 'rounded', 'shadow');
        newSectionDiv.dataset.checklistSectionId = newSectionId;
        newSectionDiv.dataset.taskType = "other";

        newSectionDiv.innerHTML = `
            <label for="checklist_section_${newSectionId}" class="block text-gray-700 text-sm font-bold mb-2">New Checklist Section:</label>
            <div class="flex space-x-2">
                <input type="text" id="checklist_section_${newSectionId}" name="checklistSections[${newSectionId}][title]" value="" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
            </div>
            <div class="checklistItemsContainer">
                <!-- Add Checklist Item Button -->
                <button type="button" class="add-checklist-item bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Add Checklist Item
                </button>
            </div>
        `;

        checklistSectionsContainer.appendChild(newSectionDiv);
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('add-checklist-item')) {
            var checklistItemsContainer = e.target.closest('.item').querySelector('.checklistItemsContainer');
            var newSectionId = e.target.closest('.item').getAttribute('data-checklist-section-id');
            var newItemId = 'new_' + Date.now();

            var newItemDiv = document.createElement('div');
            newItemDiv.classList.add('item', 'mb-4', 'bg-white', 'p-2', 'rounded', 'shadow-inner');
            newItemDiv.dataset.checklistItemId = newItemId;
            newItemDiv.dataset.taskType = "other";

            newItemDiv.innerHTML = `
                <label for="checklist_item_${newItemId}" class="block text-gray-700 text-sm font-bold mb-2">New Checklist Item:</label>
                <div class="flex space-x-2">
                    <input type="text" id="checklist_item_${newItemId}" name="checklistSections[${newSectionId}][items][${newItemId}][item]" value="" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <button type="button" class="remove-item bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove</button>
                </div>
            `;

            checklistItemsContainer.appendChild(newItemDiv);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item')) {
            var parentElement = e.target.closest('.item');
            var id = parentElement.getAttribute('data-custom-field-id') || parentElement.getAttribute('data-checklist-section-id') || parentElement.getAttribute('data-checklist-item-id');

            if (id && id.startsWith('new_')) {
                parentElement.remove();
            } else if (!id) {
                console.error('ID is null');
            }
        }
    });

    function openRegistrationModal() {
        document.getElementById('registration-modal').classList.remove('hidden');
    }

    function closeRegistrationModal() {
        document.getElementById('registration-modal').classList.add('hidden');
    }

    function submitRegistrationForm(taskType) {
        console.log('Task Type:', taskType); // Log the task type

        var form = document.getElementById('registration-form');
        var formData = new FormData(form);

        // Check if the hours and minutes fields are empty, and if they are, set their value to 0
        var hoursInput = document.getElementById('hours_worked');
        var minutesInput = document.getElementById('minutes_worked');
        if (hoursInput && (!formData.get('hours_worked') || formData.get('hours_worked').trim() === '')) {
            formData.set('hours_worked', '0');
        }
        if (minutesInput && (!formData.get('minutes_worked') || formData.get('minutes_worked').trim() === '')) {
            formData.set('minutes_worked', '0');
        }

        // Specifically log the distance value
        var distanceInput = document.getElementById('distance');
        var distanceValue = distanceInput ? distanceInput.value : null;
        console.log('Distance Value:', distanceValue);

        // Set the value of the 'distance' field in the form data
        formData.set('distance', distanceValue);

        // Log each form data entry
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Check if the distance field is filled in for 'distance' task type
        if (taskType === 'distance') {
            if (distanceInput && (!distanceValue || distanceValue.trim() === '')) {
                distanceInput.setCustomValidity('Please fill in the distance field');
                distanceInput.reportValidity();
                return;
            } else if (distanceInput) {
                distanceInput.setCustomValidity('');
            }
        }

        // Check if the hours field is filled in for 'hourly' task type
        var hoursInput = document.getElementById('hours_worked');
        var minutesInput = document.getElementById('minutes_worked');
        if (taskType === 'hourly') {
            var hoursValue = hoursInput ? formData.get('hours_worked') : null;
            var minutesValue = minutesInput ? formData.get('minutes_worked') : null;

            // Convert the hours and minutes values to integers
            var hoursInt = parseInt(hoursValue, 10);
            var minutesInt = parseInt(minutesValue, 10);

            // Check if both the hours and minutes are 0
            if (hoursInput && minutesInput && (isNaN(hoursInt) || hoursInt === 0) && (isNaN(minutesInt) ||
                    minutesInt === 0)) {
                hoursInput.setCustomValidity('Please fill in either the hours or minutes field');
                minutesInput.setCustomValidity('Please fill in either the hours or minutes field');
                hoursInput.reportValidity();
                minutesInput.reportValidity();
                return;
            } else {
                if (hoursInput) hoursInput.setCustomValidity('');
                if (minutesInput) minutesInput.setCustomValidity('');
            }
        }

        var route;
        if (taskType === 'hourly') {
            route = storeHourlyUrl;
        } else if (taskType === 'distance') {
            route = storeDistanceUrl;
        }

        console.log('Route:', route); // Log the route

        fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                console.log('Response:', response); // Log the response

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Log the response data

                if (data.success) {
                    closeRegistrationModal();
                    location.reload();
                } else {
                    alert('Error creating registration');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error); // Log the error
                alert('Error creating registration');
            });
    }
</script>

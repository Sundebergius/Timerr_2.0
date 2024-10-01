<!DOCTYPE html>
<html lang='en'>

<head>
    <!-- Character Set and CSRF Token -->
    <meta charset='utf-8' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Viewport Settings for Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page Title -->
    <title>Calendar</title>
    
    <!-- Bootstrap CSS for Styling -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* FullCalendar Styling */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            height: 100vh;
        }

        /* Enhanced style for highlighted event */
        .highlighted-event {
            border: 3px solid #f39c12 !important; 
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 0 15px rgba(243, 156, 18, 0.8), 0 0 5px rgba(0, 0, 0, 0.2); 
        }

        .highlighted-event:hover {
            box-shadow: 0 0 20px rgba(243, 156, 18, 1), 0 0 10px rgba(0, 0, 0, 0.3);
            transform: scale(1.02);
        }

        /* Pulse animation for highlighted event (optional) */
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 15px rgba(243, 156, 18, 0.8), 0 0 5px rgba(0, 0, 0, 0.2);
            }
            50% {
                box-shadow: 0 0 20px rgba(243, 156, 18, 1), 0 0 10px rgba(0, 0, 0, 0.3);
            }
        }

        .highlighted-event.pulse {
            animation: pulse 1.5s infinite;
        }

        /* FullCalendar button styling */
        .fc-button {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 4px;
            margin: 0 5px;
            transition: background-color 0.3s ease;
        }

        .fc-button:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .fc-button-active {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }

        /* Styling for FullCalendar title */
        .fc-toolbar-title {
            font-size: 1.75rem;  /* Larger for better visibility */
            font-weight: bold;
            text-align: center;
            margin: 30px 0; /* Add space above and below the title */
        }

        /* FullCalendar toolbar layout and spacing */
        .fc-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        /* Flexbox for mobile responsiveness */
        @media (max-width: 768px) {
            .fc-toolbar {
                flex-direction: column;
                align-items: center;
            }

            .fc-toolbar-chunk {
                justify-content: center;
                flex-direction: column; /* Stack buttons on mobile */
                margin-bottom: 20px;
            }

            .fc-button {
                width: 100%; /* Full-width buttons on mobile */
                margin: 5px 0;
            }

            .fc-toolbar-title {
                margin-bottom: 20px;
            }
        }
    </style>

    <!-- jQuery: Required for Bootstrap and Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- FullCalendar: Main Calendar Library -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <!-- XLSX.js: For Importing/Exporting Calendar Data -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    
    <!-- Bootstrap JS: Required for Modal and Other Bootstrap Components -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
</head>

<body>
    <div class="container mt-5">
        <div class="row mb-3 align-items-center"> 
            <!-- Search Input Area -->
            <div class="col-md-8 mb-3 mb-md-0">
                <div class="input-group w-full">
                    <div class="relative w-full">
                        <!-- Search Input -->
                        <input id="searchInput" type="text" class="form-control w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search for an event" autocomplete="off" />

                        <!-- Dropdown for search results -->
                        <div id="dropdown" class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg hidden">
                            <ul id="searchResults" class="list-none p-0 m-0"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Actions Area -->
            <div class="col-md-4 d-flex justify-content-md-end justify-content-center">
                <div class="btn-group" role="group" aria-label="Calendar Actions">
                    <button id="exportButton" class="btn btn-primary mx-2 px-4" onclick="exportCalendar()">{{ __('Export Calendar') }}</button>
                    <button id="importButton" class="btn btn-primary mx-2 px-4" data-toggle="modal" data-target="#importModal">{{ __('Import Calendar') }}</button>
                    <button id="addEventButton" class="btn btn-primary mx-2 px-4" data-toggle="modal" data-target="#eventModal">{{ __('Add') }}</button>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="card">
            <div class="card-body">
                <div id='calendar'></div>
            </div>
        </div>
    </div>

    <!-- Import ICS Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Calendar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="icsFile">Choose ICS File</label>
                            <input type="file" class="form-control-file" id="icsFile" name="icsFile" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Import</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for='title'>{{ __('Title') }}</label>
                            <input type='text' class='form-control' id='title' name='title' required>
                        </div>
                        <div class="form-group">
                            <label for="start">{{ __('Start') }}</label>
                            <input type='datetime-local' class='form-control' id='start' name='start' required>
                        </div>
                        <div class="form-group">
                            <label for="end">{{ __('End') }}</label>
                            <input type='datetime-local' class='form-control' id='end' name='end' required>
                        </div>
                        <div class="form-group">
                            <label for="description">{{ __('Description') }}</label>
                            <textarea id="description" name="description" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="color" class="form-control" id="color" name="color">
                        </div>
                        <div class="form-group">
                            <label for="project">Project</label>
                            <select id="project" name="project_id" class="form-control">
                                <option value="">Select Project</option>
                                <!-- Project options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="client">Client</label>
                            <select id="client" name="client_id" class="form-control">
                                <option value="">Select Client</option>
                                <!-- Client options will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" id="deleteButton" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cachedProjects = null;
        let cachedClients = null;
        let cachedEvents = null;
        
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today', // Place navigation buttons to the left
                    center: 'title',         // Center the title (date)
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'  // Place the view buttons to the right
                },
                initialView: 'dayGridMonth',
                timeZone: 'local',
                events: '/events',
                editable: true,
                selectable: true,
                eventClick: handleEventClick,
                select: handleSelect,
                eventSourceSuccess: function(content, xhr) {
                    cachedEvents = content;
                }
            });

            calendar.render();
    
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const dropdown = document.getElementById('dropdown');

            // Listen for input on the search field
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const filteredEvents = cachedEvents.filter(event => event.title.toLowerCase().includes(query));
                updateDropdown(filteredEvents);
            });

            // Update dropdown with filtered events
            function updateDropdown(events) {
                searchResults.innerHTML = '';
                if (events.length === 0) {
                    dropdown.classList.add('hidden');
                    return;
                }

                events.forEach(event => {
                    const li = document.createElement('li');
                    li.classList.add('p-2', 'cursor-pointer', 'hover:bg-blue-100', 'border-b');
                    li.textContent = event.title;
                    li.addEventListener('click', function () {
                        navigateToEvent(event);
                    });
                    searchResults.appendChild(li);
                });

                dropdown.classList.remove('hidden');
            }

            // Function to navigate to the selected event
            function navigateToEvent(event) {
                const currentView = calendar.view.type;
                const eventStart = new Date(event.start);

                // Navigate based on the current view
                if (currentView === 'dayGridMonth') {
                    calendar.gotoDate(eventStart); // Navigate to the event's month
                } else if (currentView === 'timeGridWeek' || currentView === 'timeGridDay') {
                    calendar.gotoDate(eventStart); // Navigate to the week/day
                    calendar.changeView(currentView); // Retain the current view (week/day)
                }

                // Highlight the event by adding a border effect
                const calendarEvent = calendar.getEventById(event.id);
                if (calendarEvent) {
                    removeEventHighlight(); // Remove highlight from previous events
                    highlightEvent(calendarEvent); // Highlight the selected event
                }

                dropdown.classList.add('hidden'); // Hide the dropdown
                searchInput.value = ''; // Clear the search input
            }

            // Remove highlight from all events (if previously highlighted)
            function removeEventHighlight() {
                cachedEvents.forEach(event => {
                    const calendarEvent = calendar.getEventById(event.id);
                    if (calendarEvent) {
                        calendarEvent.setProp('borderColor', calendarEvent.extendedProps.originalBorderColor || '');
                        calendarEvent.setProp('classNames', ''); // Remove any additional classes
                    }
                });
            }

            // Highlight the selected event
            function highlightEvent(calendarEvent) {
                const originalBorderColor = calendarEvent.borderColor || '#000'; // Save original color if exists
                calendarEvent.setExtendedProp('originalBorderColor', originalBorderColor); // Store the original border color
                calendarEvent.setProp('borderColor', '#ff0000'); // Set highlight border color
                calendarEvent.setProp('classNames', 'highlighted-event'); // Optional: add a CSS class for custom styling
            }
    
            // Preload project and client data into the cache
            preloadData();
    
            function preloadData() {
                // Show loading indicators while fetching
                document.getElementById('project').innerHTML = '<option>Loading projects...</option>';
                document.getElementById('client').innerHTML = '<option>Loading clients...</option>';
                
                // Wait for both projects and clients to load before removing the "loading" text
                Promise.all([
                    fetchData('/api/events/projects', 'project').then(data => cachedProjects = data),
                    fetchData('/api/events/clients', 'client').then(data => cachedClients = data)
                ]).then(() => {
                    populateDropdowns();
                });
            }
    
            // Clear modal data and reset dropdowns when the modal is closed
            $('#eventModal').on('hidden.bs.modal', function () {
                $(this).removeData('eventId');
                $(this).removeData('projectId');
                $(this).removeData('clientId');
                $('#project').val(''); // Reset project dropdown
                $('#client').val(''); // Reset client dropdown
            });
    
            // Populate dropdowns for projects and clients, using cached data if available
            function populateDropdowns(selectedProjectId = null, selectedClientId = null) {
                populateDropdown('project', cachedProjects, selectedProjectId);
                populateDropdown('client', cachedClients, selectedClientId);
            }
    
            // Populate dropdowns with cached data or fetch if unavailable
            function populateDropdown(elementId, cachedData, selectedValue = null) {
                const selectElement = document.getElementById(elementId);
    
                if (cachedData) {
                    selectElement.innerHTML = ''; // Clear existing options
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = `Select a ${elementId}`;
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    selectElement.appendChild(placeholder);
    
                    cachedData.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name || item.title;
                        if (item.id == selectedValue) {
                            option.selected = true; // Set the option as selected if it matches the event value
                        }
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = `<option>Loading ${elementId}...</option>`; // Show loading if data is not cached
                }
            }
    
            // Fetch data function
            function fetchData(url, elementId) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'include'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok.');
                    }
                    return response.json();
                })
                .catch(error => console.error(`Error fetching ${elementId}:`, error));
            }
    
            // Handle Event Creation (user selects a time frame)
            function handleSelect(info) {
                // Clear the project and client dropdowns to avoid populating with old data
                $('#project').val('');
                $('#client').val('');
    
                $('#start').val(formatDateForInput(new Date(info.startStr)));
                $('#end').val(formatDateForInput(new Date(info.endStr || info.startStr)));
                $('#title').val('');
                $('#description').val('');
                $('#color').val(logoColor); // Always use the logo color
                $('#deleteButton').hide(); // Hide delete button on new event creation
    
                // Remove any eventId from the modal data when creating a new event
                $('#eventModal').removeData('eventId');
                $('#eventModal').removeData('projectId');
                $('#eventModal').removeData('clientId');
    
                $('#eventModal').modal('show');
                $('#eventForm').off('submit').on('submit', handleCreateEvent);
            }
    
            // Utility function to format date for datetime-local input
            function formatDateForInput(date) {
                const year = date.getFullYear();
                const month = ('0' + (date.getMonth() + 1)).slice(-2);
                const day = ('0' + date.getDate()).slice(-2);
                const hours = ('0' + date.getHours()).slice(-2);
                const minutes = ('0' + date.getMinutes()).slice(-2);
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
    
            // Handle Event Creation (when submitted)
            function handleCreateEvent(event) {
                event.preventDefault();
                const eventData = {
                    title: $('#title').val(),
                    start: $('#start').val(),
                    end: $('#end').val(),
                    description: $('#description').val(),
                    color: $('#color').val() || logoColor,
                    project_id: $('#project').val(),
                    client_id: $('#client').val()
                };
    
                fetch('/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(eventData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        calendar.addEvent({
                            id: data.id,
                            title: data.title,
                            start: data.start,
                            end: data.end,
                            description: data.description,
                            backgroundColor: data.color
                        });
                        $('#eventModal').modal('hide');
                    } else {
                        alert('Error adding event');
                    }
                })
                .catch(error => console.error('Error adding event:', error));
            }
    
            // Handle Event Click (edit existing event)
            function handleEventClick(info) {
                $('#title').val(info.event.title);
                $('#description').val(info.event.extendedProps.description);
                $('#start').val(formatDateForInput(new Date(info.event.start)));
                $('#end').val(info.event.end ? formatDateForInput(new Date(info.event.end)) : '');
                $('#color').val(info.event.backgroundColor || logoColor); // Default to logo color if none is set
                $('#deleteButton').show(); // Show delete button on edit
    
                // Store eventId, projectId, and clientId in modal's data
                $('#eventModal').data('eventId', info.event.id);
                $('#eventModal').data('projectId', info.event.extendedProps.project_id);
                $('#eventModal').data('clientId', info.event.extendedProps.client_id);
    
                // Populate dropdowns with the selected project and client
                populateDropdowns(info.event.extendedProps.project_id, info.event.extendedProps.client_id);
    
                $('#eventModal').modal('show');
                $('#eventForm').off('submit').on('submit', (e) => handleUpdateEvent(e, info.event));
    
                $('#deleteButton').off('click').on('click', function () {
                    if (confirm('Are you sure you want to delete this event?')) {
                        fetch(`/events/${info.event.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                info.event.remove(); // Remove the event from the calendar
                                $('#eventModal').modal('hide');
                            } else {
                                return response.json().then(data => {
                                    alert(data.error || 'Error deleting event');
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting event:', error);
                            alert('Error deleting event');
                        });
                    }
                });
            }
    
            // Handle Event Update (when submitted)
            function handleUpdateEvent(event, calendarEvent) {
                event.preventDefault();
                const updatedEventData = {
                    title: $('#title').val(),
                    start: $('#start').val(),
                    end: $('#end').val(),
                    description: $('#description').val(),
                    color: $('#color').val() || logoColor,
                    project_id: $('#project').val(),
                    client_id: $('#client').val()
                };
    
                fetch(`/events/${calendarEvent.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(updatedEventData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendarEvent.setProp('title', updatedEventData.title);
                        calendarEvent.setStart(updatedEventData.start);
                        calendarEvent.setEnd(updatedEventData.end);
                        calendarEvent.setExtendedProp('description', updatedEventData.description);
                        calendarEvent.setProp('backgroundColor', updatedEventData.color);
                        $('#eventModal').modal('hide');
                    } else {
                        alert('Error updating event');
                    }
                })
                .catch(error => {
                    console.error('Error updating event:', error);
                    alert('Error updating event');
                });
            }
        });
    </script>    
    </body>
</html>

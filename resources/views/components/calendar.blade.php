<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='utf-8' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCalendar Example</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            height: 100vh;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <!-- Search and export buttons -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search events">
                    <div class="input-group-append">
                        <button id="searchButton" class="btn btn-primary">{{ __('Search') }}</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="btn-group" role="group" aria-label="Calendar Actions">
                    <button id="exportButton" class="btn btn-success"
                        onclick="exportCalendar()">{{ __('Export Calendar') }}</button>
                    <button id="importButton" class="btn btn-success" data-toggle="modal"
                        data-target="#importModal">{{ __('Import Calendar') }}</button>
                    <button id="addEventButton" class="btn btn-success" data-toggle="modal"
                        data-target="#eventModal">{{ __('Add') }}</button>
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

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
        // Fetch and populate projects and clients
        function populateDropdowns() {
    // Fetch projects
    fetch('/events/api/projects', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Add CSRF token if needed
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(projects => {
            const projectSelect = document.getElementById('project');
            projectSelect.innerHTML = ''; // Clear existing options

            // Add placeholder option
            const placeholderProjectOption = document.createElement('option');
            placeholderProjectOption.value = ''; // Empty value
            placeholderProjectOption.textContent = 'Select a project'; // Placeholder text
            placeholderProjectOption.disabled = true;
            placeholderProjectOption.selected = true;
            projectSelect.appendChild(placeholderProjectOption);

            // Add project options
            projects.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.title; // Adjust based on your project model
                projectSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching projects:', error));

    // Fetch clients
    fetch('/events/api/clients', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Add CSRF token if needed
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(clients => {
            const clientSelect = document.getElementById('client');
            clientSelect.innerHTML = ''; // Clear existing options

            // Add placeholder option
            const placeholderClientOption = document.createElement('option');
            placeholderClientOption.value = ''; // Empty value
            placeholderClientOption.textContent = 'Select a client'; // Placeholder text
            placeholderClientOption.disabled = true;
            placeholderClientOption.selected = true;
            clientSelect.appendChild(placeholderClientOption);

            // Add client options
            clients.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.name; // Adjust based on your client model
                clientSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching clients:', error));
}



    // Call this function when the modal is shown
    $('#eventModal').on('shown.bs.modal', function() {
        populateDropdowns();
    });

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        timeZone: 'local',
        events: '/events', // Initial fetch of all events
        editable: true,
        eventResizableFromStart: true,
        selectable: true,
        select: handleSelect,
        eventClick: handleEventClick,
        eventDrop: handleEventDrop,
        eventResize: handleEventResize,
        windowResize: handleWindowResize
    });

    calendar.render();

    function handleSelect(info) {
        const startDate = new Date(info.startStr);
        const endDate = new Date(info.endStr);
        endDate.setDate(endDate.getDate() - 1); // Adjust end date to be the same day as start date

        startDate.setHours(0, 0, 0, 0);
        endDate.setHours(23, 59, 59, 999);

        $('#start').val(formatDateForInput(startDate));
        $('#end').val(formatDateForInput(endDate));
        $('#title').val('');
        $('#description').val('');
        $('#color').val('#03577a');
        $('#deleteButton').hide();

        $('#eventModal').modal('show');
        $('#eventForm').off('submit').on('submit', handleCreateEvent);
    }

    function handleEventClick(info) {
        $('#title').val(info.event.title);
        $('#description').val(info.event.extendedProps.description);
        $('#start').val(formatDateForInput(new Date(info.event.start)));
        $('#end').val(info.event.end ? formatDateForInput(new Date(info.event.end)) : '');
        $('#color').val(info.event.backgroundColor);
        $('#deleteButton').show();

        $('#eventModal').modal('show');
        $('#eventForm').off('submit').on('submit', handleUpdateEvent);

        $('#deleteButton').off('click').on('click', function() {
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
                            $('#eventModal').modal('hide'); // Close the modal
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

    document.getElementById('searchButton').addEventListener('click', function() {
        var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
        filterAndDisplayEvents(searchKeywords);
    });

    function filterAndDisplayEvents(searchKeywords) {
        $.ajax({
            method: 'GET',
            url: '/events/search',
            data: {
                title: searchKeywords
            },
            success: function(response) {
                calendar.removeAllEvents();
                if (searchKeywords === '') {
                    // Fetch all events when the search term is empty
                    fetch('/events')
                        .then(response => response.json())
                        .then(allEvents => {
                            calendar.addEventSource(allEvents);
                        })
                        .catch(error => {
                            console.error('Error fetching all events:', error);
                        });
                } else {
                    calendar.addEventSource(response);
                }
            },
            error: function(error) {
                console.error('Error searching events:', error);
            }
        });
    }

    function handleCreateEvent(event) {
        event.preventDefault();
        const eventData = {
            title: $('#title').val(),
            start: $('#start').val(),
            end: $('#end').val(),
            description: $('#description').val(),
            color: $('#color').val(),
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

    function handleUpdateEvent(event) {
        event.preventDefault();
        const updatedEventData = {
            title: $('#title').val(),
            start: $('#start').val(),
            end: $('#end').val(),
            description: $('#description').val(),
            color: $('#color').val(),
        };

        fetch(`/events/${info.event.id}`, {
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
                    info.event.setProp('title', updatedEventData.title);
                    info.event.setStart(updatedEventData.start);
                    info.event.setEnd(updatedEventData.end);
                    info.event.setExtendedProp('description', updatedEventData.description);
                    info.event.setProp('backgroundColor', updatedEventData.color);
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

    function handleEventDrop(info) {
        updateEvent(info.event);
    }

    function handleEventResize(info) {
        updateEvent(info.event);
    }

    function handleWindowResize(view) {
        const isMobile = window.matchMedia("(max-width: 767px)").matches;
        if (isMobile) {
            calendar.changeView('timeGridDay');
            calendar.setOption('headerToolbar', {
                left: 'prev,next today',
                center: '',
                right: 'title'
            });
        } else {
            calendar.changeView('timeGridWeek');
            calendar.setOption('headerToolbar', {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            });
        }
    }

    function updateEvent(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            start: event.start.toISOString().slice(0, 16),
            end: event.end ? event.end.toISOString().slice(0, 16) : null,
            description: event.extendedProps.description,
            color: event.backgroundColor
        };

        fetch(`/events/${event.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating event');
                }
            })
            .catch(error => {
                console.error('Error updating event:', error);
                alert('Error updating event');
            });
    }

    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const day = ('0' + date.getDate()).slice(-2);
        const hours = ('0' + date.getHours()).slice(-2);
        const minutes = ('0' + date.getMinutes()).slice(-2);
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
});

function exportCalendar() {
    window.location.href = '{{ route('events.export') }}';
}

$('#importForm').on('submit', function(event) {
    event.preventDefault();

    var formData = new FormData(this);

    fetch('{{ route('events.import') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Events imported successfully.');
                location.reload(); // Reload page to refresh calendar
            } else {
                alert('Error importing events.');
            }
        })
        .catch(error => {
            console.error('Error importing events:', error);
            alert('Error importing events.');
        });
});

    </script>
</body>

</html>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/core/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/daygrid/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/timegrid/main.min.css' rel='stylesheet' /> --}}
    <style>
        /* Ensure the calendar container fits well on small screens */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
    </style>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '/api/events',
                selectable: true,
                select: async function(info) {
                    var title = prompt('Event Title:');
                    if (title) {
                        var eventData = {
                            title: title,
                            start: info.startStr,
                            end: info.endStr,
                        };
                        
                        await fetch('/api/events', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(eventData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            calendar.addEvent({
                                id: data.id,
                                title: data.title,
                                start: data.start,
                                end: data.end,
                            });
                        })
                        .catch(error => console.error('Error adding event:', error));
                    }
                    calendar.unselect();
                },
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
                },
                windowResize: function(view) {
                    var isMobile = window.matchMedia("(max-width: 767px)").matches;
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
            });

            calendar.render();
        });
    </script>
</head>
<body>
    <div class="max-w-7xl mx-auto p-4 shadow-lg">
        <div id='calendar'></div>
    </div>
</body>
</html>

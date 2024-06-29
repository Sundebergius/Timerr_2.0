<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/core/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                events: '/api/events', // Adjust the endpoint as needed

                // Handling date/time selection
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

                // Optionally, handle event clicks
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
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

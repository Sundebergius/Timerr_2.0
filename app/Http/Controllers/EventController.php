<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Sabre\VObject\Reader as VObjectReader;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('user_id', auth()->id())->get();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        \Log::info('Store method called'); // Log when store method is called
        \Log::info('Request data:', $request->all()); // Log the request data

        try {
            // If the 'end' field is 'N/A', remove it from the request data
            if ($request->input('end') === 'N/A') {
                $request->merge(['end' => null]);
            }

            // Validate request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start' => 'required|date_format:Y-m-d\TH:i',
                'end' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start',
                'color' => 'nullable|string|max:7',
                'project_id' => 'nullable|exists:projects,id',
                'client_id' => 'nullable|exists:clients,id',
                'task_id' => 'nullable|exists:tasks,id',
            ]);

            // If the 'end' date is not provided, treat the event as a single-day event
            if (empty($validatedData['end'])) {
                // Adjust end time to the end of the same day (11:59 PM) or keep as the start time
                $validatedData['end'] = \Carbon\Carbon::parse($validatedData['start'])->endOfDay()->format('Y-m-d\TH:i');
            }

            \Log::info('Validated data:', $validatedData); // Log validated data

            // Get the authenticated user's ID
            $userId = auth()->id();

            // Merge the authenticated user's ID into the event data
            $eventData = array_merge($validatedData, [
                'user_id' => $userId,
            ]);

            \Log::info('Event data to be saved:', $eventData); // Log data before saving

            // Create event
            $event = Event::create($eventData);

            \Log::info('Event created successfully:', $event->toArray()); // Log success
            return response()->json($event);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', ['errors' => $e->errors()]); // Log validation errors
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {
            \Log::error('Error creating event:', ['exception' => $e->getMessage()]); // Log general errors
            return response()->json(['error' => 'Unable to create event'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Event::find($id));

        Log::info('Update method called', ['id' => $id]); // Log method call and ID

        try {
            // Fetch the event first
            $event = Event::findOrFail($id);
            Log::info('Event found', ['event' => $event->toArray()]); // Log fetched event

            // Check if the authenticated user owns the event
            $userId = auth()->id();
            if ($event->user_id !== $userId) {
                Log::warning('Unauthorized access attempt', ['user_id' => $userId, 'event_user_id' => $event->user_id]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Log::info('User authorized to update event', ['user_id' => $userId]);

            // Log incoming request data
            Log::info('Incoming request data', ['request_data' => $request->all()]);

            // Validate the request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start' => 'required|date_format:Y-m-d\TH:i',
                'end' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start',
                'color' => 'nullable|string|max:7',
                'project_id' => 'nullable|exists:projects,id',
                'client_id' => 'nullable|exists:clients,id',
                'task_id' => 'nullable|exists:tasks,id',
            ]);

            Log::info('Validated data', ['validated_data' => $validatedData]);

            // Update the event with validated data
            $event->update($validatedData);

            Log::info('Event updated successfully', ['event' => $event->toArray()]);

            return response()->json(['success' => true]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Event not found', ['id' => $id, 'exception' => $e->getMessage()]);
            return response()->json(['error' => 'Event not found'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Error updating event', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to update event'], 500);
        }
    }

    // public function update(Request $request, Event $event)
    // {
    //     if ($event->user_id !== auth()->id()) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $validatedData = $request->validate([
    //         'title' => 'sometimes|required|string|max:255',
    //         'description' => 'nullable|string',
    //         'start' => 'sometimes|required|date_format:Y-m-d\TH:i:sP',  // Ensuring proper date format
    //         'end' => 'nullable|date_format:Y-m-d\TH:i:sP|after_or_equal:start',
    //         'project_id' => 'nullable|exists:projects,id',
    //         'client_id' => 'nullable|exists:clients,id',
    //         'task_id' => 'nullable|exists:tasks,id',
    //     ]);

    //     $event->update($validatedData);
    //     return response()->json($event);
    // }

    public function destroy($id)
    {
        Log::info('Destroy method called', ['event_id' => $id]); // Log the method call and event ID

        try {
            // Fetch the event by ID
            $event = Event::findOrFail($id);
            Log::info('Event found', ['event' => $event->toArray()]); // Log the fetched event

            // Check if the authenticated user owns the event
            if ($event->user_id !== auth()->id()) {
                Log::warning('Unauthorized delete attempt', [
                    'user_id' => auth()->id(),
                    'event_user_id' => $event->user_id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Log::info('User authorized to delete event', ['user_id' => auth()->id()]);

            // Delete the event
            $event->delete();

            Log::info('Event deleted successfully', ['event_id' => $id]);

            return response()->json(null, 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Event not found', ['id' => $id, 'exception' => $e->getMessage()]);
            return response()->json(['error' => 'Event not found'], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting event', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to delete event'], 500);
        }
    }

    Public function exportToICS()
    {
        $events = Event::all(); // Fetch all events, or filter based on user or criteria

        // Create ICS content
        $ical = "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\n";

        foreach ($events as $event) {
            // Ensure start and end are Carbon instances
            $start = $event->start instanceof \Carbon\Carbon ? $event->start : \Carbon\Carbon::parse($event->start);
            $end = $event->end instanceof \Carbon\Carbon ? $event->end : \Carbon\Carbon::parse($event->end);

            // If end is null, set default end time (e.g., one hour after start)
            if (is_null($event->end)) {
                $end = $start->copy()->addHour();
            }

            $ical .= "BEGIN:VEVENT\n";
            $ical .= "SUMMARY:" . $event->title . "\n";
            $ical .= "DESCRIPTION:" . ($event->description ? $event->description : '') . "\n";
            $ical .= "DTSTART:" . $start->format('Ymd\THis\Z') . "\n";
            $ical .= "DTEND:" . $end->format('Ymd\THis\Z') . "\n";
            // Color can be included in the DESCRIPTION if necessary
            $ical .= "DESCRIPTION:" . ($event->description ? $event->description : '') . " (Color: " . ($event->color ? $event->color : '#FFFFFF') . ")\n";
            $ical .= "END:VEVENT\n";
        }

        $ical .= "END:VCALENDAR";

        return response($ical, 200)
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="events.ics"');
    }

    public function importFromICS(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'icsFile' => 'required|file|mimes:ics,text/calendar|max:2048',
        ]);

        // Read the ICS file content
        $file = $request->file('icsFile');
        $content = file_get_contents($file->getPathname());

        // Log the raw ICS content for debugging
        Log::info('ICS File Content: ' . $content);

        // Clean and format ICS content
        // Ensure that there is only one END:VCALENDAR
        $content = preg_replace('/END:VEVENT\s*END:VCALENDAR/', "END:VEVENT\nEND:VCALENDAR", $content);
        $content = preg_replace('/\r\n|\r|\n/', "\r\n", $content); // Normalize line endings
        $content = rtrim($content, "\r\n") . "\r\n"; // Ensure content ends with newline

        try {
            // Parse ICS content
            $vobject = VObjectReader::read($content);

            if (!$vobject instanceof \Sabre\VObject\Component\VCalendar) {
                throw new \Exception('Parsed object is not a VCalendar.');
            }

            // Process each VEVENT
            $events = $vobject->select('VEVENT');

            foreach ($events as $vEvent) {
                // Extract event data safely
                $start = isset($vEvent->DTSTART) ? Carbon::parse($vEvent->DTSTART->getValue()) : null;
                $end = isset($vEvent->DTEND) ? Carbon::parse($vEvent->DTEND->getValue()) : null;
                $title = isset($vEvent->SUMMARY) ? $vEvent->SUMMARY->getValue() : null;
                $description = isset($vEvent->DESCRIPTION) ? $vEvent->DESCRIPTION->getValue() : null;
                $color = '#03577a'; // Default color if not specified

                // Save or update the event
                Event::updateOrCreate([
                    'title' => $title,
                    'start' => $start,
                    'end' => $end,
                ], [
                    'description' => $description,
                    'color' => $color,
                    'user_id' => auth()->id(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Log error details
            Log::error('Import failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to import ICS file.'], 500);
        }
    }

public function search(Request $request)
    {
        // Validate search query
        $query = $request->input('title', '');

        // Fetch events matching the search query
        $events = Event::where('title', 'like', "%{$query}%")
            ->where('user_id', auth()->id()) // Ensure the user is authenticated
            ->get();

        // Return events as JSON
        return response()->json($events);
    }
}

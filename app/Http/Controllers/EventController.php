<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        \Log::info('Fetching all events for the authenticated user');
        $events = Event::where('user_id', auth()->id())->get();
        \Log::info('Events:', $events->toArray()); // Log the actual events being fetched
        return response()->json($events);
    }

    public function store(Request $request)
    {
        \Log::info($request->all()); // Log the request data

        // Map 'start' and 'end' from the request to 'start_time' and 'end_time'
        $eventData = $request->all();
        $eventData['user_id'] = auth()->id(); 

        $event = Event::create($eventData);
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $event->update($request->all());
        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $event->delete();
        return response()->json(null, 204);
    }
}

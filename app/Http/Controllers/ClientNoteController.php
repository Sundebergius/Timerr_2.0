<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Http\Request;

class ClientNoteController extends Controller
{
    public function update(Request $request, Client $client, ClientNote $note)
    {
        $request->validate([
            'content' => 'required|string|max:10000', // Example validation
        ]);

        $note->update([
            'content' => $request->input('content'),
        ]);

        return response()->json(['success' => true, 'message' => 'Note updated successfully.']);
    }

    public function destroy(Client $client, ClientNote $note)
    {
        // Check if the note belongs to the client
        if ($note->client_id !== $client->id) {
            return redirect()->back()->with('error', 'This note does not belong to the specified client.');
        }

        // Delete the note
        $note->delete();

        return redirect()->route('clients.edit', $client)->with('success', 'Note deleted successfully.');
    }
}

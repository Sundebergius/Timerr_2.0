<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ContactPerson;
use Illuminate\Http\Request;

class ContactPersonController extends Controller
{
    public function update(Request $request, Client $client, ContactPerson $contactPerson)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'notes' => 'nullable|string|max:5000',
        ]);

        $contactPerson->update($request->all());

        // Return the updated contact person data
        return response()->json([
            'success' => true,
            'contact' => $contactPerson,  // Return updated contact person
            'message' => 'Contact person updated successfully.'
        ]);
    }

    public function destroy(Client $client, ContactPerson $contactPerson)
    {
        // Check if the contact person belongs to the client
        if ($contactPerson->client_id !== $client->id) {
            return redirect()->back()->with('error', 'This contact person does not belong to the specified client.');
        }

        // Delete the contact person
        $contactPerson->delete();

        return redirect()->route('clients.edit', $client)->with('success', 'Contact person deleted successfully.');
    }
}

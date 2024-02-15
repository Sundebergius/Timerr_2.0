<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->id(); // set user_id to the ID of the currently authenticated user
        $data['country'] = 'DK'; // set default country to 'DK'
        $data['status'] = Client::STATUS_LEAD; // set default status to 'lead'
    
        $client = Client::create($data);
    
        return redirect()->route('clients.index');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $client->update($request->all());
        return redirect()->route('clients.index');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index');
    }

    public function create()
    {
        return view('clients.create');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $csvData = file_get_contents($file);
        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $row = array_combine($header, $row);

            Client::create([
                'name' => $row['name'],
                'contact_details' => $row['contact_details'],
                'status' => $row['status'],
                'type' => $row['type'],
                'company_name' => $row['company_name'],
                'company_size' => $row['company_size'],
            ]);
        }

        return redirect()->route('clients.index');
    }
    
    public function updateStatus(Request $request, $id)
    {
        $client = Client::find($id);
        $client->status = $request->status;
        $client->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}

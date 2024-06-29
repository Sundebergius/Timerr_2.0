<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\TaskProject;
use App\Models\TaskHourly;
use App\Models\TaskDistance;
use App\Models\TaskProduct;
use App\Models\TaskOther;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure all actions are authenticated
    }

    public function index()
    {
        $invoices = Invoice::where('user_id', Auth::id())->get(); // Fetch invoices for the authenticated user
        return view('invoices.index', compact('invoices')); // Assuming you have an 'invoices.index' view
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'subtotal' => 'nullable|numeric',
            'discount' => 'nullable|numeric', // Allow discount to be nullable and numeric
            'vat' => 'nullable|numeric',
            'total' => 'required|numeric',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'last_reminder_sent' => 'nullable|date',
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        // Check if an invoice already exists for the given project_id
        if ($request->project_id && Invoice::where('project_id', $request->project_id)->exists()) {
            // Redirect back with an error message
            return back()->withInput()->withErrors(['project_id' => 'An invoice for this project already exists.']);
        }

        // Find or create the invoice based on project_id
        $invoice = Invoice::firstOrNew(['project_id' => $request->project_id]);

        // Populate the invoice data
        $invoice->user_id = Auth::id(); // Set the user_id to the authenticated user
        $invoice->title = $request->title;
        $invoice->status = $request->status;
        $invoice->issue_date = $request->issue_date;
        $invoice->due_date = $request->due_date;
        $invoice->currency = $request->currency;
        $invoice->subtotal = $request->subtotal;

        // Calculate and set discount amount if discount percentage is provided
        if ($request->has('discount') && !is_null($request->discount)) {
            $discountAmount = ($request->subtotal * $request->discount) / 100;
            $invoice->discount = $discountAmount;
        } else {
            $invoice->discount = 0.00; // Default to 0 if no discount or invalid discount provided
        }

        $invoice->vat = $request->vat;

        // Calculate total considering subtotal, discount, and VAT
        $invoice->total = $invoice->subtotal - $invoice->discount + $invoice->vat;

        $invoice->payment_terms = $request->payment_terms;
        $invoice->payment_method = $request->payment_method;
        $invoice->transaction_id = $request->transaction_id;
        // Handle file upload if needed
        // $invoice->file_path = $request->file_path;

        // Associate project and client
        $invoice->project_id = $request->project_id;
        $invoice->client_id = $request->client_id;

        // Save the invoice
        $invoice->save();

        return redirect()->route('invoices.index');
    }

    public function show($id)
    {
        // Fetch the project using the provided $id
        $project = Project::findOrFail($id); // Assuming you have a Project model

        if ($project->status != 'completed') {
            abort(404); // Or redirect to a different page with an error message
        }

        // Fetch tasks related to the project
        $tasks = $project->tasks;

        // Separate tasks into different types using collection filters
        $projectTasks = $tasks->filter(function ($task) {
            return $task->taskable_type === TaskProject::class;
        });
        $hourlyTasks = $tasks->filter(function ($task) {
            return $task->taskable_type === TaskHourly::class;
        });
        $distanceTasks = $tasks->filter(function ($task) {
            return $task->taskable_type === TaskDistance::class;
        });
        $productTasks = $tasks->filter(function ($task) {
            return $task->taskable_type === TaskProduct::class;
        });
        $otherTasks = $tasks->filter(function ($task) {
            return $task->taskable_type === TaskOther::class;
        });

        // Load relationships for product tasks
        $productTasks->load('taskProduct.product');

        // Extract products
        $products = collect();
        foreach ($productTasks as $task) {
            foreach ($task->taskProduct as $taskProduct) {
                $products->push([
                    'product' => $taskProduct->product,
                    'total_sold' => $taskProduct->total_sold,
                    'total_price' => $taskProduct->product->price * $taskProduct->total_sold
                ]);
            }
        }

        // Load other necessary relationships for each task type
        $projectTasks->load('taskable');
        $hourlyTasks->load('taskable.registrationHourly');
        $distanceTasks->load('taskable.registrationDistances');
        $productTasks->load('taskable');
        $otherTasks->load('taskable');

        // Fetch the invoice data
        $invoice = Invoice::where('project_id', $project->id)->firstOrFail();

        // Calculate totals from the invoice data
        $subtotal = $invoice->subtotal;
        $discountPercentage = $invoice->discount;
        $discountAmount = ($discountPercentage / 100) * $subtotal;
        $totalAfterDiscount = $subtotal - $discountAmount;
        $vat = $totalAfterDiscount * 0.25;
        $totalWithVat = $totalAfterDiscount + $vat;

        // // Calculate totals
        // $total = 0;
        // foreach ($projectTasks as $task) {
        //     $total += $task->taskable->price;
        // }
        // foreach ($hourlyTasks as $task) {
        //     $hours = $task->taskable->registrationHourly->sum('minutes_worked') / 60;
        //     $total += $hours * $task->taskable->rate_per_hour;
        // }
        // foreach ($products as $product) {
        //     $total += $product['total_price'];
        // }
        // foreach ($distanceTasks as $task) {
        //     $distance = $task->taskable->registrationDistances->sum('distance');
        //     $total += $distance * $task->taskable->price_per_km;
        // }

        // // Calculate VAT and total with VAT
        // $vat = $total * 0.25;
        // $totalWithVat = $total * 1.25;

        // Generate PDF
        $pdf = PDF::loadView('invoices.show', [
            'project' => $project,
            'projectTasks' => $projectTasks,
            'hourlyTasks' => $hourlyTasks,
            'distanceTasks' => $distanceTasks,
            'productTasks' => $productTasks,
            'products' => $products,
            'otherTasks' => $otherTasks,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'totalAfterDiscount' => $totalAfterDiscount,
            'vat' => $vat,
            'totalWithVat' => $totalWithVat,
        ]);

        // Save PDF to storage
        $fileName = 'invoices/invoice_' . $project->id . '_' . time() . '.pdf';
        Storage::put($fileName, $pdf->output());

        // Find or create the invoice
        $invoice = Invoice::firstOrNew(['project_id' => $project->id]);
        $invoice->user_id = auth()->id();
        $invoice->project_id = $project->id;
        $invoice->client_id = $project->client ? $project->client->id : null;
        $invoice->title = 'Invoice for Project ' . $project->id;
        $invoice->subtotal = $subtotal;
        $invoice->discount = $discountAmount;
        $invoice->total = $totalWithVat;
        $invoice->vat = $vat;
        $invoice->file_path = $fileName;
        $invoice->save();

        // Stream the PDF for viewing/download
        return $pdf->stream($project->id . '_invoice.pdf');
    }

    public function edit($id)
    {
        $invoice = Invoice::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $clients = Client::all();
        $projects = Project::all();
        return view('invoices.edit', compact('invoice', 'clients', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'subtotal' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'vat' => 'nullable|numeric',
            'total' => 'required|numeric',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            //'file_path' => 'nullable|string',
            'last_reminder_sent' => 'nullable|date',
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);
    
        Log::info('Validated Data:', $validatedData);
    
        $invoice = Invoice::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $invoice->update($validatedData);

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully');
    }

    public function destroy($id)
    {
        $invoice = Invoice::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $invoice->delete();

        return redirect()->route('invoices.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $invoice = Invoice::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $invoice->status = $request->input('status'); // Use the status from the form
        $invoice->save();

        return redirect()->route('invoices.index');
    }
}

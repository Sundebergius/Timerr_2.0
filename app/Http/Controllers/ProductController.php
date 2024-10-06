<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\Project;
use App\Models\TaskProduct;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Store method called');

        // Log the request data
        \Log::info('Request data:', $request->all());

        // Common validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'active' => 'required|boolean',
            'parent_id' => 'nullable|exists:products,id',
            'type' => 'required|in:product,service',
            'user_id' => 'required|exists:users,id',  // Ensure user exists
            'team_id' => 'nullable|exists:teams,id',  // Ensure team exists
        ];

        // Additional validation for services
        if ($request->input('type') === 'service') {
            $rules['attributes'] = 'required|array';
            $rules['attributes.*.key'] = 'required|string|max:255';
            $rules['attributes.*.value'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json($validator->errors(), 422);
        }

        try {
            \Log::info('Validation passed');

            // Check the provided team ID
            $teamId = $request->input('team_id');
            \Log::info('Team ID from request:', ['team_id' => $teamId]);

            $team = Team::find($teamId);  // Get the team from the database
            \Log::info('Fetched team:', $team ? $team->toArray() : 'No team found');

            // Initialize userId and teamId for product data
            $assignedUserId = $request->input('user_id'); // Always assign user_id
            $assignedTeamId = null;

            // Check if a valid team exists
            if ($team) {
                if ($team->personal_team) {
                    \Log::info('Personal team detected. Using assigned user_id:', ['assigned_user_id' => $assignedUserId]);
                    // Don't assign team_id for personal teams
                } else {
                    \Log::info('Non-personal team detected. Assigning team_id.');
                    $assignedTeamId = $team->id; // Use team_id for public teams
                    \Log::info('Assigned team_id:', ['assigned_team_id' => $assignedTeamId]);
                }
            } else {
                \Log::error('No valid team found for the user. Team ID: ' . $teamId);
                return response()->json(['error' => 'Invalid team provided'], 422);
            }

            // Prepare the product data
            $productData = [
                'user_id' => $assignedUserId, // Always set user_id
                'team_id' => $assignedTeamId, // Set team_id if public team or null if personal
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price ?: 0,
                'quantity_in_stock' => $request->quantity_in_stock ?: 0,
                'active' => $request->active,
                'parent_id' => $request->parent_id,
                'type' => $request->type,
                'attributes' => $request->input('attributes') ? $this->formatAttributes($request->input('attributes')) : null,
            ];

            \Log::info('Product data to be saved:', $productData);

            // Create the product
            $product = Product::create($productData);

            \Log::info('Product created successfully:', $product->toArray());

            return response()->json(['message' => 'Product created successfully', 'product' => $product], 200);
        } catch (\Exception $e) {
            \Log::error('Error creating product:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to create product'], 500);
        }
    }




    private function formatAttributes(array $attributes)
    {
        \Log::info('Original attributes:', $attributes);

        $formattedAttributes = [];

        foreach ($attributes as $attribute) {
            // Check if the attribute is marked for deletion
            if (isset($attribute['delete']) && $attribute['delete'] === '1') {
                continue; // Skip this attribute
            }

            // Ensure the attribute has a key and a value
            if (!empty($attribute['key'])) {
                $formattedAttributes[] = [
                    'key' => $attribute['key'], // Keep the key as a string
                    'value' => !empty($attribute['value']) ? (float) $attribute['value'] : null, // Ensure the value is a float, or null if empty
                ];
            }
        }

        \Log::info('Formatted attributes:', $formattedAttributes);

        return $formattedAttributes;
    }

    public function getUserProducts($userId)
    {
        try {
            // Fetch the current user
            $user = User::findOrFail($userId);
            
            // Determine if the user is working within their personal team
            if ($user->currentTeam && $user->currentTeam->personal_team) {
                // Fetch products for the user's personal account
                $products = Product::where('user_id', $user->id)->get();
            } else {
                // Fetch products for the current team
                $products = Product::where('team_id', $user->current_team_id)->get();
            }

            // Format the products and ensure attributes and type are correctly returned
            $formattedProducts = $products->map(function ($product) {
                // Check if attributes is a string, if so decode it, otherwise return the array
                $attributes = is_string($product->attributes) ? json_decode($product->attributes) : $product->attributes;

                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'type' => $product->type,  // Ensure type is returned
                    'price' => $product->price,
                    'quantity_in_stock' => $product->quantity_in_stock,
                    'description' => $product->description,
                    'attributes' => $attributes ? $attributes : [],  // Always return an array for attributes
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            return response()->json(['products' => $formattedProducts], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching products: '.$e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('pageSize', 10); // Default to 10 if pageSize is not set
        
        $user = auth()->user(); // Get the authenticated user

        // Get the user's subscription plan
        $subscriptionPlan = app(\App\Services\PlanService::class)->getPlanNameByPriceId($user->subscription('default')?->stripe_price ?? null);

        // Get the product limit for the user's plan
        $productLimit = app(\App\Services\PlanService::class)->getPlanLimits($subscriptionPlan)['products'] ?? 5; // Default to 5 for 'free' plan

        // Get the current number of products created by the user
        $productCount = $user->products()->count();

        // Check if the user is working in their personal team or a team
        if ($user->currentTeam && $user->currentTeam->personal_team) {
            // Fetch personal products
            $products = Product::where('user_id', $user->id)->paginate($pageSize);
        } else {
            // Fetch team products
            $products = Product::where('team_id', $user->current_team_id)->paginate($pageSize);
        }

        return view('products.index', [
            'products' => $products,
            'productCount' => $productCount,
            'productLimit' => $productLimit,
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        // Check if the product is associated with any tasks via TaskProduct
        $tasksCount = TaskProduct::where('product_id', $product->id)->count();

        // If the user hasn't confirmed the deletion yet, show a warning
        if ($tasksCount > 0 && !$request->has('confirm')) {
            $projects = TaskProduct::where('product_id', $product->id)
                ->with('task.project')
                ->limit(5)  // Limit the number of displayed tasks
                ->get()
                ->map(function ($taskProduct) {
                    return [
                        'project_title' => $taskProduct->task->project->title,
                        'task_title' => $taskProduct->task->title,
                    ];
                });

            $projectSummary = $projects->map(function ($item) {
                return 'Project: ' . $item['project_title'] . ', Task: ' . $item['task_title'];
            })->join('; ');

            // If there are more than 5 tasks, display a message saying there are more
            if ($tasksCount > 5) {
                $projectSummary .= ' and ' . ($tasksCount - 5) . ' more tasks';
            }

            // Show warning and confirmation form
            return redirect()->route('products.index')
                ->with('warning', 'This product is associated with the following tasks and projects: ' . $projectSummary . '. Are you sure you want to delete it? <form action="' . route('products.destroy', $product->id) . '" method="POST" class="inline">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="text-red-500 underline">Click here to confirm</button>
                </form>');
        }

        // Authorize the deletion
        $this->authorize('delete', $product);

        // Proceed with deletion
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'quantity_in_stock' => 'nullable|integer|min:0',
            'quantity_sold' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:products,id',
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|distinct|string|max:255',
            'attributes.*.value' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productData = [
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price ?: 0,
                'quantity_in_stock' => $request->quantity_in_stock ?: 0,
                'quantity_sold' => $request->quantity_sold ?: 0,
                'active' => $request->has('active') ? 1 : 0,
                'parent_id' => $request->parent_id,
                'attributes' => $this->formatAttributes($request->input('attributes', [])),
            ];

            \Log::info('Product data to be updated:', $productData);

            $product->update($productData);

            \Log::info('Product updated successfully:', $product->toArray());

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error updating product:', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Unable to update product');
        }
    }

    private function filterAttributes(array $attributes): array
    {
        return array_filter($attributes, function ($attribute) {
            return !empty($attribute['key']) || !empty($attribute['value']);
        });
    }

}

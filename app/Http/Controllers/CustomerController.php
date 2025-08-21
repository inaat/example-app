<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index()
    {
        $customers = Customer::orderBy('name')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:20|unique:customers,vat_number',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($request->all());

        // Check if this was opened from invoice creation
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'invoices/create')) {
            return redirect()->back()
                           ->with('success', 'Customer created successfully! Please refresh the invoice page to see the new customer.')
                           ->with('close_tab', true);
        }

        return redirect()->route('customers.index')
                        ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load(['invoices' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);
        
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:20|unique:customers,vat_number,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.index')
                        ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->route('customers.index')
                           ->with('error', 'Cannot delete customer with existing invoices. Deactivate instead.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
                        ->with('success', 'Customer deleted successfully.');
    }
}

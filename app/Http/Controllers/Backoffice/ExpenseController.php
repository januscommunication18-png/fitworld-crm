<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by recurring
        if ($request->filled('recurring')) {
            $query->where('is_recurring', $request->recurring === 'yes');
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('service_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('service_date', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('service_name', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderBy('service_date', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total' => Expense::sum('amount'),
            'this_month' => Expense::thisMonth()->sum('amount'),
            'recurring' => Expense::recurring()->sum('amount'),
            'one_time' => Expense::oneTime()->sum('amount'),
        ];

        return view('backoffice.expenses.index', [
            'expenses' => $expenses,
            'categories' => Expense::CATEGORIES,
            'stats' => $stats,
            'filters' => $request->only(['category', 'recurring', 'start_date', 'end_date', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        return view('backoffice.expenses.create', [
            'categories' => Expense::CATEGORIES,
        ]);
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'service_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => 'required|string|size:3',
            'is_recurring' => 'boolean',
            'service_date' => 'required|date',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');

        // Handle invoice upload
        if ($request->hasFile('invoice')) {
            $path = $request->file('invoice')->store('invoices', 'public');
            $validated['invoice_path'] = $path;
        }

        Expense::create($validated);

        return redirect()
            ->route('backoffice.expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense)
    {
        return view('backoffice.expenses.show', [
            'expense' => $expense,
        ]);
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(Expense $expense)
    {
        return view('backoffice.expenses.edit', [
            'expense' => $expense,
            'categories' => Expense::CATEGORIES,
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'service_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => 'required|string|size:3',
            'is_recurring' => 'boolean',
            'service_date' => 'required|date',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');

        // Handle invoice upload
        if ($request->hasFile('invoice')) {
            // Delete old invoice if exists
            if ($expense->invoice_path) {
                Storage::disk('public')->delete($expense->invoice_path);
            }
            $path = $request->file('invoice')->store('invoices', 'public');
            $validated['invoice_path'] = $path;
        }

        $expense->update($validated);

        return redirect()
            ->route('backoffice.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        // Delete invoice file if exists
        if ($expense->invoice_path) {
            Storage::disk('public')->delete($expense->invoice_path);
        }

        $expense->delete();

        return redirect()
            ->route('backoffice.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(Expense $expense)
    {
        if (!$expense->invoice_path || !Storage::disk('public')->exists($expense->invoice_path)) {
            return back()->with('error', 'Invoice not found.');
        }

        return Storage::disk('public')->download(
            $expense->invoice_path,
            'invoice-' . $expense->id . '.' . pathinfo($expense->invoice_path, PATHINFO_EXTENSION)
        );
    }
}

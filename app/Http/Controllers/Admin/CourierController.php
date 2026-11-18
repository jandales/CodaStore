<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index()
    {
        return view('admin.couriers.index', [
            'couriers' => Courier::withCount('orders')->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.couriers.form', [
            'courier' => new Courier(['active' => true, 'sort_order' => (int) Courier::max('sort_order') + 10]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = Courier::codeFor($data['name']);
        $courier = Courier::create($data);

        ActivityLog::record('courier.created', $courier, 'Added courier ' . $courier->name);

        return redirect()->route('admin.couriers')->with('success', $courier->name . ' added.');
    }

    public function edit(Courier $courier)
    {
        return view('admin.couriers.form', ['courier' => $courier]);
    }

    public function update(Request $request, Courier $courier)
    {
        $courier->fill($this->validated($request));
        $changes = array_keys($courier->getDirty());
        $courier->save();

        if ($changes) {
            $labels = ['name' => 'name', 'tracking_url' => 'tracking link', 'active' => $courier->active ? 'turned on' : 'turned off', 'sort_order' => 'order'];
            ActivityLog::record('courier.updated', $courier, 'Updated courier ' . $courier->name . ': ' . implode(', ', array_map(fn ($f) => $labels[$f] ?? $f, $changes)));
        }

        return redirect()->route('admin.couriers')->with('success', $courier->name . ' saved.');
    }

    public function destroy(Courier $courier)
    {
        // Orders keep the courier's code; a used courier can only be turned off.
        if ($courier->orders()->exists()) {
            return back()->with('error', $courier->name . ' has been used on orders, so it can\'t be deleted. Turn it off instead.');
        }

        $courier->delete();
        ActivityLog::record('courier.deleted', null, 'Deleted courier ' . $courier->name);

        return redirect()->route('admin.couriers')->with('success', $courier->name . ' deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'tracking_url' => ['nullable', 'string', 'max:500', 'starts_with:https://', function ($attribute, $value, $fail) {
                if ($value && !str_contains($value, '{number}')) {
                    $fail('Put {number} in the tracking link where the tracking number goes.');
                }
                if ($value && !filter_var(str_replace('{number}', '123', $value), FILTER_VALIDATE_URL)) {
                    $fail('Enter a valid web address for the tracking link.');
                }
            }],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'tracking_url.starts_with' => 'The tracking link must start with https://.',
        ]);

        return [
            'name' => trim($data['name']),
            'tracking_url' => ($data['tracking_url'] ?? null) ?: null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'active' => $request->boolean('active'),
        ];
    }
}

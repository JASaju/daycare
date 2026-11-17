<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Support\Sanitize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InstrumentController extends Controller
{
    protected function rules(): array
    {
        return [
            "name"           => "required|string|max:255",
            "category"       => "nullable|string|max:100",
            "quantity"       => "required|integer|min:0|max:100000",
            "condition"      => "required|in:new,good,fair,damaged",
            "purchase_date"  => "nullable|date|before_or_equal:today",
            "purchase_price" => "nullable|numeric|min:0|max:99999999",
            "notes"          => "nullable|string|max:2000",
        ];
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Instrument::class);

        $request->validate(['search' => 'nullable|string|max:100']);

        $instruments = Instrument::when($request->filled('search'), function ($q) use ($request) {
                $q->where("name", "like", "%" . Sanitize::like($request->input('search')) . "%");
            })
            ->orderBy("name")
            ->paginate(15)
            ->withQueryString();

        return view("instruments.index", compact("instruments"));
    }

    public function create()
    {
        Gate::authorize('create', Instrument::class);

        return view("instruments.create");
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Instrument::class);

        Instrument::create($request->validate($this->rules()));

        return redirect()->route("instruments.index")->with("success", "Item added to inventory.");
    }

    public function edit(Instrument $instrument)
    {
        Gate::authorize('update', $instrument);

        return view("instruments.edit", compact("instrument"));
    }

    public function update(Request $request, Instrument $instrument)
    {
        Gate::authorize('update', $instrument);

        $instrument->update($request->validate($this->rules()));

        return redirect()->route("instruments.index")->with("success", "Item updated.");
    }

    public function destroy(Instrument $instrument)
    {
        Gate::authorize('delete', $instrument);

        $instrument->delete();

        return redirect()->route("instruments.index")->with("success", "Item removed.");
    }
}

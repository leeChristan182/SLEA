<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Cluster;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = Organization::with('cluster')
            ->when(
                $request->q,
                fn($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('cluster', fn($c) => $c->where('name', 'like', "%{$search}%"))
            )
            ->when(
                $request->cluster_filter,
                fn($q, $id) =>
                $q->where('cluster_id', $id)
            )
            ->orderBy('name')
            ->paginate(10)
            ->appends($request->except('page')); // ✅ keeps search/filter across pages

        $clusters = Cluster::orderBy('name')->get();

        return view('admin.organizations.index', compact('organizations', 'clusters'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
            'description' => 'nullable|string|max:1000',
        ]);

        Organization::create($request->only('name', 'cluster_id', 'description'));

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $organization->update($request->only('name', 'cluster_id', 'description'));

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}

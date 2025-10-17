<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Cluster;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('cluster')->get();
        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        $clusters = Cluster::orderBy('name')->get();
        return view('organizations.create', compact('clusters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
            'description' => 'nullable|string'
        ]);

        $data = $request->all();
        
        // Get cluster name and combine with organization name
        $cluster = Cluster::find($request->cluster_id);
        if ($cluster) {
            $data['combined_name'] = $cluster->name . ' - ' . $request->name;
        } else {
            $data['combined_name'] = $request->name;
        }

        Organization::create($data);

        return redirect()->route('organizations.index')
                         ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        return view('organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $clusters = Cluster::orderBy('name')->get();
        return view('organizations.edit', compact('organization', 'clusters'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
            'description' => 'nullable|string'
        ]);

        $data = $request->all();
        
        // Get cluster name and combine with organization name
        $cluster = Cluster::find($request->cluster_id);
        if ($cluster) {
            $data['combined_name'] = $cluster->name . ' - ' . $request->name;
        } else {
            $data['combined_name'] = $request->name;
        }

        $organization->update($data);

        return redirect()->route('organizations.index')
                         ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('organizations.index')
                         ->with('success', 'Organization deleted successfully.');
    }
}

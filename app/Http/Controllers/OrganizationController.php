<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Cluster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::with('cluster');

        // Search by org name or cluster name
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('cluster', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by cluster
        if ($clusterId = $request->input('cluster_filter')) {
            $query->where('cluster_id', $clusterId);
        }

        $organizations = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $clusters = Cluster::orderBy('name')->get();

        return view('admin.organizations.index', compact('organizations', 'clusters'));
    }

    /**
     * Helper to generate a unique slug from the name.
     */
    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        $existsQuery = Organization::where('slug', $slug);
        if ($ignoreId) {
            $existsQuery->where('id', '!=', $ignoreId);
        }

        while ($existsQuery->exists()) {
            $slug = $base . '-' . $counter++;
            $existsQuery = Organization::where('slug', $slug);
            if ($ignoreId) {
                $existsQuery->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
        ]);

        $organization             = new Organization();
        $organization->name       = $validated['name'];
        $organization->cluster_id = $validated['cluster_id'];
        $organization->slug       = $this->generateUniqueSlug($validated['name']);
        $organization->save();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'cluster_id' => 'required|exists:clusters,id',
        ]);

        $organization->name       = $validated['name'];
        $organization->cluster_id = $validated['cluster_id'];

        // If the name changed, refresh the slug but keep it unique
        if ($organization->isDirty('name')) {
            $organization->slug = $this->generateUniqueSlug($validated['name'], $organization->id);
        }

        $organization->save();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}

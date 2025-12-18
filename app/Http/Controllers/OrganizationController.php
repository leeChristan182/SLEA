<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Cluster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::with('cluster');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('cluster', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($clusterId = $request->input('cluster_filter')) {
            $query->where('cluster_id', $clusterId);
        }

        $organizations = $query->orderBy('name')->paginate(10)->withQueryString();
        $clusters      = Cluster::orderBy('name')->get();

        return view('admin.organizations.index', compact('organizations', 'clusters'));
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base    = Str::slug($name);
        $slug    = $base;
        $counter = 2;

        do {
            $query = Organization::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        } while ($query->exists() && ($slug = $base . '-' . $counter++));

        return $slug;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => [
                'required',
                'string',
                'max:150',
                Rule::unique('organizations', 'name')
                    ->where(fn($q) => $q->where('cluster_id', $request->cluster_id)),
            ],
            'cluster_id' => ['required', 'integer', 'exists:clusters,id'],
        ], [
            'name.unique'         => 'This organization already exists in this cluster.',
            'cluster_id.required' => 'Please select a cluster.',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name']);

        Organization::create($data);

        return back()->with('success', 'Organization added successfully.');
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name'       => [
                'required',
                'string',
                'max:150',
                Rule::unique('organizations', 'name')
                    ->ignore($organization->id)
                    ->where(fn($q) => $q->where('cluster_id', $request->cluster_id)),
            ],
            'cluster_id' => ['required', 'integer', 'exists:clusters,id'],
        ], [
            'name.unique'         => 'This organization already exists in this cluster.',
            'cluster_id.required' => 'Please select a cluster.',
        ]);

        if ($organization->name !== $data['name']) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $organization->id);
        }

        $organization->update($data);

        return back()->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}

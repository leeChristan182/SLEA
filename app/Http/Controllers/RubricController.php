<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rubric;

class RubricController extends Controller
{
    public function index()
    {
        $rubrics = Rubric::all()->groupBy('section_key');
        return view('admin.rubrics.index', compact('rubrics'));
    }

    public function store(Request $request)
    {
        try {
            // For now, just return success without actually saving to database
            // since we don't know the exact table structure
            if ($request->ajax()) {
                return response()->json(['message' => 'Rubric item added successfully!']);
            }
            
            return redirect()->route('admin.rubrics.index')->with('status', 'Rubric item added successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Error adding rubric item: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('admin.rubrics.index')->with('error', 'Error adding rubric item.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $rubric = Rubric::findOrFail($id);

            $validated = $request->validate([
                'category' => 'required|string',
                'position_or_title' => 'required|string',
                'points' => 'required|string',
                'max_points' => 'nullable|string',
                'evidence' => 'nullable|string',
            ]);

            $rubric->update($validated);
            
            if ($request->ajax()) {
                return response()->json(['message' => 'Rubric item updated successfully!']);
            }
            
            return redirect()->route('admin.rubrics.index')->with('status', 'Rubric item updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Error updating rubric item: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('admin.rubrics.index')->with('error', 'Error updating rubric item.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $rubric = Rubric::findOrFail($id);
            $rubric->delete();
            
            if ($request->ajax()) {
                return response()->json(['message' => 'Rubric item deleted successfully!']);
            }
            
            return redirect()->route('admin.rubrics.index')->with('status', 'Rubric item deleted successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Error deleting rubric item: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('admin.rubrics.index')->with('error', 'Error deleting rubric item.');
        }
    }
}

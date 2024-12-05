<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        // Fetch all groups for the authenticated user, default to an empty collection
        $groups = auth()->user()->groups ?? collect();
    
        return view('groups.index', compact('groups'));
    }    

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        auth()->user()->groups()->create(['name' => $request->name]);

        return back()->with('success', 'Group created successfully.');
    }

    public function update(Request $request, Group $group)
    {
        $this->authorize('update', $group); // Ensure group belongs to user
        $request->validate(['name' => 'required|string|max:255']);
        $group->update(['name' => $request->name]);

        return back()->with('success', 'Group renamed successfully.');
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group); // Ensure group belongs to user
        $group->delete();

        return back()->with('success', 'Group deleted successfully.');
    }

    public function show(Group $group)
    {
        // Ensure the user owns the group
        $this->authorize('view', $group);

        // Fetch sentiments for the group
        $sentiments = $group->sentiments;

        return view('groups.show', compact('group', 'sentiments'));
    }

}

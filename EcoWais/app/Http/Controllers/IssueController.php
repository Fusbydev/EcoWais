<?php


namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;
class IssueController extends Controller
{

    public function index()
{
    $issues = Issue::orderBy('issue_name', 'asc')->get(); // Fetch all issues from DB
    return view('reports.index', compact('issues'));
}
    // Fetch all issues (for modal)
    public function getIssues()
    {
        $issues = Issue::orderBy('id', 'asc')->get();
        return response()->json($issues);
    }

    // Add new issue
    public function addIssue(Request $request)
    {
        $request->validate(['issue_name' => 'required|string|max:255']);

        $issue = Issue::create([
            'issue_name' => $request->issue_name,
        ]);

        return response()->json($issue);
    }

    // Delete issue
    public function deleteIssue($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();
        return response()->json(['success' => true]);
    }
}

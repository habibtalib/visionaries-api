<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->timelineEvents()->orderBy('created_at', 'desc');
        if ($request->has('category')) $query->where('category', $request->category);
        return response()->json($query->paginate($request->get('per_page', 20)));
    }
}

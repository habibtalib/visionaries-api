<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->checkIns()->orderBy('check_in_date', 'desc');
        if ($request->has('from')) $query->where('check_in_date', '>=', $request->from);
        if ($request->has('to')) $query->where('check_in_date', '<=', $request->to);
        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'check_in_date' => 'required|date',
            'gratitude' => 'nullable|string',
            'struggle' => 'nullable|string',
            'dua' => 'nullable|string',
            'tawakkul_moment' => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;

        $checkIn = CheckIn::updateOrCreate(
            ['user_id' => $data['user_id'], 'check_in_date' => $data['check_in_date']],
            $data
        );

        return response()->json($checkIn, 201);
    }

    public function show(Request $request, string $id)
    {
        return response()->json($request->user()->checkIns()->findOrFail($id));
    }

    public function today(Request $request)
    {
        $checkIn = $request->user()->checkIns()->where('check_in_date', now()->toDateString())->first();
        return response()->json($checkIn);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->checkIns()->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function updateLocale(Request $request)
    {
        $data = $request->validate(['locale' => 'required|in:en,ms']);
        $request->user()->update($data);
        return response()->json(['locale' => $data['locale']]);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user->toArray(),
            'vision' => $user->vision,
            'traits' => $user->userTraits()->with('trait')->get(),
            'actions' => $user->actions()->with('logs')->get(),
            'journal' => $user->journalEntries,
        ]);
    }
}

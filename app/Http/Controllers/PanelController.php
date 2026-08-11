<?php

namespace App\Http\Controllers;

use App\Models\Panel;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $panels = Panel::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->get();

        if ($panels->isEmpty()) {
            return response()->json((object) []);
        }

        return response()->json($panels->groupBy('zone'));
    }

    public function store(Request $request)
    {
        $panel = Panel::create([
            'user_id' => $request->user()?->id,
            'title' => $request->title,
            'zone' => $request->zone ?? 'center',
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'is_open' => $request->is_open ?? true,
            'settings' => $request->settings,
        ]);

        return response()->json($panel);
    }

    public function update(Request $request, $id)
    {
        $panel = Panel::where('id', $id)->firstOrFail();
        $panel->update($request->only(['title', 'zone', 'type', 'order', 'is_open', 'settings']));

        return response()->json($panel);
    }

    public function destroy($id)
    {
        $panel = Panel::where('id', $id)->firstOrFail();
        $panel->delete();

        return response()->json(['success' => true]);
    }
}

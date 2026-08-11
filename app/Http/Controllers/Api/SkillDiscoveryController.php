<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Engines\SkillEngine;
use Illuminate\Http\Request;

class SkillDiscoveryController extends Controller
{
    protected ;

    public function __construct(SkillEngine )
    {
        ->skillEngine = ;
    }

    // البحث عن المهارات باستخدام المحرك
    public function search(Request )
    {
        ->validate(['query' => 'required|string']);
        return response()->json(->skillEngine->search(->input('query')));
    }

    // إضافة مهارة للـ RAG باستخدام المحرك
    public function store(Request )
    {
        ->validate(['name' => 'required', 'description' => 'required', 'code' => 'required']);
         = ->skillEngine->store(->all());
        return response()->json();
    }
}

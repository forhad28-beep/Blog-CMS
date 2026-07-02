<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:newsletters,email',
        ]);

        Newsletter::create($data);

        return response()->json([
            'message' => 'Subscribed successfully.'
        ], 201);
    }
}

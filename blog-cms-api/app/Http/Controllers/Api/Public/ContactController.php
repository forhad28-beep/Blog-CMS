<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([

            'name' => 'required|max:255',

            'email' => 'required|email',

            'subject' => 'required|max:255',

            'message' => 'required',

        ]);

        Contact::create($data);

        return response()->json([
            'message' => 'Message sent successfully.'
        ], 201);
    }
}
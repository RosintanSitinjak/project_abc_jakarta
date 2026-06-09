<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Jobs\SendContactInquiry;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        Contact::create($data);

        SendContactInquiry::dispatch($data);

        return response()->json(['status' => 'sent']);
    }
}

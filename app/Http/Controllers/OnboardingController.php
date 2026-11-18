<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('documents');

        return view('onboarding.show', [
            'user' => $user,
            'documents' => $user->documents()->latest()->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'business_name' => ['nullable', 'string', 'max:160'],
            'rc_number' => ['nullable', 'string', 'max:40'],
            'nin' => ['nullable', 'digits:11'],
            'bvn' => ['nullable', 'digits:11'],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:200'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'digits_between:10,10'],
            'guarantor_name' => ['nullable', 'string', 'max:120'],
            'guarantor_phone' => ['nullable', 'regex:/^0\d{10}$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach (['nin', 'bvn'] as $secret) {
            if (blank($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        $user->update($data);

        return back()->with('status', 'Profile saved.');
    }

    public function storeDocument(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(config('marketplace.document_types')))],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $file = $data['document'];
        $path = $file->store('kyc/'.$user->id, 'public');

        Document::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'status' => 'pending',
        ]);

        if (in_array($user->status, ['rejected', 'pending'], true)) {
            $user->update(['status' => 'under_review', 'rejection_reason' => null]);
        }

        return back()->with('status', 'Document received. An operator will review it.');
    }
}

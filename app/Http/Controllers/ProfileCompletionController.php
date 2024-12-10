<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileCompletionController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Profile/Complete', [
            'categories' => [
                'Grains, Pulses & Pulses',
                'Vegetables',
                'Agro Commodities Inc',
                'Coffee, Cocoa & Tea',
                'Processed Products',
                'Edible Nuts',
                'Meat & Livestock',
                'Fish and Seafood',
                'Flowers',
                'Other'
            ]
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'looking_for' => ['nullable', 'string'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string']
        ]);

        $request->user()->profile()->create([
            ...$validated,
            'profile_completed' => true
        ]);

        return Redirect::route('dashboard');
    }
}

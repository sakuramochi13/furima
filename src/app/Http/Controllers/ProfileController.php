<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;

        return view('profile', compact('user', 'profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        $user->name = $request->input('name');
        $user->save();

        $profile = $user->profile()->firstOrNew();
        $profile->postal_code = $request->input('postal_code');
        $profile->address     = $request->input('address');
        $profile->building    = $request->input('building');

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profile->profile_image_url = Storage::url($path);
        }

        $profile->save();

        return redirect()->route('profile.edit')->with('status', 'プロフィールを更新しました。');
    }
}



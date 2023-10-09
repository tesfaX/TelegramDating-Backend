<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class ProfileController extends Controller
{
    function getProfile(Request $request, $id = null) {

        if ($id) {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
        } else {
             $user = auth()->user();
        }

        if ($user->photos) {
            $baseUrl = URL::to('/');
            $userPhotos = json_decode($user->photos, true);

            $user['photos'] = array_map(function ($photo) use ($baseUrl) {
                return $baseUrl . "/api/photos/" . $photo;
            }, $userPhotos);
        }

        $userData = collect($user)->except([
            'gender_id',
        ])->toArray();
        $userData['gender'] = collect($user['gender'])->only(['id', 'name'])->toArray();;

        return response()->json([
            'success' => true,
            'user' => $userData
        ], 200);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'sometimes|nullable|string|max:100',
            'age' => 'sometimes|integer|max:100',
            'gender_id' => 'sometimes|integer|in:1,2',
            'interests' => 'array',
            'interests.*' => 'exists:interests,id',
        ]);

        $user->update($request->only(['name', 'bio', 'age', 'gender_id']));
        if ($request->has('interests')) {
            $user->interests()->sync($validatedData['interests']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated'
        ], 200);
    }



}

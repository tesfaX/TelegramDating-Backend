<?php

namespace App\Http\Controllers;

use App\Models\Dislike;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use App\Models\Like;
use App\Models\UserMatch;
use Illuminate\Support\Facades\Validator;


class DatingController extends Controller
{

    public function fetchUsers(Request $request){
        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;
        $userGenderId = $currentUser->gender_id;
        $oppositeGenderId = ($userGenderId == 1) ? 2 : 1;


        $likedUserIds = Like::where('like_by', $currentUserId)
            ->pluck('like_for')
            ->toArray();

        $dislikedUserIds = Dislike::select('dislike_by as user_id')
            ->where('dislike_for', $currentUserId)
            ->union(Dislike::select('dislike_for as user_id')
                ->where('dislike_by', $currentUserId)
            )
            ->pluck('user_id')
            ->toArray();

        $excludedUserIds = array_merge($likedUserIds, $dislikedUserIds);
        $excludedUserIds = array_unique($excludedUserIds);

        $users = User::where('id', '!=', $currentUserId)
            ->whereNotIn('id', $excludedUserIds)
            ->where('gender_id', $oppositeGenderId)
            ->with(['gender:id,name'])
            ->paginate(15);

        $customResponse = [
            'success' => true,
            'users' => $users->map(function ($user) use ($currentUserId) {
                $filteredUser = collect($user)->except(['photos', 'email', 'email_verified_at', 'gender_id', 'created_at', 'updated_at'])->toArray();
                $likedYou = Like::where('like_by', $user->id)
                        ->where('like_for', $currentUserId)
                        ->exists();
                if ($user->photos) {
                    $baseUrl = URL::to('/');
                    $userPhotos = json_decode($user->photos, true);

                    $filteredUser['photos'] = array_map(function ($photo) use ($baseUrl) {
                        return $baseUrl . "/api/photos/" . $photo;
                    }, $userPhotos);

                    $filteredUser['liked_you'] = $likedYou;
                    $filteredUser['gender'] = $user->gender;
                } else {
                    $filteredUser['photos'] = null;
                }
                return $filteredUser;
            }),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'has_next_page' => $users->lastPage() > $users->currentPage(),
        ];

        return response()->json(
            $customResponse,
            200
        );
    }


    public function like(Request $request) {
        $rules = [
            'user_id' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $userId = $request->input('user_id');
            $likedUser = User::find($userId);
            if($likedUser){

                $isMatch = Like::where([
                    'like_by' => $userId,
                    'like_for' => auth()->id()
                ])->exists();

                $newLike = new Like([
                    'like_by' => auth()->id(),
                    'like_for' => $userId
                ]);
                $newLike->save();

                if($isMatch){
                    $newMatch = new UserMatch([
                        'first_user_id' => $userId,
                        'second_user_id' => auth()->id(),
                        'status' => 1
                    ]);
                    $newMatch->save();

                    $accountName = auth()->user()->name;
                    $message = "<b>Congratulations!</b> 🎉 \n\nYou've matched with <b>{$accountName}</b>! \nStart a conversation now and get to know each other better.";
                    NotificationController::sendMessage($likedUser->tg_id, $message);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Like stored'
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Liked user doesn\'t exist'
                ], 404);
            }
        }
    }

    public function dislike(Request $request) {
        $rules = [
            'user_id' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $userId = $request->input('user_id');
            $dislikedUser = User::find($userId);
            if($dislikedUser){
                $newLike = new Dislike([
                    'dislike_by' => auth()->id(),
                    'dislike_for' => $userId
                ]);
                $newLike->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Dislike stored'
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Disliked user doesn\'t exist'
                ], 404);
            }
        }
    }

    public function matches(Request $request) {

        $baseUrl = URL::to('/');
        $currentUser = auth()->user();
        $currentUserId = auth()->id();

        $matches = UserMatch::where(function ($query) use ($currentUserId) {
            $query->where('first_user_id', $currentUserId)
                ->orWhere('second_user_id', $currentUserId);
        })
        ->where('status', 1)
        ->get();

        $filteredMatchedUsers = [];

        foreach ($matches as $match) {
            if ($match->first_user_id == $currentUserId) {
                $matchedUser = User::find($match->second_user_id);
                $matchedUser->match_id = $match->id;
            } else {
                $matchedUser = User::find($match->first_user_id);
                $matchedUser->match_id = $match->id;
            }

            if ($matchedUser->photos) {
                        $userPhotos = json_decode($matchedUser->photos, true);
                        $matchedUser->photos = array_map(function ($photo) use ($baseUrl) {
                            return $baseUrl . "/api/photos/" . $photo;
                        }, $userPhotos);
            }
            $matchedUser->gender =  $matchedUser->gender;
            $filteredMatchedUsers[] = $matchedUser;

        }

        return response()->json([
            'success' => true,
            'matches' => $filteredMatchedUsers,
        ], 200);

    }

    public function unmatch(Request $request) {
        $rules = [
            'match_id' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $matchId = $request->input('match_id');
            $userMatch = UserMatch::find($matchId);
            $userId = auth()->id();
            if($userMatch){
                if($userMatch->first_user_id == $userId || $userMatch->second_user_id == $userId){
                    if($userMatch->status == 1){
                        $userMatch -> status = 2;
                        $userMatch -> save();
                        return response()->json([
                            'success' => true,
                            'message' => 'User unmatched'
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Already unmatched'
                        ], 200);
                    }

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden'
                    ], 403);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Match doesn\'t exist'
                ], 404);
            }
        }
    }


}

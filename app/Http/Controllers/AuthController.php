<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender_id' => 'required|integer|in:1,2',
            'tg_data' => 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $telegramData = $request->input('tg_data');
            $data_check_array = explode('&', rawurldecode($telegramData));
            if( $this->isValidData($data_check_array) ){
                $userString = str_replace('user=', "", $data_check_array[1]);
                $telegramUser = json_decode($userString, true);
                $telegramId = $telegramUser['id'];

                $user = User::where('tg_id', $telegramId)->first();

                if($user){
                    return response()->json([
                        'success' => false,
                        'message' => 'User already exists'
                    ], 409);
                } else {
                    $isPremium = $telegramUser['is_premium'] ?? false;
                    $username = $telegramUser['username'];
                    $userPhotos = $this->getUserPhotos($telegramId);
                    $oppositeGenderId = ($request->gender_id == 1) ? 2 : 1;

                    $newUser = new User([
                        'name' => $request->input('name'),
                        'age' => $request->input('age'),
                        'gender_id' => $request->input('gender_id'),
                        'tg_id' => $telegramId,
                        'tg_username' => $username,
                        'has_telegram_premium' => $isPremium,
                        'interested_in' => $oppositeGenderId,
                        'user_type' => 1,
                        'status' => 1,
                        'is_pro_user' => 0,
                        'photos' => $userPhotos
                    ]);

                    $newUser->save();
                    $newUser->gender = $newUser->gender;


                    $token = $this->getTokenByTelegramId($telegramId);
                    return response()->json([
                        'success' => true,
                        'user' => $newUser,
                        'token' => $token,
                        'message' => 'User registered successfully'
                    ], 201);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request'
                ], 400);
            }
        }



    }


    public function getTokenByTelegramId($telegramId) {
        $user = User::where('tg_id', $telegramId)->first();
        if ($user) {
            $token = $user->createToken('api-token')->plainTextToken;
            return $token;
        }
        return null;
    }


    public function login(Request $request) {
        $rules = [
            'check_value' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        } else {
            $dataCheckString = $request->input('check_value');
            $data_check_array = explode('&', rawurldecode($dataCheckString));

            if( $this->isValidData($data_check_array) ){
                $userString = str_replace('user=', "", $data_check_array[1]);
                $telegramUser = json_decode($userString, true);
                $tg_id = $telegramUser['id'];

                $user = User::where('tg_id', $tg_id)->first();

                if($user){
                    $user -> photos = $this->getUserPhotos($tg_id);
                    $user -> tg_username = $telegramUser['username'];
                    $user -> save();

                    $user->gender = $user->gender;

                    $token = $this->getTokenByTelegramId($tg_id);
                    return response()->json([
                        'success' => true,
                        'user' => $user,
                        'token' => $token,
                        'message' => 'Login successful'
                    ], 200);

                } else {
                    return response()->json([
                        'success' => false,
                        'user' => null,
                        'token' => null,
                        'message' => 'User doesn\'t exist'
                    ], 200);
                }
            } else{
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request'
                ], 400);
            }
        }



    }

    private function isValidData($data_check_arr): string
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $needle = 'hash=';
        $check_hash = FALSE;
        foreach($data_check_arr AS &$val ){
            if( substr( $val, 0, strlen($needle) ) === $needle ){
                $check_hash = substr_replace( $val, '', 0, strlen($needle) );
                $val = NULL;
            }
        }

        $data_check_arr = array_filter($data_check_arr);
        sort($data_check_arr);

        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash_hmac( 'sha256', $botToken, "WebAppData", TRUE );
        $hash = bin2hex( hash_hmac( 'sha256', $data_check_string, $secret_key, TRUE ) );

        if( strcmp($hash, $check_hash) === 0 ){
            return true;
        }else{
            return false;
        }
    }

    public function getUserPhotos($telegramId) {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        $url = "https://api.telegram.org/bot{$botToken}/getUserProfilePhotos";

        $data = [
            'user_id' => $telegramId,
            'limit' => 3
        ];

        $response = Http::post($url, $data);
        if ($response->successful()) {
            $responseData = $response->json();
            $photo_list = $responseData['result']['photos'];
            $photos = [];
            if(count($photo_list) > 0){
                foreach ($photo_list as $photo) {
                    $photos[] = $photo[1]['file_id'];
                }
                return json_encode($photos);
            } else {
                return null;
            }
        }
    }

}

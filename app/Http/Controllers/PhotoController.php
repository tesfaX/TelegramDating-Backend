<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PhotoController extends Controller
{
    public function getPhoto($file_id){

        $fileId = $file_id;
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if($fileId){

            $url = "https://api.telegram.org/bot{$botToken}/getFile";

            $data = [
                'file_id' => $fileId,
            ];

            $response = Http::post($url, $data);

            // Check the response
            if ($response->successful()) {
                $responseData = $response->json();
                $filePath = $responseData['result']['file_path'];
                $photoFile = "https://api.telegram.org/file/bot{$botToken}/".$filePath;

                $imageData = Http::get($photoFile)->body();
                $response = response()->stream(
                    function () use ($imageData) {
                        echo $imageData;
                    },
                    200,
                    [
                        'Content-Type' => 'image/jpeg',
                    ]
                );

                return $response;
            } else {
                return $response->json();
            }
        } else {
            echo 'No file id';
        }



    }
}

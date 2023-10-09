<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use App\Models\Interest;
use App\Models\RelationshipType;


class AppMetaController extends Controller
{
    public function getAppMetas(){
        $genders = Gender::select('id', 'name')->get();
        $interests = Interest::select('id', 'name')->get();
        $relationshipTypes = RelationshipType::select('id', 'name')->get();
        $data = [
            'genders' => $genders,
            'interests' => $interests,
            'relationship_types' => $relationshipTypes,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);

    }
}

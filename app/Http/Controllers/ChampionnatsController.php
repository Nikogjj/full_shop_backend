<?php

namespace App\Http\Controllers;

use App\Models\Championnats;
use Illuminate\Http\Request;

class ChampionnatsController extends Controller
{
    public function addChampionnatToBDD(Request $request){
        
        return $test;
    }

    public function addTeamToChampionnat(Request $request){
        $request->validate([
            'championnat' => 'required|string',
            'name' => 'required|string',
        ]);

    }
}

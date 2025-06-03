<?php

namespace App\Http\Controllers;

use Brick\Math\BigInteger;
use Illuminate\Http\Request;
use App\Models\User;
use Ramsey\Uuid\Type\Integer;

class AuthController extends Controller
{
    /**
     * Controle le login de l'utilisateur
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        try {
            $validatedData = $request->validate([
                "email"=> "required|email",
                "password"=> "required|string|min:6",
            ]);
            $user = User::where("email", $validatedData["email"])->first();

            return response()->json(["msg" => $validatedData],200);
        } catch (\Throwable $th) {
            return response()->json(["msg"=> $th->getMessage()],500);
        }
    }

    public function register(Request $request){
        try {
            // champs attendus pour l'inscription :
            // name,email,password,birthdate,panier_id,role_id

            // je recup les champs du formulaire : name,email,password,role_id,birthdate
            $validatedData = $request->validate([
                "name"=> "required|string|min:3|max:50",
                "email"=> "required|email|unique:users,email",
                "password"=> "required|string|min:6|confirmed",
                "role_id" => "required|integer|in:1,2",
            ]);

            //je recup le panier_id
            //pas fini

            $this->createAdminUser($validatedData["name"],$validatedData["email"],$validatedData["password"], $validatedData[]);
        } 
        catch (\Throwable $th) {
            //throw $th;
        }
    }

    private function createAdminUser(String $name, String $email, String $password, BigInteger $role ){
        try {
            $user = User::create([
                "name" => $name,
                "email" => $email,
                "password" => bcrypt($password),
                "role" => $role
            ]);
            return $user;
        } 
        catch (\Throwable $th) {
            //throw $th;
            return null;
        }

    }
}

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use DB;




class LoginCrdt extends Controller
{
    public function login(Request $request)
    {

        if ($request->info =='crdtpat') {
            //Jerena sody tsy miexiste le login
            $login=Utilisateur::where("crdtpat.UTILISATEUR.LOGIN", $request->login)->first();
    
            if(!is_null($login)) { 
                //Jerena sody diso ny login sy mot de pass
                $password=Utilisateur::where("crdtpat.UTILISATEUR.LOGIN", $request->login)->where("crdtpat.UTILISATEUR.PASSWORD", $request->password)->first();
                
                // Raha oatraka marina daholo
                if(!is_null($password)) {
                    return response()->json(["status"=>'200', "success"=>true, "succedmsg"=>"Identification succé!! vous êtes connectez","data"=>$password]);
                
                }else{
                    return response()->json(["status" =>"failed", "success"=>false, "message"=>"Mot de passe incorect !" ]);
                }
            }else{
                return response()->json(["status" => "failed", "success" => false, "message" => "Ce matricule n'existe pas ",'data'=>$login]);
            }
        }
        else if ($request->info =='crdtfact') {            
             $data1=array();
             $sql="SELECT * FROM MIANDRALITINA.IDENTIFICATION  where login='".$request->login."' and password='".$request->password."'";
             $login=DB::select($sql); 

             if(!is_null($login)) {
                $data1=[
                    "login"=> "Login ou mot de passe erronne , Merci de verifier",
                    "password"=> "",
                    "nom"=>""
                ];
            }
             foreach($login as $row){
                 $data1=$row;
            }
            $resultat=[
                'login'=>$data1,    
            ]; 
            return response()->json($resultat);
        }
    }
    public function changemdp(Request $req){
        $an_mdp = $req->input("an_mdp");
        $nv_mdp = $req->input("nv_mdp");
        $user = $req->input("user");

        $resultat=array();
        $sql="SELECT * FROM MIANDRALITINA.IDENTIFICATION  where login='".$user."' and password='".$an_mdp."'";
        $login=DB::select($sql); 

        if($login==[]) {
            $resultat= [
                "etat"=> "warn",
                "titre"=> "Changement mot de passe",
                "message"=>"Votre ancien mot de passe est incorrect"
            ];
        }else{
            $sql="UPDATE MIANDRALITINA.IDENTIFICATION set  password='".$nv_mdp."' WHERE login='".$user."'";
            $login=DB::update($sql); 
            $resultat= [
                "etat"=> "success",
                "titre"=> "Changement mot de passe",
                "message"=>"Mot de passe changé !"
            ];
        }
        return response()->json($resultat);

    }
}

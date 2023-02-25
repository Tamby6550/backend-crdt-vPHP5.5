<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ClientFact extends Controller
{
    public function insertClient(Request $req)
    {
        $code_cli = $req->input("code_client");
        $nom = $req->input("nom");
        $desc = $req->input("desc");
        $rc = $req->input("rc");
        $stat = $req->input("stat");
        $cif = $req->input("cif");
        $nif = $req->input("nif");
        $resultat = "";
        $verf = array();
        $sql = "select MIANDRALITINA.VIEW_CLIENT_VER('".$code_cli."') as verfi from dual ";
        $req2 = DB::select($sql);
        foreach($req2 as $row){
            $verf=$row;
        }
        foreach($verf as $row){
            $verf=$row;
        }

        if($verf== "--a-"){
            $donne = [$code_cli, $nom, $desc, $rc, $stat, $cif, $nif];
            $sql = "INSERT INTO MIANDRALITINA.CLIENT (code_client,nom,description,rc,stat,cif,nif) values (trim(?),trim(?),trim(?),trim(?),trim(?),trim(?),trim(?) )";
            $requette = DB::insert($sql, $donne);            
            try {
                $resultat = [
                    "etat" => 'success',
                    "message" => "Enregistrement éfféctuée"
                ];
            } catch (\exception $e) {
                $resultat = [
                    "success" => false,
                    "message" => "Erreur sur l'enregistrement"
                ];
            }
        }
        else{
            $resultat = [
                "etat" => 'warn',
                "message" => "Enregistrement déjà présent! "
            ];
        }

        // return response()->json($verf);
        return response()->json($resultat);
    }

    public function getClientFact()
    {
        $data1=array();
        $sqlIdExam="SELECT nvl(count(code_client),0) as nbenreg FROM MIANDRALITINA.client ";
        $sql = "SELECT code_client,nom,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description,rc,stat,cif,nif from MIANDRALITINA.client where ROWNUM <= 10 order by code_client  asc   ";
        $req1=DB::select($sqlIdExam);
        $req2 = DB::select($sql);
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'nbenreg'=>$data1,
            'all'=>$req2
        ]; 
        return response()->json($resultat);    
    }
    public function getClientFactF()
    {
        $data1=array();
        $sqlIdExam="SELECT nvl(count(code_client),0) as nbenreg FROM MIANDRALITINA.client ";
        $sql = "SELECT code_client,nom,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description,rc,stat,cif,nif from MIANDRALITINA.client  order by code_client  asc   ";
        $req1=DB::select($sqlIdExam);
        $req2 = DB::select($sql);
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'nbenreg'=>$data1,
            'all'=>$req2
        ]; 
        return response()->json($resultat);    
    }

    public function rechercheClientFact(Request $req)
    {
        $sql = "";
        $code_cli = $req->input("code_client");
        $nom = $req->input("nom");

        $code = strtoupper($code_cli);
        $nomM = strtoupper($nom);
        $sql = "SELECT code_client,nom,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description,rc,stat,cif,nif from MIANDRALITINA.client where upper(code_client) like '%" . $code . "%' and  upper(nom) like '%" . $nomM . "%'  ";
        $requette = DB::select($sql);

        return response()->json($requette);

    }

    public function updateClientFact(Request $req)
    {
        $resultat = array();
        $code_cli = $req->input("code_client");
        $nom = $req->input("nom");
        $desc = $req->input("desc");
        $rc = $req->input("rc");
        $stat = $req->input("stat");
        $cif = $req->input("cif");
        $nif = $req->input("nif");

        $donne = [$nom, $desc, $rc, $stat, $cif, $nif, $code_cli];
        $sql = "UPDATE MIANDRALITINA.CLIENT SET  NOM=trim(?),description=trim(?),RC=trim(?),STAT=trim(?),CIF=trim(?),NIF=trim(?) WHERE CODE_CLIENT=? ";

        $requette = DB::update($sql, $donne);
        if (!is_null($requette)) {
            $resultat = [
                "etat" => 'success',
                "message" => 'Modification éfféctuée',
                'res' => $requette
            ];
        }
        return response()->json($resultat);
    }
    public function deleteClientFact($code_cli)
    {
        $sql = "DELETE FROM MIANDRALITINA.CLIENT WHERE (CODE_CLIENT=? )";

        $resultat = [];
        $requette = DB::delete($sql, [$code_cli]);
        if (!is_null($requette)) {
            $resultat = [
                "success" => true,
                "message" => "Suppression éfféctuée",
                'res' => $requette
            ];
        }
        return response()->json($resultat);
    }
}
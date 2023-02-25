<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Reglement extends Controller
{
    public function insertReglement(Request $req)
    {
        $resultat=array();
        $maxEnreg=array();
        $sqlIdMax="SELECT nvl(max(REGLEMENT_ID),0)+1 as nbenreg FROM MIANDRALITINA.REGLEMENT";//Maka max enregistrement farany
        $req1=DB::select($sqlIdMax);
        foreach($req1 as $row){
            $maxEnreg=$row;
        }
        foreach($maxEnreg as $row){
            $maxEnreg=$row;
        }
        // $resultat=[
        //     'idEnrg'=>$maxEnreg 
        // ];
        $nom = $req->input("nom");
        $desc = $req->input("desc");

        $donne=[$maxEnreg,$nom,$desc];

        $sqlInsert = "INSERT INTO MIANDRALITINA.REGLEMENT (REGLEMENT_ID, LIBELLE, DESCRIPTION) values (?,trim(?),trim(?))";
        $requette=DB::insert($sqlInsert,$donne);

        if (!is_null($requette)) {
            $resultat=[
                "etat"=>"success",
                "message"=>"Enregistrement éfféctuée",
                'sql'=>$requette 
            ];
        }
        else{
            $resultat=[
                "etat"=>'error', 
                "message"=>"Erreur sur l'enregistrement",
                'sql'=>$requette  
            ];
       }
        return response()->json($resultat);
    }

    public function getAllReglementFact()//Affiche liste reglement enregistré
    {
        $sql="select REGLEMENT_ID,LIBELLE,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description from MIANDRALITINA.REGLEMENT order by REGLEMENT_ID ASC  ";
        $req2=DB::select($sql); 
  
        return response()->json($req2);
    }

    public function rechercheReglementFact(Request $req)
    {
        $sql="";
        $nom=$req->input("nom");

        $sql="SELECT REGLEMENT_ID,LIBELLE,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description from MIANDRALITINA.REGLEMENT where upper(LIBELLE) like upper('%".$nom."%')";
        $requette=DB::select($sql);

        return response()->json($requette);
    }

    public function rechercheReglementParUser($indication)
    {
        $req="select REGLEMENT_ID,LIBELLE,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description from MIANDRALITINA.REGLEMENT where REGLEMENT_ID<>'3' order by REGLEMENT_ID  ASC";	
        $req1="select REGLEMENT_ID,LIBELLE,decode(DESCRIPTION,'null',' ',null,' ',DESCRIPTION) as description from MIANDRALITINA.REGLEMENT where (REGLEMENT_ID<>'2' and REGLEMENT_ID<>'1')   order by REGLEMENT_ID  ASC";	

        if ($indication=='admin') {
          $req=$req1;
        }

        $requette=DB::select($req);

        return response()->json($requette);
    }

    public function updateReglementFact(Request $req)
    {
        $resultat=array();
        $reglement_id=$req->input("reglement_id");
        $nom=$req->input("nom");
        $desc=$req->input("desc");

        $donne=[$nom,$desc,$reglement_id];
        $sql=" UPDATE MIANDRALITINA.REGLEMENT SET  LIBELLE=trim(?),description=trim(?) WHERE REGLEMENT_ID=?";
        
        $requette=DB::update($sql, $donne);
        if (!is_null($requette)) {
         $resultat=[
             "etat"=>'success',
             "message"=>"Modification éfféctuée",
             'sql'=>$requette 
         ];
        }
        return response()->json($resultat);
    }
    public function deleteReglementFact($reglement_id)
    {
        $sql="DELETE FROM MIANDRALITINA.REGLEMENT WHERE (REGLEMENT_ID=?)";

        $resultat=[];
        $requette=DB::delete($sql, [$reglement_id]);
        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
                "message"=>"Suppression éfféctuée",
                'res'=>$requette 
            ];
        }
        return response()->json($resultat);
    }
}

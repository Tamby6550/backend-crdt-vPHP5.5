<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class SaisieReglement extends Controller
{
    public function getSaisieReglementFact()//Affiche liste saisie reglement le 10 dernier jour
    {
        $sql="select num_fact as NUM_FACTURE,TYPE_FACTURE,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,MONTANT_NET,nvl(CLIENT,'-') as CLIENT,PATIENT,STATUS,REMISE,PEC,MONTANT_PATIENT,MONTANT_PATIENT_REGLE,RESTE_PATIENT,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,RESTE from MIANDRALITINA.billing where DATE_EXAMEN>sysdate-10 order by NUM_FACTURE desc  ";
        $req2=DB::select($sql); 
  
        return response()->json($req2);
    }
    public function rechercheSaisieReglement(Request $req)
    {
        $sql="";
        $num_fact = $req->input("num_fact");
        $client = $req->input("client");
        $patient = $req->input("patient");
        
        //date format dd/mm/aaaa
        $date = $req->input("date");
        
        $status = $req->input("status");
        $ann = $req->input("ann");
        
        $sql="SELECT num_fact as NUM_FACTURE,TYPE_FACTURE,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,MONTANT_NET,nvl(CLIENT,'-') CLIENT,PATIENT,STATUS,REMISE,PEC from MIANDRALITINA.billing where 1=1";
        
        //Recherche 

        if ($patient != "") {
            //par patient
            $sql = $sql ." AND upper(PATIENT) like upper('%" . $patient . "%')";
        }

        if ($client != "") {
            //par client
            $sql = $sql . " AND upper(client) like upper('%" . $client . "%')";
        }

        if ($num_fact != "") {
            //par numéro facture
            $sql = $sql . " AND NUM_FACT like '%" . $num_fact . "%'";
        }

        if ($date != "") {
            //par date
            $sql = $sql . " AND to_char(DATE_EXAMEN,'DD/MM/YYYY')='" . $date . "'";
        }

        if ($status != "") {
            //par status
            $sql = $sql . " AND STATUS like '%" . $status . "%' ";
        }

        if ($ann != "") {
            //par type facture
            $sql = $sql . " AND TYPE_FACTURE like '%" . $ann . "%'";
        }

        $sql = $sql ." order by NUM_FACTURE desc";
        $requette=DB::select($sql);

        return response()->json($requette);
    }

    public function afficheDetailsSaisieReglement(Request $req)
    {
        $resultat=array();
        $num_fact = $req->input("num_fact");

        $sql="select num_fact as NUM_FACTURE,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,MONTANT_NET,nvl(CLIENT,'-') CLIENT,PATIENT,STATUS,REMISE,PEC,MONTANT_PATIENT,MONTANT_PATIENT_REGLE,RESTE_PATIENT,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,RESTE from MIANDRALITINA.billing where num_fact=? ";
        
        $requette=DB::select($sql, [$num_fact]);
        foreach($requette as $row){
            $resultat=$row;
        }
        
        return response()->json($resultat);
    }


    public function insertReglementDetails(Request $req)
    {
        $resultat=array();
        $num_fact = $req->input("num_fact");

        //code reglement (Ex: virement = 3 , espèce = 1 )
        $code_reglement = $req->input("code_reglement");

        $rib = $req->input("rib");
        $montant = $req->input("montant");
        $type = $req->input("type");
        $comment = $req->input("motif");

        // $donne=[$num_fact,$reglement,$num_fact,$reglement,$rib,$montant,$type,$comment];
        $sqlInsert = "INSERT INTO MIANDRALITINA.REGLEMENT_DETAILS(ID_REGLEMENT_DETAILS,NUM_FACT,REGLEMENT_ID,RIB,MONTANT,DATE_REGLEMENT,TYPE_RGLMT,COMMENTAIRE) values ('" .$num_fact ."-" .$code_reglement ."','" .$num_fact ."','" .$code_reglement ."','" .$rib."',to_number(" .$montant. "),sysdate,'" .$type. "','" .$comment."')";
        $requette=DB::insert($sqlInsert);

        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
                "message"=>"Enregistrement éfféctuée",
                'sql'=>$requette 
            ];
        }
        else{
            $resultat=[
                "success"=>false, 
                "message"=>"Erreur sur l'enregistrement",
                'sql'=>$requette  
            ];
       }
        return response()->json($resultat);
    }

    public function affichePaimentDetailsReglmnt(Request $req)
    {
        $resultat=array();
        $num_fact = $req->input("num_fact");

        $sql="SELECT NUM_FACT,MONTANT,MIANDRALITINA.VIEW_REGLEMENT(REGLEMENT_ID) as REGLEMENT,nvl(RIB,' ') RIB, to_char(DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLEMENT ,TYPE_RGLMT FROM MIANDRALITINA.REGLEMENT_DETAILS WHERE NUM_FACT=? and REGLEMENT_ID<>'0' order by DATE_REGLEMENT desc ";
        
        $requette=DB::select($sql, [$num_fact]);
        foreach($requette as $row){
            $resultat=$row;
        }
        
        return response()->json($resultat);
    }
}

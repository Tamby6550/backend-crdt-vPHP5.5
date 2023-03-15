<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Registre extends Controller
{
    public function getNumArriv()
    {
        $resultat=array();
        $data1=array();

        $datej=date('d/m/Y');
        $sqlRegistre="select crdtpat.FORMAT_NUM_REGISTR(sysdate) from dual";
        $req=DB::select($sqlRegistre); 
        foreach($req as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        $resultat=[
            'numarr'=>$data1,
            'datej'=>$datej,
        ];
        
        return response()->json($resultat);
    }

    public function insertRegistre(Request $req)
    {
        $resultat=array();
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $id_patient = $req->input("id_patient");
        $verf_exam = 0;
        $verf_fact = 0;
        $obs_fact='0';
        $obs_exam='0';

     
        $donne=[$num_arriv,$date_arriv,$id_patient,$verf_exam,$verf_fact,$obs_fact,$obs_exam];
        $sqlInsert="INSERT INTO crdtpat.REGISTRE (NUM_ARRIV,DATE_ARRIV,ID_PATIENT,VERF_EXAM,LAST_UPDATE,VERF_FACT,OBS_EXAM,OBS_FACT) values (?,TO_DATE(?,'dd-mm-yyyy'),?,?,sysdate,?,?,?)";
        
        $sql="UPDATE crdtpat.PATIENT SET LAST_UPDATE=sysdate WHERE ID_PATIENT='".$id_patient."' ";
        try {
            $requette=DB::insert($sqlInsert,$donne);
            $requetteUp=DB::update($sql);

            $resultat=[
                "etat"=>'success',
                "message"=>"Numéro de journal d'arrivée : ".$num_arriv,
                'num_arr'=>$num_arriv 
            ];
        } catch (\Throwable $th) {
            $resultat=[
                "etat"=>'error', 
                "message"=>"Erreur sur l'enregistrement !" ,
            ];
        }
       
           return response()->json($resultat);
    }
    public function getListRegistre()
    {    
        $dates=date_create();  
        $sqlRegistre="SELECT  to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(DATE_ARR,'MM/DD/YYYY') as date_arrive,NUMERO as numero,
        ID_PATIENT as id_patient,TYPE_PATIENT as type_pat,VERF_EXAMEN as verf_exam,VERF_FACT as verf_fact,
        NOM as nom,to_char(DATE_NAISS,'DD/MM/YYYY')  as date_naiss,TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE 
        WHERE to_char(DATE_ARR,'DD/MM/YYYY')=to_char(sysdate,'DD/MM/YYYY') or VERF_EXAMEN=0 or VERF_EXAMEN=1 order by LAST_UPDATE ASC";
        $req=DB::select($sqlRegistre); 

        return response()->json($req);
    }

    public function updateRegistre(Request $req)
    {
        $resultat=array();
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $id_patient = $req->input("id_patient");
        $sql="UPDATE crdtpat.REGISTRE SET ID_PATIENT='".$id_patient."',LAST_UPDATE=sysdate WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
        
        $requette=DB::update($sql);
        if (!is_null($requette)) {
         $resultat=[
            "etat"=>'success',
             "message"=>"N° journal modifié",
             'res'=>$sql 
         ];
        }
        return response()->json($resultat);
    }
    public function rechercheRegistre(Request $req)
    {
        $sql="";
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $id_patient = $req->input("id_patient");
        $sql="SELECT to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(DATE_ARR,'MM/DD/YYYY') as date_arrive,NUMERO as numero,ID_PATIENT as id_patient,TYPE_PATIENT as type_pat,VERF_EXAMEN as verf_exam,
        NOM as nom,to_char(DATE_NAISS,'DD/MM/YYYY')  as date_naiss,TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE 
        WHERE 1=1";
        if ($num_arriv!="")  { $sql=$sql." AND NUMERO='".$num_arriv."'";}
        if ($date_arriv!="") {$sql=$sql." AND DATE_ARR=TO_DATE('".$date_arriv."','dd-mm-yyyy')";}
        if ($id_patient!="") {$sql=$sql." AND ID_PATIENT='".$id_patient."' ";}
        $sql = $sql ." ORDER BY LAST_UPDATE ASC ";
        $requette=DB::select($sql);

        return response()->json($requette);
    }

    public function deleteRegistre($num_arriv,$date_arriv)
    {
        $sql="DELETE FROM crdtpat.REGISTRE WHERE DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') AND  NUM_ARRIV='".$num_arriv."' ";

        $resultat=[];
        $requette=DB::delete($sql);
        if (!is_null($requette)) {
            $resultat=[
                "etat"=>'success',
                "message"=>"Suppression éfféctuée",
                'res'=>$requette 
            ];
        }
        return response()->json($resultat);
    }
}

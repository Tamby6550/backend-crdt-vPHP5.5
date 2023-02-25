<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Patient extends Controller
{
    public function getPatient()//Affiche liste patient de 15 dernier jours % au date de l'enregistrement
    {
        $resultat=array();
        $data1=array();
        $sqlIdExam="SELECT nvl(max(ID_PATIENT),0)+1 as nbenreg FROM crdtpat.PATIENT";
        //trunc(LAST_UPDATE)>=trunc(sysdate-15) = Ze enregistrer le dernier 15 jours ou égale
        $sqlExam="SELECT ID_PATIENT,initcap(NOM) as NOM,initcap(nvl(PRENOM,' ')) as PRENOM,to_char(DATENAISS,'DD/MM/YYYY') as DATENAISS ,TYPE_PATIENT as type,SEXE,nvl(ADRESSE,' ') as ADRESSE,nvl(TELEPHONE,' ') as TELEPHONE FROM crdtpat.PATIENT WHERE trunc(LAST_UPDATE)>=trunc(sysdate-15) order by LAST_UPDATE DESC  ";
        $req1=DB::select($sqlIdExam);
        $req2=DB::select($sqlExam); 
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'nbenreg'=>$data1,
            'listePatient'=>$req2
        ]; 
        return response()->json($resultat);
    }
    public function affichePatient($id_patient)
    {
        $data1=array();
        $sqlExam="SELECT ID_PATIENT,initcap(NOM) as NOM,initcap(nvl(PRENOM,' ')) as PRENOM,to_char(DATENAISS,'DD/MM/YYYY') as DATENAISS ,TYPE_PATIENT,SEXE,nvl(ADRESSE,' ') as ADRESSE,nvl(TELEPHONE,' ') as TELEPHONE FROM crdtpat.PATIENT  WHERE ID_PATIENT=?  ";
        $req2=DB::select($sqlExam,[$id_patient]); 
        foreach($req2 as $row){
            $data1=$row;
        }
        
        return response()->json($data1);
    }

    public function insertPatient(Request $req)
    {
        $resultat=array();
        $id_patient = $req->input("id_patient");
        $noms = $req->input("nom");
        $prenom = $req->input("prenom");
        $type = $req->input("type");
        $sexe = $req->input("sexe");
        $date_naiss = $req->input("date_naiss");
        $telephone = $req->input("telephone");
        $adresse = $req->input("adresse");
        if (trim($adresse)=='') {
            $adresse='-';
          }
          if (trim($telephone)=='') {
            $telephone='-';
          }
        $donne=[$id_patient,$noms,$prenom,$date_naiss,$type,$sexe,$adresse,$telephone];
        $sqlInsert="INSERT INTO crdtpat.PATIENT (ID_PATIENT,NOM,PRENOM,DATENAISS,TYPE_PATIENT,SEXE,ADRESSE,TELEPHONE,LAST_UPDATE) values (?,trim(initcap(?)),trim(initcap(?)),TO_DATE(?,'dd-mm-yyyy'),trim(upper(?)),trim(upper(?)),trim(?),trim(upper(?)),sysdate )";
        $requette=DB::insert($sqlInsert,$donne);

        if (!is_null($requette)) {
            $resultat=[
                "etat"=>'success',
                "message"=>"Enregistrement éfféctuée ",
                'res'=>$requette 
            ];
           }else{
            $resultat=[
                "success"=>false, 
                "message"=>"Erreur sur l'enregistrement" 
            ];
           }
           return response()->json($resultat);
    }
    public function recherchePatient(Request $req)
    {
        $sql="";
        $noms=$req->input("nom");
        $prenom=$req->input("prenom");
        $type=$req->input("type");
        $sexe=$req->input("sexe");
        $date_naiss=$req->input("date_naiss"); 
        $telephone=$req->input("telephone");
        $sql="SELECT ID_PATIENT,initcap(NOM) as NOM,initcap(nvl(PRENOM,' ')) as PRENOM,to_char(DATENAISS,'DD/MM/YYYY') as DATENAISS ,TYPE_PATIENT as type,SEXE,nvl(ADRESSE,' ') as ADRESSE,nvl(TELEPHONE,' ') as TELEPHONE FROM crdtpat.PATIENT WHERE 1=1";

        if ($noms!="")          {$sql=$sql." AND upper(nom) like upper('%".$noms."%') ";}
        if ($prenom!="")        {$sql=$sql." AND upper(prenom) like upper('%".$prenom."%') ";}
        if ($type!="")          {$sql=$sql." AND TYPE_PATIENT='".$type."' ";}
        if ($sexe!="")          {$sql=$sql." AND SEXE='".$sexe."' ";}
        if ($date_naiss!="")    {$sql=$sql." AND trunc(DATENAISS)=TO_DATE('".$date_naiss."','dd-mm-yyyy')";}
        if ($telephone!="")     {$sql=$sql." AND upper(TELEPHONE) like upper('%".$telephone."%') ";}
        $sql = $sql ." ORDER BY LAST_UPDATE DESC";
        $requette=DB::select($sql);

        return response()->json($requette);
    }
    public function updatePatient(Request $req)
    {
        $resultat=array();
        $id_patient = $req->input("id_patient");
        $noms = $req->input("nom");
        $prenom = $req->input("prenom");
        $type = $req->input("type");
        $sexe = $req->input("sexe");
        $date_naiss = $req->input("date_naiss");
        $telephone = $req->input("telephone");
        $adresse = $req->input("adresse");
        if (trim($adresse)=='') {
            $adresse='-';
          }
          if (trim($telephone)=='') {
            $telephone='-';
          }
        $donne=[$noms,$prenom,$date_naiss,$type,$sexe,$adresse,$telephone,$id_patient];
        $sql="UPDATE crdtpat.PATIENT SET NOM=?,PRENOM=?,DATENAISS=TO_DATE(?,'dd-mm-yyyy'),TYPE_PATIENT=trim(?),SEXE=trim(?),ADRESSE=trim(?),TELEPHONE=trim(?),LAST_UPDATE=sysdate WHERE ID_PATIENT=?";
        
        $requette=DB::update($sql, $donne);
        if (!is_null($requette)) {
         $resultat=[
            "etat"=>'success',
             "message"=>"Modification éfféctuée",
             'res'=>$requette 
         ];
        }
        return response()->json($resultat);
    }

    public function deletePatient($id_patient)
    {
        $sql="DELETE FROM crdtpat.PATIENT WHERE ID_PATIENT=?  ";

        $resultat=[];
        $requette=DB::delete($sql, [$id_patient]);
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

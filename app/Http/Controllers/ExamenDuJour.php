<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ExamenDuJour extends Controller
{
    public function getExamenNonEff()//Vef_examen dans registre est 0
    {    
        $sql="SELECT to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(DATE_ARR,'MM/DD/YYYY') as date_arrive,NUMERO as numero,ID_PATIENT as id_patient,TYPE_PATIENT as type_pat,VERF_EXAMEN as verf_exam,
        NOM as nom,to_char(DATE_NAISS,'DD/MM/YYYY')  as date_naiss,TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE 
        WHERE VERF_EXAMEN='0' order by LAST_UPDATE ASC";
        $req=DB::select($sql); 

        return response()->json($req);
    }
    public function insertExamenJour(Request $req)
    {
        $resultat=array();
        $nbinput=$req->input('nbinput');
        $verf=0;
        $num_facture = "-";
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $donneExam = $req->input("donne");
        $cr_name="-";
        $sqlInsert="INSERT INTO MIANDRALITINA.EXAMEN_DETAILS(NUM_FACT,LIB_EXAMEN,CODE_TARIF,QUANTITE,MONTANT,DATE_EXAMEN,TYPE,NUM_ARRIV,DATE_ARRIV,CR_NAME) 
        values(?,?,?,REPLACE(?,'.',','),?,sysdate,?,?,TO_DATE(?,'dd-mm-yyyy'),?)";
        $sql="UPDATE crdtpat.REGISTRE SET VERF_EXAM=1 ,LAST_UPDATE=sysdate WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') ";
        
        for ($i=0; $i < count($donneExam); $i++) { 
            $lib_examen = $donneExam[$i]['lib_examen'];
            $code_tarif = $donneExam[$i]['code_tarif'];
            $quantite = $donneExam[$i]['quantite'];
            $montant = $donneExam[$i]['montant'];
            $type_examen = $donneExam[$i]['type_examen'];
            $donne=[$num_facture,$lib_examen,$code_tarif,$quantite,$montant,$type_examen,$num_arriv,$date_arriv,$cr_name];
            try {
                $requette=DB::insert($sqlInsert,$donne);
                $verf=1;
            } catch (\Throwable $th) {
                $verf=0;
                break;
            }
        }
        
        if ($verf==1) {
            $requette=DB::update($sql);
            $resultat=[
                "etat"=>'success',
                "message"=>"Enregistrement éfféctuée ",
                'num_arriv'=>$num_arriv, 
                'date_arriv'=>$date_arriv, 
                'donneExam'=>$donneExam[0]['lib_examen'],
                'donneExam'=>count($donneExam)
            ];
        }
        else{
            $resultat=[
                "etat"=>'error',
                "message"=>"Erreur sur l'enregistrement , verifier la connexion de la base de donne" 
            ];
        }
        return response()->json($resultat);
    }

    public function getExamenEff()//Vef_examen dans registre est 1
    {    
        $sql="SELECT to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(ls.DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(ls.DATE_ARR,'MM/DD/YYYY') as date_arrive,
        ls.NUMERO as numero,ls.ID_PATIENT as id_patient,ls.TYPE_PATIENT as type_pat,ls.VERF_EXAMEN as verf_exam, 
        (select distinct  to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') from MIANDRALITINA.EXAMEN_DETAILS ex where ex.NUM_ARRIV=ls.NUMERO and ex.DATE_ARRIV=ls.DATE_ARR ) as date_examen,
        ls.NOM as nom,to_char(ls.DATE_NAISS,'DD/MM/YYYY')  as date_naiss,ls.TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE ls
        WHERE ls.VERF_EXAMEN='1' order by LAST_UPDATE DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }
    
    public function getPatientExamenEff($num_arriv,$date_arriv)
    {    
        $verf=false;
        $sql1=" select lib_examen,cr_name,Type as type from MIANDRALITINA.EXAMEN_DETAILS where NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') ";
        $sql="SELECT ex.*,to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') as date_exam FROM MIANDRALITINA.EXAMEN_DETAILS ex WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') order by LIB_EXAMEN DESC";
        $req=DB::select($sql); 
        $req1=DB::select($sql1); 
        $cr_names = collect($req1)->pluck('cr_name');
        $types = collect($req1)->pluck('type');
        $libelle_exams = collect($req1)->pluck('lib_examen');
        for ($i=0; $i < count($cr_names); $i++) { 
            if ($types[$i] !="ECG" ) {
                if ( $types[$i] !="PANORAMIQUE DENTAIRE") {
                if ( $libelle_exams[$i] !="DENTASCAN") {
                if ( $libelle_exams[$i] !="CRANE 1 INC") {
                if ( $libelle_exams[$i] !="CRANE 2 INC") {
                if ( $libelle_exams[$i] !="CRANE SHULLER ( les 2 côtés )") {
                if ( $libelle_exams[$i] !="PRODUIT DE CONTRTASTE SCAN") {
                        if ( $cr_names[$i] =='-') {
                            $verf=true;
                        }
                    }
                }}}}}}
                
        }
        
        $resultat=[
                "liste"=>$req,
                "2"=>count($cr_names),
                "verf"=>$verf,
            ];
        return response()->json($resultat);
    }

    public function deleteExamenDetails(Request $req)
    {
    
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $lib_examen = $req->input("lib_examen");
        //Ovaina / ny tiret rehetra raha misy
        // $lib_examens = str_replace('-', '/', $lib_examen);
        //Supprssion dans examen details
        //Rehefa iray no examen natao, ka supprimena dia mivadika ho lasa tsis examen vita
        $data1=array();

        $sqlNbreExam="SELECT MIANDRALITINA.COUNT_EXAMEN_DETAILS('".$num_arriv."', TO_DATE('".$date_arriv."','dd-mm-yyyy')) from dual";
        $req1=DB::select($sqlNbreExam);
        
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        if ($data1==1) {//Ovaina ny registre ho lasa verfexam=0 ,
            $sql2="UPDATE crdtpat.REGISTRE SET VERF_EXAM=0,LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
            $req2=DB::update($sql2);
        }

        $sql="DELETE FROM MIANDRALITINA.EXAMEN_DETAILS WHERE ROWNUM = 1 AND NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') AND trim(upper(LIB_EXAMEN)) = trim(upper('".$lib_examen."')) ";
        $resultat=[];
        $requette=DB::delete($sql);
        if (!is_null($requette)) {
            $resultat=[
                "etat"=>'success',
                "message"=>"Examen ".$lib_examen." est bien supprimer !",
                'nbrexamen'=>$data1, 
                'res'=>$sqlNbreExam, 
                'lib'=>$lib_examen
            ];
        }
        return response()->json($resultat);
    }

    public function updateExamenDetailsCR(Request $req)
    {
        $resultat=array();
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $cr_name = $req->input("cr_name");
        $lib_examen = $req->input("lib_examen");

        $donne=[$cr_name,$num_arriv,$date_arriv,];
        $sql="UPDATE MIANDRALITINA.EXAMEN_DETAILS SET CR_NAME=? WHERE NUM_ARRIV=? AND  DATE_ARRIV=TO_DATE(?,'dd-mm-yyyy') AND trim(upper(LIB_EXAMEN)) = trim(upper('".$lib_examen."'))";
        
        $requette=DB::update($sql, $donne);
        if (!is_null($requette)) {
         $resultat=[
            "etat"=>'success',
             "message"=>"Compte Rendu enregistré",
             'res'=>$cr_name 
         ];
        }
        return response()->json($resultat);
    }
    public function validationExamen(Request $req)
    {
        $resultat=array();
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $verfexamen = $req->input("verfexamen");
        $sql2="UPDATE crdtpat.REGISTRE SET VERF_EXAM='".$verfexamen."',LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
        
        $req2=DB::update($sql2);
        if (!is_null($req2)) {
         $resultat=[
            "etat"=>'success',
             "message"=>"Examen(s) Validés",
             'res'=>$num_arriv ,
             'date'=>$date_arriv ,
             'req'=>$sql2 
         ];
        }
        return response()->json($resultat);
    }


    //Vef_examen dans registre est 2
    public function getExamenEffValide()//le 1 dernier jour qui effectue leur examen ou qui n'est pas facturé
    {    
        $sql="SELECT ls.VERF_FACT, to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(ls.DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(ls.DATE_ARR,'MM/DD/YYYY') as date_arrive,
        ls.NUMERO as numero,ls.ID_PATIENT as id_patient,ls.TYPE_PATIENT as type_pat,ls.VERF_EXAMEN as verf_exam,
        (select distinct  to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') from MIANDRALITINA.EXAMEN_DETAILS ex where ex.NUM_ARRIV=ls.NUMERO and ex.DATE_ARRIV=ls.DATE_ARR ) as date_examen,
        NOM as nom,to_char(DATE_NAISS,'DD/MM/YYYY')  as date_naiss,TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE ls
        WHERE (trunc(DATE_ARR)>=trunc(sysdate-1) or VERF_FACT='0') AND VERF_EXAMEN='2' order by ls.LAST_UPDATE DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }

    //Recherche Vef_examen dans registre est 2
    public function getRehercheExamenEffValide(Request $req)
    {    
        $numero_arr = $req->input("numero_arr");
        $date_arr = $req->input("date_arr");
        $date_naiss = $req->input("date_naiss");
        $nom = $req->input("nom");

        $sql="SELECT  ls.VERF_FACT, to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(ls.DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(ls.DATE_ARR,'MM/DD/YYYY') as date_arrive,
        ls.NUMERO as numero,ls.ID_PATIENT as id_patient,ls.TYPE_PATIENT as type_pat,ls.VERF_EXAMEN as verf_exam,
        (select distinct  to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') from MIANDRALITINA.EXAMEN_DETAILS ex where ex.NUM_ARRIV=ls.NUMERO and ex.DATE_ARRIV=ls.DATE_ARR ) as date_examen,
        ls.NOM as nom,to_char(ls.DATE_NAISS,'DD/MM/YYYY')  as date_naiss,ls.TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE  ls
        WHERE ls.VERF_EXAMEN='2'   ";
        
        if ($numero_arr!="")          {$sql=$sql." AND ls.NUMERO='".$numero_arr."' ";}
        if ($date_arr!="")        {$sql=$sql." AND trunc(ls.DATE_ARR)=TO_DATE('".$date_arr."','dd-mm-yyyy')";}
        if ($date_naiss!="")    {$sql=$sql." AND trunc(ls.DATE_NAISS)=TO_DATE('".$date_naiss."','dd-mm-yyyy')";}
        if ($nom!="")          {$sql=$sql." AND upper(ls.NOM) like upper('%".$nom."%') ";}

        $sql = $sql ." order by ls.LAST_UPDATE  DESC";
        $requette=DB::select($sql);
        return response()->json($requette);
    }
   
}

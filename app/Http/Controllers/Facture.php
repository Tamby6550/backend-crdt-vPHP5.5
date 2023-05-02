<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Facture extends Controller
{
    //Vef_examen dans registre est 2 et verf_fact = 0
    public function getNonFacture()
    {    
        $sql="SELECT to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(DATE_ARR,'DD/MM/YYYY') as date_arr,to_char(DATE_ARR,'MM/DD/YYYY') as date_arrive,NUMERO as numero,ID_PATIENT as id_patient,TYPE_PATIENT as type_pat,VERF_EXAMEN as verf_exam,
        NOM as nom,to_char(DATE_NAISS,'DD/MM/YYYY')  as date_naiss,TELEPHONE as telephone FROM CRDTPAT.LISTEREGISTRE 
        WHERE VERF_EXAMEN='2' AND VERF_FACT='0' order by LAST_UPDATE DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }
    public function getPatientExamenFacture($num_arriv,$date_arriv)
    {    
        $data1=array();
        $sql1="select sum(ex.QUANTITE*ex.MONTANT) as total from MIANDRALITINA.EXAMEN_DETAILS ex where ex.NUM_ARRIV='".$num_arriv."' AND ex.DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') ";
        $requette=DB::select($sql1);

        $sql2="SELECT  
        (select  PEC from MIANDRALITINA.Facture where NUM_FACT=ex.NUM_FACT) as pec,
        (select  REMISE from MIANDRALITINA.Facture where NUM_FACT=ex.NUM_FACT) as remise,

        (select  CODE_PRESC from MIANDRALITINA.Facture where NUM_FACT=ex.NUM_FACT) as code_presc,
        (select  CODE_CLIENT from MIANDRALITINA.Facture where NUM_FACT=ex.NUM_FACT) as code_client,
        (select  cli.NOM from MIANDRALITINA.Facture fac,MIANDRALITINA.CLIENT cli where cli.CODE_CLIENT=fac.CODE_CLIENT and fac.NUM_FACT=ex.NUM_FACT) as nom_client,
        (select  presc.NOM from MIANDRALITINA.Facture fac,CRDTPAT.PRESCRIPTEUR presc where presc.CODE_PRESC=fac.CODE_PRESC and fac.NUM_FACT=ex.NUM_FACT) as nom_presc

        FROM MIANDRALITINA.EXAMEN_DETAILS ex 
        WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')";
        $requett2=DB::select($sql2);
        $firstRow = get_object_vars($requett2[0]);

        $sql="SELECT ex.*,to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') as date_exam 
        FROM MIANDRALITINA.EXAMEN_DETAILS ex WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') order by LIB_EXAMEN DESC";
        $req=DB::select($sql); 
        foreach($requette as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'total'=>$data1,
            'all'=>$req,
            'pec_rmise'=>$firstRow

        ]; 
        return response()->json($resultat);
    }

    //Affiche zavtr ilaina
    public function getPageFacture($num_arriv,$date_arriv)
    {
        $resultat=array();
        $data1=array();
        $sqlIdFacture="SELECT MIANDRALITINA.FORMAT_NUM(SYSDATE)  as num_facture,sysdate as datej,ls.TYPE_PATIENT as tarif FROM CRDTPAT.LISTEREGISTRE ls where ls.DATE_ARR=TO_DATE('".$date_arriv."','dd-mm-yyyy') and ls.NUMERO='".$num_arriv."'";
        $req1=DB::select($sqlIdFacture);
        foreach($req1 as $row){
            $data1=$row;
        }
        return response()->json($data1);
    }
    public function changmentTarif(Request $req)
    {
        $id_patient = $req->input("id_patient");

        $donneExam = $req->input("donne");
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $tarifNouveau = $req->input("tarif");
        $a=array();
        $verf=0;
        // $data1=array();
        $nouveauMontant=0;

        //Mis a jour Type patient
        $sqlUpdatePatient="UPDATE crdtpat.PATIENT SET TYPE_PATIENT=trim(?),LAST_UPDATE=sysdate WHERE ID_PATIENT=?";
        $test=array();
        $sqlUpdate="UPDATE MIANDRALITINA.EXAMEN_DETAILS SET MONTANT=? WHERE NUM_ARRIV=? AND  DATE_ARRIV=TO_DATE(?,'dd-mm-yyyy') AND trim(upper(LIB_EXAMEN)) = trim(upper(?))";
        for ($i=0; $i <count($donneExam); $i++) { 
            $lib_examen = $donneExam[$i]['lib_examen'];
            $code_tarif = $donneExam[$i]['code_tarif'];
            $types=$donneExam[$i]['type'];
            //Maka ny montant tarif vao2
            $sql="SELECT MONTANT as montant FROM MIANDRALITINA.EXAMEN ex WHERE CODE_TARIF='".$code_tarif."' AND trim(upper(LIBELLE)) = trim(upper('".$lib_examen."'))  AND TARIF='".$tarifNouveau."' AND trim(upper(TYPES)) = trim(upper('".$types."'))";
            $req1=DB::select($sql);
            // $montant_modif = collect($req1)->pluck('montant');
            foreach($req1 as $row){
                    $data1=$row;
                }
            foreach($data1 as $row){
                $data1=$row;
            }
            $nouveauMontant=$data1;
            array_push($test,$data1);
            
            $a[$i]=$nouveauMontant;
            // //Manova ny table examens details
            $donne=[$a[$i],$num_arriv,$date_arriv,$lib_examen];
            try {
                $req2=DB::update($sqlUpdate,$donne);
                $verf=1;
                $data1=array();

            } catch (\Throwable $th) {
                $verf=0;
                break;
            }
        }
        if ($verf==1) {
            $requette=DB::update($sqlUpdatePatient,[$tarifNouveau,$id_patient]);
            $resultat=[
                "etat"=>'success',
                "message"=>"Modification tarif éfféctuée avec succés ",
                'num_arriv'=>$num_arriv, 
                'date_arriv'=>$date_arriv, 
                'donneExam'=>$donneExam[0]['lib_examen'],
                'nouveau'=>$nouveauMontant,
                'test'=>$test,
                
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
    
    public function insertFacture(Request $req)
    {
        
        $resultat=array();
        $num_facture = $req->input("num_facture");
        $ref_carte = $req->input("ref_carte");
        $date_facture = $req->input("date_facture");
        $patient = $req->input("patient");
        $type = $req->input("type");
        $avoir = $req->input("type_facture");
        $reglement_id = $req->input("reglement_id");

        $rib = $req->input("rib");

        $code_cli = $req->input("code_cli");
        $nom_cli = $req->input("nom_cli");
        $pec = $req->input("pec");
        $remise = $req->input("remise");
        $code_presc = $req->input("code_presc");
        $nom_presc = $req->input("nom_presc");
        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");

        $montant_brute = $req->input("montant_brute");
        $montant_brute = str_replace(" ", "", $montant_brute);
        $montant_brute = round($montant_brute, 2);

        $montant_net = $req->input("montant_net");
        $montant_net = str_replace(" ", "", $montant_net);
        $montant_net = round($montant_net, 2);

        $montant_patient = $req->input("montant_patient");
        $montant_patient = str_replace(" ", "", $montant_patient);
        $montant_patient = round($montant_patient, 2);

    

        $montant_pech = $req->input("montant_pech");
        $montant_pech = str_replace(" ", "", $montant_pech);
        $montant_pech = round($montant_pech, 2);

        
        $sqlInsertFacture="INSERT INTO MIANDRALITINA.FACTURE (NUM_FACT,DATY,TYPE_CLIENT,TYPE_FACTURE,PATIENT,REGLEMENT_ID,REMISE,PEC,RIB,CODE_CLIENT,CODE_PRESC,MONTANT_BRUTE,MONTANT_NET,MONTANT_PATIENT,MONTANT_PEC,REF_CARTE) 
        values ('".$num_facture."',sysdate,'".$type."','".$avoir."','".$patient."','".$reglement_id."','".$remise."','".$pec."','".$rib."','".$code_cli."','".$code_presc."','".$montant_brute."','".$montant_net."','".$montant_patient."','".$montant_pech."','".$ref_carte."')";
        

        $sqlUpdateExamen="UPDATE MIANDRALITINA.EXAMEN_DETAILS SET NUM_FACT=? WHERE NUM_ARRIV=? AND  DATE_ARRIV=TO_DATE(?,'dd-mm-yyyy') ";
        $donne=[$num_facture,$num_arriv,$date_arriv];
        $sql2="UPDATE crdtpat.REGISTRE SET VERF_FACT=1,LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
        

        $sqlInsertReglementId="INSERT INTO MIANDRALITINA.REGLEMENT_DETAILS(ID_REGLEMENT_DETAILS,NUM_FACT,REGLEMENT_ID,RIB,MONTANT,DATE_REGLEMENT,TYPE_RGLMT) 
        values ('".$num_facture.'-'.$reglement_id."','".$num_facture."','".$reglement_id."',null,'0',sysdate,'P')";
        
        try {
            //Insertion Facture
            $requetteFact=DB::insert($sqlInsertFacture);
              
            //Insertion Reglement_Id
            $requetteRId=DB::insert($sqlInsertReglementId);
                
            //Update table examen_details pour le numéro facture
            $req2=DB::update($sqlUpdateExamen,$donne);

            //modif registre
            $req2=DB::update($sql2);
            $resultat=[
                "etat"=>'success',
                "message"=>"Facture bien éfféctuée !",
               
            ];

        } catch (\Throwable $th) {
            $resultat=[
                "success"=>false, 
                "message"=>"Erreur sur l'enregistrement !" ,
                "erreur"=>$th
            ];
        }
        
        return response()->json($resultat);
    }

    public function insertReglementFacture(Request $req)
    {
        $num_facture = $req->input("num_facture");
        $reglement_id = $req->input("reglement_id");

        $type_reglmnt=$req->input("type_reglmnt");

        $montantreglement = $req->input("montantreglement");
        $montantreglement = str_replace(" ", "", $montantreglement);
        $montantreglement = round($montantreglement, 2);

        $rib = $req->input("rib");

        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");

        $regle='0';
        $sqlInsertReglementId="INSERT INTO MIANDRALITINA.REGLEMENT_DETAILS(ID_REGLEMENT_DETAILS,NUM_FACT,REGLEMENT_ID,RIB,MONTANT,DATE_REGLEMENT,TYPE_RGLMT) 
        values ('".$num_facture.'-'.$reglement_id."','".$num_facture."','".$reglement_id."','".$rib."','".$montantreglement."',sysdate,'".$type_reglmnt."')";
        try {

        //Insertion Reglement_Id
        $requetteRId=DB::insert($sqlInsertReglementId);

        //Mijery ny reste, raha 0 de  payé zany hoe miova table registre
        $data1=array();
        $sql="select RESTE as reste from MIANDRALITINA.billing1 where num_fact='".$num_facture."' ";
        $req=DB::select($sql); 

        foreach($req as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        if ($data1==0) {
            $sql2="UPDATE crdtpat.REGISTRE SET VERF_FACT=2,LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
            $req2=DB::update($sql2);
            $regle='1';
        }

        if ($regle=='1') {
            $resultat=[
                "etat"=>'success',
                "message"=>"Enregistrement bien éfféctuée avec facture réglé !",
                'res'=>$sqlInsertReglementId ,
                'regle'=>$regle
            ];
        }else{
            $resultat=[
                "etat"=>'success',
                "message"=>"Enregistrement bien éfféctuée !",
                'res'=>$sqlInsertReglementId ,
                'regle'=>$regle
                
            ];
        }
        } catch (\Throwable $th) {
            $resultat=[
                "etat"=>'error',
                "success"=>false, 
                "message"=>"Erreur sur l'enregistrement , ressayer ulterièrement" ,
                "erreur"=>$th
            ];
        }
        return response()->json($resultat);
    }


    //Vef_examen dans registre est 2 et verf_fact = 1, facture non regler, le 3 dernier jour 
    public function getEffectFacture()
    {    
        //Ny Type facture de avy @facture fa tsy patient eto
        $sql="SELECT  R.NUM_ARRIV AS NUMERO,R.DATE_ARRIV AS DATE_ARR,R.ID_PATIENT AS ID_PATIENT,
        to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(R.DATE_ARRIV,'DD/MM/YYYY') as date_arr,to_char(R.DATE_ARRIV,'MM/DD/YYYY') as date_arrive,
		initcap(P.NOM||' '||nvl(P.PRENOM,' ')) as NOM,P.TYPE_PATIENT AS TYPE_PATIENT,
		to_char(P.DATENAISS,'DD/MM/YYYY') AS DATE_NAISS,P.TELEPHONE AS TELEPHONE,
		R.VERF_EXAM AS VERF_EXAMEN,R.VERF_FACT AS VERF_FACT,
		RRF.NUM_FACT,  to_char(RRF.DATE_EXAMEN,'DD/MM/YYYY') AS DATE_EXAMEN,RRF.TYPE_CLIENT AS TYPE_PATIENT,
        to_char(RRF.DATE_FACTURE,'DD/MM/YYYY') AS DATE_FACTURE,to_char(RRF.DATE_FACTURE,'MM/DD/YYYY') as date_fverf,
		R.LAST_UPDATE as LAST_UPDATE,
    
        (SELECT count(*)  FROM MIANDRALITINA.REGLEMENT_DETAILS WHERE NUM_FACT=RRF.NUM_FACT and REGLEMENT_ID<>'0') as nbreRgl,
        (SELECT bill.MONTANT_PATIENT   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT  AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as totalpat,
        (SELECT bill.MONTANT_PEC   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as totalpec,
        (SELECT bill.MONTANT_PATIENT_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as rpatient,
        (SELECT bill.MONTANT_PEC_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as rclient
        
        FROM CRDTPAT.REGISTRE R,CRDTPAT.PATIENT P ,MIANDRALITINA.RELIER_REGISTRE_FACTURE RRF
		WHERE R.VERF_EXAM='2' AND R.VERF_FACT='1' AND
        R.ID_PATIENT=P.ID_PATIENT AND R.DATE_ARRIV=RRF.DATE_ARRIV AND R.NUM_ARRIV=RRF.NUM_ARRIV  
        AND (trunc(RRF.DATE_FACTURE)>=trunc(sysdate-5) or (SELECT count(*)  FROM MIANDRALITINA.REGLEMENT_DETAILS WHERE NUM_FACT=RRF.NUM_FACT and REGLEMENT_ID<>'0')=0)
		ORDER BY  RRF.NUM_FACT DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }
    public function getRechercheEffectFacture(Request $req)
    {    
        $num_facture = $req->input("num_facture");
        $date_facture = $req->input("date_facture");
        $nom_patient = $req->input("nom_patient");
        $nom_client = $req->input("nom_client");
        $numero_arr = $req->input("numero_arr");
        $date_arr = $req->input("date_arr");
        $pec = $req->input("pec");
        $date_debut = $req->input("date_debut");
        $date_fin = $req->input("date_fin");

        
        //Ny Type facture de avy @facture fa tsy patient eto
        $sql="SELECT  R.NUM_ARRIV AS NUMERO,R.DATE_ARRIV AS DATE_ARR,R.ID_PATIENT AS ID_PATIENT,
        to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(R.DATE_ARRIV,'DD/MM/YYYY') as date_arr,to_char(R.DATE_ARRIV,'MM/DD/YYYY') as date_arrive,
		initcap(P.NOM||' '||nvl(P.PRENOM,' ')) as NOM,P.TYPE_PATIENT AS TYPE_PATIENT,
		to_char(P.DATENAISS,'DD/MM/YYYY') AS DATE_NAISS,P.TELEPHONE AS TELEPHONE,
		R.VERF_EXAM AS VERF_EXAMEN,R.VERF_FACT AS VERF_FACT,
		RRF.NUM_FACT,  to_char(RRF.DATE_EXAMEN,'DD/MM/YYYY') AS DATE_EXAMEN,RRF.TYPE_CLIENT AS TYPE_PATIENT,
        to_char(RRF.DATE_FACTURE,'DD/MM/YYYY') AS DATE_FACTURE,to_char(RRF.DATE_FACTURE,'MM/DD/YYYY') as date_fverf,
		R.LAST_UPDATE as LAST_UPDATE,
    
        (SELECT count(*)  FROM MIANDRALITINA.REGLEMENT_DETAILS WHERE NUM_FACT=RRF.NUM_FACT and REGLEMENT_ID<>'0') as nbreRgl,
        (SELECT bill.MONTANT_PATIENT   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT  AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as totalpat,
        (SELECT bill.MONTANT_PEC   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as totalpec,
        (SELECT bill.MONTANT_PATIENT_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as rpatient,
        (SELECT bill.MONTANT_PEC_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT AND bill.DATE_ARRIV=RRF.DATE_ARRIV AND bill.NUM_ARRIV=RRF.NUM_ARRIV ) as rclient
        
        FROM CRDTPAT.REGISTRE R,CRDTPAT.PATIENT P ,MIANDRALITINA.RELIER_REGISTRE_FACTURE RRF
		WHERE R.VERF_EXAM='2' AND R.VERF_FACT='1' AND
        R.ID_PATIENT=P.ID_PATIENT AND R.DATE_ARRIV=RRF.DATE_ARRIV AND R.NUM_ARRIV=RRF.NUM_ARRIV";

        if ($num_facture!="")          {$sql=$sql." AND RRF.NUM_FACT='".$num_facture."' ";}
        if ($date_facture!="")        {$sql=$sql." AND to_char(RRF.DATE_FACTURE,'DD/MM/YYYY')='".$date_facture."'";} 
        if ($nom_patient!="")          {$sql=$sql." AND upper(initcap(P.NOM||' '||nvl(P.PRENOM,' '))) like upper('%".$nom_patient."%')  ";}

         //rehefa recherche par client fotsiny
        if ($nom_client!="" )          {
            $sql=$sql." AND (SELECT bill.CLIENT   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT  AND upper(bill.CLIENT) 
            like upper('%".$nom_client."%') ) like upper('%".$nom_client."%')";
        }

        //Recherche izay misy prise en charge
        if ($pec)   {
            $sql=$sql." AND (SELECT bill.PEC   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT  AND bill.PEC>0) >0";
        }

        if ($numero_arr!="")    {$sql=$sql." AND  R.NUM_ARRIV='".$numero_arr."' ";}
        if ($date_arr!="")     {$sql=$sql." AND R.DATE_ARRIV=TO_DATE('".$date_arr."','dd-mm-yyyy') ";}

        //rehefa misafidy ny recherche entre deux date
        if ($date_debut !="" && $date_fin !="" )     {
            $sql=$sql."  AND  R.DATE_ARRIV>=TO_DATE('".$date_debut."','dd-mm-yyyy') AND R.DATE_ARRIV<=TO_DATE('".$date_fin."','dd-mm-yyyy')";
        }


        $sql = $sql ." ORDER BY  RRF.NUM_FACT DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }

    public function getFactureRegler()
    {    
        //Ny Type facture de avy @facture fa tsy patient eto
        $sql="SELECT R.NUM_ARRIV AS NUMERO,R.DATE_ARRIV AS DATE_ARR,R.ID_PATIENT AS ID_PATIENT,
        to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(R.DATE_ARRIV,'DD/MM/YYYY') as date_arr,to_char(R.DATE_ARRIV,'MM/DD/YYYY') as date_arrive,
		initcap(P.NOM||' '||nvl(P.PRENOM,' ')) as NOM,P.TYPE_PATIENT AS TYPE_PATIENT,
		to_char(P.DATENAISS,'DD/MM/YYYY') AS DATE_NAISS,P.TELEPHONE AS TELEPHONE,
		R.VERF_EXAM AS VERF_EXAMEN,R.VERF_FACT AS VERF_FACT,
		RRF.NUM_FACT,  to_char(RRF.DATE_EXAMEN,'DD/MM/YYYY') AS DATE_EXAMEN,RRF.TYPE_CLIENT AS TYPE_PATIENT,
        to_char(RRF.DATE_FACTURE,'DD/MM/YYYY') AS DATE_FACTURE,
		R.LAST_UPDATE as LAST_UPDATE
		FROM CRDTPAT.REGISTRE R,CRDTPAT.PATIENT P ,MIANDRALITINA.RELIER_REGISTRE_FACTURE RRF
		WHERE R.VERF_EXAM='2' AND R.VERF_FACT='2' AND
        R.ID_PATIENT=P.ID_PATIENT AND R.DATE_ARRIV=RRF.DATE_ARRIV AND R.NUM_ARRIV=RRF.NUM_ARRIV AND trunc(R.LAST_UPDATE)>=trunc(sysdate-5) 
		ORDER BY  RRF.NUM_FACT DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }

    public function getRechercheFactureRegle(Request $req)
    {    
        $num_facture = $req->input("num_facture");
        $date_facture = $req->input("date_facture");
        $nom_patient = $req->input("nom_patient");
        $nom_client = $req->input("nom_client");
        $numero_arr = $req->input("numero_arr");
        $date_arr = $req->input("date_arr");

        
        //Ny Type facture de avy @facture fa tsy patient eto
        $sql="SELECT  R.NUM_ARRIV AS NUMERO,R.DATE_ARRIV AS DATE_ARR,R.ID_PATIENT AS ID_PATIENT,
        to_char(sysdate,'MM/DD/YYYY')  as jourj, to_char(R.DATE_ARRIV,'DD/MM/YYYY') as date_arr,to_char(R.DATE_ARRIV,'MM/DD/YYYY') as date_arrive,
		initcap(P.NOM||' '||nvl(P.PRENOM,' ')) as NOM,P.TYPE_PATIENT AS TYPE_PATIENT,
		to_char(P.DATENAISS,'DD/MM/YYYY') AS DATE_NAISS,P.TELEPHONE AS TELEPHONE,
		R.VERF_EXAM AS VERF_EXAMEN,R.VERF_FACT AS VERF_FACT,
		RRF.NUM_FACT,  to_char(RRF.DATE_EXAMEN,'DD/MM/YYYY') AS DATE_EXAMEN,RRF.TYPE_CLIENT AS TYPE_PATIENT,
        to_char(RRF.DATE_FACTURE,'DD/MM/YYYY') AS DATE_FACTURE,
		R.LAST_UPDATE as LAST_UPDATE
          
        FROM CRDTPAT.REGISTRE R,CRDTPAT.PATIENT P ,MIANDRALITINA.RELIER_REGISTRE_FACTURE RRF
		WHERE R.VERF_EXAM='2' AND R.VERF_FACT='2' AND
        R.ID_PATIENT=P.ID_PATIENT AND R.DATE_ARRIV=RRF.DATE_ARRIV AND R.NUM_ARRIV=RRF.NUM_ARRIV";

        if ($num_facture!="")          {$sql=$sql." AND RRF.NUM_FACT='".$num_facture."' ";}
        if ($date_facture!="")        {$sql=$sql." AND to_char(RRF.DATE_FACTURE,'DD/MM/YYYY')='".$date_facture."'";} 
        if ($nom_patient!="")          {$sql=$sql." AND upper(initcap(P.NOM||' '||nvl(P.PRENOM,' '))) like upper('%".$nom_patient."%')  ";}

        if ($nom_client!="")          {
            $sql=$sql." AND (SELECT bill.CLIENT   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RRF.NUM_FACT  AND upper(bill.CLIENT) 
            like upper('%".$nom_client."%') ) like upper('%".$nom_client."%')";
        }

        if ($numero_arr!="")    {$sql=$sql." AND  R.NUM_ARRIV='".$numero_arr."' ";}
        if ($date_arr!="")     {$sql=$sql." AND R.DATE_ARRIV=TO_DATE('".$date_arr."','dd-mm-yyyy') ";}

        $sql = $sql ." ORDER BY  RRF.NUM_FACT DESC";
        $req=DB::select($sql); 
        
        return response()->json($req);
    }
    public function getInfoPatientFacture($num_facture)
    {    
        $data1=array();
        $num_facture= str_replace("-", "/", $num_facture);
        $sql="Select bill.*,
        (SELECT RC FROM MIANDRALITINA.CLIENT WHERE CODE_CLIENT=bill.CODE_CLI) as RC,
        (SELECT STAT   FROM MIANDRALITINA.CLIENT WHERE CODE_CLIENT=bill.CODE_CLI) as STAT,
        (SELECT CIF   FROM MIANDRALITINA.CLIENT WHERE CODE_CLIENT=bill.CODE_CLI) as CIF,
        (SELECT NIF   FROM MIANDRALITINA.CLIENT WHERE CODE_CLIENT=bill.CODE_CLI) as NIF,
        (SELECT trim(initcap(to_char(DATY,'DAY'))  ||' '|| to_char(DATY,'DD')  || ' ' || initcap(to_char(DATY,'MONTH'))|| ' ' ||
        to_char(DATY,'YYYY') ) FROM MIANDRALITINA.Facture   WHERE NUM_FACT='".$num_facture."') as date_fact,
        (SELECT ref_carte FROM MIANDRALITINA.Facture   WHERE NUM_FACT='".$num_facture."') as ref_carte,
        MONTANT_NET as net_mtnet,MONTANT_PEC as net_pec from MIANDRALITINA.BILLING1 bill where NUM_FACT='".$num_facture."'";
        $req=DB::select($sql); 
        foreach($req as $row){
            $data1=$row;
        }
        return response()->json($data1);
    }

    public function getInfoPatientReglementFacture($num_facture)
    {    
        $data1=array();
        $num_facture= str_replace("-", "/", $num_facture);
        $sql="select num_fact as NUM_FACTURE,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,MONTANT_NET,nvl(CLIENT,'-') CLIENT,
        PATIENT,STATUS,REMISE,PEC,MONTANT_PATIENT,MONTANT_PATIENT_REGLE,RESTE_PATIENT,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,
        RESTE from MIANDRALITINA.billing1 where num_fact='".$num_facture."' ";
        $req=DB::select($sql); 
        foreach($req as $row){
            $data1=$row;
        }
        return response()->json($data1);
    }

    public function getListReglementFacture($num_facture)
    {    
        $data1=array();
        $num_facture= str_replace("-", "/", $num_facture);
        $sql="SELECT to_char(sysdate,'DD/MM/YYYY') as ajr,RGL.REGLEMENT_ID as regl_id ,RGL.NUM_FACT,RGL.MONTANT as net,RGL.MONTANT,
        MIANDRALITINA.VIEW_REGLEMENT(REGLEMENT_ID) as REGLEMENT,nvl(RGL.RIB,' ') RIB,
        to_char(RGL.DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLEMENT ,
        (SELECT distinct bill.RESTE_PATIENT   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RGL.NUM_FACT ) as reste_patient,
        (SELECT distinct bill.RESTE_PEC   FROM MIANDRALITINA.BILLING1 bill WHERE NUM_FACT=RGL.NUM_FACT ) as reste_client,
        TYPE_RGLMT FROM MIANDRALITINA.REGLEMENT_DETAILS RGL WHERE NUM_FACT='".$num_facture."' and REGLEMENT_ID<>'0' order by DATE_REGLEMENT desc  ";
        $req=DB::select($sql); 
        
        $resultat=[
            "etat"=>'success',
            "message"=>"Modification règlement !",
            'res'=>$sql ,
            'req'=>$req,
        ];
        return response()->json($req);
    }

    public function modifReglementFacture(Request $req)
    {
        $num_facture = $req->input("num_facture");
        $reglement_id = $req->input("reglement_id");

        $type_reglmnt=$req->input("type_reglmnt");

        $montantreglement = $req->input("montantreglement");
        $montantreglement = str_replace(" ", "", $montantreglement);
        $montantreglement = round($montantreglement, 2);

        $rib = $req->input("rib");
        $date_reglmnt = $req->input("date_reglmnt");

        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");

        $regle='0';
        $sqlInsertReglementId="UPDATE MIANDRALITINA.REGLEMENT_DETAILS SET REGLEMENT_ID=".$reglement_id." , RIB='".$rib."' , TYPE_RGLMT='".$type_reglmnt."'  
        WHERE MONTANT=".$montantreglement." AND NUM_FACT='".$num_facture."' AND  to_char(DATE_REGLEMENT,'DD/MM/YYYY')='".$date_reglmnt."'";
        try {

        //Mis a jour Reglement
        $requetteRId=DB::update($sqlInsertReglementId);


         $data1=array();
         $sql="select OBS_FACT from crdtpat.REGISTRE where NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
         $reqreg=DB::select($sql); 
 
        foreach($reqreg as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }

        // $reobs=array();
        // // MD-R : Modif Reglement
        if ( is_numeric($data1)) {
            $sql2="UPDATE crdtpat.REGISTRE SET OBS_FACT='MD-R-1',LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
            $req2=DB::update($sql2);
            $data1='hhaha';
        }else{
            $reobs=str_split($data1);
            $obs_fact='MD-R-'.round($reobs['5']+1);
            $sql2="UPDATE crdtpat.REGISTRE SET OBS_FACT='".$obs_fact."',LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";
            $req2=DB::update($sql2);
            $data1='heelo';
        }
        

        $resultat=[
            "etat"=>'success',
            "message"=>"Modification règlement !",
            'res'=>$data1
        ];
        } catch (\Throwable $th) {
            $resultat=[
                "success"=>false, 
                "message"=>"Erreur sur modification !" ,
                "erreur"=>$th,
                "sql"=>$sqlInsertReglementId
            ];
        }
       
        return response()->json($resultat);
    }
    public function modifPecRemiseFacture(Request $req)
    {
        $num_facture = $req->input("num_facture");
        $pec = $req->input("pec");
        $remise = $req->input("remise");
        $code_presc = $req->input("code_presc");
        $code_cli = $req->input("code_cli");


        $montant_net = $req->input("montant_net");
        $montant_net = str_replace(" ", "", $montant_net);
        $montant_net = round($montant_net, 2);

        $montant_patient = $req->input("montant_patient");
        $montant_patient = str_replace(" ", "", $montant_patient);
        $montant_patient = round($montant_patient, 2);

        $montant_pech = $req->input("montant_pech");
        $montant_pech = str_replace(" ", "", $montant_pech);
        $montant_pech = round($montant_pech, 2);

        $sqlmdpecremise="UPDATE MIANDRALITINA.FACTURE SET CODE_PRESC='".$code_presc."',CODE_CLIENT='".$code_cli."', 
        PEC=".$pec." , REMISE='".$remise."' , MONTANT_NET='".$montant_net."', MONTANT_PATIENT='".$montant_patient."',
        MONTANT_PEC='".$montant_pech."'WHERE NUM_FACT='".$num_facture."'";
        try {

        //Mis a jour Reglement
        $requetteUpdate=DB::update($sqlmdpecremise);

        $resultat=[
            "etat"=>'success',
            "message"=>"Modification facture !",
        ];
        } catch (\Throwable $th) {
            $resultat=[
                "success"=>false, 
                "message"=>"Erreur sur modification !" ,
                "erreur"=>$th
            ];
        }
       
        return response()->json($resultat);
    }

    public function retourFactNonRegleEnNonPaye(Request $req)
    {

        $num_arriv = $req->input("num_arriv");
        $date_arriv = $req->input("date_arriv");
        $num_facture = $req->input("num_facture");

        //Mamafa ny ao @reglement, ilay 0 montant
        $sqlSupprimeReglmDetails="DELETE FROM miandralitina.REGLEMENT_DETAILS  WHERE NUM_FACT='".$num_facture."'";

        //Mamafa ny ao @facture
        $sqlSupprimeFacture="DELETE FROM miandralitina.FACTURE  WHERE NUM_FACT='".$num_facture."'";

        //Manao mis a jour ny examen_details ny num_fact
        $sqlUpdateExamenDetails="UPDATE MIANDRALITINA.EXAMEN_DETAILS SET NUM_FACT='-' WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy') ";
        
        //Manao mis a jour ny registre ny verf_fact
        $sql2="UPDATE crdtpat.REGISTRE SET VERF_FACT=0,LAST_UPDATE=sysdate  WHERE NUM_ARRIV='".$num_arriv."' AND DATE_ARRIV=TO_DATE('".$date_arriv."','dd-mm-yyyy')  ";

        $requette1=DB::delete($sqlSupprimeReglmDetails);
        $requette2=DB::delete($sqlSupprimeFacture);
        $reqUpdate3=DB::update($sqlUpdateExamenDetails);
        $reqUpdate4=DB::update($sql2);

        $resultat=[
            "etat"=>'info',
            "message"=>"Déplacement bien éfféctuée ! ",
        ];
       
        return response()->json($resultat);

    }
   public function testAPL()
   {
    $string='MR-L-1';
    $reobs=str_split($string);
    $reobs['5']=round($reobs['5']+1);

    $resultat=[
        "success"=>false, 
        "message"=>"Erreur sur l'enregistrement !" ,
        "erreur"=>$reobs
    ];
    return response()->json($resultat);

   }
}

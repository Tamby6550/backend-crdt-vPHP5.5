<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Rapport extends Controller
{
    //Facture du jour
    public function getMtFacturejour($date_facture)
    {
        $sql1="SELECT NVL(MIN(substr(NUM_FACT,7,4)),0) as starts ,NVL(MAX(substr(NUM_FACT,7,4)),0) as ends,NVL(count(*),0) as counts,
        trim(NVL(to_char(sum(MONTANT_NET),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0)) as montant,
        trim(NVL(to_char(sum(MONTANT_PATIENT_REGLE+MONTANT_PEC_REGLE),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0)) as montant_rglmt 
        FROM MIANDRALITINA.BILLING1 WHERE to_char(DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."'";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $starts=$data1->starts;
        $ends=$data1->ends;
        $counts=$data1->counts;
        $montant=$data1->montant;
        $montant_rglmt=$data1->montant_rglmt;

        $resultat=[
            'starts'=>$starts,
            'ends'=>$ends,
            'counts'=>$counts,
            'montant'=>$montant,
            'montant_rglmt'=>$montant_rglmt,
        ]; 
        return response()->json($resultat);
    }
    public function getFactureJour($starts,$ends,$date_facture)
    {    
        $sql2="SELECT NUM_ARRIV,to_char(DATE_ARRIV,'DD/MM/YYYY') as DATE_ARRIV,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,NUM_FACT,substr(CLIENT,1,25) as CLIENT,substr(PATIENT,1,29) as PATIENT,
        trim(to_char(MONTANT_NET,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) MONTANT,substr(REGLEMNT,1,1) as REGLEMNT,
        trim(to_char(MONTANT_PATIENT_REGLE+MONTANT_PEC_REGLE,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) MONTANT_REGL,
        substr(TYPE_FACTURE,1,1) as TYPE_FACTURE ,MIANDRALITINA.VIEW_ECHO(NUM_FACT) as ECHO,MIANDRALITINA.VIEW_MAMMO(NUM_FACT) as MAMO,
        MIANDRALITINA.VIEW_PANO(NUM_FACT) as PANNO,MIANDRALITINA.VIEW_ECG(NUM_FACT) as ECG,MIANDRALITINA.VIEW_AUTRES(NUM_FACT) as PRODUIT,
        MIANDRALITINA.VIEW_RADIO(NUM_FACT) as RADIO,MIANDRALITINA.VIEW_SCAN(NUM_FACT) as SCAN,OBSERVATION 
        FROM MIANDRALITINA.BILLING1 WHERE to_char(DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."' 
        and ( to_number(substr(NUM_FACT,7,4))>='".$starts."' and to_number(substr(NUM_FACT,7,4))<='".$ends."') ORDER BY NUM_FACT ASC";
        $req2=DB::select($sql2); 

        return response()->json(['Data'=>$req2]);
    }
    
    // -------------------------------------------Recette du jour---------------------------------------//
    public function getMtRecettejour($date_facture)
    {
        //Espèces
        $sql1ESP="SELECT sum(MONTANT) as MONTANT_ESP 
        FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT
        AND TYPE_FACTURE='0' AND A.REGLEMENT_ID=1 AND to_char(DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."'";

        //Chèques
        $sql1CH="SELECT sum(MONTANT) as MONTANT_CHQ 
        FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT
        AND TYPE_FACTURE='0' AND A.REGLEMENT_ID=2 AND to_char(DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."'";

        //Montant
        $sql1Mt="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts,
        sum(MONTANT) as montant
        FROM (SELECT ROWNUM as ID,to_char(DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLMT,A.NUM_FACT as NUM_FACT,PATIENT,MIANDRALITINA.VIEW_CLIENT(CODE_CLIENT) as CLIENT,
        MIANDRALITINA.VIEW_REGLEMENT(A.REGLEMENT_ID) as REGLEMNT,MONTANT 
        FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B
        WHERE A.NUM_FACT=B.NUM_FACT AND TYPE_FACTURE='0' AND A.REGLEMENT_ID in ('1','2') 
        AND to_char(DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."'   ORDER BY A.NUM_FACT ASC  )";

        $req1=DB::select($sql1ESP); 
        $req2=DB::select($sql1CH); 
        $req3=DB::select($sql1Mt); 

        $data1=array();
        $data2=array();
        $data3=array();
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($req2 as $row){
            $data2=$row;
        }
        foreach($req3 as $row){
            $data3=$row;
        }

        $montant_esp=$data1->montant_esp;
        $montant_chq=$data2->montant_chq;

        $starts=$data3->starts;
        $ends=$data3->ends;
        $counts=$data3->counts;
        $montant=$data3->montant;

        if ($starts==''|| $starts==null) {
           $starts='0';
           $ends='0';
        }
        $resultat=[
            'montant_chq'=>trim($montant_chq),
            'montant_esp'=>trim($montant_esp),
            'starts'=>trim($starts),
            'ends'=>trim($ends),
            'counts'=>trim($counts),
            'montant'=>trim($montant)
        ]; 

        return response()->json($resultat);
    }
    public function getRecetteJour($starts,$ends,$date_facture)
    {    
        $sql2="SELECT ID,DATE_REGLMT,NUM_FACT,PATIENT,CLIENT,REGLEMNT,NUM_ARRIV,DATE_ARRIV,trim(MONTANT) as MONTANT FROM 
        ( SELECT ROWNUM as ID,to_char(DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLMT,A.NUM_FACT as NUM_FACT,
        PATIENT,MIANDRALITINA.VIEW_CLIENT(CODE_CLIENT) as CLIENT,MIANDRALITINA.VIEW_REGLEMENT(A.REGLEMENT_ID) as REGLEMNT,
        MIANDRALITINA.VIEW_NUM_ARRIV(A.NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(A.NUM_FACT) AS DATE_ARRIV,
        MONTANT 
        FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B  WHERE 
        A.NUM_FACT=B.NUM_FACT AND TYPE_FACTURE='0' AND A.REGLEMENT_ID in ('1','2') AND to_char(DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' ) 
        WHERE ID>='".$starts."' and ID <='".$ends."' ORDER BY NUM_FACT ASC";
        $req2=DB::select($sql2); 

        return response()->json(['Data'=>$req2]);
    }



     // -------------------------------------------Virement du jour---------------------------------------//
     public function getMtVirementjour($date_debut,$date_fin)
    {
        $sql1="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts,
        trim(NVL(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0)) as montant
        FROM (SELECT ROWNUM as ID,to_char(DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLMT,A.NUM_FACT as NUM_FACT,PATIENT,MIANDRALITINA.VIEW_CLIENT(CODE_CLIENT) as CLIENT,
        MIANDRALITINA.VIEW_REGLEMENT(A.REGLEMENT_ID) as REGLEMNT,MONTANT FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B 
        WHERE A.NUM_FACT=B.NUM_FACT AND TYPE_FACTURE='0' AND A.REGLEMENT_ID='3' AND 
        trunc(DATE_REGLEMENT)>=to_date('".$date_debut."','dd/mm/yyyy') and trunc(DATE_REGLEMENT)<=to_date('".$date_fin."','dd/mm/yyyy')   ORDER BY A.NUM_FACT ASC)";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $starts=$data1->starts;
        $ends=$data1->ends;
        $counts=$data1->counts;
        $montant=$data1->montant;

        $resultat=[
            'starts'=>$starts,
            'ends'=>$ends,
            'counts'=>$counts,
            'montant'=>$montant,
        ]; 
        return response()->json($resultat);
    }
    public function getVirementJour($starts,$ends,$date_debut,$date_fin)
    {    
        $sql2="SELECT ID,DATE_REGLMT,NUM_FACT,PATIENT,CLIENT,REGLEMNT,NUM_ARRIV,DATE_ARRIV,trim(to_char(MONTANT,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT FROM 
        ( SELECT ROWNUM as ID,to_char(DATE_REGLEMENT,'DD/MM/YYYY') as DATE_REGLMT,A.NUM_FACT as NUM_FACT,
        PATIENT,MIANDRALITINA.VIEW_CLIENT(CODE_CLIENT) as CLIENT,MIANDRALITINA.VIEW_REGLEMENT(A.REGLEMENT_ID) as REGLEMNT,
        MIANDRALITINA.VIEW_NUM_ARRIV(A.NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(A.NUM_FACT) AS DATE_ARRIV,
        MONTANT 
        FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B WHERE 
        A.NUM_FACT=B.NUM_FACT AND TYPE_FACTURE='0' AND A.REGLEMENT_ID='3' AND  trunc(DATE_REGLEMENT)>=to_date('".$date_debut."','dd/mm/yyyy') and trunc(DATE_REGLEMENT)<=to_date('".$date_fin."','dd/mm/yyyy')  ) 
        WHERE ID>='".$starts."' and ID <='".$ends."' ORDER BY NUM_FACT ASC";
        $req2=DB::select($sql2); 

        return response()->json(['Data'=>$req2]);
    }
    
    
    // -------------------------------------------Statistique examen---------------------------------------//
    public function getStatExamen(Request $req)
    {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");

        $sql1="SELECT sum(NOMBRE) NOMBRE,sum(MONTANT) as TOTAL,trim(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) MONTANT FROM 
        (SELECT TYPE ,count(*) as NOMBRE ,sum(MONTANT_NET) as MONTANT FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE)";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $nombre=$data1->nombre;
        $total=$data1->total;
        $montant=$data1->montant;

        $sql="SELECT TYPE ,sum(MONTANT_NET) as MONT,count(*) as NOMBRE,trim(to_char(sum(MONTANT_NET),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE ORDER BY TYPE DESC";
        $req=DB::select($sql); 

        $resultat=[
            'nombre'=>$nombre,
            'total'=>$total,
            'montant'=>$montant,
            'data'=>$req,
        ]; 
        return response()->json($resultat);
    }


    // -------------------------------------------Statistique Client---------------------------------------//
    public function getMtClientStat(Request $req)
    {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");
        $code_client=$req->input("code_cli");

        $sql1="SELECT sum(QUANTITE) as QUANTITE,trim(to_char(sum(QUANTITE*MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT 
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B  
        WHERE A.NUM_FACT=B.NUM_FACT and REJET<>'1' and TYPE<>'AUTRES' and CODE_CLIENT='".$code_client."' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy')";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $quantite=$data1->quantite;
        $montant=$data1->montant;

        $sql="SELECT  min(ID) as starts,max(ID) as ends,count(id) as counts FROM(SELECT ROWNUM as ID,NUM_FACT,LIB_EXAMEN,QUANTITE,PU,MONTANT,DATY FROM 
        ( SELECT A.NUM_FACT as NUM_FACT, LIB_EXAMEN, QUANTITE, MONTANT as PU, QUANTITE*MONTANT as MONTANT ,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATY 
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT and REJET<>'1' and TYPE<>'AUTRES' and CODE_CLIENT='".$code_client."' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') ORDER BY DATE_EXAMEN))";
        $req=DB::select($sql); 

        $data2=array();
        foreach($req as $row){
            $data2=$row;
        }

        $starts=$data2->starts;
        $ends=$data2->ends;
        $counts=$data2->counts;
        
        $resultat=[
            'quantite'=>$quantite,
            'montant'=>$montant,
            'starts'=>$starts,
            'ends'=>$ends,
            'counts'=>$counts
        ]; 
        return response()->json($resultat);
    }
    public function getClientStat(Request $req)
    {    
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");
        $code_client=$req->input("code_cli");
        $starts=$req->input("starts");
        $ends=$req->input("ends");

        $sql2="SELECT ID,NUM_ARRIV,DATE_ARRIV,NUM_FACT,LIB_EXAMEN,PATIENT,QUANTITE,
        trim(to_char(PU,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as PU,
        trim(to_char(MONTANT,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT,
        DATY FROM(SELECT ROWNUM as ID,MIANDRALITINA.VIEW_NUM_ARRIV(NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(NUM_FACT) AS DATE_ARRIV,
        NUM_FACT,LIB_EXAMEN,PATIENT,QUANTITE,PU,MONTANT,DATY FROM 
        (SELECT MIANDRALITINA.VIEW_NUM_ARRIV(A.NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(A.NUM_FACT) AS DATE_ARRIV
        ,A.NUM_FACT as NUM_FACT, LIB_EXAMEN,PATIENT,QUANTITE, MONTANT as PU, QUANTITE*MONTANT as MONTANT ,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATY 
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT and REJET<>'1'
        and TYPE<>'AUTRES' and CODE_CLIENT='".$code_client."' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy')
        ORDER BY DATE_EXAMEN)) where ID>='".$starts."' and ID<='".$ends."'";
        $req2=DB::select($sql2); 
    
        return response()->json(['Data'=>$req2]);
    }


      // -------------------------------------------Statistique detaille examen---------------------------------------//
      public function getStatDetailleExamen(Request $req)
      {
          $date_deb=$req->input("date_deb");
          $date_fin=$req->input("date_fin");
  
          $sql1="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts FROM( 
            SELECT ROWNUM as ID,EXAMEN,COUNT,MONTANT 
            FROM (SELECT LIB_EXAMEN as EXAMEN,sum(QUANTITE) as COUNT,SUM(MONTANT_NET) as MONTANT 
            FROM MIANDRALITINA.EXAMEN_STAT where REJET<>'1' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') 
            and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY LIB_EXAMEN ORDER BY LIB_EXAMEN ASC))";
          $req1=DB::select($sql1); 
  
          $data1=array();
          foreach($req1 as $row){
              $data1=$row;
          }
  
          $starts=$data1->starts;
          $ends=$data1->ends;
          $counts=$data1->counts;
  
          $sql="SELECT ID,EXAMEN,COUNT,MONTANT FROM 
          (SELECT ROWNUM as ID,EXAMEN,COUNT,trim(to_char(MONTANT,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT FROM 
          (SELECT LIB_EXAMEN as EXAMEN,sum(QUANTITE) as COUNT,SUM(QUANTITE*MONTANT) as MONTANT 
          FROM MIANDRALITINA.EXAMEN_DETAILS WHERE trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') 
          and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') 
          and REJET<>'1' GROUP BY LIB_EXAMEN ORDER BY LIB_EXAMEN ASC))
          where ID>='".$starts."' and ID<='".$ends."'";
          $req=DB::select($sql); 
  
          $resultat=[
              'starts'=>$starts,
              'ends'=>$ends,
              'counts'=>$counts,
              'data'=>$req,
          ]; 
          return response()->json($resultat);
      }

    // -------------------------------------------Statistique Prescripteur---------------------------------------//
    public function getMtStatPrescripteur(Request $req)
    {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");
        $code_presc=$req->input("code_presc");

        $sql1="SELECT sum(QUANTITE) as QUANTITE,trim(to_char(sum(QUANTITE*MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B  WHERE A.NUM_FACT=B.NUM_FACT and TYPE<>'PRODUIT' and CODE_PRESC='".$code_presc."' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy')";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $quantite=$data1->quantite;
        $montant=$data1->montant;

        $sql="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts FROM(SELECT ROWNUM as ID,NUM_FACT,LIB_EXAMEN,QUANTITE,PU,MONTANT,DATY FROM 
        ( SELECT A.NUM_FACT as NUM_FACT, LIB_EXAMEN, QUANTITE, MONTANT as PU, QUANTITE*MONTANT as MONTANT ,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATY 
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT and TYPE<>'PRODUIT' and CODE_PRESC='".$code_presc."' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') ORDER BY DATE_EXAMEN))";
        $req=DB::select($sql); 

        $data2=array();
        foreach($req as $row){
            $data2=$row;
        }

        $starts=$data2->starts;
        $ends=$data2->ends;
        $counts=$data2->counts;
        
        $resultat=[
            'quantite'=>$quantite,
            'montant'=>$montant,
            'starts'=>$starts,
            'ends'=>$ends,
            'counts'=>$counts,
            'sql'=>$sql,
        ]; 
        return response()->json($resultat);
    }
    public function getStatPrescripteur(Request $req)
    {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");
        $starts=$req->input("starts");
        $ends=$req->input("ends");
        $code_presc=$req->input("code_presc");

        $sql1="SELECT ID,NUM_ARRIV,DATE_ARRIV,NUM_FACT,LIB_EXAMEN,PATIENT,QUANTITE,
        trim(to_char(PU,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as PU,
        trim(to_char(MONTANT,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT,
        DATY FROM(SELECT ROWNUM as ID,MIANDRALITINA.VIEW_NUM_ARRIV(NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(NUM_FACT) AS DATE_ARRIV,
        NUM_FACT,LIB_EXAMEN,PATIENT,QUANTITE,PU,MONTANT,DATY FROM 
        (SELECT MIANDRALITINA.VIEW_NUM_ARRIV(A.NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(A.NUM_FACT) AS DATE_ARRIV
        ,A.NUM_FACT as NUM_FACT, LIB_EXAMEN,PATIENT,QUANTITE, MONTANT as PU, QUANTITE*MONTANT as MONTANT ,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATY
        FROM MIANDRALITINA.EXAMEN_DETAILS A,MIANDRALITINA.FACTURE B WHERE A.NUM_FACT=B.NUM_FACT and TYPE<>'PRODUIT' 
        and CODE_PRESC='".$code_presc."' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') 
        ORDER BY DATE_EXAMEN)) where ID>='".$starts."' and ID<='".$ends."'";
        $req1=DB::select($sql1); 

        return response()->json(['Data'=>$req1]);
    }
    
    

     // -------------------------------------------Statistique Catégorie---------------------------------------//
     public function getStatCategorie(Request $req)
     {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");

        $sql1="SELECT sum(NOMBRE) NOMBRE,sum(MONTANT) as TOTAL,
        trim(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) MONTANT 
        FROM ( SELECT TYPE_CLIENT ,count(*) as NOMBRE ,sum(MONTANT_NET) as MONTANT FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' 
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE_CLIENT)";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $nombre=$data1->nombre;
        $total=$data1->total;
        $montant=$data1->montant;

        $sql="SELECT TYPE_CLIENT ,sum(MONTANT_NET) as MONT,count(*) as NOMBRE,
        trim(to_char(sum(MONTANT_NET),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
        trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE_CLIENT";
        $req=DB::select($sql); 

        $resultat=[
            'nombre'=>$nombre,
            'total'=>$total,
            'montant'=>$montant,
            'data'=>$req,
        ]; 
        return response()->json($resultat);
     }

     // -------------------------------------------Statistique Cumul chiffre d'affaire---------------------------------------//
     public function getCumulChiffre(Request $req)
     {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");

        $sql1="SELECT sum(NOMBRE) NOMBRE,sum(MONTANT) as TOTAL,trim(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) MONTANT 
        FROM ( SELECT TYPE ,count(*) as NOMBRE ,sum(MONTANT_NET) as MONTANT 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'  and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE)";
        $req1=DB::select($sql1); 

        $data1=array();
        foreach($req1 as $row){
            $data1=$row;
        }

        $nombre=$data1->nombre;
        $total=$data1->total;
        $montant=$data1->montant;

        $sql="SELECT TYPE ,sum(MONTANT_NET) as MONT,count(*) as NOMBRE,
        trim(to_char(sum(MONTANT_NET),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as MONTANT 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'  
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') GROUP BY TYPE";
        $req=DB::select($sql); 

        $resultat=[
            'nombre'=>$nombre,
            'total'=>$total,
            'montant'=>$montant,
            'data'=>$req,
        ]; 
        return response()->json($resultat);
     }



    // -------------------------------------------Releve facture---------------------------------------//
     public function getMtReleveFact(Request $req)
     {
         $date_deb=$req->input("date_deb");
         $date_fin=$req->input("date_fin");
         $code_client=$req->input("code_cli");
 
         $sql1="SELECT trim(to_char(sum(MONTANT_PEC),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''))  as montant_pec,
         trim(to_char(sum(MONTANT_PEC_REGLE),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as montant_pec_regle,
         trim(to_char(sum(RESTE_PEC),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as reste_pec_regle 
         FROM MIANDRALITINA.BILLING1 WHERE TYPE_FACTURE<>'Oui' and CODE_CLI='".$code_client."' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy')";
         $req1=DB::select($sql1); 
 
         $data1=array();
         foreach($req1 as $row){
             $data1=$row;
         }
 
         $montant_pec=$data1->montant_pec;
         $montant_pec_regle=$data1->montant_pec_regle;
         $reste_pec_regle=$data1->reste_pec_regle;
 
         $sql="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts 
         FROM (SELECT  ROWNUM AS ID,NUM_FACT,DATE_EXAMEN,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,PATIENT FROM MIANDRALITINA.BILLING1 
         WHERE TYPE_FACTURE<>'Oui' and CODE_CLI='".$code_client."' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') 
         ORDER BY trunc(DATE_EXAMEN) ASC)";
         $req=DB::select($sql); 
 
         $data2=array();
         foreach($req as $row){
             $data2=$row;
         }
 
         $starts=$data2->starts;
         $ends=$data2->ends;
         $counts=$data2->counts;
         
         $resultat=[
             'montant_pec'=>$montant_pec,
             'montant_pec_regle'=>$montant_pec_regle,
             'reste_pec_regle'=>$reste_pec_regle,
             'starts'=>$starts,
             'ends'=>$ends,
             'counts'=>$counts
         ]; 
         return response()->json($resultat);
     }
     public function getRelevefacture(Request $req)
     {
        $date_deb=$req->input("date_deb");
        $date_fin=$req->input("date_fin");
        $starts=$req->input("starts");
        $ends=$req->input("ends");
        $code_client=$req->input("code_cli");
     
        $sql1="SELECT NUM_ARRIV,DATE_ARRIV,NUM_FACT,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,
        trim(to_char(MONTANT_PEC,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as pec,
        trim(to_char(MONTANT_PEC_REGLE,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as pec_regle,
        trim(to_char(RESTE_PEC,'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. ''')) as reste_pec,PATIENT 
        FROM (SELECT ROWNUM AS ID,MIANDRALITINA.VIEW_NUM_ARRIV(NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(NUM_FACT) AS DATE_ARRIV,
        NUM_FACT, DATE_EXAMEN,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,PATIENT 
        FROM(SELECT  MIANDRALITINA.VIEW_NUM_ARRIV(NUM_FACT) AS NUM_ARRIV,MIANDRALITINA.VIEW_DATE_ARRIV(NUM_FACT) AS DATE_ARRIV,
        NUM_FACT,trunc(DATE_EXAMEN) as DATE_EXAMEN,MONTANT_PEC,MONTANT_PEC_REGLE,RESTE_PEC,PATIENT 
        FROM MIANDRALITINA.BILLING1 WHERE TYPE_FACTURE<>'Oui' 
        and CODE_CLI='".$code_client."' and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') 
        and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') ORDER BY DATE_ARRIV,NUM_ARRIV,DATE_EXAMEN,substr(NUM_FACT,7,4) ASC)) where ID>='".$starts."' and ID<='".$ends."'";
        $req1=DB::select($sql1); 
    
        return response()->json(['Data'=>$req1]);
     }


     // -------------------------------------------Journal du jour ---------------------------------------//
     public function getJournalJour($date_facture)
    {    
        $sql2="SELECT 
        to_char(reg.date_arriv,'DD/MM/YYYY') as date_arriv,reg.Num_arriv as num_arriv,
        (pat.nom|| ' '||pat.prenom) as nom, pat.type_patient ,pat.sexe as sexe,to_char(pat.datenaiss,'DD/MM/YYYY') as datenaiss,
        CASE
                WHEN reg.VERF_EXAM=0 THEN 'Non effectué'
                WHEN reg.VERF_EXAM=1 THEN 'Non valider'
                ELSE 'Effectué'
        END STATUS_EXAM,
        CASE
                WHEN reg.VERF_FACT=0 THEN 'Non'
                WHEN reg.VERF_FACT=1 THEN 'Oui'
                ELSE 'Oui'
        END STATUS_FACT,
        (SELECT distinct bill.MONTANT_PATIENT   FROM MIANDRALITINA.BILLING1 bill WHERE bill.DATE_ARRIV=reg.DATE_ARRIV AND bill.NUM_ARRIV=reg.NUM_ARRIV ) as totalpat,
        (SELECT distinct bill.MONTANT_PEC   FROM MIANDRALITINA.BILLING1 bill WHERE  bill.DATE_ARRIV=reg.DATE_ARRIV AND bill.NUM_ARRIV=reg.NUM_ARRIV ) as totalpec,
        (SELECT distinct bill.MONTANT_PATIENT_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE  bill.DATE_ARRIV=reg.DATE_ARRIV AND bill.NUM_ARRIV=reg.NUM_ARRIV ) as rpatient,
        (SELECT distinct bill.MONTANT_PEC_REGLE   FROM MIANDRALITINA.BILLING1 bill WHERE  bill.DATE_ARRIV=reg.DATE_ARRIV AND bill.NUM_ARRIV=reg.NUM_ARRIV ) as rclient
        FROM crdtpat.registre reg,CRDTPAT.Patient pat
        WHERE  reg.ID_PATIENT=pat.id_patient and reg.DATE_ARRIV=to_date('".$date_facture."','dd-mm-yyyy') order by reg.NUM_ARRIV ASC";
        $req2=DB::select($sql2); 

        return response()->json(['Data'=>$req2]);
    }


     // -------------------------------------------Examen du jour ---------------------------------------//
     public function getMtExamenJour($date_facture)
     {
        //Fanotanina , ze reglement androan ve no affichena @le examen du jour sa ze examen tandroany,
        //de raha ze regelment androan de iza no raisina  type reglement(esp sa chq) raha samy nanao anio aby androany

         //Espèces , raha atao mitovy @recette du jour
         $sql1ESP="SELECT sum(distinct A.MONTANT) as montant_esp
         FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B , MIANDRALITINA.EXAMEN_DETAILS ex
         WHERE A.NUM_FACT=B.NUM_FACT AND ex.NUM_FACT=A.NUM_FACT
         AND TYPE_FACTURE='0' AND A.REGLEMENT_ID=1 AND to_char(A.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' 
         and to_char(ex.DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."' 
         ";

        //Raha ze examen androan ihny no jerena, wher date_exam
        //  $sql1ESP="SELECT sum(distinct A.MONTANT) as montant_esp
        //  FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B , MIANDRALITINA.EXAMEN_DETAILS ex
        //  WHERE A.NUM_FACT=B.NUM_FACT AND ex.NUM_FACT=A.NUM_FACT
        //  AND TYPE_FACTURE='0' AND A.REGLEMENT_ID=1 AND to_char(A.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' 
        //  (and to_char(ex.DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."' )
        //  ";
 
         //Chèques
         $sql1CH="SELECT sum(distinct A.MONTANT) as montant_chq
         FROM MIANDRALITINA.REGLEMENT_DETAILS A,MIANDRALITINA.FACTURE B , MIANDRALITINA.EXAMEN_DETAILS ex
         WHERE A.NUM_FACT=B.NUM_FACT AND ex.NUM_FACT=A.NUM_FACT
         AND TYPE_FACTURE='0' AND A.REGLEMENT_ID=2 AND to_char(A.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' 
         and to_char(ex.DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."' 
         ";
 
         //Montant
         $sql1Mt="SELECT sum(count(distinct ex.NUM_FACT)) as count
         FROM MIANDRALITINA.EXAMEN_DETAILS ex ,MIANDRALITINA.REGLEMENT_DETAILS regl where  ex.NUM_FACT=regl.NUM_FACT AND
         to_char(regl.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' --(and to_char(ex.DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."') 
         GROUP BY ex.LIB_EXAMEN";
 
         $req1=DB::select($sql1ESP); 
         $req2=DB::select($sql1CH); 
         $req3=DB::select($sql1Mt); 
 
         $data1=array();
         $data2=array();
         $data3=array();
         foreach($req1 as $row){
             $data1=$row;
         }
         foreach($req2 as $row){
             $data2=$row;
         }
         foreach($req3 as $row){
             $data3=$row;
         }
 
         $montant_esp=$data1->montant_esp;
         $montant_chq=$data2->montant_chq;

         $count=$data3->count;
 
        
         $resultat=[
             'montant_chq'=>trim($montant_chq),
             'montant_esp'=>trim($montant_esp),
             'count'=>trim($count)
         ]; 
 
         return response()->json($resultat);
     }
     public function getExamenJour($date_facture)
    {    
        //Fanotanina , ze reglement androan ve no affichena @le examen du jour sa ze examen tandroany,
        //de raha ze regelment androan de iza no raisina  type reglement(esp sa chq) raha samy nanao anio aby androany

        $sql2="SELECT 
        distinct ex.NUM_FACT, to_char(ex.DATE_ARRIV,'DD/MM/YYYY') as DATE_ARRIV,ex.NUM_ARRIV ,  to_char(ex.DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,
        (pat.NOM ||' '||pat.PRENOM) as NOM,
        --Manao select view_reglement zay reglement ny montant max t@niny andro iny,jerena ny reglment ny, mba hialana @le reglement 0 na montant 0
        (SELECT MIANDRALITINA.VIEW_REGLEMENT(rg.REGLEMENT_ID) as REGLEMNT from MIANDRALITINA.REGLEMENT_DETAILS rg 
        WHERE rg.NUM_FACT=regl.NUM_FACT and to_char(rg.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' AND rg.MONTANT=(select max(rg.MONTANT) 
        FROM MIANDRALITINA.REGLEMENT_DETAILS rg where rg.NUM_FACT=regl.NUM_FACT and to_char(rg.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' )) 
        as REGLEMENT,
        --atao ny  somme montant t@date reglement iray
        (select sum(rg.MONTANT)
        from MIANDRALITINA.REGLEMENT_DETAILS rg where rg.NUM_FACT=regl.NUM_FACT and to_char(rg.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' ) as MONTANT_REGL

        FROM MIANDRALITINA.EXAMEN_DETAILS ex ,MIANDRALITINA.REGLEMENT_DETAILS regl,MIANDRALITINA.FACTURE fac,CRDTPAT.REGISTRE reg,CRDTPAT.PATIENT pat
        WHERE  ex.NUM_FACT=regl.NUM_FACT and ex.NUM_FACT=fac.NUM_FACT and  ex.NUM_ARRIV=reg.NUM_ARRIV and ex.DATE_ARRIV=reg.DATE_ARRIV
        and reg.ID_PATIENT=pat.ID_PATIENT 
        and fac.TYPE_FACTURE='0' and to_char(regl.DATE_REGLEMENT,'DD-MM-YYYY')='".$date_facture."' and to_char(ex.DATE_EXAMEN,'DD-MM-YYYY')='".$date_facture."'
        ";
        $req2=DB::select($sql2); 

        for ($i=0; $i < count($req2); $i++) { 
            $sql3="SELECT ex.LIB_EXAMEN,ex.CODE_TARIF from miandralitina.examen_details ex where ex.NUM_ARRIV='".$req2[$i]->num_arriv."'
            and ex.DATE_ARRIV=to_date('".$req2[$i]->date_arriv."','DD/MM/YYYY')";
            $req3=DB::select($sql3); 
            $req2[$i]->examen=$req3;
        }

        return response()->json(['Data'=>$req2]);
    }
}

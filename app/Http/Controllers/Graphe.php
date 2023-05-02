<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class Graphe extends Controller
{
     public function getChartCategorie()
     {
         $sql1="SELECT count(*) as L2,

         (SELECT count(*) FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and TYPE_CLIENT='L1' GROUP BY TYPE_CLIENT) as L1,
         
         (SELECT count(*) FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and TYPE_CLIENT='E' GROUP BY TYPE_CLIENT) as E
         
         FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and TYPE_CLIENT='L2' GROUP BY TYPE_CLIENT";
         $req1=DB::select($sql1); 
 
         $data1=array();
         $chartCategorie=array();
         foreach($req1 as $row){
             $data1=$row;
         }
 
        //  $starts=$data1->starts;
        //  $ends=$data1->ends;
        //  $counts=$data1->counts;
        //  $montant=$data1->montant;
        //  $montant_rglmt=$data1->montant_rglmt;
        $e=$data1->e;
        $l1=$data1->l1;
        $l2=$data1->l2;
        if ($data1->e==null) {
           $e='0';
        }
        if ($data1->l1==null) {
            $l1='0';
        }
        if ($data1->l2==null) {
            $l2='0';
        }
        $chartCategorie=[$e,$l1,$l2];
        $resultat=[
            'categorie'=>$chartCategorie
        ]; 
        return response()->json($resultat);
     }
     public function getRechercheChart($date1,$date2)
     {
        $resultat=array();
         $sql1="SELECT count(*) as L2,

         (SELECT count(*) FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
        DATE_EXAMEN>= to_date('".$date1."','DD/MM/YYYY') AND DATE_EXAMEN<=to_date('".$date2."','DD/MM/YYYY') 
         and TYPE_CLIENT='L1' GROUP BY TYPE_CLIENT) as L1,
         
         (SELECT count(*) FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         DATE_EXAMEN>= to_date('".$date1."','DD/MM/YYYY') AND DATE_EXAMEN<=to_date('".$date2."','DD/MM/YYYY') 
          and TYPE_CLIENT='E' GROUP BY TYPE_CLIENT) as E
         
         FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         DATE_EXAMEN>= to_date('".$date1."','DD/MM/YYYY') AND DATE_EXAMEN<=to_date('".$date2."','DD/MM/YYYY') 
         and TYPE_CLIENT='L2' GROUP BY TYPE_CLIENT";
         $req1=DB::select($sql1); 
 
         $data1=array();
         $chartCategorie=array();
         foreach($req1 as $row){
             $data1=$row;
         }
 
        if ($req1) {    
            $e=$data1->e;
            $l1=$data1->l1;
            $l2=$data1->l2;
            if ($data1->e==null) {
               $e='0';
            }
            if ($data1->l1==null) {
                $l1='0';
            }
            if ($data1->l2==null) {
                $l2='0';
            }
            $chartCategorie=[$e,$l1,$l2];
        }
        $resultat=[
            'categorie'=>$chartCategorie,
            'sq'=>$sql1,
        ]; 
        return response()->json($resultat);
     }
    

     public function getStatAcceuil()
    {
        $resultat=array();

        /**Total Patient*/
        $totalPatient=array();
        $totalPatientActif=array();
        $sqltotalPat="SELECT count(*) as nbenreg FROM crdtpat.PATIENT";
        $sqlTotalPatActif="SELECT count(DISTINCT(trim(upper(PATIENT)))) as nbre from MIANDRALITINA.FACTURE where to_char(DATY,'YYYY')=to_char(sysdate,'YYYY')";

        $req1=DB::select($sqltotalPat);
        $req12=DB::select($sqlTotalPatActif);

        foreach($req1 as $row){
            $totalPatient=$row;
        }
        foreach($totalPatient as $row){
            $totalPatient=$row;
        }
        foreach($req12 as $row){
            $totalPatientActif=$row;
        }
        foreach($totalPatientActif as $row){
            $totalPatientActif=$row;
        }
        /**Total Patient*/

        /**Total Client*/
        $totalClient=array();
        $totalClientActif=array();
        $sqlTotalClient="SELECT count(*) as nbenreg FROM MIANDRALITINA.client";
        $sqlTotalClientActif="SELECT count(DISTINCT(CODE_CLIENT)) as nbre from MIANDRALITINA.FACTURE where to_char(DATY,'YYYY')=to_char(sysdate,'YYYY')";

        $req1=DB::select($sqlTotalClient);
        $req12=DB::select($sqlTotalClientActif);
        foreach($req1 as $row){
            $totalClient=$row;
        }
        foreach($totalClient as $row){
            $totalClient=$row;
        }
        foreach($req12 as $row){
            $sqlTotalClientActif=$row;
        }
        foreach($sqlTotalClientActif as $row){
            $sqlTotalClientActif=$row;
        }
        /**Total Cleint*/

        /**Total Examen*/
        $totalExamen=array();
        $sqltotalExamen="SELECT count(*) as nbenreg FROM miandralitina.EXAMEN";
        $req1=DB::select($sqltotalExamen);
        foreach($req1 as $row){
            $totalExamen=$row;
        }
        foreach($totalExamen as $row){
            $totalExamen=$row;
        }
        /**Total Examen*/


        $resultat=[
            'totalPatient'=>$totalPatient,
            'totalPatientActif'=>$totalPatientActif,
            'totalClient'=>$totalClient,
            'totalClientActif'=>$sqlTotalClientActif,
            'totalExamen'=>$totalExamen,
        ]; 
        return response()->json($resultat);    
    }

    public function getRechercheType()
    {
       $resultat=array();
        $sql1="SELECT count(*) as scann,
        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='RADIOGRAPHIE' GROUP BY TYPE ) as radio,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='ECHOGRAPHIE' GROUP BY TYPE ) as echo,

       

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='PANORAMIQUE DENTAIRE' GROUP BY TYPE ) as panno,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='ECG' GROUP BY TYPE ) as ecg,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='MAMMOGRAPHIE' GROUP BY TYPE ) as mammo,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='AUTRES' GROUP BY TYPE ) as autre


        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and to_char(DATE_EXAMEN,'YYYY')=to_char(sysdate,'yyyy') and trim(TYPE)='SCANNER' GROUP BY TYPE  ";
        $req1=DB::select($sql1); 

        $data1=array();
        $chartCategorie=array();
        foreach($req1 as $row){
            $data1=$row;
        }

       if ($req1) {    
           $radiographie=$data1->radio;
           $autres=$data1->autre;
           $echo=$data1->echo;
           $sca=$data1->scann;
           $pann=$data1->panno;
           $ecg=$data1->ecg;
           $mammo=$data1->mammo;
           if ($data1->radio==null) {
              $radiographie='0';
           }
           if ($data1->autre==null) {
               $autres='0';
           }
           if ($data1->echo==null) {
               $echo='0';
           }
           if ($data1->scann==null) {
               $sca='0';
           }
           if ($data1->panno==null) {
               $pann='0';
           }
           if ($data1->ecg==null) {
               $ecg='0';
           }
           if ($data1->mammo==null) {
               $mammo='0';
           }
           $chartCategorie=[$radiographie,$autres,$echo,$sca,$pann,$ecg,$mammo];
       }
       $resultat=[
           'categorie'=>$chartCategorie,
           'sq'=>$sql1,
       ]; 
       return response()->json($resultat);
    }
    public function getRechercheChartType($date_deb,$date_fin)
    {
       $resultat=array();
        $sql1="SELECT 
        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='RADIOGRAPHIE' GROUP BY TYPE ) as radio,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='ECHOGRAPHIE' GROUP BY TYPE ) as echo,
        
        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='SCANNER' GROUP BY TYPE ) as scann,

  
        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='PANORAMIQUE DENTAIRE' GROUP BY TYPE ) as panno,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='ECG' GROUP BY TYPE ) as ecg,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='MAMMOGRAPHIE' GROUP BY TYPE ) as mammo,

        (SELECT count(*) 
        FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1'   
        and trunc(DATE_EXAMEN)>=to_date('".$date_deb."','dd/mm/yyyy') and trunc(DATE_EXAMEN)<=to_date('".$date_fin."','dd/mm/yyyy') and trim(TYPE)='AUTRES' GROUP BY TYPE ) as autre


        FROM Dual ";
        $req1=DB::select($sql1); 

        $data1=array();
        $chartCategorie=array();
        foreach($req1 as $row){
            $data1=$row;
        }
        if ($req1) {    
            $radiographie=$data1->radio;
            $autres=$data1->autre;
            $echo=$data1->echo;
            $sca=$data1->scann;
            $pann=$data1->panno;
            $ecg=$data1->ecg;
            $mammo=$data1->mammo;
            if ($data1->radio==null) {
               $radiographie='0';
            }
            if ($data1->autre==null) {
                $autres='0';
            }
            if ($data1->echo==null) {
                $echo='0';
            }
            if ($data1->scann==null) {
                $sca='0';
            }
            if ($data1->panno==null) {
                $pann='0';
            }
            if ($data1->ecg==null) {
                $ecg='0';
            }
            if ($data1->mammo==null) {
                $mammo='0';
            }
            $chartCategorie=[$radiographie,$autres,$echo,$sca,$pann,$ecg,$mammo];
        }
        $resultat=[
            'categorie'=>$chartCategorie,
            'sq'=>$sql1,
        ]; 
               return response()->json($resultat);
    }
}

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
         trunc(DATE_EXAMEN)>=trunc(sysdate-30) and TYPE_CLIENT='L1' GROUP BY TYPE_CLIENT) as L1,
         
         (SELECT count(*) FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         trunc(DATE_EXAMEN)>=trunc(sysdate-30) and TYPE_CLIENT='E' GROUP BY TYPE_CLIENT) as E
         
         FROM MIANDRALITINA.EXAMEN_STAT WHERE REJET<>'1' and 
         trunc(DATE_EXAMEN)>=trunc(sysdate-30) and TYPE_CLIENT='L2' GROUP BY TYPE_CLIENT";
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
        $sqltotalPat="SELECT count(*) as nbenreg FROM crdtpat.PATIENT";
        $req1=DB::select($sqltotalPat);
        foreach($req1 as $row){
            $totalPatient=$row;
        }
        foreach($totalPatient as $row){
            $totalPatient=$row;
        }
        /**Total Patient*/

        /**Total Client*/
        $totalClient=array();
        $sqlTotalClient="SELECT count(*) as nbenreg FROM MIANDRALITINA.client";
        $req1=DB::select($sqlTotalClient);
        foreach($req1 as $row){
            $totalClient=$row;
        }
        foreach($totalClient as $row){
            $totalClient=$row;
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
            'totalClient'=>$totalClient,
            'totalExamen'=>$totalExamen,
        ]; 
        return response()->json($resultat);
    }
}

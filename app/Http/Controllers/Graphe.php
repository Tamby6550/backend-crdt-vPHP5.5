<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class Graphe extends Controller
{
     //Facture du jour
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
 
         $chartCategorie=[$data1->e,$data1->l1,$data1->l2];
         $resultat=[
             'categorie'=>$chartCategorie
         ]; 
         return response()->json($resultat);
     }
}

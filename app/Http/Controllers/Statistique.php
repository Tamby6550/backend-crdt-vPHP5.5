<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class Statistique extends Controller
{
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
        foreach($totalPatient as $row){
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
        foreach($totalPatient as $row){
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

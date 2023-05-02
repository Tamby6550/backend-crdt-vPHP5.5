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
        $totalPatientActif=array();
        $sqltotalPat="SELECT count(*) as nbenreg FROM crdtpat.PATIENT";
        $sqlTotalPatActif="SELECT count(DISTINCT(PATIENT)) as nbre from MIANDRALITINA.FACTURE where to_char(DATY,'YYYY')=to_char(sysdate,'YYYY');";

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
        $sqlTotalClientActif="SELECT count(DISTINCT(CODE_CLIENT)) as nbre from MIANDRALITINA.FACTURE where to_char(DATY,'YYYY')=to_char(sysdate,'YYYY');";

        $req1=DB::select($sqlTotalClient);
        $req12=DB::select($sqlTotalClientActif);
        foreach($req1 as $row){
            $totalClient=$row;
        }
        foreach($totalPatient as $row){
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
        foreach($totalPatient as $row){
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
}

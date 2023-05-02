<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Examen extends Controller
{
    public function getAllExamen()
    {
        $resultat=array();
        $data1=array();
        $sqlIdExam="SELECT nvl(max(ID_EXAMEN),0)+1 as nbenreg FROM miandralitina.EXAMEN ";
        $sqlExam="SELECT ID_EXAMEN,nvl(LIBELLE,' ') as LIB,CODE_TARIF,TYPES,MONTANT,TARIF  FROM miandralitina.EXAMEN  order by ID_EXAMEN ASC";
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
            'allexamen'=>$req2
        ]; 
        return response()->json($resultat);
    }
   
    public function insertExamen(Request $req)
    {
        $resultat=array();
        $id_exam = $req->input("id_exam");
        $id_exam1 = $req->input("id_exam1");
        $id_exam2 = $req->input("id_exam2");
        $code_tarif = $req->input("code_tarif");
        $montant_e = $req->input("montant_e");
        $montant_l1 = $req->input("montant_l1");
        $montant_l2 = $req->input("montant_l2");
        $desc = $req->input("lib");
        $type = $req->input("type");
        $tar_e = $req->input("tarif_e");
        $tar_l1 = $req->input("tarif_l1");
        $tar_l2 = $req->input("tarif_l2");
        $donne=[$id_exam,$code_tarif,$desc,$type,$montant_e,$tar_e];
        $donne1=[$id_exam1,$code_tarif,$desc,$type,$montant_l1,$tar_l1];
        $donne2=[$id_exam2,$code_tarif,$desc,$type,$montant_l2,$tar_l2];
        $sqlInsert="INSERT INTO miandralitina.EXAMEN (ID_EXAMEN,CODE_TARIF, LIBELLE,TYPES,MONTANT,TARIF) values (?,trim(upper(?)),trim(upper(?)),trim(upper(?)),trim(?),trim(upper(?)))";
        $requette=DB::insert($sqlInsert,$donne);
        $requette1=DB::insert($sqlInsert,$donne1);
        $requette2=DB::insert($sqlInsert,$donne2);

        if (!is_null($requette)) {
            $resultat=[
                "etat"=>'success',
                "message"=>"Enregistrement éfféctuée",
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
    public function rechercheExamen(Request $req)
    {
        $sql="";
        $desc = $req->input("lib");
        $code_tarif = $req->input("code_tarif");
        $type = $req->input("type");
        $tarif = $req->input("tarif");
        $sql="SELECT ID_EXAMEN,nvl(LIBELLE,'') as LIB,CODE_TARIF,TYPES,MONTANT,TARIF  from miandralitina.EXAMEN WHERE 1=1";

        if ($desc != "") {
            $sql = $sql ." AND trim(upper(LIBELLE)) like trim(upper('%".$desc."%')) ";
        }
        if ($code_tarif != "") {
            $sql = $sql . " AND trim(upper(code_tarif)) like trim(upper('%" . $code_tarif . "%')) ";
        }
        if ($type!= "") {
            $sql = $sql . " AND trim(types)=trim('" . $type . "')";
        }
        if ($tarif!="") {
            $sql = $sql . " AND tarif='".$tarif."' ";
        }
        $sql = $sql ." order by ID_EXAMEN ASC";
        $requette=DB::select($sql);

        return response()->json($requette);
    }
    public function updateExamen(Request $req)
    {
        $resultat=array();
        $id_exam = $req->input("id_examen");
        $code_tarif = $req->input("code_tarif");
        $montant = $req->input("montant");
        $desc = $req->input("lib");
        $type = $req->input("type");
        $tar = $req->input("tarif");

        $donne=[$code_tarif,$desc,$montant,$type,$tar,$id_exam];
        $sql="UPDATE miandralitina.EXAMEN SET CODE_TARIF=trim(upper(?)),LIBELLE=trim(upper(?)),MONTANT=upper(trim(?)),TYPES=trim(upper(?)),TARIF=trim(upper(?)) WHERE ID_EXAMEN=?";
        
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
    public function deleteExamen($id_exam)
    {
        $sql="DELETE FROM miandralitina.EXAMEN WHERE ID_EXAMEN=?";

        $resultat=[];
        $requette=DB::delete($sql, [$id_exam]);
        if (!is_null($requette)) {
            $resultat=[
                "etat"=>'success',
                "message"=>"Suppression éfféctuée",
                'res'=>$requette 
            ];
        }
        return response()->json($resultat);
    }
    public function rechercheExamParTarif($tarif)
    {
        $sql="";
        //substr exemple : substr(L1,1,1)= L
        $sql="SELECT ID_EXAMEN,nvl(LIBELLE,' ') as LIB,CODE_TARIF,TYPES,MONTANT,TARIF    from miandralitina.EXAMEN WHERE TARIF='".$tarif."' order by ID_EXAMEN asc ";
        $requette=DB::select($sql);

        return response()->json($requette);
    }
   
}

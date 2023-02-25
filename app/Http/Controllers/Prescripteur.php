<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Prescripteur extends Controller
{
    public function insertPrescripteur(Request $req)
    {
       $resultat=[];
       $code_presc=$req->input("code_presc"); 
       $titre=$req->input("titre");
       $name=$req->input("nom");
       $phone1=$req->input("phone1");
       $phone2=$req->input("phone2");
       $mobile=$req->input("mobile");
       $adresse=$req->input("adresse");
       $donne=[$code_presc,$titre,$name,$phone1,$phone2,$mobile,$adresse];

       $sql="INSERT INTO crdtpat.PRESCRIPTEUR (CODE_PRESC,TITRE,NOM,PHONE1,PHONE2,MOBILE,ADRESSE) values (trim(?),trim(?),trim(upper(?)),trim(?),trim(?),trim(?),trim(?))";
       $requette=DB::insert($sql, $donne);
       if (!is_null($requette)) {
        $resultat=[
            "etat"=>'success',
            "message"=>"Enregistrement éfféctuée",
            'res'=>$requette 
        ];
       }else{
        $resultat=[
            "etat"=>'warn', 
            "message"=>"Erreur sur l'enregistrement" 
        ];
       }
       return response()->json($resultat);
    }

    public function getPrescripteur()
    {
        $data1=array();
        $sqlIdExam="SELECT nvl(count(CODE_PRESC),0) as nbenreg FROM crdtpat.PRESCRIPTEUR ";
        $sql="SELECT CODE_PRESC,initcap(upper(TITRE||' '||NOM)) as NOM,PHONE1,PHONE2,MOBILE,ADRESSE FROM crdtpat.PRESCRIPTEUR where ROWNUM <= 10 ORDER BY LAST_UPDATE ASC";
        $req1=DB::select($sqlIdExam);
        $requette=DB::select($sql);
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'nbenreg'=>$data1,
            'all'=>$requette
        ]; 
        return response()->json($resultat);
    }

    public function getPrescripteurF()
    {
        $data1=array();
        $sqlIdExam="SELECT nvl(count(CODE_PRESC),0) as nbenreg FROM crdtpat.PRESCRIPTEUR ";
        $sql="SELECT CODE_PRESC,initcap(upper(TITRE||' '||NOM)) as NOM,PHONE1,PHONE2,MOBILE,ADRESSE FROM crdtpat.PRESCRIPTEUR ORDER BY LAST_UPDATE ASC";
        $req1=DB::select($sqlIdExam);
        $requette=DB::select($sql);
        foreach($req1 as $row){
            $data1=$row;
        }
        foreach($data1 as $row){
            $data1=$row;
        }
        
        $resultat=[
            'nbenreg'=>$data1,
            'all'=>$requette
        ]; 
        return response()->json($resultat);
    }
    public function getPrescripteurFact()
    {
        $sql="SELECT CODE_PRESC,initcap(upper(TITRE||' '||NOM)) as NOM,PHONE1,PHONE2,MOBILE,ADRESSE FROM PRESCRIPTEUR ORDER BY CODE_PRESC ASC";
        $requette=DB::select($sql);
        return response()->json($requette);
    }

    public function recherchePrescripteur(Request $req)
    {
       $sql="";
       $code_presc=$req->input("code_presc"); 
       $titre=$req->input("titre");
       $name=$req->input("nom");
       $phone1=$req->input("phone1");
       $phone2=$req->input("phone2");
       $mobile=$req->input("mobile");
       $adresse=$req->input("adresse");

       $sql="SELECT CODE_PRESC,initcap(upper(TITRE||' '||NOM)) as NOM,PHONE1,PHONE2,MOBILE,ADRESSE FROM crdtpat.PRESCRIPTEUR WHERE 1=1";

       if ($code_presc!="")  {$sql=$sql." AND upper(code_presc) like upper('%".$code_presc."%') ";}
       if ($titre!="")       {$sql=$sql." AND titre='".$titre."' ";}
       if ($name!="")        {$sql=$sql." AND upper(nom) like upper('%".$name."%') ";}
       if ($phone1!="")      {$sql=$sql." AND upper(phone1) like upper('%".$phone1."%') ";} 
       if ($phone2!="")      {$sql=$sql." AND upper(phone2) like upper('%".$phone2."%') ";} 
       if ($mobile!="")      {$sql=$sql." AND upper(mobile) like upper('%".$mobile."%') ";} 
       if ($adresse!="")     {$sql=$sql." AND upper(adresse) like upper('%".$adresse."%') ";} 

       $sql=$sql." ORDER BY LAST_UPDATE DESC";
       $requette=DB::select($sql);
       return response()->json($requette);
    }
    public function modifierPrescripteur(Request $req)
    {
        $resultat=[];
       $code_presc=$req->input("code_presc"); 
       $titre=$req->input("titre");
       $name=$req->input("nom");
       $phone1=$req->input("phone1");
       $phone2=$req->input("phone2");
       $mobile=$req->input("mobile");
       $adresse=$req->input("adresse");
    //    $login=$req->input("login");

       $donne=[$code_presc,$titre,$name,$phone1,$phone2,$mobile,$adresse,$code_presc];
       $sql="UPDATE crdtpat.PRESCRIPTEUR SET CODE_PRESC=?,TITRE=?,NOM=trim(?),PHONE1=trim(?),PHONE2=trim(?),MOBILE=trim(?),ADRESSE=trim(?),LAST_UPDATE=sysdate WHERE CODE_PRESC=? ";

       $requette=DB::update($sql, $donne);
       if (!is_null($requette)) {
        $resultat=[
            "etat"=>'success',
            "message"=>"Modification éfféctuée",
            'res'=>$requette 
        ];
       }else{
        $resultat=[
            "success"=>false, 
            "message"=>"Erreur sur la modification" 
        ];
       }
       return response()->json($resultat);
    }
    public function deletePrescripteur($code_presc)
    {
        $sql="DELETE FROM crdtpat.PRESCRIPTEUR WHERE CODE_PRESC=?";

        $resultat=[];
        $requette=DB::delete($sql, [$code_presc]);
        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
                "message"=>"Suppression éfféctuée",
                'res'=>$requette 
            ];
        }
        return response()->json($resultat);
    }
}

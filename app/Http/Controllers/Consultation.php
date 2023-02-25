<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Consultation extends Controller
{
    public function insertConsultation(Request $req)
    {
        $resultat=array();
        $data1=array();
        //format_num fonction sur oracle
        $sqlIdConsul="SELECT crdtpat.format_num(sysdate) as num_consult FROM DUAL";//Maka ny id_consultation
        $req1=DB::select($sqlIdConsul);
        foreach($req1 as $row){
            $data1=$row;
        }

        $num_consult=$data1->num_consult;//id_consuloltation
        $code_examen=$req->input("id_examen");//Id_examen
        $code_presc=$req->input("code_presc");//code_presc
        $numero= $req->input("id_patient"); //Id_patient
        $date_examen= $req->input("date_examen"); //Date_examen
        $num_arriv= $req->input("num_arriv"); //Numéro Régistre
        $login= $req->input("login"); //login

        $donne=[$num_consult,$numero,$code_presc,$code_examen,$num_arriv,$date_examen,$login];
        $sql="INSERT INTO crdtpat.CONSULTATION (ID_CONSULTATION,ID_PATIENT,CODE_PRESC,ID_EXAMEN,DATE_EXAMEN,NUM_ARRIV,LAST_UPDATE,USER_UPDATE) values (?,trim(initcap(?)),trim(initcap(?)),trim(initcap(?)),TO_DATE(?,'dd-mm-yyyy'),trim(initcap(?)),sysdate,? )";
        $requette=DB::insert($sql,$donne);
        
        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
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
    public function ajoutFactureMontant(Request $req)
    {
        $resultat=array();
        $id_consult=$req->input("id_consult");
        $montant=$req->input("montant");
        $tarif=$req->input("tarif");
        $numfact=$req->input("numfact");
        $login= $req->input("login"); 

        $donne=[$montant,$numfact,$tarif,$login,$id_consult];
        $sql="UPDATE crdtpat.CONSULTATION SET MONTANT_FLAG=1,MONTANT=?,NUMFACT=?,TARIF=?,LAST_UPDATE=sysdate ,USER_UPDATE=TRIM(?) WHERE ID_CONSULTATION=? ";

        $requette=DB::update($sql, $donne);
        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
                "message"=>"Modification éfféctuée",
                'res'=>$requette 
            ];
           }
           return response()->json($resultat);
    }
    public function ajoutFichierDoc(Request $req)//path storage/document/id_cons+nom+prenom+extension
    {
        $resultat=array();
        $id_consult=$req->input("id_consult");
        $fichier=$req->file('fichier');
        $noms = strtoupper( $req->input("nom"));
        $prenom = $req->input("prenom");
        $login=$req->input('login');

        if($req->hasFile('fichier')){
            // $nom_fichier=$fichier->getClientOriginalName();Ex: fichier.pdf
            $nom_fichier=$noms.'_'.$prenom;
            $extension_fichier=$fichier->getClientOriginalExtension();//Ex: '.doc'
            $path="storage/document/".$id_consult."".$nom_fichier.".".$extension_fichier;
            if (!(public_path($path))) {
                unlink(public_path($path));//Supprimerna raha oatraka ao
            }
            $stockage=$fichier->move(public_path('/storage/document/'),($id_consult."".$nom_fichier.".".$extension_fichier));//Ajout vao2 @Public/storage/document/...
            $donne=[$path,$login,$id_consult];

            $sql="UPDATE crdtpat.CONSULTATION SET CR_NAME=?,CR_FLAG='1',LAST_UPDATE=sysdate,USER_UPDATE=? WHERE ID_CONSULTATION=? ";
            $requette=DB::update($sql, $donne);
            if (!is_null($requette)) {
                $resultat=[
                    "success"=>true,
                    "message"=>"Modification éfféctuée",
                    'res'=>$requette 
                ];
            } 
          
        }else{
            $resultat=["success"=>false,"fichier"=>null];
        }
        return response()->json($resultat);
    }

    public function deleteConsultation($id_consult)
    {
        $sql="DELETE FROM crdtpat.CONSULTATION WHERE ID_CONSULTATION=? ";

        $resultat=[];
        $requette=DB::delete($sql, [$id_consult]);
        if (!is_null($requette)) {
            $resultat=[
                "success"=>true,
                "message"=>"Suppression éfféctuée",
                'res'=>$requette 
            ];
        }
        return response()->json($resultat);
    }

    public function getConsultation($id_patient)
    {
        $resultat=array();
        $sqlExam="SELECT DECODE(NVL(to_char(NUMFACT),'-'),'-','-',LPAD(NUMFACT,4,'0')) as NUMFACT,ID_CONSULTATION,crdtpat.VIEW_PATIENT(ID_PATIENT) as PATIENT,to_char(DATE_EXAMEN,'DD/MM/YYYY') as DATE_EXAMEN,crdtpat.VIEW_EXAMEN(ID_EXAMEN) as EXAMEN,crdtpat.VIEW_PRESC(CODE_PRESC) as PRESCRIPTEUR,nvl(COMPTE_RENDU,' ') as COMPTE_RENDU,CR_NAME,CR_FLAG,NVL(MONTANT,0) as MONTANT,nvl(TARIF,'') as TARIF FROM crdtpat.CONSULTATION WHERE ID_PATIENT=? order by ID_CONSULTATION DESC";

        $req2=DB::select($sqlExam,[$id_patient]); 
       
        return response()->json($req2);
    }
    public function getRapportExamenDetails($daty)//Par date du jour
    {
        $data1=array();
        $data2=array();
        $data3=array();
        $data4=array();

        //Somme tarif E
        $getSommeTarifE="SELECT NVL(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0) as montant_esp   FROM crdtpat.CONSULTATION WHERE trunc(DATE_EXAMEN)=TO_DATE('".$daty."','dd-mm-yyyy') and TARIF='E' ";
        $req1=DB::select($getSommeTarifE);
        foreach($req1 as $row){
            $data1=$row;
        }
        $sommeTarifE=$data1->montant_esp;//Somme total tarif E ou en Espèce


        //Somme tarif C
        $getSommeTarifC="SELECT NVL(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0) as montant_cheq   FROM crdtpat.CONSULTATION WHERE trunc(DATE_EXAMEN)=TO_DATE('".$daty."','dd-mm-yyyy') and TARIF='C'";
        $req=DB::select($getSommeTarifC);
        foreach($req as $row){
            $data2=$row;
        }
        $sommeTarifC=$data2->montant_cheq;//Somme total tarif C ou Chéque



       //Somme total rehetra miaraka @nombre examen natao
        $getSommeTotalNbExamen="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts,NVL(to_char(sum(MONTANT),'999G999G999G999G999','NLS_NUMERIC_CHARACTERS=''. '''),0) as montant_net FROM (SELECT  ROWNUM AS ID, ID_CONSULTATION,crdtpat.VIEW_PATIENT(ID_PATIENT) as PATIENT,to_char(DATE_EXAMEN,'DD-MM-YYYY') as DATE_EXAMEN,crdtpat.VIEW_EXAMEN(ID_EXAMEN) as EXAMEN,crdtpat.VIEW_PRESC(CODE_PRESC) as PRESCRIPTEUR,nvl(COMPTE_RENDU,' ') as COMPTE_RENDU,MONTANT FROM crdtpat.CONSULTATION WHERE  trunc(DATE_EXAMEN)=TO_DATE('".$daty."','dd-mm-yyyy')  ORDER BY DATE_EXAMEN ASC)";
        $req3=DB::select($getSommeTotalNbExamen);
        foreach($req3 as $row){
            $data3=$row;
        }
        $SommeTotalNbExamen=$data3;

        //Liste avec nom , prenom et details examen 
        $getAllListe="SELECT ID_CONSULTATION,DECODE(NVL(to_char(NUMFACT),'-'),'-','-',LPAD(NUMFACT,4,'0')) as NUMFACT,substr(PATIENT,1,43) as PATIENT,DATE_EXAMEN,EXAMEN,PRESCRIPTEUR,NVL(to_char(MONTANT,'999G999G999G999G999'),0) as MONTANT,TARIF,CODE_TARIF FROM (SELECT  ROWNUM AS ID, ID_CONSULTATION,NUMFACT,crdtpat.VIEW_PATIENT(ID_PATIENT) as PATIENT,to_char(DATE_EXAMEN,'DD-MM-YYYY') as DATE_EXAMEN,crdtpat.VIEW_EXAMEN(ID_EXAMEN) as EXAMEN,crdtpat.VIEW_PRESC(CODE_PRESC) as PRESCRIPTEUR,MONTANT,case when TARIF='E' then 'Espèce' when TARIF='C' then 'Chèque' else  '-' end as TARIF,crdtpat.VIEW_CODETARIF(ID_EXAMEN) as CODE_TARIF FROM crdtpat.CONSULTATION WHERE  trunc(DATE_EXAMEN)=TO_DATE('".$daty."','dd-mm-yyyy')  ORDER BY NUMFACT,ID_CONSULTATION asc ) where ID>='".$SommeTotalNbExamen->starts."' and ID<='".$SommeTotalNbExamen->ends."' ";
        $req4=DB::select($getAllListe);
        $listeRapport=$req4;

        $resul=[
            'espece'=>$sommeTarifE,
            'cheque'=>$sommeTarifC,
            // 'totals'=>$SommeTotalNbExamen->montant_net,
            'montanttotal'=>$SommeTotalNbExamen,
            'liste'=>$listeRapport
        ];

        return response()->json($resul);
    }
    public function getRapportExamenDetailsPatient($id_patient,$date_deb,$date_fin)//Par patient
    {
        $data1=array();
        $data2=array();
        $data3=array();
        //Affiche nombre total examen
        $sql="SELECT min(ID) as starts,max(ID) as ends,count(id) as counts FROM (SELECT  ROWNUM AS ID, ID_CONSULTATION,crdtpat.VIEW_PATIENT(ID_PATIENT) as PATIENT,to_char(DATE_EXAMEN,'DD-MM-YYYY') as DATE_EXAMEN,crdtpat.VIEW_EXAMEN(ID_EXAMEN) as EXAMEN,crdtpat.VIEW_PRESC(CODE_PRESC) as PRESCRIPTEUR,nvl(COMPTE_RENDU,' ') as COMPTE_RENDU FROM crdtpat.CONSULTATION WHERE ID_PATIENT='".$id_patient."' and trunc(DATE_EXAMEN)>=TO_DATE('".$date_deb."','dd-mm-yyyy') and trunc(DATE_EXAMEN)<=TO_DATE('".$date_fin."','dd-mm-yyyy') ORDER BY DATE_EXAMEN ASC)";
        $requette=DB::select($sql);
        foreach($requette as $row){
            $data1=$row;
        }
        $NbtotalExamen=$data1;

        //Liste avec nom , prenom et details patient et examen 
        $getAllListe="SELECT ID_CONSULTATION,PATIENT,DATE_EXAMEN,EXAMEN,PRESCRIPTEUR FROM (SELECT  ROWNUM AS ID, ID_CONSULTATION,crdtpat.VIEW_PATIENT(ID_PATIENT) as PATIENT,to_char(DATE_EXAMEN,'DD-MM-YYYY') as DATE_EXAMEN,crdtpat.VIEW_EXAMEN(ID_EXAMEN) as EXAMEN,crdtpat.VIEW_PRESC(CODE_PRESC) as PRESCRIPTEUR FROM crdtpat.CONSULTATION WHERE ID_PATIENT='".$id_patient."' and trunc(DATE_EXAMEN)>=TO_DATE('".$date_deb."','dd-mm-yyyy') and trunc(DATE_EXAMEN)<=TO_DATE('".$date_fin."','dd-mm-yyyy') ORDER BY ID_CONSULTATION ASC ) where ID>='".$NbtotalExamen->starts."' and ID<='".$NbtotalExamen->counts."'    ";
        $req4=DB::select($getAllListe);
        $listeRapport=$req4;

        $resul=[
            'date'=>"LISTE DES EXAMENS POUR LA PERIODE DU ".$date_deb." AU ".$date_fin,
            'nbexamen'=>$NbtotalExamen,
            'liste'=>$listeRapport,
        ];

        return response()->json($resul);
    }
}

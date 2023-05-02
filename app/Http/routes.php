<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/getTestAPI', function () {
    return view('welcome');
});


//Controlleur TestApi 
//Route::apiResource("Post","TestApi");
Route::post('ajoute','TestApi@ajouter');
Route::get('affiche/{numIm}','TestApi@affiche');
Route::put('modifier/{numIm}','TestApi@modifier');





// Test api
Route::get("getTestAPI","TestAPI@getTestAPI");


//-----------------------------Login------------------------------//
//Login sur crdt
Route::post("login","LoginCrdt@login");
Route::post("changemdp","LoginCrdt@changemdp");
// //
// Route::post("activiteCompte","LoginCrdt@activiteCompteListe");
// //
// Route::get("activite/{nomStructure}","LoginCrdt@activiteListe");
// //
// Route::delete("activite/{nomactivite}","LoginCrdt@activiteDelete");
// //
// Route::put("activite/{nomStructure}/{nomActivite}","LoginCrdt@activiteUpdate");


//-----------------------------Prescripteur------------------------------//
//Insertion prescripteur
Route::post("insertPrescripteur","Prescripteur@insertPrescripteur");
//GetAll
Route::get("getPrescripteur","Prescripteur@getPrescripteur");
Route::get("getPrescripteurF","Prescripteur@getPrescripteurF");
//Recherche
Route::post("recherchePrescripteur","Prescripteur@recherchePrescripteur");
// Suppression
Route::delete("deletePrescripteur/{code_presc}","Prescripteur@deletePrescripteur");
// Modification
Route::put("modifierPrescripteur","Prescripteur@modifierPrescripteur");



//-----------------------------Examen------------------------------//
//Insertion 
Route::post("insertExamen","Examen@insertExamen");
//GetAll
Route::get("getAllExamen","Examen@getAllExamen");
//Recherche
Route::post("rechercheExamen","Examen@rechercheExamen");
// Suppression
Route::delete("deleteExamen/{id_exam}","Examen@deleteExamen");
// Modification
Route::put("updateExamen","Examen@updateExamen");
//Recheche par Tarif 
Route::get("rechercheExamParTarif/{tarif}","Examen@rechercheExamParTarif");


//-----------------------------Patient------------------------------//
//Insertion 
Route::post("insertPatient","Patient@insertPatient");
//GetAll
Route::get("getPatient","Patient@getPatient");
Route::get("affichePatient/{id_patient}","Patient@affichePatient");
//Recherche
Route::post("recherchePatient","Patient@recherchePatient");
// Suppression
Route::delete("deletePatient/{id_patient}","Patient@deletePatient");
// Modification
Route::put("updatePatient","Patient@updatePatient");


//-----------------------------Consultation------------------------------//
//Insertion 
Route::post("insertConsultation","Consultation@insertConsultation");
//GetAll
Route::get("getConsultation/{id_patient}","Consultation@getConsultation");
//Get examen rapport du jour
Route::get("getRapportExamenDetails/{daty}","Consultation@getRapportExamenDetails");
//Get examen rapport par patient
Route::get("getRapportExamenDetailsPatient/{id_patient}&{date_deb}&{date_fin}","Consultation@getRapportExamenDetailsPatient");
// Suppression
Route::delete("deleteConsultation/{id_consult}","Consultation@deleteConsultation");
// Modification facture ou ajout facture
Route::put("ajoutFactureMontant","Consultation@ajoutFactureMontant");
// Modification document ou ajout document
Route::post("ajoutFichierDoc","Consultation@ajoutFichierDoc");


//-----------------------------CRDT Facture Tamby------------------------------//


//----------------------------- Client ------------------------------//
//Insertion 
Route::post("insertClient","ClientFact@insertClient");
//GetAll
Route::get("getClientFact","ClientFact@getClientFact");
Route::get("getClientFactF","ClientFact@getClientFactF");
// Recherche Client Fact 
Route::post("rechercheClientFact","ClientFact@rechercheClientFact");
// Suppression
Route::delete("deleteClientFact/{code_cli}","ClientFact@deleteClientFact");
// Modification facture ou ajout facture
Route::put("updateClientFact","ClientFact@updateClientFact");


//-----------------------------Prescripteur Fact------------------------------//
//Insertion prescripteur
Route::post("insertPrescripteur","Prescripteur@insertPrescripteur");
//GetAll
Route::get("getPrescripteurFact","Prescripteur@getPrescripteurFact");
//Recherche
Route::post("recherchePrescripteur","Prescripteur@recherchePrescripteur");
// Suppression
Route::delete("deletePrescripteur/{code_presc}","Prescripteur@deletePrescripteur");
// Modification
Route::put("modifierPrescripteur","Prescripteur@modifierPrescripteur");



//----------------------------- Reglement Fact ------------------------------//
//Insertion reglement
Route::post("insertReglement","Reglement@insertReglement");
//GetAll
Route::get("getAllReglementFact","Reglement@getAllReglementFact");
//Recherche
Route::post("rechercheReglementFact","Reglement@rechercheReglementFact");
// Modification
Route::put("updateReglementFact","Reglement@updateReglementFact");
// Suppression
Route::delete("deleteReglementFact/{code_presc}","Reglement@deleteReglementFact");
Route::get("rechercheReglementParUser/{indication}","Reglement@rechercheReglementParUser");


//----------------------------- Saisie Reglement Fact ------------------------------//
//GetAll
Route::get("getSaisieReglementFact","SaisieReglement@getSaisieReglementFact");

//Recherche saisie reglement
Route::post("rechercheSaisieReglement","SaisieReglement@rechercheSaisieReglement");
//Affiche details saisie reglement
Route::post("afficheDetailsSaisieReglement","SaisieReglement@afficheDetailsSaisieReglement");
//Insertion details saisie reglement
Route::post("insertReglementDetails","SaisieReglement@insertReglementDetails");
//Affiche list paiment details saisie reglement
Route::post("affichePaimentDetailsReglmnt","SaisieReglement@affichePaimentDetailsReglmnt");



//----------------------------- Information Registre du jour ------------------------------//
//Maka numéro registre
Route::get("getNumArriv","Registre@getNumArriv");

//Insertion dans registre
Route::post("insertRegistre","Registre@insertRegistre");

//Get Listeregistre
Route::get("getListRegistre","Registre@getListRegistre");

// Modification N°Journal
Route::put("updateRegistre","Registre@updateRegistre");

//Recherche
Route::post("rechercheRegistre","Registre@rechercheRegistre");

// Suppression
Route::delete("deleteRegistre/{num_arriv}&{date_arriv}","Registre@deleteRegistre");


//----------------------------- Examen Du Jour ------------------------------//
//Examen non effectuer
Route::get("getExamenNonEff","ExamenDuJour@getExamenNonEff");

//Insertion dans Examens_details
Route::post("insertExamenJour","ExamenDuJour@insertExamenJour");

//Get examen effectuée
Route::get("getExamenEff","ExamenDuJour@getExamenEff");

//Get examen effectuée d'un patient
Route::get("getPatientExamenEff/{num_arriv}&{date_arriv}","ExamenDuJour@getPatientExamenEff");

// Suppression
Route::post("deleteExamenDetails","ExamenDuJour@deleteExamenDetails");

// Validation et enregistrement Compte Rendu
Route::post("updateExamenDetailsCR","ExamenDuJour@updateExamenDetailsCR");
Route::put("validationExamen","ExamenDuJour@validationExamen");


//Get examen effectuée
Route::get("getExamenEffValide","ExamenDuJour@getExamenEffValide");

//Recherche
Route::post("getRehercheExamenEffValide","ExamenDuJour@getRehercheExamenEffValide");


//----------------------------- Facture ------------------------------//

//Get Non Facturé
Route::get("getNonFacture","Facture@getNonFacture");

//Get examen non facture
Route::get("getPatientExamenFacture/{num_arriv}&{date_arriv}","Facture@getPatientExamenFacture");

//Get idFacture
Route::get("getPageFacture/{num_arriv}&{date_arriv}","Facture@getPageFacture");

// Changement Tarif
Route::put("changmentTarif","Facture@changmentTarif");

//Enregistrer
Route::post("insertFacture","Facture@insertFacture");

//Reglement
Route::post("insertReglementFacture","Facture@insertReglementFacture");

//Get  Facturé
Route::get("getEffectFacture","Facture@getEffectFacture");

//Get  Facture réglé
Route::get("getFactureRegler","Facture@getFactureRegler");

//Get  INFO FACTURE PATIENT
Route::get("getInfoPatientFacture/{num_facture}","Facture@getInfoPatientFacture");

//Get  INFO FACTURE REGLEMENT
Route::get("getInfoPatientReglementFacture/{num_facture}","Facture@getInfoPatientReglementFacture");

//Get  LIST REGLEMENTS
Route::get("getListReglementFacture/{num_facture}","Facture@getListReglementFacture");
Route::get("testAPL","Facture@testAPL");

// Modifier reglement
Route::put("modifReglementFacture","Facture@modifReglementFacture");

// Modifier pec remise
Route::put("modifPecRemiseFacture","Facture@modifPecRemiseFacture");

// Modifier retour facture non regle
Route::put("retourFactNonRegleEnNonPaye","Facture@retourFactNonRegleEnNonPaye");

//Recherche Facture non regler
Route::post("getRechercheEffectFacture","Facture@getRechercheEffectFacture");

//Recherche Facture  regler
Route::post("getRechercheFactureRegle","Facture@getRechercheFactureRegle");



//----------------------------- Rapport ------------------------------//
//Facture du jour
Route::get("getMtFacturejour/{date_facture}","Rapport@getMtFacturejour");
Route::get("getFactureJour/{starts}&{ends}&{date_facture}","Rapport@getFactureJour");

//Recette du jour
Route::get("getMtRecettejour/{date_facture}","Rapport@getMtRecettejour");
Route::get("getRecetteJour/{starts}&{ends}&{date_facture}","Rapport@getRecetteJour");

//Virement du jour
Route::get("getMtVirementjour/{date_debut}&{date_fin}","Rapport@getMtVirementjour");
Route::get("getVirementJour/{starts}&{ends}&{date_debut}&{date_fin}","Rapport@getVirementJour");

//Stat examen
Route::post("getStatExamen","Rapport@getStatExamen");

//Stat Client 
Route::post("getMtClientStat","Rapport@getMtClientStat");
Route::post("getClientStat","Rapport@getClientStat");

//Stat détaillés examen
Route::post("getStatDetailleExamen","Rapport@getStatDetailleExamen");

//Prescripteur stat
Route::post("getMtStatPrescripteur","Rapport@getMtStatPrescripteur");
Route::post("getStatPrescripteur","Rapport@getStatPrescripteur");

//Stat Prescripteur
Route::post("getStatCategorie","Rapport@getStatCategorie");

//Stat Cumul chiffre d'affaire
Route::post("getCumulChiffre","Rapport@getCumulChiffre");

//Releve facture
Route::post("getMtReleveFact","Rapport@getMtReleveFact");
Route::post("getRelevefacture","Rapport@getRelevefacture");

//Journal du jour
Route::get("getJournalJour/{date_facture}","Rapport@getJournalJour");

//Examen du jour
Route::get("getMtExamenJour/{date_facture}","Rapport@getMtExamenJour");
Route::get("getExamenJour/{date_facture}","Rapport@getExamenJour");



//Graphe
Route::get("getChartCategorie","Graphe@getChartCategorie");
Route::get("getRechercheChart/{date1}&{date2}","Graphe@getRechercheChart");
Route::get("getRechercheType","Graphe@getRechercheType");
Route::get("getRechercheChartType/{date1}&{date2}","Graphe@getRechercheChartType");
Route::get("getStatAcceuil","Graphe@getStatAcceuil");
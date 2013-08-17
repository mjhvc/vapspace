<?php
 
/**
* Controleur Principal + IndexCtrl.php
*/
define("CONNECT_PROFIL","Mijn VAP profiel");
define("CONNECT_WELKOM","Welkom bezoeker");
define("CONNECT_NUMBER","Uw VAP nummer:  : ");
define("CONNECT_LOGOUT","Logout"); 
define("CONTROL_EMPTY",'Compleet gebrek aan gegevens, vul het formulier in');
define("CONTROL_TOOBIG"," : Te veel karakters in dit veld, de maximale grootte is: ");
define("CONTROL_REGEX","Verboden karakters in dit veld: ");
define("CONTROL_RGX_MAIL","E-Mail: Vul een geldig e-mailadres bvd :mijn.naam@mijn.iap.org");
define("CONTROL_OBLIG","Deze vakken zijn verplicht:  ");
define("CONTROL_ANT_OBLIG",'U moet een bestemmingsantenne kiezen');
define("THIS_EXIST"," Bestaat al! maak een andere keuze.");
define("VAP_TEAM","Het VAP-team");
define("ENT_ANT",'Antenne');
define("ENT_ANTS",'Antennes');
define("ENT_CACHANT",'cache Antenne opladen');
define("ENT_DEL_CONFIRM","Deze verwijdering bevestigen");
define("ENT_CTXT","opladen contexten");
define("ENT_EDIT","Aanpassen");
define("ENT_DEL","Verwijderen");
define("ENT_DELETED"," Verwijderd");
define("ENT_SUBSCRIBED","Ingeschreven");
define("ENT_UNDEFINED","Onbepaald");
define("ENT_STANDBY"," In stand-by");
define("ENT_MAIL"," Mailed!");
define("ENT_DOWNL"," Downloaded");
define("ENT_CACHERROR",'cache mislukt ');
define("ENT_DELERROR",'Kan niet verwijderd worden: ');
define("ENT_MBR","Een Lid");
define("ENT_MBRS","Leden");
define("ENT_MBR_WITHOUT","Een lid zonder e-mail ");
define("ENT_FILES","Documenten");
define("ENT_REGIO",'Een gewest');
define("ENT_REGIOS",'Gewesten');
define("ENT_SOCIETY","Vervoersmaatschappij");
define("ENT_SOCIETIES",'Vervoermaatschapijen');
define("ENT_CHATS_LAST","De meest recente chats ");
define("ENT_LOC",'Een trefpunt');
define("ENT_NEWS",'De nieuwsbrieven');
define("ENT_NEWS_STANDBY","Standby nieuwsbrieven");
define("ENT_CACHET","Aansluitdatum");
define("ENT_INSCRIT","Gebruiker");
define("ENT_ERROR","Er is een fout opgetreden, sorry");
define("WEBMASTER","Webmaster: ");
define("ENT_ZIP","Postcode");
define("PAGI_PREV","Vorige Pagina");
define("PAGI_NEXT","Volgende Pagina");
/**
* Controleur AuthCtrl.php
*/
define("BACK2SITE","Terug naar website");
define("SESSION_ERROR","Uw sessie is afgelopen, gelieve opnieuw in te loggen");
define("UNSUBSCRIBE","U bent correct uitgeschreven uit VAP");
define("AUTH_TITLE_HEAD","Vap-space : naar een nieuwe mobiliteit door gestructureerd liften");
define("AUTH_TITLE_PAGE","VAP: wij willen liften onder buren");
define("AUTH_BACK_ERROR","Identificatiefout, gelieve opnieuw te proberen");
define("AUTH_MAIL_ERROR"," Is geen geldig e-mailadres");
define("AUTH_MAIL_PERMIS"," Heeft geen toegang tot Vap-space");
define("AUTH_HELP","Paswoord vergeten?");
define("AUTH_SUBSCRIBE","Inschrijving");
/**
* Controleur HelpCtrl.php
*/
define("HELP_TITLE_PAGE","Paswoord vergeten?");
define("HELP_TITLE_HEAD","Vap: paswoord help");
define("MAIL_UNKNOWN"," :  Dit e-mailadres is onbekend, sorry");
define("HELP_MAIL_SUJET","Uw idenificatie met VAP\n");
define("HELP_SENT_PASS","Een nieuw paswoord is verzonden naar : ");
define("HELP_ERROR_PASS","Fout bij het versturen van mail, sorry."); 
/**
* ABS_Membre.php
*/ 
define("MBR_ADMIN","Beheer");
define("MBR_SUBSCRIBE","VAP : Inschrijving");
define("MBR_SUB_NEXT","Volgende stap");
define("MBR_SPACE"," VAP-SPACE ");
define("MBR_ANTISPAM","Verkeerde anti-spam, u hebt een verkeerd getal opgegeven <br /> aandacht, het verbindingsteken meetellen");
define("MBR_NO_ACTION",'Verkeerde actie');
define("MBR_NO_RESULT",'Er zijn geen resultaten');
define("MBR_NOMAIL","Geen E-Mail");
define("UNI_NOMAIL","PasDeMail");
define("MBR_NO","geen");
define("MBR_LAND","België");
define("MBR_NOCONNU","Onbekend");
define("MBR_CONSULT","KIJKEN");
define("MBR_MAIL_SUJET",'Uw VAP inschrijving');
define("MBR_MAIL_RESP_SUJET",'Een nieuwe VAP inschrijving');
define("MBR_WELKOM","Uw registratie in VAP werd aanvaard ,<br />bedankt en welkom op de VAP-Space, een publiek en gratis tool voor het uitwisselen van mobiliteitsnieuws tussen  leden .");
define("MBR_ROUTE_SUBSC","Trajekt opgeslagen, dank u");
define("MBR_UPDATE","Uw gegevens worden bijgewerkt, dank u");
define("CHAT_ALL","Alle chats");
define("CHAT_MONTH","Chats van de maand ");
define("MBR_TRIPS","Mijn trajecten");
define("MBR_NOTRIPS"," : De trajektenbibliotheek is leeg. <br />Een nieuwe trajekt inschrijven zal interactie via e-mail met andere VAP-leden bevorderen.");
define("MBR_CHATS","Mijn VAP-chats");
define("MBR_NOCHATS"," : Nog geen chats ");
define("MAIL_CONTACT_MBR","Een e-mail is verzonden, hij vermeldt het e-mailadres van de contactpersoon, dank u.");
define("MAIL_PROMO_SUJET"," beveelt u Vriendelijk Anders Pendelen (VAP) aan");
define("MAIL_PROMO_ERROR"," Geen e-mailadres opgegeven  ");
define("MAIL_PROMO_OK"," Bericht goed verzonden, dank u !");
define("DATE_ERROR","AUB, een aannemelijke geboortedatum opgeven");
define("DATE_FORMAT","Datumweergave, vul in met volgende formaat: jjjj-mm-dd  bvd: 2010-09-30");
define("PASS_CONFIRM","Paswoord en bevestiging zijn verschillend, voer in beide velden een identiek paswoord");
define("PASS_SUBSCRIBE",'Kies een passwoord dat bestaat uit 12 tekens : hoofdletters of kleine letters of cijfers');
define("PASS_UPDATE",'U kunt hier een nieuw paswoord invullen (12 tekens :  hoofdletters, kleine letters, cijfers), dit vervangt het oude');
define("SUBMIT_SUBSCRIBE",' Inschrijving bevestigen');
define("SUBMIT_UPDATE",' Bijwerken data');
define("INTRO_SUBSCRIBE",'Gelieve het formulier hieronder in te vullen. <br /> Uw antenneverantwoordelijke zal u daarna zo spoedig mogelijk uw VAP-kit bezorgen.');
define("INTRO_INSTALL","Om de installatie te eindigen, is uw inschrijving noodzakelijk");
define("INTRO_UPDATE",'  Dit zijn uw VAP gegevens.');
define("INTRO_ONE","Eerste pagina");
define("INTRO_UP_REG","Van provincie veranderen? ");
define("INTRO_REG","Selecteer uw provincie.<br />Uw provincie wordt niet vermeld ? Selecteer:'Andere'");
define("SANSMAILS_ALERT",' Let op : wanneer men inschrijft zonder een e-mailadres op te geven, zal alle online interactie langs VAP-space onmogelijk zijn. ');
define("NIHIL",'nul');
/**
* Controleur AntenneCtrl.php
*/
define("ANT_TIT","VAP : Gewest ");
define("ANT_S_TIT",'VAP-Gewest ');
define("ANT_BAD",'verkeerde antenne');
/**
* Controleur ChatCtrl.php
*/
define("CHAT_TIT","VAP_CHAT ");
/**
* Controleur ChatCtrl.php
*/
define("FILE_TIT",'VAP-FILES');
define("FILE_LS",'Lijst van VAP documenten');
define("FILE_DOWN_ERR",'Probleem met downloaden');
define("FILE_DEL",'File verwijderd');
define("FILE_DEL_ERROR",'Onmogelijk deze file te verwijderen');
define("FILE_CSV_DESC",'iso-8859-15,komma, aanhalingsteken');
define("FILE_CSV_TIT",'csv file leden van :');
/**
* Controleur SponsorCtrl.php
*/
define("SPONS_TIT",'VAP : openbaar vervoer');
/**
* controleur NewsCtrl.php
*/
define("NEWS_TIT",'VAP - Newsletter');
define("NEWS_ALL",'Alle leden');
define("NEWS_ERROR_MAIL",'DE nieuwsbrief kon niet verstuurd worden naar : ');
define("NEWS_ERROR_SUBJECT",'De fouten bij het sturen van het Vap-news van: ');
define("NEWS_NOSUB",'Testfase: niet uitschrijven');
define("NEWS_ONSUB",'Ik wil me uitschrijven');
define("NEWS_NOVUE",'Testfase: geen online nieuwsbrief');
define("NEWS_VUE",'Naar nieuwsbrief online kijken');
define("NEWS_FETCH_NOVUE", "Geen standby news nu");
define("NEWS_FETCH_INTRO","Alleen geregistreerde nieuwsbrieven kunnen bewerkt worden");
define("NEWS_WORK",'De nieuwsmolen is bezet door een nieuwsbrief van : ');
define("NEWS_SEND",'Nieuws uitgezonden naar : ');
define("NEWS_NO_SEND","De verzending is mislukt, sorry");
define("NEWS_SENDER_ONLY","Alleen de auteur van een nieuws of een beheerder kan een nieuwsbrief uitzenden / stoppen");
define("NEWS_ALREADY_SEND",'Nieuwsbrief is al verzonden en niet meer bewerkbaar');
define("NEWS_ONSUB_CONFIRM","Ja, ik bevestig mijn afmelden voor de nieuwsbrief");
define("NEWS_ONSUB_OK","Afmelding effectief");
 
/**
* controleur IterCtrl.php
*/
define("ITER_TIT",'VAP-ITER: ');
define("ITER_MEET",'Trefpunten ');
define("ITER_MEET_ANT",'Trefpunten in : ');
define("ITER_STDT_MEET","Ik kies een  reeds gebruikt trefpunt :"); 
define("ITER_NEW_MEET","In mijn antenne, stel ik een nieuw trefpunt voor");
define("ITER_ALLREADY",'Dit trefpunt is al aanwezig in de lijst');
define("ITER_AUTO_DEAL","Ik sta ingeschreven als automobilist, dit trajekt is een aanbod voor voetgangers"); 
define("ITER_PEDESTRIAN_DEAL","Ik sta ingeschreven als voetganger, dit trajekt is een vraag  aan automobilisten");
define("ITER_IN_ERROR",'Fout bij het inschrijven van trefpunt, sorry');
define("ITER_OUTANT","Het startpunt van een trajekt moet binnen de eigen antenne liggen");
define("ITER_NO_CHOICE","Kies een trefpunt,aub");
define("ITER_OFFER",'Biedt aan');
define("ITER_DEMAND",'Vraagt');
define("ITER_CONTACT", 'VAP: een ander lid wenst u te contacteren.');

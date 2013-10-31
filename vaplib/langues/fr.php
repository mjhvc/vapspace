<?php
 
/**
* Controleur Principal
*/
define("CONNECT_PROFIL","Mon profil VAP");
define("CONNECT_WELKOM","Bonjour, visit(eur|euse)");
define("CONNECT_NUMBER","Membre VAP n� : ");
define("CONNECT_LOGOUT","D�connexion"); 
define("CONTROL_EMPTY",'Absence totale de donn�es, veuillez remplir le formulaire svp.');
define("CONTROL_TOOBIG"," : ce formulaire d�borde, sa taille maximale est de : "); 
define("CONTROL_REGEX","Caract�res interdits dans ce formulaire: ");
define("CONTROL_RGX_MAIL","E-Mail: Veuillez rentrer une adresse courriel valide. <br />Ex:mon.nom_prenom@mon.fai.org");
define("CONTROL_OBLIG","Ces formulaires sont obligatoires :  ");
define("CONTROL_ANT_OBLIG",'Vous devez choisir une antenne de destination');
define("THIS_EXIST"," existe d�j�! faites un autre choix.");
define("PASS_EXIST"," Le mot de passe existe d�j� pour ce e-mail, fa�tes un autre choix.");
define("VAP_TEAM","l'�quipe des VAP");
define("ENT_ANT",'Une Antenne');
define("ENT_ANTS",'Les Antennes');
define("ENT_CACHANT",'recharger cache Antenne');
define("ENT_DEL_CONFIRM","Confirmer cette suppression");
define("ENT_CTXT","recharger les contextes");
define("ENT_EDIT","�diter");
define("ENT_DEL","Supprimer");
define("ENT_DELETED"," supprim�(e)");
define("ENT_SUBSCRIBED","inscrit(e)");
define("ENT_UNDEFINED","Ind�finie");
define("ENT_STANDBY"," enregistr�e et en attente");
define("ENT_MAIL"," post�e !");
define("ENT_DOWNL"," t�l�charg� !");
define("ENT_CACHERROR",'echec creation cache: ');
define("ENT_MBR","Un Membre");
define("ENT_MBRS","Les membres");
define("ENT_MBR_WITHOUT","Un Membre Sans Mail");
define("ENT_FILES","Les fichiers");
define("ENT_LOC",'Point de rencontre');
define("ENT_REGIO",'Une R�gion');
define("ENT_REGIOS",'Les Regions');
define("ENT_SOCIETY",'Soci�t� de Transports ');
define("ENT_SOCIETIES",'Soci�t�s de Transports ');
define("ENT_STATS_REG",'Stats par R�gion');
define("ENT_STATS_GLOB","Globalit� du site");
define("ENT_CHATS_LAST","Les derniers Chats");
define("ENT_NEWS",'Une newsletter');
define("ENT_NEWS_STANDBY","Les News en attente");
define("ENT_DELERROR",'Impossible de supprimer: ');
define("ENT_CACHET","Date");
define("ENT_INSCRIT","Usager");
define("ENT_ERROR","Une erreur s'est produite, d�sol�");
define("WEBMASTER","Webmaster: ");
define("ENT_ZIP","Zip");
define("PAGI_PREV","Page pr�c�dente");
define("PAGI_NEXT","Page suivante");
/**
* Controleur AuthCtrl.php
*/
define("BACK2SITE","Retour au Site");
define("UNSUBSCRIBE","Votre d�sinscription du r�seau VAP est compl�te");
define("SESSION_ERROR"," Votre session n'est plus valide, merci de vous reconnecter");
define("AUTH_BACK_ERROR","Erreur d'identification, veuillez re-essayer");
define("AUTH_TITLE_HEAD","vapspace une mobilit� de proximit� par un auto-stop organis�");
define("AUTH_TITLE_PAGE","VAP: organisons un Auto-Stop de proximit� ");
define("AUTH_MAIL_ERROR"," n'est pas un courriel valide");
define("AUTH_MAIL_PERMIS"," ne peut acc�der au vapspace!");
define("AUTH_HELP","Pass oubli�?");
define("AUTH_SUBSCRIBE","Inscription");
/**
* Controleur HelpCtrl.php
*/
define("HELP_TITLE_PAGE","Mot de passe oubli�?");
define("HELP_TITLE_HEAD","Vap: soutien mot de passe");
define("MAIL_UNKNOWN"," : ce mail est inconnu aux VAP, d�sol�.");
define("VAP_UNKNOWN"," Le Pr�nom, le Nom ou le Mail ne correspondent pas � un compte Vap, d�sol�. ");
define("HELP_MAIL_VAP","vap: demande un nouveau mot de passe" );
define("HELP_MAIL_SUJET","Votre identification aux VAP\n");
define("HELP_SENT_PASS","Un nouveau mot de passe  a �t� envoy� � : ");
define("HELP_ERROR_PASS","Erreur � l'envoi de mail, d�sol�. ");
define("HELP_IMP_PASS","Hum, votre mail est connu mais plusieurs comptes y sont reli�s. Dans ce cas, un(e) administrateur est contact� pour v�rification. Merci de patienter."); 
/**
* ABS_Membre.php + MembreUNCtrl.php + MembreDeuxCtrl.php
*/ 
define("MBR_ADMIN","Gestion");
define("MBR_SUBSCRIBE","VAP  Inscription");
define("MBR_SUB_NEXT","Poursuivre");
define("MBR_SPACE"," VAP-SPACE ");
define("MBR_ANTISPAM","anti-spam incorrect, le nombre fran�ais fourni pour vap-vap est inexact<br /> Attention, le tiret compte !");
define("MBR_NO_ACTION",'action incorrecte');
define("MBR_NO_RESULT",'Pas de r�sultats');
define("MBR_NOMAIL","PasDeMail");
define("UNI_NOMAIL","PasDeMail");
define("MBR_NO","non");
define("MBR_LAND","Belgique");
define("MBR_NOCONNU","Inconnu");
define("MBR_CONSULT","CONSULTER");
define("MBR_MAIL_SUJET",'Votre inscription aux VAP');
define("MBR_MAIL_RESP_SUJET",'Nouvelle inscription aux VAP');
define("MBR_WELKOM","Votre inscription aux VAP est enregistr�e,<br /> merci et bienvenu(e) dans l\'espace membre, un outil libre et public destin� � �changer des infos de mobilit� entre membres.");
define("MBR_ROUTE_SUBSC","Trajet enregistr�, merci");
define("MBR_UPDATE","Vos donn�es sont � jour, merci");
define("MBR_PIEDS","Pi�tons");
define("MBR_AUTOS","Automobilistes");
define("MBR_DEUX","Deux usages");
define("MBR_FEM","Membres_F�minins");
define("MBR_MAL","Membres_Masculins");
define("MBR_GLB_REG","Membres");
define("MBR_GLB_GLB","Global");
define("MBR_GLB_MOB","Voyagent_Avec");
define("CHAT_ALL","Tout le chat ");
define("CHAT_MONTH","Chat mensuel ");
define("MBR_TRIPS","Mes trajets");
define("MBR_NOTRIPS"," : la biblioth�que des trajets est vide. <br />Inscrire un nouveau trajet permettra une interaction par e-mail avec d'autres membres VAP.");
define("MBR_CHATS","Mes VAP-chats");
define("MBR_NOCHATS"," : pas de chats post�s ");
define("MAIL_CONTACT_MBR","Un courrier vous a �t� envoy�, il contient le mail de votre contact, merci.");
define("MAIL_PROMO_SUJET"," vous recommande les Voitures A Plusieurs");
define("MAIL_PROMO_ERROR"," absence de mails? ");
define("MAIL_PROMO_OK","Message bien envoy�, merci!");
define("DATE_ERROR","Veuillez entrer une date de naissance plausible");
define("DATE_FORMAT","formulaire cachet, le format est: aaaa-mm-jj  ex: 2010-09-30");
define("PASS_CONFIRM","Le mot de passe et sa confirmation diff�rent, veuillez rentrer un mot de passe identique dans les deux champs");
define("PASS_SUBSCRIBE",'Choisir un mot de passe <strong>personnel</strong>form� de 12 caract�res maximum parmis : Majuscules,minuscules,chiffres');
define("PASS_UPDATE",'En rentrant ici un mot de passe  <strong>personnel</strong>, vous modifiez celui enregistr�');
define("SUBMIT_SUBSCRIBE",'Inscription');
define("SUBMIT_UPDATE",' Mise � jour ');
define("INTRO_SUBSCRIBE",'Veuillez remplir le formulaire ci-dessous. <br />Le responsable de votre antenne vous fera parvenir votre kit dans les meilleurs d�lais.');
define("INTRO_INSTALL","Afin de compl�ter l'installation, votre inscription est n�cessaire");
define("INTRO_UPDATE",' voici vos donn�es VAP connues.');
define("INTRO_ONE","Page Un");
define("INTRO_UP_REG","Changer votre r�gion actuelle ?");
define("INTRO_REG"," Votre province est dans la liste? Cochez-l�.<br /> Votre province est absente de cette liste?  Cochez: 'Autre'");
define("SANSMAILS_ALERT",'Inscrire un membres sans mail empechera toute interaction en ligne de ce membre dans le Vapspace');
define("NIHIL",'n�ant');
define("MBR_FEMI","Madame");
define("MBR_MASC","Monsieur");
/**
* Controleur AntenneCtrl.php et RegionCtrl.php
*/
define("ANT_TIT","VAP : R�gion ");
define("ANT_S_TIT",'VAP-Regions ');
define("ANT_BAD",'Antenne incorrecte');
/**
* Controleur ChatCtrl.php
*/
define("CHAT_TIT","VAP_CHAT ");
/**
* Controleur FileCtrl.php
*/
define("FILE_TIT",'VAP-FICHIERS');
define("FILE_LS",'Liste de documents VAP');
define("FILE_DOWN_ERR",'Erreur lors du telechargement');
define("FILE_DEL",'fichier supprim�');
define("FILE_DEL_ERROR",'�chec suppression de fichier');
define("FILE_CSV_DESC",'iso-8859-15,virgule,guillemet');
define("FILE_CSV_TIT",'fichier csv des membres de :');
/**
* Controleur SponsorCtrl.php
*/
define("SPONS_TIT",'VAP : transports publics');
define("SPONS_AVEC","Voyagent_Avec_abonnement");
define("SPONS_SANS","Voyagent_Sans_abonnement");
/**
* controleur NewsCtrl.php
*/
define("NEWS_TIT",'VAP - Newsletter');
define("NEWS_ALL",'Tous les membres');
define("NEWS_ERROR_MAIL",'Vapnews ne sait envoyer de mails �: ');
define("NEWS_ERROR_SUBJECT",'Les erreur � l\'envoi Vap-news de: ');
define("NEWS_NOSUB",'Phase test: pas de d�sinscription');
define("NEWS_ONSUB",'Je souhaite me d�sabonner');
define("NEWS_NOVUE",'phase test: pas de newsletter en ligne');
define("NEWS_VUE",'Voir la newsletter sur notre site');
define("NEWS_FETCH_NOVUE", "Pas de news en attente pour l'instant");
define("NEWS_FETCH_INTRO","Seules les newsletters en attente de diffusion sont �ditables ici");
define("NEWS_WORK",'Le moulin � News est occup� par : ');
define("NEWS_SEND",'news envoy�e � : ');
define("NEWS_NO_SEND","Erreur � l'envoi de la news");
define("NEWS_SENDER_ONLY","Seuls le/la commanditaire de la news ou un(e) administrateur peuvent diffuser/stopper une newsletter");
define("NEWS_ALREADY_SEND",'newsletter d�j� envoy�e et non �ditable');
define("NEWS_ONSUB_CONFIRM","Oui, je confirme ma d�sinscription � votre newsletter");
define("NEWS_ONSUB_OK","D�sinscription effective");
 
/**
* controleur IterCtrl.php
*/
define("ITER_TIT",'VAP-ITER: ');
define("ITER_MEET",'Points de rencontre: ');
define("ITER_MEET_ANT",'Points de rencontre par antenne : ');
define("ITER_STDT_MEET","Je choisis un point de rencontre habituellement utilis� :"); 
define("ITER_NEW_MEET","Je propose un nouveau point de rencontre dans ma commune:") ;
define("ITER_ALLREADY",'Ce point de rencontre est d�j� pr�sent dans la liste');
define("ITER_AUTO_DEAL","Je suis inscrit comme automobiliste, le deal du trajet est <strong>offre<strong> aux membres pi�tons");
define("ITER_PEDESTRIAN_DEAL","Je suis inscrit comme <strong>pi�ton</strong>, le deal du trajet est </strong>une demande</strong> aux membres automobilistes");
define("ITER_IN_ERROR",'Erreur insertion du lieu de passage');
define("ITER_OUTANT","L'inscription d'un trajet se r�alise  uniquement dans votre vapspace");
define("ITER_NO_CHOICE","Veuillez choisir un point de rencontre");
define("ITER_OFFER",'Offre');
define("ITER_DEMAND",'Demande');
define("ITER_CONTACT", 'VAP: un membre VAP vous contacte');

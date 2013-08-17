<?php
class MailLang
{ 
  static function frHelpCtrl($prenom,$nom,$newPasse,$mailvap)
  {
    $text = "Bonjour ".$prenom." ".$nom.", \n"
          ." Voici un nouveau mot de passe : ". $newPasse." \n"			    
          ." qui, associé à votre courriel  ".$mailvap." \n"
			    ." vous identifiera sur notre espace membre: ".BASEURL."index.php"."\n"
          ." \n"
          ." Vous pouvez ensuite changer ce mot de passe:\n" 
          ." - en allant dans votre profil VAP,\n"  
          ." - en y encodant un mot de passe choisi par vous  (avec confirmation)\n" 
          ." - et en validant la mise à jour.\n"
          ." \n"   
			    ." Merci, l'équipe des VAP \n";
    return $text;
  }
  static function frHelpImp($mail)
  {
    $text = "Bonjour, \n"
            . $mail." souhaite obtenir un nouveau mot de passe \n"
            ." Comme plusieurs comptes possèdent un même mail, nom et prénom, \n"
            ." L'application ne sait distinguer automatiquement quel compte modifier. \n"
            ." Merci de contacter: ".$mail." afin de lui demander son compte exact et lui envoyer un mot de passe unique.";
    return $text;
  }
  static function nlHelpImp($mail)
  {
    $text =  $mail."  wenst een nieuw paswoord te bekomen. \n"
            ." Aangezien verschillende accounts dezelfde naam, voornaam en e-mailadres hebben, \n"
            ." kan het systeem niet zien welke account gewijzigd moet worden.   \n"
            ." Gelieve dit e-mailadres te contacteren om te vragen over welke account het gaat en deze een nieuw persoonlijk paswoord te bezorgen.";
    return $text;
  }
  
  static function nlHelpCtrl($prenom,$nom,$newPasse,$mailvap)
  {
    $text = "Dag ".$prenom." ".$nom.", \n"
            ." Hier komt uw nieuw paswoord :". $newPasse." \n"	
            ." dat u, in combinatie met uw e-emailadres :".$mailvap." \n"
            ." met succes op vapSpace: ".BASEURL."index.php"."\n"
            ." zal identificeren.\n\n"
            ." Binnen vapSpace kunt u dit paswoord wijzingen door een aanpassing van uw vap-profiel.\n\n"
            ." Bedankt, het Vap team";
    return $text;    
  }
  static function frIterContact($iterNom,$iterMbr,$iterMail,$clickNom,$clickPre)
  {
    $text = 'Bonjour '.$clickPre.' '.$clickNom.", \n"
          ."Suite à votre intérêt pour le trajet proposé par Mme/M: ".$iterNom.", membre du réseau VAP n°: ".$iterMbr." \n"
          ."et, en accord avec M/Mme".$iterNom.", nous vous communiquons son adresse mail pour partager certains trajets. \n"
          ."  \n"          
          .$iterMail. " \n"
          ."  \n"
          ."Merci de votre intérêt pour le réseau VAP.\n"
          ."Bien à vous. \n"          
          ."<a href=\"http://www.vap-vap.be\">L'équipe des VAP, pour une mobilité partagée.</a> \n"
          ."  \n";
    return $text; 
  }
  static function nlIterContact($iterNom,$iterMbr,$iterMail,$clickNom,$clickPre)
  {
    $text = 'Dag '.$clickPre.' '.$clickNom.", \n"
      ."Naar aanleiding van uw interesse in de voorgestelde wijze door mevrouw / M: ".$iterNom.", lid van de VAP n°: ".$iterMbr." \n"
      ."en in overleg met dhr/mvr ".$iterNom." geven wij u zijn e-mailadres om een aantal routes te delen. \n"
       ."  \n"          
       .$iterMail. " \n"
       ."  \n"
       ."Dank u voor uw interesse in het netwerk VAP.\n"         
       ."<a href=\"http://www.vap-vap.be/?lang=nl\">Het VAP team, voor een gedeelde mobiliteit. \n"
       ."  \n";
    return $text; 
  }
  static function textIterContact($iterNom,$iterMbr,$iterMail,$clickNom,$clickPre,$lang)
  {
    if ($lang == 'fr') { $text = MailLang::frIterContact($iterNom,$iterMbr,$iterMail,$clickNom,$clickPre); }
    else { $text = MailLang::nlIterContact($iterNom,$iterMbr,$iterMail,$clickNom,$clickPre); }
    return $text; 
  }

  static function textHelpCtrl($prenom,$nom,$newPasse,$mailvap,$lang)
  {
    if ($lang == 'fr') { $text = MailLang::frHelpCtrl($prenom,$nom,$newPasse,$mailvap); }
    else  { $text = MailLang::nlHelpCtrl($prenom,$nom,$newPasse,$mailvap); }
    return $text;
  }
  static function textHelpImp($mail,$lang)
  {
    if ($lang == 'fr') { $text = MailLang::frHelpImp($mail); }
    else  { $text = MailLang::nlHelpImp($mail); }
    return $text;
  }
  static function nlTextMailChat()
  {
    $test = "VAPChat:"
    .$postpseudo ."heeft geantwoord op uw vapchat gepost op BASEURL..."
     ."De titel van uw chat was : ".$pstsujet
    ."Het begin van uw bericht luidde als volgt : ".$pstcontenu
    ."Hier volgt het antwoord van : ".$pstpseudo
    ."Zijn e-mailadres is : ".$pstmail
    ."Indien u beslist verder te e-mailen met deze persoon, gelieve hem aan te duiden als enige bestemmeling teneinde zijn e-mailadres niet te verspreiden."
    ."U bent echter ook vrij de discussie verder te voeren op onze chat-space door in te loggen op BASEURL..."
    ."Het VAP team, voor een gedeelde mobiliteit";
  
  }
}

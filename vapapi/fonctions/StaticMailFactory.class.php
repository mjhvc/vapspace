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
          ." - en encodant un mot de passe choisi par vous  (avec confirmation)\n" 
          ." - et en validant la mise à jour.\n"
          ." \n"   
			    ." Merci, l'équipe des VAP \n";
    return $text;
  }
  
  static function nlHelpCtrl($prenom,$nom,$newPasse,$mailvap)
  {
    $text = "Dag ".$prenom." ".$nom.", \n"
            ." Hier komt uw nieuw passwoord :". $newPasse." \n"	
            ." die, met uw e-emailadres :".$mailvap." \n"
            ." op vapSpace: ".BASEURL."index.php"."\n"
            ." met succes zal u identificeren.\n\n"
            ." Op het vapSpace, met een verandering in uw Vap profiel\n"
            ." kan uw dit passwoord wijzingen.\n\n"
            ." Bedankt, de Vap team".
    return $text;    
  }
  
   static function textHelpCtrl($prenom,$nom,$newPasse,$mailvap,$lang)
  {
    if ($lang=='fr') { $text = MailLang::frHelpCtrl($prenom,$nom,$newPasse,$mailvap); }
    else  { $text = MailLang::nlHelpCtrl($prenom,$nom,$newPasse,$mailvap); }
    return $text;
  }
}

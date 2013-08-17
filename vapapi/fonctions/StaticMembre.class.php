<?php
class StaticMembre
{
  static function textToMail($tableau,$lang)
  {
    if ($lang=='fr') { $text = StaticMembre::frInsToMbr($tableau); }
    else  { $text = StaticMembre::nlInsToMbr($tableau); }
    return $text;
  }
  static function textToResp($nom,$prenom,$antenne,$lang)
  {
    if ($lang=='fr') { $text = StaticMembre::frToResp($nom,$prenom,$antenne); }
    else  { $text = StaticMembre::nlToResp($nom,$prenom,$antenne); }
    return $text;
  } 
  static function textPromo($nom,$prenom,$mailAmi,$lang,$msgAmi=NULL)
  {
    if ($lang=='fr') { $text = StaticMembre::frTextPromo($nom,$prenom,$mailAmi,$msgAmi); }
    else  { $text = StaticMembre::nlTextPromo($nom,$prenom,$mailAmi,$msgAmi); }
    return $text;
  } 
  static function sujeToMail($lang)
  {
    if ($lang == 'fr') { $sujet = 'Votre inscription aux VAP'; }
    else { $sujet = 'Uw VAP inschrijving'; }
    return $sujet;
  }
  static function sujeToResp($lang)
  {
    if ($lang == 'fr') { $sujet = 'Nouvelle inscription aux VAP' ; }
    else { $sujet =  'Een nieuwe VAP inschrijving' ; }
    return $sujet;
  }

  //calcul la valeur d'un nombre $nbr exposé à puissance $puissance
  static function expose($nbr,$puissance)
  {
    if ($puissance == 0){ $total = 1 ;}
    else {
      for ($total = $nbr; $puissance > 1 ; $puissance--){  
        $total = $total * $nbr; 
      }
    }
    return ($total); 
  } 
  //extrait le chiffre décimal d'une chaine (numero de membre)
  static function classmbr($chaine)
  { 
    $pos = (strlen($chaine)-1);
    $long = strlen($chaine);
    $nombre = 0; 
    while ($pos >= 0 ) {
      if (preg_match('#^[0-9]$#',$chaine[$pos],$match)) {
        $puissance =  ($long - $pos - 1);
        $deci = StaticMembre::expose(10,$puissance);
        $int = intval($match[0]);
        $nombre = $nombre + ($int * $deci);
      }
    $pos = $pos - 1;
    }
    return $nombre;
  } 
  /**
* l'usine à fabriquer les bonnes conditions de vue du contexte
*/
  static function sqlMbr($condition=array(),$queue=NULL,$liaison=NULL)
  {    
    if (!empty($liaison)) {    
      $where = T_CORE.".idvap = ".T_HUM.".idhum AND ".T_HUM_SOC.".lienhum = ".T_HUM.".idhum";
    } 
    else {  $where = T_CORE.".idvap = ".T_HUM.".idhum"; } 
    $supplement = "";    
    $order = "";      
    if (!empty($condition)) {  
      $conj = " AND ";      
      foreach ($condition as $cle=>$expression) {
        $supplement .= $conj.$expression;
      }
    }
    if (!empty($queue)) { $order .= $queue; } 
    $where .= $supplement.$order;
    return $where;
  }
  
  static function frInsToMbr($tableau)
  {
    if ($tableau['inscrit'] == 'pieton') { $vignette = ' -votre carte de membre et des cartons de directions.'; }
    elseif($tableau['inscrit'] == 'auto')	 { $vignette = '-votre carte de membre et une vignette VAP à coller sur le pare-brise avant coté passager'; }
    elseif($tableau['inscrit'] == 'deux')	 { 
      $vignette = '-deux cartes de membres à votre nom, une vignette VAP à coller sur le pare-brise et des cartons de directions pour le stop'; 
    } 
	  $introt = "Merci pour votre inscription aux VAP"."\n"
			." Vous recevrez prochainement votre kit du vappeur qui contient: \n"
      ." $vignette \n"
		  ." \n ";	 	 
    $datapersot = 'Rappel de vos données principales VAP: '."\n"
			.' Nom : '.$tableau['nom'].'<strong> Prénom :'.$tableau['prenom']."\n"
			.' Courriel : '.$tableau['mail']."\n"
			.' Mot de passe : '.$tableau['passe']."\n";
    $datavapt = ' Mon Antenne Vap : '.$tableau['nomantenne']."\n"
			.' Mon utilisation des VAP:(piéton-auto-les deux) : '.$tableau['inscrit']."\n";
		$conclut = "Rendez-vous sur l'espace membre ".BASEURL." qui vous permettra de: \n"
			." - modifier toutes vos données personnelles,  \n"
      ." - imprimer des cartes VAP  avec vos destinations, \n"
      ." - inscrire vos trajets réguliers ou non dans la bibliothèque des trajets de votre antenne, \n"
      ." - poster un commentaire sur votre vap-chat, \n"
      ." - voir qui est membre dans votre antenne. \n"
      ." \n"
      ." À vous rencontrer bientot sur la route? \n"
      ." L'équipe des VAP: pour une mobilité partagée. \n";
    $contenut = $introt.$datapersot.$datavapt.$conclut;		
    return $contenut;
  }	

  static function nlInsToMbr($tableau)
  {
    if ($tableau['inscrit'] == 'pieton') { $vignette = ' -uw lidkaart met pictogrammen.'; $use = 'voetganger' ;}
    elseif($tableau['inscrit'] == 'auto')	 { $vignette = '-uw lidkaart met een VAP-zelfklever in de rechterbovenhoek van de voorruit te plaatsen.'; $use = 'automobilist'; }
    elseif($tableau['inscrit'] == 'deux')	 { 
      $vignette = '- twee lidkaarten, pictogrammen en een VAP-zelfklever in de rechterbovenhoek van de voorruit te plaatsen'; 
      $use = 'automobilist en voetganger '; 
    } 
	  $introt = "Bedankt voor uw inschrijving met de VAP "."\n"
			." U ontvangt binnenkort uw VAP-Kit \n"
      ." $vignette \n"
		  ." \n ";	 	 
    $datapersot = 'Herinnering van uw belangrijkste VAP gegevens: '."\n"
			.' Naam : '.$tableau['nom'].'<strong> Prénom :'.$tableau['prenom']."\n"
			.' E-mailadres : '.$tableau['mail']."\n"
			.' Paswoord : '.$tableau['passe']."\n";
    $datavapt = ' Mijn Vap antenne : '.$tableau['nomantenne']."\n"
			.' Met de VAP, verplaats ik mij als : '.$use."\n";
		$conclut = "Bezoek het lid zone ".BASEURL." die uw zal: \n"
			." - uw persoonlijk gegevens veranderen.  \n"
      ." - persoonlik VAP pictogrammen in te drukken, \n"
      ." - uw woon-werkverkeer of niet in de bibliotheekpaden van uw antenne inschrijven, \n"
      ." - een commentaar op de VAP-Chat plaatsen, \n"
      ." - met anderen leden van uw antenne contact. \n"
      ." \n"
      ."  Met u, op het weg over te steken? \n"
      ." Het VAP team : voor een gedeelde mobiliteit. \n";
    $contenut = $introt.$datapersot.$datavapt.$conclut;		
    return $contenut;
  }	  
 
  static function frToResp($nom,$prenom,$antenne)
  {   
    $contenutext = 'Bonjour, '."\n"
			.$prenom.' '.$nom.' s\'est inscrit(e) aux  VAP de '.$antenne."\n"
			.' Afin de lui envoyer sa carte de membre, merci de vous identifier sur : '."\n"
			. BASEURL."\n"
			." Une fois identifié(e), cliquer en base de page sur: \n"
			." Gestion > Gérer les Membres > lister les nouveaux membres \n"
			." Dans la liste des membres, cliquer ensuite sur 'modifier membre' \n"
      ." vous accéderez à la page des données du membre \n"
			." et pourrez lui attribuer un véritable numéro de membre \n"
			." Merci et bien à vous \n";
    return $contenutext;
  }
  static function nlToResp($nom,$prenom,$antenne)
  {   
    $contenutext = 'Hello, '."\n"
			.$prenom.' '.$nom.' VAP : '.$antenne." heeft zich geregistreerd \n"
			.' Om hem/haar zijn lidkaart door te sturen, dank u voor het identificeren van : '."\n"
			. BASEURL."\n"
			." Toen het login gedaan is, rechterbenedenhoek, clicken op : \n"
			." Beheer > Beheeren-Opvragen  : de leden > lijst van nieuwe members \n"
			." Op dit lijst, zoeken naar het lijn van  $nom $prenom daarna op 'te wijzigen' clicken \n"
      ." nu kunt u een nieuwe lid nummer voor $prenom $nom geven. \n"
			." Bedankt \n";
    return $contenutext;
  }
  
  
  static function frTextPromo($nom,$prenom,$mailAmi,$msgAmi=NULL)
  {
    $titre = " Madame, Monsieur, \n \n";
    $intro = $prenom." ".$nom." s'est inscrit(e) au réseau VAP : \n".
    "L'autostop entre voisins pour des trajets courts, variés, en complément des transports publics!". 
    "Il/Elle vous conseille de vous rendre sur le site : \n".
    " http://www.vap-vap.be \n".
    "Pour découvrir cette  initiative conviviale.\n". 
    "L'inscription au réseau et la pratique des VAP sont gratuites.\n".
    "Plus il y aura de membres, plus les déplacements seront faciles pour chacun!\n\n";
    if (!empty($msgAmi)) { $message = $mailAmi." vous a laissé ce message : \n".$msgAmi." \n"; }
    else { $message = "Répondre à :  ".$mailAmi." ? \n"; }
    $conclu = "Pour toute question ou remarque, n'hésitez pas à nous contacter \n".
    "à l'adresse : info@vap-vap.be \n\n".
    "Peut-être nous rejoindrez-vous bientôt ? \n". 
    "Bien à vous,\n".
    "L'équipe des VAP.\n";
    $texte = $titre.$intro.$message.$conclu;
    return $texte;    
  } 
  static function nlTextPromo($nom,$prenom,$mailAmi,$msgAmi=NULL)
  {
    $titre = " Mevrouw, Mijnheer, \n \n";
    $intro = $prenom." ".$nom."  heeft zich bij het VAP-netwerk aangesloten : \n".
    "Liften onder buren voor korte trips, als aanvulling op het openbaar vervoer!". 
    "Hij / zij adviseert u een bezoek aan de site: \n".
    " http://www.vap-vap.be \n".
    "Om dit sympathiek initiatief te ontdekken.\n". 
    " Aansluiten bij het VAP-netwerk is gratis.\n".
    "Hoe meer leden, hoe gemakkelijker het wordt  voor iedereen!\n\n";
    if (!empty($msgAmi)) { $message = $mailAmi." heeft voor u dit bericht achtergelaten : \n".$msgAmi." \n"; }
    else { $message = "Gelieven te antwoorden :  ".$mailAmi." ? \n"; }
    $conclu = "Voor vragen of opmerkingen, kunt u ons contacteren op : info@vap-vap.be \n\n".
    "Tot binnenkort misschien ? \n". 
    "Het VAP-Team\n";
    $texte = $titre.$intro.$message.$conclu;
    return $texte;    
  } 
} 	



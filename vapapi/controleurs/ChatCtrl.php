<?php

/******************************************************************************
*    vapspace est un logiciel libre : vous pouvez le redistribuer ou le       *
*    modifier selon les termes de la GNU General Public Licence tels que      *
*    publiés par la Free Software Foundation : à votre choix, soit la         *
*    version 3 de la licence, soit une version ultérieure quelle qu'elle      *
*    soit.
*
*    vapspace est distribué dans l'espoir qu'il sera utile, mais SANS AUCUNE  *
*    GARANTIE ; sans même la garantie implicite de QUALITÉ MARCHANDE ou       *
*    D'ADÉQUATION À UNE UTILISATION PARTICULIÈRE. Pour plus de détails,       *
*    reportez-vous à la GNU General Public License.                           *
*
*    Vous devez avoir reçu une copie de la GNU General Public License         *
*    avec vapspace. Si ce n'est pas le cas, consultez                         *
*    <http://www.gnu.org/licenses/>                                           *
******************************************************************************** 
*/

/**
* Class ChatCtrl, la classe controleur du Contexte Chat.ini table:T_CHAT
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include("Controleur.class.php");
include("VapCal.class.php");

class ChatCtrl extends Controleur
{
  protected $champPassif;
  private $contexte;
  private $idxPassif;
  private $statut;
  private $droits;
  private $action;
  private $table;
  private $tabTables = array();
  private $tabAntennes = array();
  private $tabindex;
  private $urlMois;
  private $valppk;
  private $idpere;
  private $sujet;
  private $urlPage;
/**
* methode fille __construct()  necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appelle  parent:: __construct($contexte) qui charge: statut,modele et vue selon session et contexte 
* valeursPassive(1) 1 est l'idx de la valeur 'passive' à selectionner dans la ligne [passive][champs][1]
* valeursPassive retourne toujours le champ [passive][champ][0] comme clé de la ligne
 */
  public function __construct()
  {
    $this->contexte = "Chat";
    parent::__construct($this->contexte);
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();     
    $this->tabTables = $this->modele->getTables();
    $this->table = $this->tabTables[0];
    $this->action = '';
    $this->tabindex = array(); 
    $this->tabAntenne = $this->valeursPassive(1); 
    $this->champPassif = 'lienant';
    $this->idxPassif = 1; 
    $this->urlPage = BASEURL."index.php?ctrl=chat&amp;action=voirMens&amp;token=".$this->token."&amp;chatmois=";
    $this->urlPagination = BASEURL."index.php?ctrl=chat&amp;action=voir&amp;token=".$this->token."&amp;page=";
    $this->valppk = '';  
    $this->idpere = 0 ; 
    $this->sujet = '';     
  }
  public function __destruct()
  { unset($_SESSION['stampCalende']); }

/**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_admin est dans pied.tpl
  */ 
  private function globalEntities($lienant=NULL)
  {
    if (!empty($lienant) && array_key_exists($lienant,$this->tabAntenne)) { $antenne = $this->tabAntenne[$lienant]; }
    else {$antenne = $_SESSION['nomantenne']; }
    $this->vue->titre_head = CHAT_TIT;
    $this->vue->titre_page = CHAT_TIT.$antenne;
    $this->vue->vapspace = $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token);   
       
    if (! ($this->droits & RESPONSABLE)){   
      $this->vue->index_admin = ''; 
    }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token;
      $this->vue->admin =  MBR_ADMIN;   
    }      
    return true;
  }
  /**
  * constructEntity cote enfant: on appelle parent:: 
  * on récupère les variables du contexte enfant pour la vue
  */  
  protected function constructEntity($tableau=array(),$table=NULL,$flgStat=NULL)
  {  
    try { 
      parent::constructEntity($tableau,$table,$flgStat);    
      foreach ($this->valueForm as $name=>$value) { $this->vue->$name = $value; }
      foreach ($this->sizeForm as $size=>$value) {  $this->vue->$size = $value; }
      foreach ($this->obligForm as $obliga=>$aster) {   $this->vue->$obliga = $aster;  }
      return true;  
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }  
  /**
  * function lastStamp
  * fourni le cachet (stamp) du dernier message posté soit:
  * @param int $lienant
  * @return array  
  */  
  private function lastStamp($lienant=NULL)
  {
    $stamp = array();    
    $dbh = $this->modele->getCnx();
    if (!empty($lienant)){ $sql = "SELECT MAX(poste) AS max,MIN(poste) as min FROM ".T_CHAT." WHERE lienant=:lienant"; }
    else { $sql = "SELECT MAX(poste) AS max,MIN(poste) AS min FROM ".T_CHAT; }
    $stmt = $dbh->prepare($sql);
    if (!empty($lienant)){ $stmt->execute(array(":lienant"=>$lienant)); }
    else { $stmt->execute(); }
    $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($rslt)){ 
      $stamp['old'] = strtotime($rslt['min']); 
      $stamp['new'] = strtotime($rslt['max']);
    }
    else { $stamp = NULL; }
    return $stamp;
  }
  /**
  * sqlMensuel
  * calcul sql des chats mensuels
  * @param string date $lim, $calende et $butoir 
  * @param int $lienant
  * @return array
  */
  private function sqlMensuel($calende,$butoir,$lienant=NULL,$lim=NULL)
  {
    $rslt = array(); $limite = $butoir.'%';   
    if (!empty($lienant)){    
      if (!empty($lim)) { $sql = "SELECT * FROM ".T_CHAT." WHERE lienant = :lienant AND poste LIKE '$limite' ";  }
      else { $sql = "SELECT * FROM ".T_CHAT." WHERE lienant = :lienant AND poste BETWEEN '$calende' AND '$butoir'  ORDER BY poste DESC "; }   
    }
    else { 
      if (!empty($lim)) { $sql = "SELECT * FROM ".T_CHAT." WHERE lienant = :lienant AND poste LIKE '$limite' ";  }
       else { $sql = "SELECT * FROM ".T_CHAT." WHERE poste BETWEEN '$calende' AND '$butoir'  ORDER BY poste DESC "; }
    } 
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    if (!empty($lienant)) { $stmt->bindParam(':lienant',$lienant,PDO::PARAM_INT); } 
    $stmt->execute();
    $rslt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rslt;
  }
 
  /**
  * supprime les fichiers php du cache de l'antennne fournie en argument
  */  
  private function viderUnCache($idant)
  {
    $ok = $this->viderCache($idant);
    return $ok;
  }
  /**
  * Recrée le répertoire des caches d'antennes supprimés.
  * function qui crée  un repertoire par antenne dans tmpvap/antenne 
  */  
  private function creerCacheAntenne()		
  {
	  $ok = $this->creerCache($this->tabAntennes);
    return $ok;
  }
  /**
  * fonction de surcharge du class Controleur, Ecris le fichier de cache $lienant.'_chat.csv'   
  */  
  protected function ecrisCache($lienant,$masq,$ligne='',$nom='antennes')
  {
    $ok = NULL;  $tabExtract = array();  
    $colonnes = array('idchat','sujet');  
    $condi = " ORDER by poste DESC LIMIT 0,5 ";
    $tabExtract = $this->modele->ligneParValeurs($colonnes,$this->table,"lienant",$lienant,'all',$condi);
    foreach($tabExtract as $key=>$tableau) {
      $ligne .= $tableau['idchat'].',"'.$tableau['sujet'].'"'."\n";
    }
    $ok = parent::ecrisCache($lienant,$masq,$ligne);
    return $ok;
  }
  protected function controler($tableau)
  {
    try {
      $ramasseError = array();    
      $hidden = array('chatpost','chatprevue');     
      //faire d'abord les filtres génériques du controleur principal
      $this->vue->retour = parent::controler($tableau);
      if (empty($this->vue->retour)){                  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){  
          $ramasseError[] = CONTROL_OBLIG.$testOblig;
        }
        if ($this->action == 'insert') {        
          if ($postCorrupt = $this->filtre->postInattendu($tableau,$hidden)){  
            throw new MyPhpException('formulaire du contexte:'.$this->contexte.' corrompu');
          }
        }
        if (! empty($ramasseError[0])){ // preparation de la chaine de sortie des erreurs : 
          $this->vue->retour = '<span style="color:red">'.$ramasseError[0].'</span>'; 
          $ramasseError = array();
        }      
      }
      return $this->vue->retour;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  } 
  /*
  * les entités et leur substitution pour voir un chat
  * cette methode fait la substitution des entités de la vue d'un message
  */
  private function voirUnMessage($rslt=array())
  {
    $format = 'd/m/Y-H:i';    
    $timestamp = strtotime($rslt['poste']);			
    $this->vue->tampon = date($format,$timestamp);
    $this->vue->pseudo = $rslt['pseudo'];
    $this->vue->vapattache = $rslt['vapattache'];
    $sujetcode = urlencode($rslt['sujet']);
		$this->vue->sujet = nl2br($rslt['sujet']);
		$this->vue->message = nl2br($rslt['message']);
		switch ($rslt['vaputile'])
		{
			case 'deux' :	$this->vue->vaputile = '<img src="'.BASEURL.'photos/deux.png" alt="pieton-automobiliste" />';break;
			case 'pieton':	$this->vue->vaputile =	'<img src="'.BASEURL.'photos/pieton3.png" alt="piéton" />';break;
			case 'auto':	$this->vue->vaputile = '<img src="'.BASEURL.'photos/auto3.png" alt="automobiliste" />';break;
		}
		$this->vue->urlreponse = BASEURL.'index.php?ctrl=chat&amp;action=poster&amp;chatidant='.$rslt['lienant'].'&amp;idchat='.$rslt['idchat'].'&amp;sujet='.$sujetcode ;		
		if (($this->droits & RESPONSABLE))
		{	$this->vue->urlsuppress = '<a class="queueright" href="'.BASEURL.'index.php?ctrl=chat&amp;action=supprimer&amp;idchat='.$rslt['idchat'].'">'.ENT_DEL.'</a>' ; }
		else {	$this->vue->urlsuppress = '';}
    return true;
  }
  //Le formulaire pour poster, previsualiser,modifier un chat
  private function vueChat($action,$lienant,$tableau=array())
  {
    $out = '';
    $this->action = $action;    
    $this->globalEntities($lienant);
    if ($this->action == 'insert'){ 
      if (empty($tableau)) { $this->constructEntity(); }
      else { $this->constructEntity($tableau); }
    }
    $this->vue->pseudo = $_SESSION['prenom'].' '.$_SESSION['nom'];
    $this->vue->lienhum = $_SESSION['idhum'];
    $this->vue->lienant = $lienant;
    if (empty($tableau['idpere'])) { $this->vue->idpere = $this->idpere; }
    else { $this->vue->idpere = $tableau['idpere']; }
    $this->vue->vapattache = $_SESSION['nomantenne'];
    $this->vue->bis_vaputile = $_SESSION['inscrit'];
    $this->vue->chatmail = $_SESSION['mail'];
    $this->vue->script_formu = BASEURL."index.php?ctrl=chat&amp;action=poster&amp;chatidant=".$lienant."&amp;token=".$this->token;       
    if ($this->action == 'chatprevue') {
      $format = 'd/m/Y-H:i'; 
      $this->vue->tampon = date($format);
      switch ($tableau['vaputile'])
		  {
			  case 'deux' :	$this->vue->vaputile = '<img src="'.BASEURL.'photos/deux.png" alt="pieton-automobiliste" />';break;
			  case 'pieton':	$this->vue->vaputile =	'<img src="'.BASEURL.'photos/pieton3.png" alt="piéton" />';break;
			  case 'auto':	$this->vue->vaputile = '<img src="'.BASEURL.'photos/auto3.png" alt="automobiliste" />';break;
		  }
      $this->vue->sujet = htmlspecialchars($tableau['sujet'],ENT_QUOTES,"ISO-8859-1");
      $messageClean = htmlspecialchars($tableau['message'],ENT_QUOTES,"ISO-8859-1");
      $this->vue->message = nl2br($messageClean);
      $this->vue->setFile('contenu','prevision_Chat.tpl');
    }
    elseif ($this->action == 'insert'){ 
      $this->vue->antenne = $this->tabAntenne[$lienant];
      $this->vue->chatAntenne = BASEURL."index.php?ctrl=chat&amp;action=voirMens&amp;lienant=".$lienant."&amp;token=".$this->token;
      if (!empty($this->sujet)){ $this->vue->sujet = $this->sujet; }
       $this->vue->urlOffre =  BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=offre&amp;antiter=".$lienant."&amp;token=".$this->token;
      $this->vue->urlDemande = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=demande&amp;antiter=".$lienant."&amp;token=".$this->token;
      $this->vue->urlInsItTer = BASEURL."index.php?ctrl=iter&amp;action=inscrire&amp;iterant=".$_SESSION['idant']."&amp;token=".$this->token;
      $this->vue->setFile('contenu','inscrire_Chat.tpl');
    }
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;        
  }
  /*  
  * calcule recursivement l'ensemble des chats d'une antenne
  * @param: idpere, integer, la cle du chat pere ou 0 si c'est un chat orphelin
  * @param: lienant, integer, la cle de l'antenne du chat
  * retourne la valeur  du noeud 'pere' (appelé en premier) rempli des noeuds fils
  */
  private function afficheRecur($idpere,$lienant)
  {
    try {      
      $sql = "SELECT * FROM ".T_CHAT." WHERE lienant = :lienant AND idpere = :idpere ORDER BY poste DESC";
      $nomGroupe = "groupe".$idpere;
      $this->vue->setVar($nomGroupe,"");  //creation d'un noeud pere
      $dbh = $this->modele->getCnx();
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':lienant',$lienant,PDO::PARAM_INT); 
      $stmt->bindParam(':idpere',$idpere,PDO::PARAM_INT);
      $stmt->execute();
      while ($rslt = $stmt->fetch(PDO::FETCH_ASSOC)){      
        $this->vue->reponses = $this->afficheRecur($rslt['idchat'],$lienant);          
        $this->voirUnMessage($rslt);              
        $this->vue->append($nomGroupe,'chattin');    //rajoute l'entité 'chatmess' substituée à l'entité nomgroupe                  
              
      }
      return $this->vue->getVar($nomGroupe);  //retourne la valeur du noeud pere complet
    }
     catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
  /**
  * la methode de vision 'classique' des chats d'un membre pour editer et supprimer ses propres chats
  */  
  public function voir()
  {
    try {
      $noligne = 0;
      $this->globalEntities();  
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }
      elseif ( ! (filter_has_var(INPUT_GET,'chathum') && ($lienhum = filter_input(INPUT_GET,'chathum',FILTER_VALIDATE_INT)) && ($ok=$this->filtre->getOld('idvap','lienperso',$lienhum,T_CORE)))) {
        throw new MyPhpException('contexte Chat/voir : cle invalide'); 
      }
      elseif ($_SESSION['idhum'] != $lienhum) { $this->vue->retour = MBR_NO_ACTION; }
      else {
        $condi = $this->table.".lienhum = ".$lienhum." ORDER BY poste DESC ";
        $sql = $this->sqlFactory($condi,$condi);
        $nbrPages = $this->comptePages($sql['pagin']);
        if (isset($_GET['page'])) {
          $pageActuelle = intval($_GET['page']);
          if ($pageActuelle > $nbrPages) {
            $pageActuelle = $nbrPages;
          }
        } 
        else { $pageActuelle = 1 ; }     
        $dataPagin = $this->pagination($nbrPages,$pageActuelle,$this->urlPagination,$sql['stdt']);
        $fsql = $dataPagin['sql'];
        $this->vue->pagination = $dataPagin['pages'] ;
        $dbh = $this->modele->getCnx(); 
        $stmt = $dbh->prepare($fsql);
        $stmt->execute();
        $compteur = $stmt->rowCount();
        //sortir le resultat dans la vue      
        if ($compteur == 0){ $this->vue->retour =  MBR_NO_RESULT; }
        else {
          $this->vue->setFile("contenu","voir_Chat_Perso.tpl");
          $this->vue->setBlock("contenu","meschats","chat");
          while ($rslt = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $classe = "A".(($noligne++)%2);
            $ppk = $rslt['idchat'];
            $this->vue->cssline = $classe;
            $this->vue->urlchatedit = BASEURL."index.php?ctrl=chat&amp;action=editer&amp;token=".$token."&amp;chatppk=".$ppk."&amp;mode=edit";
            $this->vue->urldelchat = BASEURL."index.php?ctrl=chat&amp;action=supprimer&amp;token=".$token."&amp;idchat=".$ppk;
            $this->vue->sujet = $rslt['sujet'];
            $this->vue->message = $rslt['message'];
            $tstamp = strtotime($rslt['poste']);
            $this->vue->poste = strftime('%A %d %B %Y',$tstamp);
            $this->vue->append("chat","meschats");
          }
        } 
      }      
      $this->vue->setFile("pied","pied.tpl"); 
      $page = $this->vue->render("page");
      $this->output($page);  
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }
  }          
  /*
  * gestion de la vision arborescente par:
  * gestion d'un cache, appel de afficheRecur()
  */
  public function voirGlob()
  {
    try {    
      $token = $_GET['token'];
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
        exit();
      }
      else {       
        //filtrer la clé de l'antenne
        if ( filter_has_var(INPUT_GET,'chatidant')) { $lienant = filter_input(INPUT_GET,'chatidant',FILTER_VALIDATE_INT); }
        elseif ( filter_has_var(INPUT_POST,'lienant')) { $lienant = filter_input(INPUT_POST,'lienant',FILTER_VALIDATE_INT); }
	      else { throw new MyPhpException("Contexte Chat, lienant obligatoire"); }
        if (! array_key_exists($lienant,$this->tabAntenne)) { throw new MyPhpException("Contexte Chat, lienant n'existe pas"); }
        
        $fileCache = DIRCACHANT.$lienant.'/'.$lienant.'_chat.csv';
        if (! file_exists($fileCache)) { $this->ecrisCache($lienant,'_chat.csv'); }
        //obtenir les stamp premiers et derniers, la page de cache     
        $cachet = $this->lastStamp($lienant);    
        $this->stampIni = $cachet['old'];
        $this->stampUlt = $cachet['new'];
        $pathcache = DIRCACHANT.$lienant.'/' ; 
        //le fichier de cache, diffère selon le statut.   
		    if ( $this->statut == 'membre') {	
		  	  $page_cache = strval($lienant).'_mbr.php';
		  	  $page_protect_mbr = 'protect_'.$page_cache;
		  	  $fichier_cache_mbr  = $pathcache.$page_cache ;	
		  	  $fichier_protect_mbr = $pathcache.$page_protect_mbr;	
		  	  if (!file_exists($fichier_protect_mbr)) {	touch($fichier_protect_mbr);}	
		    }
		    else {	
		  	  $page_cache = strval($lienant).'_resp.php';
		  	  $page_protect_resp = 'protect_'.$page_cache;
		  	  $fichier_cache_resp  = $pathcache.$page_cache ;
		  	  $fichier_protect_resp = $pathcache.$page_protect_resp;
		  	  if (!file_exists($fichier_protect_resp)) {	touch($fichier_protect_resp);}	
		    }		
		    //Choix du bon cache selon le statut
		    if ($this->statut == 'membre') {	
          $fichier_cache = $fichier_cache_mbr; 
          $fichier_protect = $fichier_protect_mbr;
        }
		    else {	
          $fichier_cache = $fichier_cache_resp; 
          $fichier_protect = $fichier_protect_resp; 
        }
        $this->globalEntities($lienant); 
		    //si cache, on le lit sinon, on le crée et affiche le résultat	du calcul
        // l'appel récursif ne fournit que l'entité 'contenu' (pas de token dans le cache)
		    if ( file_exists($fichier_cache) && filemtime($fichier_cache) > $this->stampUlt ){	       
          $buffer= file_get_contents($fichier_cache);          
          $this->vue->contenu = $buffer;
        }							 
		    else {	
          $this->vue->setFile('contenu','voir_Chat_R.tpl');
          $this->vue->setBlock('contenu','chattin','messages');
          $this->vue->reponses = '';	                               	
		  	  $vp = fopen($fichier_protect,'rb');						            //ouverture du fichier de protection  
		  	  flock($vp,LOCK_EX);													              //verrou exclusif sur fichier de protection				
		  	  $fp = fopen($fichier_cache,'wb');							            //ouverture du fichier, le crée s'il n'existe pas		
		  	  ob_start();																                //ouverture du tampon
		  	  $this->vue->messages = $this->afficheRecur(0,$lienant);          
		  	  echo $this->vue->messages;								                  //mise en tampon de la page
		  	  $arbre = ob_get_contents();											          //$page reçoit le tampon
		  	  ob_end_clean();														                //fermeture du tamp
		  	  file_put_contents($fichier_cache,$arbre);					        //ecriture du fichier de cache
		  	  fclose($fp);															                //fermeture du fichier
		  	  flock($vp,LOCK_UN);													              //déverrouillage du fichier de protection
		  	  fclose($vp);															                //fermeture du fichier de protection
		  	  $this->vue->contenu = $this->vue->messages;               //$this->output($arbre) //affichage normal de la page  
		    }
        $this->vue->setFile("pied","pied.tpl"); 
        $out = $this->vue->render("page");           
        $this->output($out);            
      }
    }    
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  }
   /**
  * Fonction appellée par voirMens() pour calculer les dates
  * @param $stamp, timestamp unix
  * @param $month, str, valeurs limitees à chatnow, chatsuiv et chatprevue
  * si stamp est NULL ...
  *  
  */
  private function calDates($stamp,$month='chatnow')
  {
    $tabDataMonth = array();
    $calende = $stampCalende = $butoir = $stampSuiv = '';
    if ($month == 'chatpre') {
      $tabDataMonth = VapCal::moisPre($stamp);          
      $calende =  $tabDataMonth['datePre'];
      $stampCalende = $tabDataMonth['stamp'];
      $butoir = VapCal::jourUltime($stamp); 
      $stampSuiv = strtotime($butoir);
      $_SESSION['stampCalende'] = $stampCalende;
    } 
    elseif ($month == 'chatsuiv') {
      $tabDataMonth = VapCal::moisSuiv($stamp);
      $calende = $tabDataMonth['dateSuiv'];            
      $stampCalende = $tabDataMonth['stamp'];            
      $dataFinMois = VapCal::finMoisSuiv($stampCalende);
      $butoir = $dataFinMois['dateFin']; 
      $stampSuiv = $dataFinMois['stamp'];
      $_SESSION['stampCalende'] = $stampCalende ;
    }  
    elseif ($month == 'chatnow') {
      $calende = VapCal::calende($stamp);           //la calende du stamp de dernier message : date 
      $jourFutur = intval($stamp + 86400);          //stamp dernier message + 1 jour
      $butoir = date('Y-m-d',$jourFutur);           //la date du stamp butoir 
      $stampCalende = strtotime($calende);          //le stamp de la calende
      $stampSuiv = $stamp;                          //le stamp du dernier message
      $_SESSION['stampCalende'] = $stampCalende;    //mise en session du stamp de calende  
    }
    $out = array($calende,$stampCalende,$butoir,$stampSuiv);
    return $out;
  } 
  /*
  * traitement d'une vue non recursive mais avec pagination mensuelle.
  */
  public function voirMens()
  { 
    $tabMois = array();
    try {    
      $token = $_GET['token'];
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      else {       
        //obtenir la clé de l'antenne facultative ici
        if ( filter_has_var(INPUT_GET,'chatidant')) { 
          $lienant = filter_input(INPUT_GET,'chatidant',FILTER_VALIDATE_INT); 
          $fileCache = DIRCACHANT.$lienant.'/'.$lienant.'_chat.csv';
          if (! file_exists($fileCache)) { $this->ecrisCache($lienant,'_chat.csv'); }
        }
	      else { $lienant = false; }
        if (!empty($lienant) && (! array_key_exists($lienant,$this->tabAntenne))) { throw new MyPhpException("Contexte Chat, lienant n'existe pas"); }
        
        //obtenir les timestamp du premier et dernier chat posté  
        if (!empty($lienant)) { 
          $cachet = $this->lastStamp($lienant);
          $url_idant = '&amp;chatidant='.$lienant ; 
          $this->globalEntities($lienant);
          $fileCache = DIRCACHANT.$lienant.DIRECTORY_SEPARATOR.$lienant.'_mbr.php';
        }
        else { 
          $cachet = $this->lastStamp(); 
          $url_idant = ''; 
          $this->globalEntities();
          $fileCache = BASEDIR.'index.php';
        }  
        
        $this->stampIni = $cachet['old']; //le stamp du premier message
        $calendeIni = VapCal::calende($this->stampIni);
        $this->stampUlt = $cachet['new'];  //le stamp du dernier message
          
        //obtenir le stamp de calende, la date de calende et la date butoir
        if (isset($_GET['chatmois'])) {
          if ($_GET['chatmois'] == 'chatrw') {
            if (empty($_SESSION['stampCalende'])) { throw new MyPhpException('controleur Chat, stampCalende absent'); }
            else { $tabMois = $this->calDates($_SESSION['stampCalende'],'chatpre'); }
          } 
          elseif($_GET['chatmois'] == 'chatsuiv') {
            if (empty($_SESSION['stampCalende'])) { throw new MyPhpException('controleur Chat, stampCalende absent'); }
            else { $tabMois = $this->calDates($_SESSION['stampCalende'],'chatsuiv'); }
          } 
          elseif($_GET['chatmois'] == 'chatsautpre') {
            if (empty($_SESSION['stampSaut'])) { throw new MyPhpException('controleur Chat, stampSautPre absent'); }
            else { $tabMois = $this->calDates($_SESSION['stampSaut'],'chatpre'); }
          }
          elseif($_GET['chatmois'] == 'chatsautsuiv') {
            if (empty($_SESSION['stampSaut'])) { throw new MyPhpException('controleur Chat, stampSautSuiv absent'); }
            else { $tabMois = $this->calDates($_SESSION['stampSaut'],'chatsuiv'); }
          }
          else { throw new MyPhpException('controleur Chat, chatmois invalide'); }
        }   
        else { $tabMois = $this->calDates($this->stampUlt); } 
        
        //calcul des vues 'mois precedent' et 'mois suivant'
        $stampSuiv = $tabMois[3]; $stampCalende =  $tabMois[1]; $calende = $tabMois[0]; $butoir = $tabMois[2];
        
        if ($stampSuiv < $this->stampUlt) {
          $dataMoisSuiv = VapCal::moisSuiv($stampCalende);
          $moisSuiv = $dataMoisSuiv['date']; 
          $urlMoiS = urlencode($moisSuiv);
          $stampSuiv = $dataMoisSuiv['stamp'];
          $this->vue->moisSuiv = '<a href="'.$this->urlPage.'chatsuiv'.$url_idant.'&amp;mois='.$urlMoiS.'">'.CHAT_MONTH.' : '.$moisSuiv.'</a>';
        }
        else { $this->vue->moisSuiv = ''; $moisSuiv = ''; $stampSuiv = NULL; }
        if ($stampCalende > $this->stampIni) {
          $dataMoisPre = VapCal::moisPre($stampCalende);
          $moisPre = $dataMoisPre['date'];
          $urlMoiP = urlencode($moisPre);
          $moisSuiv = '';
          $stampPre = $dataMoisPre['stamp'];
          $this->vue->moisPre = '<a href="'.$this->urlPage.'chatrw'.$url_idant.'&amp;mois='.$urlMoiP.'">'.CHAT_MONTH.' : '.$moisPre.'</a>';
        }
        else { $this->vue->moisPre = ''; $moisPre = ''; $stampPre = NULL; }
       
        //usinage sql        
        if (!empty($lienant)){ $rslt = $this->sqlMensuel($calende,$butoir,$lienant);  }
        else { $rslt = $this->sqlMensuel($calende,$butoir); } 
        
        $compteur = count($rslt);                           
        if (($compteur == 0) && (!$ok = $this->filtre->doublon('idchat',$lienant,$this->table,'lienant'))) {
          if (! empty($lienant)) {  $this->vue->retour =  $this->tabAntenne[$lienant].MBR_NOCHATS; }
          else {  $this->vue->retour =  ENT_ANTS.MBR_NOCHATS; }
        } 
        elseif (($calendeIni == $calende) && ($compteur == 0)) { //cas limite poste un 30 ou 31 du mois
          if (empty($lienant)) { $lienant = NULL;}          
          $rslt = $this->sqlMensuel($calende,$butoir,$lienant,1); 
          $compteur = count($rslt);
        } 
        
        if ($compteur > 0) { 
          $this->vue->setFile("contenu","voir_Chat_T.tpl");          
          $this->vue->setBlock("contenu","chatton","chatMois");     
          foreach($rslt as $row){
            $this->voirUnMessage($row);
            $this->vue->append('chatMois','chatton');
          }
        }     
        else { //mois creux ?
          $_SESSION['stampSaut'] = $stampCalende;
          $mois = urldecode(filter_input(INPUT_GET,'mois',FILTER_SANITIZE_SPECIAL_CHARS));
          $urlmoiS = urlencode($mois);
          $urlmoiP = urlencode($moisPre);
          $this->vue->moisvide = $mois;
          $this->vue->moispre = $moisPre;
          $this->vue->urlchatpre = $this->urlPage.'chatsautpre'.$url_idant.'&amp;mois='.$urlmoiP;
          $this->vue->urlchatBack = BASEURL."index.php?ctrl=chat&action=voirMens&token=".$this->token.$url_idant;
          $this->vue->setFile("contenu","voir_Chat_E.tpl");   
        } 
        session_write_close();      
        $this->vue->setFile("pied","pied.tpl"); 
        $out = $this->vue->render("page");
        $this->output($out);
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  }
  /**
  * La methode poster fait soit un insert en base
  * soit lance la previsualisation qui permet de corriger son chat
  */
  public function poster()
  {
    try {
      $dataMbr = array();$page = NULL;
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = $this->token; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      else {      
        if ( filter_has_var(INPUT_GET,'chatidant')) { $lienant = filter_input(INPUT_GET,'chatidant',FILTER_VALIDATE_INT); }
        elseif ( filter_has_var(INPUT_POST,'lienant')) { $lienant = filter_input(INPUT_POST,'lienant',FILTER_VALIDATE_INT); }
	      else { throw new MyPhpException("Contexte Chat, lienant obligatoire"); }
        
        if (! array_key_exists($lienant,$this->tabAntenne)) { throw new MyPhpException("Contexte Chat, lienant n'existe pas"); }
        else {
          if (filter_has_var(INPUT_GET,'idchat')) { $this->idpere = filter_input(INPUT_GET,'idchat',FILTER_VALIDATE_INT); }
          if (filter_has_var(INPUT_GET,'sujet')) { $this->sujet  = 'Re : '.filter_input(INPUT_GET,'sujet',FILTER_SANITIZE_SPECIAL_CHARS); } 
          if (isset($_POST['chatpost']))  { //ecritures en base et re creation du cache _chat.csv          
            $this->vue->retour = $this->controler($_POST);
            if (empty($this->vue->retour)){   
              if ($this->valppk = $this->modele->inscrire($_POST)){
                $this->viderUnCache($lienant);
                $this->creerCacheAntenne();
                $this->viderCache($lienant,'./*_chat.csv');
                if ($ok = $this->ecrisCache($lienant,'_chat.csv')){
                  $loc =  "Location: ".BASEURL."index.php?ctrl=chat&action=voirMens&lienant=".$lienant."&token=".$token;
                  // Succès de l'ecriture: on envoi aussi par mail s'il s'agit d'une réponse                                  
                  if ( $_POST['idpere'] > 0 ) {
                    $idpere = intval($_POST['idpere']);
                    $champPere = array('nom','prenom','mail','lang');
                     
                    // selectionner en db la ligne dont idchat == idpere
                    $chatPere = $this->modele->selection($idpere);
                    $cleMbrPere = $chatPere[$this->table]['lienhum'];
                     
                    // selectionner les donnees de T_HUM
                    $mbrPere = $this->modele->selectUneLigne($cleMbrPere,T_HUM,$champPere); 
                
                    $argClean = str_replace('Re :','',$chatPere[$this->table]['sujet']);                    
                    $corpsClean = strip_tags($_POST['message']);
                    // appeler une fonction statique (à créer EN 2 LANGUES !) avec en param (sujet, corps, ligne['poste'], 
                    // cette fonction doit retourner $_POST['message'] clean ET inclus dans un message standart du genre:
                    $arg = "VAP : ".$_POST['pseudo']." a répondu à votre message";
                    $corpus = "Bonjour ". $mbrPere['prenom'] ." ".$mbrPere['nom'].", \n\n"
                              ."Votre message posté le: ".$chatPere[$this->table]['poste']. " sur le réseau VAP \n"
                              ."et intitulé : \"".$argClean ."\". \n"
                              ."a reçu une réponse de : ".$_POST['pseudo'].";\n"
                              ."- membre du réseau VAP n°: ".$_SESSION['membre']." à : ".$_SESSION['nomantenne']."\n"
                              ."- dont le courriel est : ".$_POST['chatmail']."\n\n" 
                              ."Votre message était : \"".$chatPere[$this->table]['message']."\"\n\n"
                              ."La réponse de  ".$_POST['pseudo']." est : \n"
                              ."\"". $corpsClean."\" \n\n" 
                              ."Si vous décidez de poursuivre cette conversation par mail, " 
                              ."merci de répondre exclusivement à ". $_POST['chatmail'].". \n"
                              ."Vous pouvez néanmoins poursuivre l'échange sur notre vap-chat "
                              ."en vous identifiant sur :  http://www.vap-vap.be/vapspace \n\n"
                              ."L'équipe des VAP: pour un mobilité partagée. ";
                    // envoyer par mail sujet clean et corps clean par mail à ligne['chatmail'] avec répondre à: $_POST['chatmail'].                 
                    $this->mailer($mbrPere['mail'],$this->expedit,$arg,$corpus);
                  }
                  header($loc);
                }
              }
              else  { throw new MyPhpException('Impossible d\'insérer votre message');}
            } // des erreurs du client?: on retourne les données dans l'entité contenu de la vue
            else { $page = $this->vueChat('insert',$lienant,$_POST); }          
          }
          elseif (isset($_POST['chatprevue'])) { $page = $this->vueChat('chatprevue',$lienant,$_POST); }
          elseif (isset($_POST['chatcorr'])) { $page = $this->vueChat('insert',$lienant,$_POST); }
          else { $page = $this->vueChat('insert',$lienant); }
          $this->output($page);
        }
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  }//fin de methode
  /**
  * methode qui publie unseul vapchat deja identifié initialement dans vapspace
  */
  public function editer()
  {
    try { 
      $dataChat = $leTabChat = array();
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = NULL; }    
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      else {
        if (filter_has_var(INPUT_POST,'chatppk') && ($chatppk = filter_input(INPUT_POST,'chatppk',FILTER_VALIDATE_INT))) { ;}
        elseif (filter_has_var(INPUT_GET,'chatppk') && ($chatppk = filter_input(INPUT_GET,'chatppk',FILTER_VALIDATE_INT))) { ;}
        else { throw new MyPhpException("contexte ChatCtrl, une ppk est obligatoire pour methode editer"); }
        
        if (filter_has_var(INPUT_GET,'mode') && ($mode = filter_input(INPUT_GET,'mode',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(edit|single)$#'))))) {        
          if ($leTabChat = $this->modele->selection($chatppk)) {
            foreach ($leTabChat[$this->table] as $cle=>$val) {
              $dataChat[$cle] = $val ;
            } 
            $dataChat['idchat'] = $chatppk;
            if (($mode != 'single') && ($dataChat['lienhum'] != $_SESSION['idhum'])) { 
              throw new MyPhpException('ChatCtrl: on ne peut editer que ses propres chats !'); 
            }
          }
          else { throw new MyPhpException('ChatCtrl/editer: impossible de faire la sélection en base'); } 
        }
        else { throw new MyPhpException('ChatCtrl/editer: mode d\'edition invalide'); } 
        //lancer la vue      
        $this->globalEntities();
        if ($mode == 'single') {
          $this->voirUnMessage($dataChat);
          $this->vue->setFile("contenu","voir_Chat.tpl");
          $this->vue->setFile("pied","pied.tpl");        
          $page = $this->vue->render("page");        
        }
        elseif ($mode == 'edit') { $page = $this->vueChat('insert',$_SESSION['idant'],$dataChat); }
        $this->output($page);
      }      
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }         
  }
  /**
  * janvier 2012: index n'est plus qu'une simple redirection vers vapspace
  */
  public function index()
  {
    try { 
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = $this->token; }
      if (! $ok = $this->validSess($token)){ $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko"; }
      else {  $loc = "Location: ".BASEURL."index.php?ctrl=membre&token=".$token; }
      header($loc);
    }
    catch(MyPhpException $e) {
     $msg = $e->getMessage();
     $e->alerte($msg);  
    }          
  } 
  
/**
* methode supprimer, utilise $this->modele->ligneParValeurs(array('lienant'),$this->table,'idchat',$cle); 
* pour selectionner l'idant du chat afin de vider le cache de l'antenne
*/
  public function supprimer()
  {
     try { 
      $tabCol = array('lienant');
      $this->globalEntities(); 
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = $this->token; }     
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
        exit();
      }
      elseif (! ($this->droits & MEMBRE)){
        $this->vue->retour =  MBR_NO_ACTION;
      }
      else { 
        if ($this->droits & RESPONSABLE) { $loc = "Location: ".BASEURL."index.php?del=chat&token=".$token; }
        else { $loc = "Location: ".BASEURL."index.php?ctrl=membre&token=".$token."&del=chat" ; }  
        if ($cle = filter_input(INPUT_GET,'idchat',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[0-9]+$#')))) {
          // D'abord traiter le vidage du cache
          $tabAntChat = $this->modele->ligneParValeurs($tabCol,$this->table,'idchat',$cle);
          $idantChat = $tabAntChat['lienant'];
          
          //Puis effacer la ligne
          if ($ok = $this->modele->supprimeUneLigne($cle,$this->table)) {
            $this->viderUnCache($idantChat);
            $this->creerCacheAntenne();
            $this->viderCache($idantChat,'./*_chat.csv');
            $this->ecrisCache($idantChat,'_chat.csv');
          } 
          header($loc);
        }
        else { throw new MyPhpException('le parametre: idchat dans le contexte: '.$this->contexte.' est non valide'); }
      }
      $this->vue->setFile("pied","pied.tpl"); 
      $out = $this->vue->render("page");
      $this->output($out);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  }//fin de methode 

  /**
  * la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
  */  
  protected function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}
     

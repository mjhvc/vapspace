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
* Class NewsCtrl, gère le contexte News.ini table: T_NEWS et T_NEWS_ANT
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include("Controleur.class.php");
include("class.phpmailer.php");

class NewsCtrl extends Controleur
{
  private $color;
  private $background;  
  protected $champPassif; 
  private $choixDestination = array(); 
  private $contexte;
  private $commanditaire;
  private $cptAntenne;
  private $dataContexte;
  private $statut;
  private $droits;
  private $errorMail;
  private $idnews;
  private $idxPassif;
  private $listeDataNews = array();
  private $listeDataVue = array();
  private $action;
  private $schemaNews = array();
  private $table;
  private $tabAntenne = array();
  private $tabNews = array();
  private $tabTables = array();
  private $titlecolor;
  private $urlCms;
  private $urlToCms;
  private $urlExit;
  private $urlLogo;
  private $ppk;
  public $pathLogo;
  private $urlPagesNews ; 

/**
* methode fille __construct()  necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appelle  parent:: __construct($contexte) qui charge: statut,modele et vue selon session et contexte 
 */
  public function __construct()
  {
    $this->contexte = "News";
    $this->commanditaire = '';
    $this->dataContexte = array();
    $this->errorMail = MAILWM;
    parent::__construct($this->contexte);
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();     
    $this->tabTables = $this->modele->getTables();
    $this->tabAntenne = $this->valeursPassive(1); //attribution de la liste des antennes
    $this->tabAntenne[0] = NEWS_ALL;
    $this->cptAntenne = (count($this->tabAntenne) + 1); // +1 valide tant que comptage commence à 1
    $this->listeDataNews = $this->modele->getListeAttr();
    $this->listeDataVue = $this->modele->getVue();
    $this->schemaNews = $this->modele->getDataTable(T_NEWS);
    $this->table = T_NEWS;
    $this->action = '';
    $this->champPassif = 'lienant';
    $this->idxPassif = 1;
    $this->urlCms = 'http://'.$_SERVER["SERVER_NAME"].'/spip.php?page=news' ;       //A personnaliser selon votre cms
    $this->urlToCms = 'http://'.$_SERVER["SERVER_NAME"].'/spip.php?page=news&amp;'; //A personnaliser selon votre cms
    $this->urlExit =  BASEURL.'index.php?ctrl=news&amp;action=desinscrire&amp;';
    $this->urlLogo = BASEURL.'photos/logo.png'; 
    $this->ppk = '';
    $this->color = 'black';
    $this->titlecolor = '#99bc0f';
    $this->background = 'white';
    $this->pathLogo = DIRIMG.'logo.png';
    $this->urlPagesNews = BASEURL."index.php?ctrl=news&amp;action=fetchNews&amp;token=".$this->token."&amp;page=";
  }

/**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_contexte est dans pied.tpl
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head = NEWS_TIT;
    $this->vue->titre_page = NEWS_TIT;
    $this->vue->vapspace =  $this->url_vapspace.$this->token; 
    $this->vue->connecte = $this->statutConnect($this->token);   
    if (! ((int)$this->droits & RESPONSABLE)){ $this->vue->index_admin = ''; }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin = MBR_ADMIN ;
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
  * La methode qui filtre les donnees clientes
  */
  protected function controler($tableau)
  {
    try {
      $ramasseError = array(); $x = 0; $antenAlt = array('respAnt',NEWS_ALL);
      $this->vue->retour = parent::controler($tableau);      
      if (empty($this->vue->retour)){                  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){  
          $ramasseError[] = $testOblig;
        }
        foreach ($this->tabAntenne as $nom) {
          if ( in_array($nom,$tableau)) { $x++; }
        } 
        if ($x == 0) {
          foreach ($antenAlt as $alt) {
            if (in_array($alt,$tableau)) { $x++; }
          }
        } 
        if ($x == 0) { 
          $ramasseError[] = CONTROL_ANT_OBLIG; 
        }
        if (! empty($ramasseError[0])){ 
          $this->vue->retour = '<span style="color:red;">'.$ramasseError[0].'</span>'; 
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
	 /**
  * Retourne une requète sql variable qui fournit les mails des destinatiares 
  * selon les antennes sélectionnées pour une news dans '".T_NEWS_ANT."'
  * ce calcul est fait dans la methode choix()
  * @param: $choixAnt array(), forme $choixAnt[integer]=(int)$lienant
  */ 
  private function sqlmail($idnews=NULL,$choixAnt=array())
  {
    $antennes=array(); $x = 0; $separator= ","; $finam =") ";    
    if (!empty($idnews)) { 	  
      $dbh = $this->modele->getCnx();	
	    $sqlO = "SELECT lienant FROM ".T_NEWS_ANT." WHERE lienews = :idnews"; //sélection des antennes qui correspondent à $idnews dans ".T_NEWS_ANT." !
	    $stmt = $dbh->prepare($sqlO);
      $stmt->bindValue(':idnews',$idnews,PDO::PARAM_INT);
      $stmt->execute();
      while($rslt = $stmt->fetch(PDO::FETCH_ASSOC)) {
	      $antennes[]= $rslt['lienant'];
      }
    }
    else {
			//recuperer le tableau $choixAnt et l'assimiler à $antennes ici 
			$antennes = $choixAnt ;    
    }
    if ($antennes[0] == -1) { //valeur -1 pour la liste des responsables d'antenne
      $sqlM = "SELECT DISTINCT mail FROM ".T_HUM." WHERE statut='responsable'";
    }
		elseif ($antennes[0] == 0) {	//Si l'option 'tous' est stoquée dans ".T_NEWS_ANT.", la rquète concerne 'tous'
		  $sqlM =  "SELECT mail FROM ".T_HUM.",".T_CORE
						." WHERE ".T_CORE.".news = 'oui'"
						." AND ".T_HUM.".mail NOT LIKE 'PasDeMail'"
						." AND ".T_CORE.".lienperso = ".T_HUM.".idhum";	 
	  }
	  else {      //préparation de la requète qui sélectionne les 'mails' en fonction des antennes perçues
		  $nbr=count($antennes);								//compte le nombre d'idant trouvé.
		  $sqlM = "SELECT mail FROM ".T_HUM.",".T_CORE
			  ." WHERE ".T_CORE.".news = 'oui'"
        ." AND ".T_HUM.".mail NOT LIKE '".MBR_NOMAIL."'"
			  ." AND ".T_CORE.".lienperso = ".T_HUM.".idhum"
			  ." AND ".T_CORE.".lienant IN (";
		  while ($x < $nbr) {
			  $idant = $antennes[$x];
			  if ($x == ($nbr - 1))	{	$sqlM = $sqlM."'".$idant."'".$finam ; }
			  else 	{	$sqlM = $sqlM."'".$idant."'".$separator; }
	 		  $x++;
		  }
	  }
	  return $sqlM;	
  }
   
  /**
  * Le moteur qui envoie les newsletters
  * ne traite que une news à la fois si elle est marquee comme active
  * recupère la ligne de la news active de la table'".T_NEWS."'
  * utilise:
  * $this->sqlmail($idnews) qui fournit la requete adéquate pour manipuler les mails des destinataires
  * $this->constructHtmlNews() qui construit la news en html
  * $this->constructAltNews() qui construit la news en texte brut
  * $this->mailerNews() pour envoyer la news construite
  * REM: pour activer le moulin avec wget: echapper ? et &
  * exemple : wget  http://localhost/vap/database/index.php\?ctrl=news\&action=moulin 
  */
  public function moulin()
  {
    $stockMails = $tabMail = array(); 
    $cpt = 0 ;    
    if (isset($_GET['action']) && $_GET['action'] == 'moulin') {
      //selection de la news marquée 'active'      
      $sql = "SELECT * FROM ".T_NEWS."  WHERE active='oui'";
      $dbh = $this->modele->getCnx();
      $stmt = $dbh->prepare($sql);
      $stmt->execute();
      $this->tabNews = $stmt->fetch(PDO::FETCH_ASSOC);
      if(empty($this->tabNews)) { exit(0); }
      //Sinonsi toutes les news sont acheminées:	
	    elseif (($this->tabNews['achemines'] > 0) && ($this->tabNews['achemines'] == $this->tabNews['destinataires'])) {	
		    $sqlfin = "UPDATE ".T_NEWS." SET active = 'non', envoye = NOW() WHERE idnews = :idnews";
		    $stmt = $dbh->prepare($sqlfin);
        $stmt->bindValue(':idnews',$this->tabNews['idnews'],PDO::PARAM_INT); 
        $stmt->execute();	
		    exit(0);
	    }	
      //recuperer les parametres de la ligne de news en base:
      else { 
        $idnews = intval($this->tabNews['idnews']);
        $achemines = intval($this->tabNews['achemines']);  //fixe le nombre de news déjà acheminées :
        $expediteur = $this->tabNews['expediteur'];
        $sujet = $this->tabNews['sujet'];
        $contenu = $this->tabNews['contenu'];
        $altcontenu = $this->tabNews['altcontenu'];
        $colorTitle = $this->tabNews['couleurtitre'];
        $colorFont = $this->tabNews['couleurpolice'];
        $background = $this->tabNews['couleurfond'];
        if ($requete = $this->sqlmail($idnews)) {
          $stmt = $dbh->prepare($requete);
          $stmt->execute();
          //fixation du mail du destinataire dans $stockMails
          while ($tabMail = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stockMails[] = $tabMail['mail'];
            $cpt++; 
          }
          //Stocker le nombre de destinataires; 
          $sqlNbrTo = "UPDATE ".T_NEWS." SET destinataires = :cpt WHERE idnews = :idnews"; 
          $stmt = $dbh->prepare($sqlNbrTo);
          $stmt->bindValue(':cpt',$cpt,PDO::PARAM_INT);
          $stmt->bindValue(':idnews',$idnews,PDO::PARAM_INT);
          //allumer le moteur du moulin:
          if ($on = $stmt->execute()) {
            $moulin = 10;	
            if (( $cpt - $achemines) < $moulin) { $moulin = $cpt - $achemines; }
		        $fin = $achemines + $moulin;
            while ($achemines < $fin) { //moulin en action 
              $destina = $stockMails[$achemines];
              $mailOut = urlencode($stockMails[$achemines]);	
              $urlMailOut = $this->urlExit.'outnews='.$mailOut;	
              $urlVueCms = $this->urlToCms.'lienews='.$idnews;
              $contentNews = $this->constructHtmlNews($contenu,$background,$colorFont,$colorTitle,$urlMailOut,$urlVueCms);
              $altContentNews = $this->constructAltNews($altcontenu,$urlMailOut,$urlVueCms);
		          if (! $news = $this->mailerNews($destina,$expediteur,$sujet,$contentNews,$altContentNews)) {
                $error = $stockMails[$achemines].NEWS_ERROR_MAIL;
              }
              $achemines ++;
            }
            if ($achemines == $fin) { //arreter le moulin
				      $sqlFin = "UPDATE ".T_NEWS." SET achemines=:achemin WHERE idnews=:idnews";
              $stmt = $dbh->prepare($sqlFin);
              $stmt->bindValue(':achemin',$achemines,PDO::PARAM_INT);
              $stmt->bindValue(':idnews',$idnews,PDO::PARAM_INT);
					    $stmt->execute();
            }
            	//gestion des erreurs d'envoi:on envoi un mail à infovap@skynet.be pour informer des destinataires défectueux
				    if (!empty($error)) {
					    $erreur = new PHPMailer();
					    $erreur->IsMail();
					    $erreur->IsHTML(true);
					    $erreur->AddAddress($this->errorMail);
					    $erreur->From = $expediteur;
					    $erreur->Subject = NEWS_ERROR_SUBJECT.$this->tabAntenne[$idnews];
					    $erreur->Body = '<p>'.$error.'</p>';
					    $erreur->Send();
					    $erreur->ClearAddresses();
            }
            exit(0);
			    }
          else { exit(0); }  
				}
        else { exit(0); } 
      }
      exit(0);     
    }
    else { exit(0); }
  } //fin methode

  private function buildPiedNews($mode,$out=null,$cms=null) {
    if ($mode == 'html') { $br = '<br />'; }
    else { $br = ''; }
    if (! $out) { 
      $urlOut = BASEURL;
      $msgOut = NEWS_NOSUB; 
    }
    else { 
      $urlOut = $out; 
      $msgOut = NEWS_ONSUB;
    }
    if (! $cms) { 
      $urlVue = BASEURL; 
      $msgVue = NEWS_NOVUE; 
    }
    else { 
      $urlVue = $cms; 
      $msgVue = NEWS_VUE;
    }      
    $pied = '<a href="'.$urlVue.'">'.$msgVue."</a>".$br."\n"
            .'<a href="'.$urlOut.'">'.$msgOut."</a>\n";
    return $pied;
  }
  /**
  * methode de construction du code complet html d'une news
  * @param $contentbody, string: le contenu de la balise <body>
  * @param $background,string: la couleur de fond
  * @param $color,string, la couleur des fonts
  * @param $colorTitle, string, la couleur des titres
  * @param $test, bool si on est dans phase de test
  * @param $out: string, url complète de desinscription personnalisée
  * @param $cms: string, url pour visionner la news dans le cms 
  */
  private function constructHtmlNews($contentbody,$background,$color,$colorTitle,$out=NULL,$cms=NULL)
  {   
    $doctype =" <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
    $html ="<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$_SESSION['lang']."\">\n";
    $meta ="<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" /><head>\n";    
    $openbody = '<body style="background-color: '.$background.' ; color: '.$color.' ; ">'."\n";
		$bann = '<p><img src="'.URLIMG.'logo.png" alt="logo.png" /></p>'."\n"; //"cid:my-attache"
    $h1new = '<h1 style="color:'.$colorTitle.';">';
		$h3new = '<h3 style="color:'.$colorTitle.';">'; 
		$contenu = str_replace('<h1>',$h1new,$contentbody);
		$contenu = str_replace('<h3>',$h3new,$contenu);
    $pied = $this->buildPiedNews('html',$out,$cms);
    $final ='<p>'.$pied."</p> </body> </html> \n";          
    $leBody = $doctype.$html.$meta.$openbody.$bann.$contenu.$final;    
    return $leBody;
  }
  /**
  * construit le contenu alternatif en texte brut de la News
  * @param $altcontenu: string, le contenu en texte
  * @param $out: string url de desinscription
  * @param $cms: string url de vision de la news sur le cms
  */
  private function constructAltNews($altcontenu,$out=NULL,$cms=NULL)
  {
    $pied =  $this->buildPiedNews('text',$out,$cms);
    $altNews = $altcontenu.$pied;
    return $altNews;   
  }
  /**
  * function qui gère l'envoi du mail par appel de l'objet phpmailer
  * @param $destina: string, le mail d'un destinataire
  * @param $sujet: string,le sujet de la News
  * @param $contenu: string, le contenu HTML de la news
  * @param $altcontenu: string, le contenu alternatif de la news
  * @param $nom: string, que mettre dans champ From
  */
  private function mailerNews($destina,$expedit,$sujet,$contenu,$altcontenu,$nom =VAP_TEAM)
  {
    //$pathLogo = DIRIMG.'logo.png';	  
    $mail = new PHPMailer();
  	$mail->IsMail();
  	$mail->IsHTML(true);
	  $mail->AddAddress($destina);
	  $mail->From = $expedit;
    $mail->FromName = $nom;
    $mail->Sender =  $this->errorMail;
	  $mail->Subject = $sujet;
	  $mail->Body = $contenu;
	  $mail->AltBody = $altcontenu;
    //$mail->AddAttachment($pathLogo);
    //$mail->AddEmbeddedImage($pathLogo,"my-attache","logo.png"); // ,$encoding = 'base64', $type = 'image/png');		
	  $mail->WordWrap = 70;
	  if(! $mail->Send() ) {	$fin = false; }
	  else {
		  $mail->ClearAddresses();
      $mail->ClearAttachments();		
		  $fin = true;
	  }
    return $fin;	 
  }
  /**
  * function qui aligne un formulaire checkbox de toutes les antennes en 3 colonnes
  * en attribuant aux entites $this->vue->debut et $this->vue->fin soit <tr>, </tr>, ou ''
  * cette fonction se base sur $this->tabAntenne, déja construit (__construc) et rajoute la vue des responsables
  * @param $tableau: array, le tableau des antennes
  * @param $singleEntity, string l'entite qui represente une ligne de block de le template
  * @param $blockEntity, string l'entite qui represente le nom du bloc dans le template
  * utilse $this->getChecked() du Controleur.class.php pour gérer le checked=checked
  */
  private function alignerAntenne($tableau,$singleEntity,$blockEntity)
  {
    $x = 1;   $this->vue->debut = ''; $this->vue->fin = '';
    //traitement de checking de la liste des responsables d'antenne
    if (!empty($tableau['toresp']) && ($tableau['toresp'] == 'respAnt')) { $this->getChecked(true,'respAnt'); }
    else { $this->getChecked(false,'respAnt'); } 
    //Aligner les antennes de $this->tabAntenne (avec 'Tous')
    foreach($this->tabAntenne as $nomant) { 
      if (!empty($tableau) && (in_array($nomant,$tableau))) { $this->getChecked(true,'antenne'); }
      else { $this->getChecked(false,'antenne'); }
       
      if (($x % 3)  == 0) { $this->vue->fin = "</tr>"; } 		
		  elseif (($x % 3) == 1)	{	
			  $this->vue->debut = "<tr>";
			  if ($x == $this->cptAntenne) { $this->vue->fin = "</tr>"; }
        else { $this->vue->fin = ''; }
		  } 
		  elseif (($x % 3) == 2) {	
        $this->vue->debut = ''; 
        if ($x == $this->cptAntenne) { $this->vue->fin = "</tr>"; }
        else { $this->vue->fin = ''; }
      }
      	 
      $this->vue->nomant = $nomant ;
      $x++;
      $this->vue->append($singleEntity,$blockEntity);
    }      
  }
  /**
  * gestion de la vue de l'interface de redaction vision de News
  * @param $action: string defini le mode d'action de la methode
  * @param $tableau: array() le tableau des donnees à afficher
  * utilise $this->listeDataVue, appel du Modèle pour savoir que afficher dans 
  */
  private function gestionVue($action,$tableau=array())
  {
    $page = '';
    $this->globalEntities();   
    if (($action == 'ecrire') || ($action == 'edition')) {
      $this->vue->script_formu = BASEURL."index.php?ctrl=news&amp;action=voir&amp;token=".$this->token;
      if ($action == 'ecrire') {
        $this->getChecked(false,'respAnt');  //pas de checking de liste 'respAnt'     
        if (empty($tableau)) {    
          $this->vue->htmlcont = ''; 
          $this->vue->expediteur = $this->expedit; 
          $this->vue->sujet = NEWS_TIT; 
          $this->vue->couleurfond = $this->vue->couleurtitre = $this->vue->couleurpolice = '' ;
        }
        else {
          if (in_array('respAnt',$tableau)) { $this->getChecked(true,'respAnt'); } //checking de liste 'respAnt'  
          foreach ($this->listeDataVue as $ent) { 
            if ($ent == 'contenu') { $this->vue->htmlcont = $tableau['contenu']; }
            else { $this->vue->$ent = $tableau[$ent]; }
          }
        }
      } 
      elseif ($action == 'edition') {
        $this->vue->htmlcont = $tableau['contenu']; 
        $this->vue->expediteur = $tableau['expediteur']; 
        $this->vue->sujet = $tableau['sujet']; 
        $this->vue->couleurfond = $tableau['couleurfond'];
        $this->vue->couleurtitre = $tableau['couleurtitre']; 
        $this->vue->couleurpolice = $tableau['couleurpolice'];  
      }
      $this->vue->setFile('contenu','ecrire_News.tpl');  
      $this->vue->setBlock('contenu','antennes','uneant');  
      $this->alignerAntenne($tableau,'uneant','antennes');    
    }
    elseif ($action == 'vueHtml') {
      $this->vue->script_formu = BASEURL."index.php?ctrl=news&amp;action=choix&amp;token=".$this->token ;
      $h1new = '<h1 style="color:'.$tableau['couleurtitre'].';">';
	    $h3new = '<h3 style="color:'.$tableau['couleurtitre'].';">';
      $contenu = preg_replace('#<h1>#',$h1new,$tableau['contenu']);
    	$contenu = preg_replace('#<h3>#',$h3new,$tableau['contenu']);
      $this->vue->htmlcont = $contenu; 
      $this->vue->couleurfond = $tableau['couleurfond'];
      $this->vue->couleurpolice = $tableau['couleurpolice'];       
      $this->vue->url_cms = BASEURL;
      $this->vue->url_desinscrit = BASEURL;
      $this->vue->url_logo = $this->urlLogo;
      $this->vue->expediteur = $tableau['expediteur'];
      $this->vue->sujet = $tableau['sujet'];  
      $this->vue->altcontenu = $tableau['altcontenu'];      
      $this->vue->setFile('contenu','vueHtml_News.tpl');
      $this->vue->setBlock('contenu','retourantennes','unretour');
      $this->alignerAntenne($tableau,'unretour','retourantennes');
      $this->vue->setBlock('contenu','retourdata','unedata');
      foreach ($tableau as $nom=>$val) {
        $this->vue->nomdata = $nom;
        $this->vue->valdata = htmlentities($val,ENT_QUOTES,"ISO-8859-1"); //important
        $this->vue->append('unedata','retourdata');
      } 
    }
    elseif ($action == 'fetch') {
      //sql et pagination : preparation
      $where = "envoye = 0";   // je ne selectionne que les news non envoyees.
      $sqlFull = $this->sqlFactory($where);
      $noligne = 0;
      $nbrPages = $this->comptePages($sqlFull['pagin']);       
      if (isset($_GET['page'])) {
        $pageActuelle = intval($_GET['page']);
        if ($pageActuelle > $nbrPages) {
          $pageActuelle = $nbrPages;
        }
      } 
      else { $pageActuelle = 1 ; } 
      $rsql = $sqlFull['stdt'];  
      $dataPagin = $this->pagination($nbrPages,$pageActuelle,$this->urlPagesNews,$rsql);
      $this->vue->pagination = $dataPagin['pages'] ;
      $fsql = $dataPagin['sql'];         
      //traitement sql
      $dbh = $this->modele->getCnx();         
      $stmt = $dbh->prepare($fsql);
      $stmt->execute();
      $compteur = $stmt->rowCount();      
      if ($compteur == 0){ $this->vue->retour = NEWS_FETCH_NOVUE; }
      else { 
        $this->vue->fetchIntro = NEWS_FETCH_INTRO;  
        $this->vue->urlPageNews = $this->urlCms;
        $this->vue->setFile('contenu','vueFetch_News.tpl');
        $this->vue->setBlock('contenu','lettre','linews');
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
          $classe = "A".(($noligne++)%2);
          $ppk = $result['idnews'];
          $this->vue->cssline = $classe;
          $this->vue->sujet = $result['sujet'];
          $this->vue->debut = strip_tags(substr($result['contenu'],0,90));
          $this->vue->editer = BASEURL."index.php?ctrl=news&amp;action=edition&amp;line=".$ppk."&amp;token=".$this->token;
          $this->vue->active = BASEURL."index.php?ctrl=news&amp;action=poster&amp;ppk=".$ppk."&amp;flag=oui&amp;token=".$this->token;
          $this->vue->inactive = BASEURL."index.php?ctrl=news&amp;action=poster&amp;ppk=".$ppk."&amp;flag=non&amp;token=".$this->token;  
          $this->vue->suppress = BASEURL."index.php?ctrl=news&amp;action=poster&amp;ppk=".$ppk."&amp;flag=del&amp;token=".$this->token;            
          $this->vue->append('linews','lettre');
        }
      }
    } 
    $page = $this->vue->getVar('contenu');
    return $page;
  }
  /**
  * ecrire: offre une interface adequate pour rediger une newsletter
  */
  public function ecrire()
  {
    try { 
      $this->globalEntities();
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = $this->token; }   
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & RESPONSABLE)) { $this->vue->retour =  MBR_NO_ACTION; }
      elseif ($redac = $this->filtre->lockMoulin()) { 
        $this->vue->retour = NEWS_WORK.$redac; 
      }
      elseif (empty($this->vue->retour)) {  
        $this->action = 'ecrire';
        $this->vue->contenu = $this->gestionVue($this->action,NULL);         
      }
      $this->vue->setFile('pied','pied.tpl');           
      $out = $this->vue->render('page'); 
      $this->output($out);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
	/**
  * Voir() offre l'interface adéquate pour visionner ce que l'on vient de rédiger
  */
  public function voir()
  {
    try { 
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = NULL; }
      $this->globalEntities();
      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & RESPONSABLE)) { $this->vue->retour = MBR_NO_ACTION; }
      elseif ($redac = $this->filtre->lockMoulin()) { 
        $this->vue->retour = NEWS_WORK.$redac; 
      }
      elseif (empty($this->vue->retour)) {
        if (isset($_POST["fileNews"])){ 
					$tabDir = $tabMail = array(); $ligne = ''; $format = 'd-m-Y';
					$date = date($format);
					$tabDir[$date] = $_SESSION['mail'];
					//création du cache News appel parent::creerCache($tabDir,'news')
					if ($ok = $this->creerCache($tabDir,$nom='news')) {	
						$longNom = strpos($_SESSION['mail'],'@');
						$fichier = substr($_SESSION['mail'],0,$longNom);
						$masqFichier = '_'.$fichier.'.txt';
						$nomFichier = $date.$masqFichier; 
						$fullPath = DIRCACHE.'news'.'/'.$date.'/'.$nomFichier;				
						//Récupérer le choix des antennes par appel de $this->choix($token)
						$this->choixDestination = $this->choix($this->token);
						//calculer la requete par appel de $this->sqlMail('',$this->choixDestination)
						$sql = $this->sqlMail('',$this->choixDestination);
						$dbh = $this->modele->getCnx(); 
						$stmt = $dbh->prepare($sql);
      			$stmt->execute();
						while ($tabMail= $stmt->fetch(PDO::FETCH_ASSOC)){
						 	$ligne .= $tabMail['mail']." \n"."\r";
						} 
						// ecrire le resulat dans DIRCACHE/news/$date
						$ok = parent::ecrisCache($date,$masqFichier,$ligne,'news');
						if ($ok = parent::downLoad($nomFichier,$fullPath)) { exit(0); }
						else { throw new MyPhpException('Erreur fichier, sorry!'); }
					}
					return true; 
				}  
        elseif (isset($_POST["voirNews"])){
          $this->vue->retour = $this->controler($_POST);
          if (empty($this->vue->retour)) {
            $this->action = "vueHtml"; 
            if (empty($_POST['couleurtitre'])) { $_POST['couleurtitre'] = $this->titlecolor ; }
            if (empty($_POST['couleurfond'])) { $_POST['couleurfond'] = $this->background ; }
            if (empty($_POST['couleurpolice'])) { $_POST['couleurpolice'] = $this->color; }
            $altcontenu = str_replace('<br />',"\n",$_POST['contenu']);
            $altcontenu = strip_tags($altcontenu,'<a>');
            $_POST['altcontenu'] = $altcontenu;      
            $this->vue->contenu = $this->gestionVue($this->action,$_POST); 
          } //Si erreurs: retour à action 'ecrire'
          else { $this->vue->contenu = $this->gestionVue('ecrire',$_POST); } 
        }
      }
      $this->vue->setFile('pied','pied.tpl');           
      $out = $this->vue->render('page');
      $this->output($out);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
  
 /**
  * La methode qui traite (aiguille via des varaibles $_GET) 3 actions : corriger news, tester news, enregistrer news
  */  
  public function choix($jeton=NULL)
  {
    try {
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      elseif (!empty($jeton)) { $token = $jeton; }
			else { $token = NULL; }
      $this->globalEntities();        
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & RESPONSABLE)) { $this->vue->retour = MBR_NO_ACTION;  }
			elseif (!empty($_POST['submitNews']) || !empty($_POST['fileNews'])) {
      	$this->commanditaire = urlencode($_SESSION['mail']);
				//détecter le choix 'Tous'  et 'antResp' avant les autres choix  
        if (in_array(NEWS_ALL,$_POST)){ $this->choixDestination[] = (int)0 ; }          
        elseif (in_array('respAnt',$_POST)) { $this->choixDestination[] = (int)-1 ; }  
        else {
          foreach ($this->tabAntenne as $lienant=>$nom) {
          	if (in_array($nom,$_POST)) {
          		$this->choixDestination[] = $lienant ; 
          	}
        	}
        }
				if (!empty($_POST['fileNews'])) { return $this->choixDestination; }
			}					      
			if ($redac = $this->filtre->lockMoulin()) { 
        $this->vue->retour =  NEWS_WORK.$redac; 
      }
      elseif (empty($this->vue->retour)) {
				$this->vue->retour = $this->controler($_POST);         
				if (empty($this->vue->retour)) {						
					if (!empty($_POST['submitNews'])) {
          	$injCommanditaire = array();
						foreach($_POST as $cle=>$val) { 
							$_POST[$cle] = html_entity_decode($val,ENT_QUOTES,"ISO-8859-1"); 
						} 
            $injCommanditaire[0] = $this->table;
            $injCommanditaire[1] = array('commanditaire');
            $injCommanditaire[2] = array($_SESSION['mail']);         
            if (! $this->ppk = $this->modele->inscrire($_POST,$injCommanditaire)) {
              throw MyPhpException('contexte News: erreur d\'insertion de news ');
            }
						//lancer l'insertion dans '".T_NEWS_ANT."' à la main (pas de contexte destination) 
            $sql = "INSERT INTO ".T_NEWS_ANT."(lienant,lienews) VALUES (:lienant,:pk)"; 
            $dbh = $this->modele->getCnx();
            $stmt = $dbh->prepare($sql);
            foreach($this->choixDestination as $idant) {
              $stmt->bindParam(':lienant',$idant,PDO::PARAM_INT);
              $stmt->bindParam(':pk',$this->ppk,PDO::PARAM_INT);
              $stmt->execute(); 
            }
            $loc =  "Location: ".BASEURL."index.php?instandby=news&token=".$token;
            $_POST['submitNews'] = NULL;
            header($loc);       
          }
        }  
        if (!empty($_POST['corrigerNews'])) { 
          $this->vue->contenu = $this->gestionVue('ecrire',$_POST);
          $_POST['corrigerNews'] = NULL; 
        } 
        elseif (!empty($_POST['testerNews'])) {
          $destina = $_SESSION['mail'];
          $expedit = $_POST['expediteur']; 
          $sujet = $_POST['sujet'];
		      $contenu = $_POST['contenu']; 
          $altcontenu  = $_POST['altcontenu'];
          $contentNews = $this->constructHtmlnews($contenu,$_POST['couleurfond'],$_POST['couleurpolice'],$_POST['couleurtitre']);
          $contentAlt = $this->constructAltNews($altcontenu);
          if ( $ok = $this->mailerNews($destina,$expedit,$sujet,$contentNews,$contentAlt)) {
            $msg = NEWS_SEND.$destina;            
          }
          else { $msg = NEWS_NO_SEND; }
          $this->vue->retour =  $msg ;
          $this->vue->contenu = $this->gestionVue('vueHtml',$_POST);
          $_POST['testerNews'] = NULL;
        }
      }
      $this->vue->setFile('pied','pied.tpl');           
      $out = $this->vue->render('page');
      $this->output($out);
    }      
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
 
  /**
  * methode poster: rend une news active 
  * devrait aussi afficher un message d'alerte (que une news est actice) dans l'interface administrative
  * pas de protection de session par token mais seul le commanditaire de la news ou un admin peut activer le postage
  */
  public function poster()
  {
    try { 
      $result = array(); $flag = 'non';
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
  
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      if (!($this->droits & RESPONSABLE)) {
        throw new MyPhpException('Action poster refusée: statut non valide');
      } 
      else {
        if (! $ppk = filter_input(INPUT_GET,'ppk',FILTER_VALIDATE_INT)) {
          throw new MyPhpException('Newsletter-poster : cle non valide');
        }
        if (filter_has_var(INPUT_GET,'flag') && ($_GET['flag'] == 'oui'))  { $flag = 'oui'; }
        elseif (filter_has_var(INPUT_GET,'flag') && ($_GET['flag'] == 'del'))  { $flag = 'del'; }        
        $sqlUn = "SELECT commanditaire FROM ".T_NEWS." WHERE idnews=:idnewsun";
        $dbh = $this->modele->getCnx();
        $stmt = $dbh->prepare($sqlUn);
        $stmt->bindValue(':idnewsun',$ppk,PDO::PARAM_INT);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
          $commanditaire = $result['commanditaire'];
        }
        if (($commanditaire != $_SESSION['mail']) && (!($this->droits & ADMIN))) {
          $this->vue->retour = NEWS_SENDER_ONLY;
          $this->vue->setFile('pied','pied.tpl');           
          $out = $this->vue->render('page');
          $this->output($out);
        }      
        elseif (($flag == 'oui') || ($flag == 'non')) {  
          $sqlDeux = "UPDATE ".T_NEWS." SET active = :act WHERE idnews = :idnews";
          $stmt2 = $dbh->prepare($sqlDeux);
          $stmt2->bindValue(':idnews',$ppk,PDO::PARAM_INT);
          $stmt2->bindValue(':act',$flag,PDO::PARAM_STR);   
          if ($ok = $stmt2->execute())  { 
            $loc = "Location: ".BASEURL."index.php?post=news&token=".$this->token;    
            header($loc); 
          }
          else { 
            $msg = NEWS_NO_SEND ;  
            $this->globalEntities();     
            $this->vue->retour = $msg;
            $this->vue->setFile('pied','pied.tpl');           
            $out = $this->vue->render('page');
            $this->output($out);
          }
        }
        elseif ($flag == 'del') {
          $sql = "DELETE FROM ".T_NEWS_ANT." WHERE lienews=:news";
          $stmt = $dbh->prepare($sql);
          $stmt->bindParam(':news',$ppk,PDO::PARAM_INT);
          $stmt->execute();
          if ($ok = $this->modele->supprimeUneLigne($ppk,$this->table)) { $loc = "Location: ".BASEURL."index.php?del=news&token=".$this->token;  }
          else {$loc = "Location: ".BASEURL."index.php?delko=news&token=".$this->token;  }
          header($loc);
        } 
      } 
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  } 
  /**
  * La methode qui affiche un tableau de sujets de news depuis la database
  */  
  public function fetchNews()
  {      
    try
    {
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      } 
      elseif (! ($this->droits & RESPONSABLE)){ $this->vue->retour =  MBR_NO_ACTION; }
      elseif ($redac = $this->filtre->lockMoulin()) { $this->vue->retour =  NEWS_WORK.$redac; }

      if (empty($this->vue->retour)) { 
        $action = 'fetch';        
        $this->vue->contenu =  $this->gestionVue('fetch');     
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
  /**
  * La methode qui edite une news enregistrée mais non encore diffusée 
  */
  public function edition()
  {
    try { 
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & RESPONSABLE)){
        $this->vue->retour =   MBR_NO_ACTION;
      }       
      elseif ($cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT )) {
        $col = array('envoye'); $data=array() ;
        $data = $this->modele->ligneParValeurs($col,$this->table,'idnews',$cle);
        $envoye = $data['envoye']; 
        if ($envoye > 0){ $this->vue->contenu = NEWS_ALREADY_SEND;}
        else { 
          $data = $this->modele->selection($cle);  
          $tableauVue = $data[$this->table]; //translation de $data en array à 1 dimmension             
          $this->vue->contenu = $this->gestionVue('edition',$tableauVue); 
          //effacer la news de la table principale et de destinations d'antennes
          $this->modele->supprimeUneLigne($cle,$this->table);
          $sql = "DELETE FROM ".T_NEWS_ANT." WHERE lienews = :cle";
          $dbh = $this->modele->getCnx();
          $stmt = $dbh->prepare($sql);
          $stmt->execute(array(':cle'=>$cle));
        }
      }
      else { throw new MyPhpException('le parametre: line dans le contexte:'.$this->contexte.' est non valide'); } 
      $this->vue->setFile("pied","pied.tpl");
      $out = $this->vue->render("page");
      $this->output($out);
    } 
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  } 
  
  /**
  *  La methode qui gère la desinscription d'un membre à la news
  */
  public function desinscrire()
  {
    try {         
      $this->globalEntities();  
      $newsToken = '02gkei395zpgl478';     
      if (filter_has_var(INPUT_GET,'outnews')) { 
        $mailOut = urldecode($_GET['outnews']); 
        if ($this->filtre->verifmail($mailOut)) {
          if (empty($_GET['newstoken'])) { 
            $this->vue->retour = 
            '<a href="'.$this->urlExit.'outnews='.$_GET['outnews'].'&amp;newstoken='.$newsToken.'">'.NEWS_ONSUB_CONFIRM.'</a><br />';
          }
          elseif ((!empty($_GET['newstoken'])) && ($_GET['newstoken'] == $newsToken )) {
            $sql = "UPDATE ".T_CORE." SET news = 'non' "
			            ." WHERE  lienperso = (SELECT idhum FROM ".T_HUM." WHERE mail = :mailOut ) ";
            $dbh = $this->modele->getCnx();
            $stmt = $dbh->prepare($sql);
            if ( $stmt->execute(array(':mailOut'=>$mailOut)) ) {
              $this->vue->retour = NEWS_ONSUB_OK.'<br /> <a href="'.$_SERVER["SERVER_NAME"].'">'.BACK2SITE.'</a>';
            }
            else { throw new MyPhpException('Newsletter-desinscrire : echec'); }
          }
          $this->vue->setFile("pied","pied.tpl");
          $out = $this->vue->render("page");
          $this->output($out);
        }
        else { throw new MyPhpException('Newsletter-desinscrire : mail fourni non valide'); }
      }
      else {
        $loc =  "Location: ".BASEURL;
        header($loc);
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
  }
     
  /**
  * index : le centre des actions liés à la newsletter
  */
  public function index()
  {
    try { 
      if (!empty($_GET['token'])) { $token = $_GET['token']; }
      else { $token = NULL; }
      $this->globalEntities();      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & ADMIN)){
        $this->vue->retour = MBR_NO_ACTION;
      }
      else { 
        //evaluation de l'etat du moulin à News:
        $sql = "SELECT commanditaire FROM ".T_NEWS." WHERE active='oui'";
        $dbh = $this->modele->getCnx();
        $sth = $dbh->query($sql);
        $rslt = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($rslt['commanditaire'])) {
          $this->vue->alert = '<p style="color:red;">'.NEWS_WORK.$rslt['commanditaire'].'</p>'; 
        }
        else { $this->vue->alert = ''; }
        //calcul de l'url de l'interface de redaction        
        $this->vue->poster_news = BASEURL."index.php?ctrl=news&amp;action=ecrire&amp;token=".$token ;
        $this->vue->setFile('contenu','index_News.tpl');
      }
      $this->vue->setFile('pied','pied.tpl');
      $page = $this->vue->render("page");
      $this->output($page);
    }
    catch(MyPhpException $e) {
     $msg = $e->getMessage();
     $e->alerte($msg);  
   }          
  }//fin de methode
  /**
  * la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
  */
  private function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  } 
}//fin de class

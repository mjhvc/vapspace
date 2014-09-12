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
*    reportez-vous à la GNU General Public License.                           *                                       *
******************************************************************************** 
*/

/**
* Classe IndexCtrl, gère l'espace administration VAP pour responsables et admins
* ou redirige vers l'espace membre pour les membres ou vers l'identification (AuthCtrl.php)  
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include("Controleur.class.php");

class IndexCtrl extends Controleur
{ 
  private $statut;
  private $droits; 
  private $allAntennes;
  private $selectant;
  /**
  * Constructeur minimum : pas de contexte, donc appel d'un modele sans charger de contexte
  */ 
  public function __construct()
  {
    parent::__construct(); 
    $this->statut = $this->getStatut();
    $this->droits = $this->getDroits();
  }
  /**
  * Des entités de la vue du contexte à appeler à chaque fois
  * l'entite index_admin est dans le pied.tpl et reference l'index de ce contexte-ci
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head = 'Vap';
    $this->vue->titre_page = "VAP";
    $this->vue->vapspace = $this->url_vapspace.$this->token; 
    $this->vue->index_admin = '';  
  }
   /**
  * l'action qui vide les caches contextes et recharge le contexte de ce controleur
  */
  private function resetContexte()
  {
    $new = array();    
    if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
    else { $token = NULL; }
    if (! $ok = $this->validSess($token)){
      $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
      header($loc);
    } 
    elseif (! ($this->droits & ADMIN)){
      return ; 
    }
    else {
      $new = $this->modele->resetContexte('Membre',$this->statut); 
      return ;    
    }    
  } 
  private function staTraj()
  {
    $result = array();  
    $sql = "SELECT COUNT(idtraj) as cpTraj, SUM(compta) as cptExch FROM ".T_ROUTE;
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); 
    return $result;
  }           
   
/**
* Affiche l'index general de tout le site VAP
* index attend un $_GET['token'] valide sinon, redirection
*/
  public function index()
  {  
    $tabAntenne = $menuInscrire = $menuGerer = $statis = array();
     
    if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
    elseif (!empty($this->token)) { $token = $this->token; }  
    else { $token = NULL; }
  
    if (empty($token)) { 
      $loc = "Location: ".BASEURL."index.php?ctrl=auth"; 
      header($loc);
    }    
    elseif (! $this->validSess($token)){ 
      $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko"; 
      header($loc);    
    }       
    elseif (!($this->droits & RESPONSABLE)) {
      $loc = "Location: ".BASEURL."index.php?ctrl=membre&token=".$token;
      header($loc);
    } 
    else {
      $out =  $outerror = '';      
      $this->globalEntities(); 
      $this->chargerModele('Membre',$this->statut);
      $this->statut = $this->getStatut();
      $this->droits = $this->getDroits();  
      $this->vue->connecte = $this->statutConnect($token); 
     
      // retour des actions administratives
      if (($this->droits & RESPONSABLE) || ($this->droits & ADMIN)) {
        if ($del = filter_input(INPUT_GET,'del',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#')))) { 
          $out = $del.ENT_DELETED; 
        } 
        elseif (filter_has_var(INPUT_GET,'in') && ($ant = filter_input(INPUT_GET,'in',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))){ 
          $out = $ant.ENT_SUBSCRIBED; 
        }
        elseif (filter_has_var(INPUT_GET,'instandby') && ($ant = filter_input(INPUT_GET,'instandby',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))){ 
          $out = $ant.ENT_STANDBY; 
        }
        elseif (filter_has_var(INPUT_GET,'up') && ( $ant = filter_input(INPUT_GET,'up',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) { 
          $out = $ant.SUBMIT_UPDATE; 
        }
        elseif (filter_has_var(INPUT_GET,'post') && ( $ant = filter_input(INPUT_GET,'post',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) { 
          $out = $ant.ENT_MAIL; 
        }
        elseif (filter_has_var(INPUT_GET,'load') && ( $ant = filter_input(INPUT_GET,'load',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) { 
          $out = $ant.ENT_DOWNL; 
        }
        elseif (filter_has_var(INPUT_GET,'nocache') && ( $ant = filter_input(INPUT_GET,'nocache',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $outerror = ENT_CACHERROR.$ant ; 
        }
        elseif (filter_has_var(INPUT_GET,'delko') && ( $ant = filter_input(INPUT_GET,'delko',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $outerror = ENT_DELERROR.$ant ; 
        }
        elseif ((filter_has_var(INPUT_GET,'reset') && ($ant = filter_input(INPUT_GET,'reset',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^on$#')))))) {
          $this->resetContexte();
        }
      }
      // chargement du menu de contexte Index selon les droits (qui sont cumulatifs)
      if (!empty($out)) { $this->vue->retour = $out; }
      elseif(!empty($outerror)) { $this->vue->retour = '<span style="color:red">'.$outerror.'</span>'; }    
      
      //Creer les menus      
      if($this->droits & RESPONSABLE) {
        $this->allRegions = $this->valeursPassive(2,NULL,'Antenne');
        $this->allRegions[0] = "Globales";
        $urlInscrireMbr = BASEURL."index.php?ctrl=membre&amp;action=inscrire";
        $urlInscrireMbrSans = BASEURL."index.php?ctrl=membre&amp;action=inscrire&amp;membre=sans";
        $urlGererMbr = BASEURL."index.php?ctrl=membre&amp;action=gerer&amp;token=".$token;
        $urlGererFile = BASEURL."index.php?ctrl=file&amp;action=voir&amp;token=".$token;
        $urlInscrireLieu = BASEURL."index.php?ctrl=pass&amp;action=inscrire&amp;token=".$token;
        $urlContNews = BASEURL.'index.php?ctrl=news&amp;action=ecrire&amp;token='.$token;        
        $urlGererNews = BASEURL."index.php?ctrl=news&amp;action=fetchNews&amp;token=".$token;
        $urlMbrStats = BASEURL."index.php?ctrl=membre&amp;action=stats&amp;recalcul=oui&amp;token=".$token;       

        $menuInscrire[] = array('url'=>$urlInscrireMbr,'act'=>ENT_MBR,'aut'=>RESPONSABLE);
        $menuInscrire[] = array('url'=>$urlInscrireMbrSans,'act'=>ENT_MBR_WITHOUT,'aut'=>RESPONSABLE);
        $menuInscrire[] = array('url'=>$urlInscrireLieu,'act'=>ENT_LOC,'aut'=>RESPONSABLE);
        $menuInscrire[] = array('url'=>$urlContNews,'act'=>ENT_NEWS,'aut'=>RESPONSABLE);        
        $menuGerer[] = array('url'=>$urlGererMbr,'act'=>ENT_MBRS,'aut'=>RESPONSABLE);
        $menuGerer[] = array('url'=>$urlGererFile,'act'=>ENT_FILES,'aut'=>RESPONSABLE);
        $menuGerer[] = array('url'=>$urlGererNews,'act'=>ENT_NEWS_STANDBY,'aut'=>RESPONSABLE); 
        $this->vue->teleFile = ''; 
        $this->vue->selectAntenne = $this->calculChampPassif('antenne',$_SESSION['nomantenne']);
        $this->vue->selectRegions = $this->getSelect('regstat',$this->allRegions);
        $this->vue->gerer_stat = $urlMbrStats;      
        $this->vue->stat_region = ENT_STATS_REG;
      }
      if($this->droits & ADMIN) {
        $this->allAntennes = $this->valeursPassive(1);
        $urlContAnt = BASEURL.'index.php?ctrl=antenne&amp;token='.$token;
        $urlInscrireAnt = BASEURL."index.php?ctrl=antenne&amp;action=inscrire&amp;token=".$token;
        $urlInscrireReg = BASEURL."index.php?ctrl=region&amp;action=inscrire&amp;token=".$token;
        $urlInscrireSpons = BASEURL."index.php?ctrl=sponsor&amp;action=inscrire&amp;token=".$token;
        $urlGererAnt =  BASEURL."index.php?ctrl=antenne&amp;action=voir&amp;token=".$token;        
        $urlContReg = BASEURL.'index.php?ctrl=region&amp;action=voir&amp;token='.$token;
        $urlContTrans = BASEURL.'index.php?ctrl=sponsor&amp;action=voir&amp;token='.$token;
        $urlChatMens = BASEURL."index.php?ctrl=chat&amp;action=voirMens&amp;token=".$token;
        
       
        $menuInscrire[] = array('url'=>$urlInscrireAnt,'act'=>ENT_ANT,'aut'=>ADMIN);
        $menuInscrire[] = array('url'=>$urlInscrireReg,'act'=>ENT_REGIO,'aut'=>ADMIN);
        $menuInscrire[] = array('url'=>$urlInscrireSpons,'act'=>ENT_SOCIETY,'aut'=>ADMIN);
        $menuGerer[] = array('url'=>$urlChatMens,'act'=>ENT_CHATS_LAST,'aut'=>RESPONSABLE);        
        $menuGerer[] = array('url'=>$urlGererAnt,'act'=>ENT_ANTS,'aut'=>ADMIN);
        $menuGerer[] = array('url'=>$urlContReg,'act'=>ENT_REGIOS,'aut'=>ADMIN);
        $menuGerer[] = array('url'=>$urlContTrans,'act'=>ENT_SOCIETIES,'aut'=>ADMIN);
        $this->vue->setFile("teleFile",'index_File.tpl') ;
        $this->vue->url_file = BASEURL."index.php?ctrl=file&amp;token=".$token; 
        $this->vue->selectAntenne = $this->getSelect('antenne',$this->allAntennes);
        
        $this->vue->url_ctxt = BASEURL.'index.php?ctrl=index&amp;reset=on&amp;token='.$token;
        $this->vue->ctxt = ENT_CTXT;
        $this->vue->url_cachAnt = BASEURL.'index.php?ctrl=antenne&amp;action=resetCache&amp;token='.$token;
        $this->vue->cachant = ENT_CACHANT;
        $statis = $this->staTraj(); //appel de methode privée de statistique trajet
        $this->vue->nbr_traj = $statis["cpTraj"];
        $this->vue->traj_exch = $statis["cptExch"];
        
       
      }      
      $this->vue->gerer_lieu =  BASEURL."index.php?ctrl=pass&amp;action=voir&amp;token=".$token;
      $this->vue->iter_meet_ant = ITER_MEET; $this->vue->iter_meet = MBR_ADMIN;
      
      //afficher les menus
      $this->vue->setFile("contenu","index.tpl"); 
      $this->vue->setBlock("contenu","menuInscrire","cont");
      foreach ($menuInscrire as $key=>$contain) {
        if ($contain['aut'] & $this->droits) {
          $this->vue->url_ins = $contain['url'];
          $this->vue->nom_ins = $contain['act'];
          $this->vue->append('cont','menuInscrire');      
        }
      }
      $this->vue->setBlock("contenu","menuGerer","gerer");
      foreach ($menuGerer as $key=>$contain) {
        if ($contain['aut'] & $this->droits) {
          $this->vue->url_ger = $contain['url'];
          $this->vue->nom_ger = $contain['act'];
          $this->vue->append('gerer','menuGerer');      
        }
      }
    }              
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    echo $out;
    return;
  }//fin de methode
  public function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}  //fin de class 
  

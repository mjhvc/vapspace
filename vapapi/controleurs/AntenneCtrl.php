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
* Class AntenneCtrl gestion du contexte antenne.ini table: T_ANT
* @category vapsapce
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require_once("Controleur.class.php");

class AntenneCtrl extends Controleur
{ 
  private $contexte;
  private $statut;
  private $droits;
  private $action;
  private $table;
  private $tabRegions = array();
  private $tabTables = array(); 
  private $tabAntenne = array();
  private $tabindex;
  private $urlPages;
  private $valppk;
 
/**
* methode fille __construct()  necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appell de parent:: __construct($contexte) qui charge statut,modele et vue selon session et contexte 
 */
  public function __construct()
  {
    $this->contexte = "Antenne";
    parent::__construct($this->contexte);
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();     
    $this->tabTables = $this->modele->getTables();
    $this->table = $this->tabTables[0];
    $this->tabAntenne = $this->valeursPassive(1,NULL,'Chat'); 
    $this->action = '';
    $this->tabindex = array(); 
    $this->champPassif = 'lienreg';
    $this->idxPassif = 2; 
    $this->tabRegions = $this->valeursPassive($this->idxPassif);
    $this->urlPages = BASEURL."index.php?ctrl=antenne&amp;action=voir&amp;token=".$this->token."&amp;page=";
    $this->valppk = '';  
  }
/**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_admin est dans pied.tpl
  */ 
  private function globalEntities($region=NULL)
  {
    if (empty($region)) { $titre =  ANT_TIT.$_SESSION['province']; }
    elseif (array_key_exists($region,$this->tabRegions)) { $titre = ANT_TIT.$this->tabRegions[$region]; }      
    else { $titre = ANT_S_TIT; }    
    $this->vue->titre_head =  $titre ;
    $this->vue->titre_page =  $titre ;
    $this->vue->vapspace =   $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token);      
    if (! ((int)$this->droits & RESPONSABLE)){ $this->vue->index_admin = ''; }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin = MBR_ADMIN;
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
  * controler() surcharge de parent::controler
  * appel de parent::controler() puis fait des controles specifiques au contexte
  * retourne $this->vue->retour
  */ 
  protected function controler($tableau)
  {     
    try {
      $ramasseError = array(); 
      $hidden = array('ppk','itoken');     
      $this->vue->retour = parent::controler($tableau);
      if (empty($this->vue->retour)){                  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){  
          $ramasseError[] = CONTROL_OBLIG.$testOblig;
        }        
        elseif (($this->action == 'insert') && ($ctldbl = $this->filtre->doublon('nomantenne',$tableau['nomantenne'],$this->table))) { 
          $ramasseError[] =  $tableau['nomantenne'].THIS_EXIST; 
        }
        elseif (($this->action == 'insert') && ($postCorrupt = $this->filtre->postInattendu($tableau))) {  
          throw new MyPhpException('formulaire du contexte:'.$this->contexte.' corrompu');
        }
        elseif (($this->action == 'update') && ($postCorrupt = $this->filtre->postInattendu($tableau,$hidden))) {
          throw new MyPhpException('formulaire du contexte: '.$this->contexte.' corrompu');
        }
        elseif (($this->action == 'update') && ($tableau['itoken'] != $this->token)) {
          throw new MyPhpException('jeton du formulaire du contexte: '.$this->contexte.' corrompu');
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
  /**
  * creerCacheAntenne() crée un cache de fichier par antenne (utilisé par le chat...)
  * utilise $this->valeursPassive(1,NULL,'Chat'); pour disposer de array('idant'=>'nomantenne')
  * le contexte passif de antenne est lui pris par les régions, d'ou l'appel au contexte chat
  */
  private function creerCacheAntenne()
  {
    $ok = $this->creerCache($this->tabAntenne);
    return $ok;
  }  
  /**
  * fonction qui vide le cache d'une seule antenne 
  */  
  private function viderUnCache($idant)
  {
	  $ok = $this->viderCache($idant,'./*');
    return $ok;
  }
  /*
  * Fonction qui supprime toute l'arborescence du cache 'antennes' et la recrée vide
  */
  public function resetCache()
  {
    try {     
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }  
      else { 
        if ($ok = $this->rstCache($this->tabAntenne)) { $loc = "Location: ".BASEURL."index.php?ctrl=index&up=cache&token=".$token; }
        else { $loc = "Location: ".BASEURL."index.php?ctrl=index&nocache=on&token=".$token; }
        header($loc); exit(0);
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
  }
          
  /**
  * les operations d'attribution à la vue pour construire la page d'inscrption et update d'antenne
  * utilise $this->constructEntity() et $this->calculPassive()  
  */
  private function vueAntenne($action,$valppk=NULL,$tableau=array())
  {
    $out = '';    
    $this->globalEntities();
    if ($action == 'insert'){ $this->vue->script_formu = BASEURL."index.php?ctrl=antenne&amp;action=inscrire&amp;token=".$this->token; }
    elseif ($action == 'update'){ $this->vue->script_formu = BASEURL."index.php?ctrl=antenne&amp;action=update"; }
    if (empty($tableau)) { $this->constructEntity(); }
    else { $this->constructEntity($tableau); }
    if (!empty($tableau['lienreg'])) { $this->vue->select = $this->calculPassive($this->champPassif,$this->idxPassif,'select',$tableau['lienreg']); }
    else { $this->vue->select = $this->calculPassive($this->champPassif,$this->idxPassif,'select'); }
    if ($action == 'insert'){ $this->vue->cachez = ""; }
    elseif ($action == 'update'){ 
      $this->vue->setFile("cachez","multi_Membre_ValPPK.tpl");      
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;      
    }
    $this->vue->setFile("contenu","inscrire_Antenne.tpl");  
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;        
  }
/** 
  * methode inscrire:
  * gère : un formulaire d'inscription à vide (si pas de $_POST) par parent::constructEntity()
  * gère : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire()
  * gère : cration du repertoire de cache specifique à l'antenne et resetContexte() 
  * gère : un re-affichage du formulaire rempli si erreurs du client par parent::constructEntity($_POST)
  * charge le template inscrire_Region.tpl
  * les erreurs clientes sont traitées, les erreurs d'ecriture lancent une exception
  */ 
  public function inscrire() {
    try {        
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }       
      elseif (! ($this->droits & ADMIN)){
        $out = MBR_NO_ACTION;
        $this->output($out); ;
      }
      else {      
        $this->action = 'insert';                    
        if (empty($_POST)) {  
          $page = $this->vueAntenne($this->action);
          $this->output($page);      
        }
        else {        
          $this->vue->retour = $this->controler($_POST);
          if (empty($this->vue->retour)){       
            if ($this->valppk = $this->modele->inscrire($_POST)){
              $this->modele->resetContexte($this->contexte,$this->statut);
              if (! $ok = $this->creerCacheAntenne()) {
                $loc = "Location: ".BASEURL."index.php?nocache=".$_POST['nomantenne']."&token=".$token;
              }
              else { $loc =  "Location: ".BASEURL."index.php?in=antenne&token=".$token; }
              header($loc);
            }
            else  { throw new MyPhpException('Impossible d\'insérer une antenne');}
          }          
          else {    
            $page = $this->vueAntenne($this->action,NULL,$_POST);
            $this->output($page);
          }      
        }
      }       
    } 
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
  * methode voir()
  * creation de la requete sql de selection du contexte par parent::sqlFactory('vue')
  * charge template voir_Region.tpl qui initie les url des methodes editer() et supprimer()
  */
  public function voir()
  {
    $sqlFull = array(); $noligne = 0;      
    try
    {
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      } 
      elseif (! ($this->droits & ADMIN)){
        $this->vue->retour =  MBR_NO_ACTION;
      }
      else {        
        //sql et pagination : preparation            
        $sqlFull = $this->sqlFactory();
        $nbrPages = $this->comptePages($sqlFull['pagin']);       
        if (isset($_GET['page'])) {
          $pageActuelle = intval($_GET['page']);
          if ($pageActuelle > $nbrPages) {
            $pageActuelle = $nbrPages;
          }
        } 
        else { $pageActuelle = 1 ; } 
        $rsql = $sqlFull['stdt'];  
        $dataPagin = $this->pagination($nbrPages,$pageActuelle,$this->urlPages,$rsql);
        $this->vue->pagination = $dataPagin['pages'] ;
        $fsql = $dataPagin['sql'];  

        //traitement sql
        $dbh = $this->modele->getCnx();         
        $stmt = $dbh->prepare($fsql);
        $stmt->execute();
        $compteur = $stmt->rowCount();      
        if ($compteur == 0){ $this->vue->retour = MBR_NO_RESULT; }
        else {
          $this->vue->setFile("contenu","voir_Antenne.tpl");
          $this->vue->setBlock("contenu","ligne","lignes");        
          while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            $classe = "A".(($noligne++)%2);
            $ppk = $result['idant'];
            $this->vue->cssline = $classe;
            $this->vue->nomantenne = $result["nomantenne"];
            $this->vue->editer  = BASEURL."index.php?ctrl=antenne&amp;action=editer&amp;line=".$ppk."&amp;token=".$token;
            $this->vue->supprimer = BASEURL."index.php?ctrl=antenne&amp;action=supprimer&amp;line=".$ppk."&amp;token=".$token; 
            $this->vue->append("lignes","ligne");
          }
        }
      }
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
  * la fonction qui edite 'une ligne' d'un contexte : 
  * utilise filter_input() pour controler la variable $_GET['line'] qui est attribuée ) $cle
  * utilise $this->modele->selection($cle) pour selectionner la ligne du contexte en base qui retourne array($tableau)
  * utilise $this->constructEntity($tableau) pour la sortie finale 
  * charge le template inscrire_Region.tpl avec l'entité 'hidden' activée
  */
  public function editer()
  {
    $tableauVue = $data =array();
    $page='';
    try { 
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & ADMIN)){
        $out =  MBR_NO_ACTION;
        $this->output($out); 
      }       
      else {
        $this->action = 'update';      
        if ($cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT ))
        {          
          $data = $this->modele->selection($cle);  
          $tableauVue = $data[$this->table]; //translation de $data en array à 1 dimmension        
          $page = $this->vueAntenne($this->action,$cle,$tableauVue);
          $this->output($page);
        }
        else { throw new MyPhpException('le parametre: line dans le contexte:'.$this->contexte.' est non valide');}
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
  * methode update()
  * recoit et filtre le parametre $_GET de la cle de ligne en base
  * appel $this->modele->mettreajour($tableau,$cle) qui réalise un update en base pour la ligne concernee 
  * si erreur du client suites aux filtres
  * retourne les donnees dans le template inscrire_Region.tpl avec donnees clientes et  entite hidden activée
  * les erreurs clientes sont traitées, les erreurs d'ecriture lancent une exception
  */
  public function update()
  {
    try {      
      if (filter_has_var(INPUT_POST,'itoken')) { $token = $_POST['itoken']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }      
      elseif (! ($this->droits & ADMIN)){
        $out =  MBR_NO_ACTION;
        $this->output($out); 
      }
      else {      
        $this->action = 'update';
        if ($cle = filter_input(INPUT_POST,'ppk',FILTER_VALIDATE_INT)){        
          $this->vue->retour = $this->controler($_POST);
          if ( !empty($this->vue->retour)){
            $page = $this->vueAntenne($this->action,$cle,$_POST); 
            $this->output($page);
          }
          else {  
            $oldLienreg = $this->filtre->getOld('lienreg','idant',$_POST['ppk'],$this->table);
            if (! $this->filtre->filtreClePPK($cle)){
              throw new MyPhpException('le parametre: ppk dans le contexte: '.$this->contexte.' est non valide');
            }            
            elseif ($oldLienreg != $_POST['lienreg']) { //la clé lienreg à changé, on adapte tous les membres
              $sql = "UPDATE ".T_HUM." SET lienreg = :lienreg WHERE "
                     . T_HUM.".idhum IN "
                     . " ( "
                     . "   SELECT idvap FROM ".T_CORE." WHERE "
                     . T_CORE.".lienant = :idant "
                     . "  ) ";                       
              $dbh = $this->modele->getCnx();
              $stmt = $dbh->prepare($sql);
              $stmt->execute(array(":lienreg"=>$_POST['lienreg'],":idant"=>$_POST['ppk']));
            } 
            if ($tabout = $this->modele->mettreajour($_POST,$cle)){
              $this->modele->resetContexte($this->contexte,$this->statut);
              $this->tabAntenne = $this->valeursPassive(1,NULL,'Chat'); 
              $this->viderUnCache($cle);
              if (! $ok = $this->creerCacheAntenne()) {
                $loc = "Location: ".BASEURL."index.php?nocache=".$_POST['nomantenne']."&token=".$token;
              }
              else { $loc =  "Location: ".BASEURL."index.php?up=antenne&token=".$token; }
              header($loc);exit(0);
            }                       
          }           
        }
        else { throw new MyPhpException('le parametre: ppk dans le contexte: '.$this->contexte.'est non valide'); }      
      } 
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
/**
* methode supprimer() appel $this-modele->supprimeUneLigne($cle,$table) qui supprime une ligne du contexte
* necessite la variable $_GET['line']
*/
  public function supprimer()
  {
    try { 
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }  
      elseif (! ($this->droits & ADMIN)){
        $out =  MBR_NO_ACTION;
        $this->output($out); ;  
      }
      else {           
        if ( $cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT )) {
          if ( $ok = $this->filtre->getOld('idvap','lienant',$cle,T_CORE,1)){ //protection referentielle             
            $loc = "Location: ".BASEURL."index.php?delko=antenne&token=".$token;
          }          
          elseif ($ok = $this->modele->supprimeUneLigne($cle,$this->table )){
            $this->viderUnCache($cle);
            $this->modele->resetContexte($this->contexte,$this->statut);
            $loc = "Location: ".BASEURL."index.php?del=antenne&token=".$token;  
          }
          header($loc);
        }
        else { throw new MyPhpException('le parametre: line dans le contexte: '.$this->contexte.' est non valide'); } 
      }        
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
/**
* methode index()
* charge tableau 2dimm $this->tabindex avec, pour chaque cle, un sous-tableau en 3 cles: ['url']['act']['aut'] 
* fait une correlation entre les lignes de tabindex et les droits pour afficher un index selon le statut du connecté.
* charge template index_Region.tpl avec l'entite block 'navigation'
* pour un INDEX GLOBAL, prévoir une classe globalCtrl.php avec une seule methode: index.php 
* qui listera tous les index des contextes. 
*/  
  public function index()
  {
    try {  
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }  
      else {
        if (filter_has_var(INPUT_GET,'region') && ($getIdReg = filter_input(INPUT_GET,'region',FILTER_VALIDATE_INT)) && (array_key_exists($getIdReg,$this->tabRegions))) {
          $idreg = $getIdReg;
          $province = $this->tabRegions[$idreg];        
          $this->globalEntities($idreg);
        }
        else {
          $idreg = $_SESSION['lienreg'];
          $province = $_SESSION['province'];
          $this->globalEntities();
        } 
        if ($this->droits & MEMBRE) {
          $cle_ant = intval($_SESSION['idant']);
          $tabAntenneParRegion = $this->valeursPassive(1,$idreg,'Chat');        
          $this->vue->region = $province;
          $this->vue->setFile('contenu','index_Antenne.tpl');
          $this->vue->setBlock('contenu','vapspaces','space');
          foreach ($tabAntenneParRegion as $id=>$val) {
            $this->vue->url_vapspace = BASEURL."index.php?ctrl=membre&amp;antenne=".$id."&amp;region=".$idreg."&amp;token=".$token;
            $this->vue->nom_vapspace = $val;      
            $this->vue->append('space','vapspaces');
          }
        }
        
        $this->vue->setFile("pied","pied.tpl"); 
        $out = $this->vue->render("page");
        echo $out;
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }       
  }
  /**
* la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
*/
  protected function output($data)
  {
    if (is_array($data)){ print_r($data); }    
    else { echo $data; }
  }
}



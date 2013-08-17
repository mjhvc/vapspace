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
* Classe abstraite ABS_MembreCtrl, gère le Contexte Membre.ini 
* tables dynamiques: T_HUM, T_CORE
* table passive : T_ANT
* table statique: T_SOC
* table de liaison : T_HUM_SOC
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include("Controleur.class.php");
include('StaticMembre.class.php');
abstract class ABS_Membre extends Controleur
{ 
  protected $action;  
  protected $allAntennes = array();
  public $champPassif;  
  protected $cleha;
  protected $nomha;
  protected $contexte;
  protected $dataContexte;
  protected $filtreRegion;  
  public $idxPassif;  
  protected $liaisonTable;
  protected $oldMbr; protected $oldIdhum; protected $oldMail; 
  protected $oldPass = array();
  protected $statut;
  protected $droits; 
  protected $phase;
  protected $promoMail = array();
  protected $promoText = array();
  protected $tabRegions = array();
  protected $tabTables = array(); 
  protected $tableUn; protected $tableDeux; 
  protected $tabCheck;
  protected $urlPages; 
  protected $o_reg; protected $o_ant; protected $o_nomant;
  protected $valppk;
  protected $ppk;
  protected $chat;
/**
* methode fille __construct()  
* necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appelle de parent:: __construct($contexte) qui charge 'le gros' 
 */
  public function __construct()
  {
    $this->action = '';     
    $this->contexte = "Membre";
    parent::__construct($this->contexte);  
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits(); 
    $this->dataContexte = array();
    $this->filtreRegion = (object) NULL;
    $this->oldMbr = $this->oldMail = $this->oldIdhum = '';
    $this->idxPassif = 1;     
    $this->liaisonTable = $this->modele->getLiaisonTable(); //si choix sponsor, ceci peut valoir null
    $this->tabTables = $this->modele->getTables();
    $this->tabRegions = $this->valeursPassive(2,NULL,'Antenne');
    $this->allAntennes = $this->valeursPassive($this->idxPassif);    
    $this->tabCheck = array('inscrit'=>array('pieton','auto','deux'),'securite'=>'oui','fiche'=>'oui','news'=>'oui','lang'=>array('fr','nl'));      
    $this->tableUn = $this->tabTables[0];
    $this->tableDeux = $this->tabTables[1];
    $this->champPassif = 'lienant';         
    $this->phase = '';
    // les formulaires de 'multi_Membre_recom.tpl' 
    $this->promoMail = array('promoUn','promoDeux','promoTrois');
    $this->promoText = array('promoMsg');         
    if ($this->o_reg = $this->modele->getGeneral('horsRegion')){ ;}
    else { $this->o_reg = NULL; }
    if ($this->o_ant = $this->modele->getGeneral('horsAntenne')) { $this->o_nomant = $this->allAntennes[$this->o_ant]; }
    else { $this->o_ant = NULL; $this->o_nomant = NULL; }
    $this->urlPages = BASEURL.'index.php?ctrl=membre&amp;action=voir&amp;page=';
    $this->valppk = '';
    $this->PPK = $this->modele->getPPK(); 
       
  }
  abstract protected function control($tableau,$lang); 
  abstract public function inscrire();
  abstract public function editer();
  abstract public function update();
  abstract public function index();
  abstract public function output($data);
/**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_admin est dans pied.tpl
  */ 
  protected function globalEntities($antenne=NULL) 
  {
    if (empty($_SESSION['nomantenne'])) { $titre = MBR_SUBSCRIBE; }   
    elseif (empty($antenne)) { $titre =  MBR_SPACE.$_SESSION['nomantenne']; }
    elseif(array_key_exists($antenne,$this->allAntennes)) { $titre = MBR_SPACE.$this->allAntennes[$antenne]; }
    else { throw new MyPhpException('CtrlMembre/globalEntities: paramètre antenne invalide si connecté'); } 
    $this->vue->titre_head =  $titre;
    $this->vue->titre_page = $titre;
    $this->vue->vapspace =  $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token);   
    if (! ((int)$this->droits & RESPONSABLE)){ 
      $this->vue->setFile('index_admin','pied_mbr.tpl');
      if (empty($this->token)) { $this->vue->urlDocu = BASEURL."index.php?ctrl=iter"; }  
      else { $this->vue->urlDocu = BASEURL."index.php?ctrl=iter&amp;token=".$this->token; }
    }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin = MBR_ADMIN;
    }      
    $this->vue->javascript = '<script type="text/javascript" src="js/scriptAjax2.js"></script>'."\n".
                             '<script type="text/javascript" src="js/scriptVap2.js"></script>';      
    return true;
  }
  /**
  *   Gestion de l'entité spam pour la vue
  */
  protected function vueSpam($statut)   
  {
    if ($statut == 'anonym') { $this->vue->setFile("spam","vuespam.tpl"); }
    else { $this->vue->spam = ''; }    
    return true;
  }
  /**
  * gestion de l'entite statut pour la vue
  * fichier tpl appellés : multi_Membre_statAdmin.tpl,multi_Membre_statResp.tpl 
  */
  protected function vueStatut($statut) 
  {
    if ($statut == 'admin'){  $this->vue->setFile("statuts","multi_Membre_statAdmin.tpl"); }
    elseif ($statut == 'responsable'){ $this->vue->setFile("statuts","multi_Membre_statResp.tpl"); }
    else {  $this->vue->statuts = ''; }
    return true;
  }
 
/**  * Appeler le template de recommandation par mail 
  * soit à l'inscription d'un ANONYM soit dans le vapspace (index) du membre
  */
  protected function vueRecommand($action='insert')
  {
    if (($this->statut == 'anonym') && ($action == 'insert')) { 
      $this->vue->setFile("multiRecomm","multi_Membre_recom.tpl");
      $this->vue->recom_size = 40; $this->vue->recom_max = 60;
      $this->vue->recom_rows = 2; $this->vue->recom_cols = 45;      
      $retour = 1;
    }
    else { 
      $this->vue->multiRecomm = '';
      $retour = false;
    }
    return $retour;
  }

  /**
  * recupData tentative de généralisation de la récupération de données selon:
  * @param: $numant, integer, une clé d'antene
  * @param $contxt, stg, un nom de contexte
  */  
  protected function recupData($numant,$contxt) 
  {
    $tabData = array(); $ok = NULL; $lignes = '';
    // données propres à chaque contexte 
    switch ($contxt) {
      case 'Passage' : $masq = '_noeud.csv'; $col = 4; break;
      case 'Chat' : $masq = '_chat.csv'; $col = 2; break;
    }
    // lecture d'un cache si, non cache, extraction depuis la database, création d'un cache et création du tableau de sortie adéquat
    if (! $tabData = $this->lisCache($numant,$masq,$col)) {
      switch ($contxt) {     
        case 'Passage' : $table = T_MEET; $champs = array('idpass','lieu','offre','demande'); $condi = " AND compta >= 1 ORDER BY compta DESC LIMIT 0,10 "; break;
        case 'Chat' : $table = T_CHAT; $champs = array('idchat','sujet');  $condi = " ORDER by poste DESC LIMIT 0,5 "; break;
      }
      $tabExtract = $this->modele->ligneParValeurs($champs,$table,'lienant',$numant,'all',$condi);
      if (! empty($tabExtract)) {    
        foreach ($tabExtract as $key=>$rslt) {
          $trainCache = ''; $trainData = array();
          foreach ($champs as $num=>$val) {
            ($num < ($col-1))? $sep = ',': $sep = "\n";  
            $trainCache .= '"'.$rslt[$val].'"'.$sep;
            $trainData[] = $rslt[$val];
          }
          $lignes .= $trainCache;
          $tabData[] = $trainData; 
        }
        $ok = $this->ecrisCache($numant,$masq,$lignes);
      } 
    }
    return $tabData;
  }
 
  /**
  * Surcharge de parent::constructEntity (cfr Constructeur.class.php)
  * ici, on prend en charge le sens base vers client pour l'aspect 'statique'
  * prise en charge dans ce contexte du flag 'statique'
  * flag atteste que les données viennent de la base et que l'on dans un contexte de 'liaison' de tables 
  * si le contexte indique une structure statique et 
  * la valeur de la cle de liaison fournie égale celle du contexte statique (calculée par IniData::calculDataStatique() )
  * on construit un tableau de correlation entre 
  *   -les valeurs stoquées dans la table de liaison et les valeurs  'statiques' à afficher en sortie (qui ici diffèrent par leur nom)
  * cette corrélation est stoquée dans $this->checkedForm 
  * les variables (array) : $this->valueForm, $this->sizeForm, $this->obligForm 
  * sont toujours calculés par appel de parent::constructEntity 
  * les attributions de $this->vue se font dans chaque methode fille. 
  */
  protected function constructEntity($tableau=array(),$table=NULL,$flgStat=NULL)
  {  
    try {
      parent::constructEntity($tableau,$table,$flgStat); 
      if(!empty($flgStat)) {  
        foreach ($this->dataStructure as $nom=>$option){ 
          if (isset($option['data']) && $tableau['lientrans'] == $option['valCleLiaison']) {  
            if ($tableau['utilisation'] == 'oui' && $tableau['abonne'] == 'oui'){
              $this->checkedForm[$nom]['ouiavec'] = "checked"; $this->checkedForm[$nom]['oui'] = "";
            }
            elseif ($tableau['utilisation'] == 'oui' && $tableau['abonne'] == 'non'){
              $this->checkedForm[$nom]['ouiavec'] = ""; $this->checkedForm[$nom]['oui'] = "checked";
            }
          }          
        }
      }       
      foreach ($this->valueForm as $name=>$value) { $this->vue->$name = $value; }
      foreach ($this->sizeForm as $size=>$value) {  $this->vue->$size = $value; }
      foreach ($this->obligForm as $obliga=>$aster) {   $this->vue->$obliga = $aster;  }
       //traiter ici l'exeption de la recommandation aux amis :
      foreach( $this->promoMail as $val) { 
        if (!empty($tableau[$val])) { $this->vue->$val = $tableau[$val]; }
        else { $this->vue->$val = ''; }
      }
      foreach( $this->promoText as $text) { 
        if (!empty($tableau[$text])) { $this->vue->$text = $tableau[$text]; }
        else { $this->vue->$text = ''; }
      }      
      return true;  
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
  * gestion des formulaires radio et checkbox pour la vue (pas les 'statiques traités dans self::vueStatique())
  * utilise $this->tabCheck: tableau des formulaires qui sont des radios ou checkbox (hors vueStatique) initiés dans constructeur
  * utilise $this->datacontexte: le tableau de la structure des données du contexte
  * utilise parent::constructEntity->valueForm: tableau construit par $this->constructentity et reliant les valeurs des données reçues aux noms de données possibles 
  *   -ceci suppose que $this->constructentity est appellé avant l'appel de $this->vueCheck
  * utilise parent::getChecked pour envoyer la valeur 'checked="checked"' à la vue
  * verifie via (parent)$this->valueForm (le tableau des donnees calculées fournies à la vue) si une valeur particuliere est presente 
  */
  protected function vueCheck() 
  {
    try {
      $clesCheck = array_keys($this->tabCheck);    
      $this->dataContexte = $this->modele->getDataContexte();   
      foreach ($this->dataContexte as $cle=>$val){
        $name = strval($cle);
        $check = $name."Check";      
        if (in_array($name,$clesCheck)){
          if ($cle == 'inscrit'){
            if (!empty($this->valueForm[$name])){          
              switch($this->valueForm[$name]){
                case('pieton') : $this->getChecked(true,'pietonCheck'); $this->getChecked(false,'autoCheck'); $this->getChecked(false,'deuxCheck');break;
                case('auto') : $this->getChecked(true,'autoCheck'); $this->getChecked(false,'pietonCheck'); $this->getChecked(false,'deuxCheck');break;
                case('deux') : $this->getChecked(true,'deuxCheck'); $this->getChecked(false,'pietonCheck'); $this->getChecked(false,'autoCheck');break;
              }
            }
            else { $this->getChecked(false,'deuxCheck');$this->getChecked(false,'pietonCheck');$this->getChecked(false,'autoCheck');}
          }
          elseif (($cle == 'fiche') || ($cle == 'news')){ //'fiche' et 'news' sont checkés par defaut
            if (empty($this->valueForm[$name])){ $this->getChecked(true,$check); }
            else {
              switch($this->valueForm[$name]){
                case('oui'):$this->getChecked(true,$check);break;
                case('non'):$this->getChecked(false,$check);break;
              }
            }
          }
          elseif ($cle == 'lang') {
            if (!empty($this->valueForm[$name])) {
              switch($this->valueForm[$name]){
                case('nl') : $this->getChecked(true,'nlCheck'); $this->getChecked(false,'frCheck');break;
                case('fr') : $this->getChecked(false,'nlCheck'); $this->getChecked(true,'frCheck');break;
              }
            }
            else { $this->getChecked(false,'nlCheck'); $this->getChecked(false,'frCheck'); }
          } 
          else {          
            if (! empty($this->valueForm[$name])){  $this->getChecked(true,$check); }
            else {  $this->getChecked(false,$check);}
          }
        }
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
    return true;
  } 
  /**
  * gestion de la vue pour donnees statiques selon la presence de [data] => statique dans le modele  
  * manipule le bloc entité 'sponsors' de inscrire_membre.tpl
  * utilise parent::getChecked
  * utilise $this->checkedForm, array formé dans this->constructentity()  
  * $this->checkedForm['nomChampStatique']['valStatique'] = 'checked' ou NULL
  */
  protected function vueStatique($drap = null)
  {
    $tabValStat = array();$cpt = 0;    
    $this->dataContexte = $this->modele->getDataContexte();
    $this->vue->setFile("multiChoice","multi_society_Membre.tpl");
    $this->vue->setBlock("multiChoice","sponsors","blocmob"); 
    foreach ($this->dataContexte as $nom=>$option){
      if(!empty($option['data'])){
        $cpt++;
        $tabValStat = $this->modele->getStatiqueValeurs();
        $name = strval($nom);   
        if (! $drap){ //peut importe la valeur de $drap
          $this->getChecked(false,'moboui');  
          $this->getChecked(false,'mobouiavec');  
        }
        elseif (!empty($this->checkedForm[$name])) {
          if ($this->checkedForm[$name]['oui'] == 'checked'){
            $this->getChecked(true,'moboui'); $this->getChecked(false,'mobouiavec');
          }
          elseif ($this->checkedForm[$name]['ouiavec']== 'checked'){
            $this->getChecked(true,'mobouiavec'); $this->getChecked(false,'moboui');
          }
          else {
            $this->getChecked(false,'mobouiavec'); $this->getChecked(false,'moboui');
          } 
        }
        else { 
          $this->getChecked(false,'moboui');  
          $this->getChecked(false,'mobouiavec');  
        }
        $this->vue->societes = $name;
        $this->vue->soc_temp = $name.'_temp';
        $this->vue->concatUno = $name.$tabValStat[0];
        $this->vue->concatDuo = $name.$tabValStat[1];
        $this->vue->valUno = $tabValStat[0];
        $this->vue->valDuo = $tabValStat[1];   
        $this->vue->append("blocmob","sponsors");
      }
    }
    if ($cpt == 0) { $this->vue->multiChoice = ''; }
    return true;  
  }
  //Récupere 3 donnees du membre selon la nouvelle clé
  protected function getOld($new) 
  {
    $retour = true; 
    $tables = $this->tableUn.','.$this->tableDeux;
    $sql = "SELECT idhum,mail,membre FROM $tables "
    ." WHERE ".T_CORE.".idvap = ".T_HUM.".idhum "
    ." AND ".T_HUM.".idhum = :new "; 
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(":new"=>$new));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (! ($this->oldIdhum =  $result['idhum'])) { $retour = false; }
    if (! ($this->oldMail = $result['mail'])) { $retour = false; }
    if (! ($this->oldMbr = $result['membre'])) { $retour = false; }
    return $retour;
  }
  /**
  * récupére (ou pas) un (ou plusieurs) passe cryptés associé(s) à un mail fourni
  */
  protected function getPass($mail)
  {
    $col = array('passe');    
    $data = $this->modele->ligneParValeurs($col,$this->tableUn,'mail',$mail,1);
    foreach ($data as $key=>$ligne) {
      $this->oldPass[] = $ligne['passe'];
    }  
    if (empty($this->oldPass)) { $this->oldPass[0] = 'empty'; }
    return $this->oldPass;
  }
  /**
  * effectue le contrôle de doublon entre un mot de passe et un mail pour action d'inscription et d'update
  */
  protected function ctlPass($mail,$secret,$action)
  {
    $oldPass = $this->getPass($mail);
    $retour = false; 
    foreach ($oldPass as $oPassCrypt) {
      if (($oldPass[0] == 'empty') && ($action == 'inscription')) { ; }
      else {
        $newCrypt = crypt($secret,$oPassCrypt);
        if ($newCrypt === $oPassCrypt) { 
          $retour = true; 
          break; 
        } 
      }
    }
    return $retour;
  }    
  /**
  * @parm $method :string, le nom de la superglobale de recuperation de $line
  * methode getPK : Récuperation de $_GET ou $_POST['line'] variable selon le statut
  * fait appel à $this->filtre->mbrInAnt($valcle,$idant) pour filtrer la valeur de $line
  */  
  protected function getPK($method) 
  {   
    $cleValide = NULL;  
    if ($this->statut == 'membre'){
      $max = $_SESSION['idhum'] ; $min = $_SESSION['idhum'] ;       
      if($method == 'GET') { $line = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT,array('options'=>array('min_range'=>$min,'max_range'=>$max))); }
      elseif($method == 'POST') { $line = filter_input(INPUT_POST,'ppk',FILTER_VALIDATE_INT,array('options'=>array('min_range'=>$min,'max_range'=>$max)));  }      
      $cleValide = $this->filtre->mbrInAnt($line,$_SESSION['idant']);        
    }
    elseif ($this->statut == 'responsable') { 
      if($method == 'GET') { $line = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT); }
      elseif($method == 'POST') { $line = filter_input(INPUT_POST,'ppk',FILTER_VALIDATE_INT); }
      $cleValide = $this->filtre->mbrInAnt($line,$_SESSION['idant']);
    }
    elseif ($this->statut == 'admin') { 
      if($method == 'GET') { $cleValide = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT); }
      elseif($method == 'POST') { $cleValide = filter_input(INPUT_POST,'ppk',FILTER_VALIDATE_INT); }
    }   
    return $cleValide;
  } 
  /**
  * simple appel de parent::controler()
  */
  protected function preCtrl($tableau)
  {
    try {
       $pre = parent::controler($tableau);
       return $pre;
    }   
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
  
 
  /**
  * La gestion de la vue du Centre de gestion des membres
  * l'entité HA devra s'adapter aux systèmes qui ne prennent pas en charge les ha...
  */  
  public function gerer() 
  {
    try {
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }      
      elseif (! ($this->droits & RESPONSABLE)){ $this->vue->retour = MBR_NO_ACTION; }
      else { 
        $this->vue->url_extract = BASEURL.'index.php?ctrl=file&amp;action=extract&amp;token='.$token;
        if ($this->droits & ADMIN){       
          $this->vue->selectAntenne = $this->calculPassive($this->champPassif,$this->idxPassif,'select');
          $this->vue->setFile("HA","gerer_Membre-HA.tpl");          
          $this->vue->url_ha = BASEURL."index.php?ctrl=membre&amp;action=voir&amp;class=ha";
          $this->vue->url_resp = BASEURL."index.php?ctrl=membre&amp;action=voir&amp;class=resp";          
          $this->vue->url_mv = BASEURL."index.php?ctrl=membre&amp;action=mv&amp;token=".$token;          
          $this->vue->antenneUn = $this->calculPassive('antFrom',$this->idxPassif,'select');
          $this->vue->antenneDeux = $this->calculPassive('antTo',$this->idxPassif,'select');
        }
        else {
          $this->vue->HA = "";
          $this->vue->selectAntenne = $this->calculChampPassif('lienant',$_SESSION['nomantenne']);
        } 
        $this->vue->url_voir = BASEURL."index.php?ctrl=membre&amp;action=voir&amp;"; 
        $this->vue->setFile("contenu","gerer_Membre.tpl");
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
  * methode mv() deplace les membres d'antenne à antenne
  * utilise $this->modele->ligneParValeurs() pour selectionner des donnees hors-contexte
  * $sqlUn met à jour la clé de l'antenne du membre, 
  * $qlDeux met à jour la clé de region du membre sauf si l'antenne est 19:Hors-Antenne
  * car cette antenne est la seule à avoir des membres de plusieurs provinces 
  */
  public function mv() // est commun
  {
    $regTo = array();    
    try {
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }      
      elseif (! ($this->droits & ADMIN)){ $this->vue->retour = MBR_NO_ACTION; }
      elseif (filter_has_var(INPUT_POST,'antFrom')) {
        $getAntFrom = filter_input(INPUT_POST,'antFrom',FILTER_VALIDATE_INT); 
        if (array_key_exists($getAntFrom,$this->allAntennes) && ($getAntTo = filter_input(INPUT_POST,'antTo',FILTER_VALIDATE_INT))) {     
          $regTo =  $this->modele->ligneParValeurs(array('lienreg'),T_ANT,'idant',$getAntTo);     
          $sqlUn = "UPDATE spip_vap_core SET lienant = :to "
                . " WHERE lienant = :from ";
          $sqlDeux = "UPDATE ".T_HUM." SET lienreg = :newreg "
                . " WHERE ".T_HUM.".idhum IN "
                . " ( "
                . "   SELECT idvap FROM ".T_CORE
                . "   WHERE ".T_CORE.".lienant = :idant "
                . " ) ";                       
          $dbh = $this->modele->getCnx();
          $stmt = $dbh->prepare($sqlUn);
          $stmt->execute(array(':to'=>$getAntTo,':from'=>$getAntFrom));
          if ($getAntTo != $this->o_ant) {
            $stmt = $dbh->prepare($sqlDeux);
            $stmt->execute(array(':newreg'=>$regTo['lienreg'],':idant'=>$getAntTo));
          }
          $loc = "Location: ".BASEURL."index.php?up=antenne&token=".$token;
          header($loc);
        }        
        else { throw new MyPhpException("contexte Membre methode mv(): clé de l'antenne non valide"); } 
      }
      else { $this->vue->retour .= MBR_NO_ACTION ; } 
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
  * methode voir() : creation et affichage de la requete sql ad hoc pour voir un membre
  * traitement sql:  
  * -Reçoit par $_POST les conditions de selections choisies  par $this->gerer()
  * -Construit les conditions (WHERE) via StaticMembre::sqlMbr, (application/fonction)
  * -Envoi le tout à parent::sqlFactory() qui construit le SELECT et le FROM via le modèle 
  * Calcule la Pagination via $this->comptePages et parent::pagination()
  * utilise parent::visionTitleEntity() pour construire la vue des titres des colonnes 
  * utilise parent::visionEntity() pour afficher les bonnes colonnes de la base   
  * charge template voirMembre.tpl qui initie les url des methodes editer() et supprimer()
  */
  public function voir() //protected car est commun
  {
    try {
      $noligne = $y = $passiFrom = 0; $sql =  $queue =  '';
      $tabCondi =  $sqlFull = $dataPagin =  $tabMarq = array();
      $contenu = "resp";         
      if (filter_has_var(INPUT_POST,'lienant') && ($valAntenne = filter_input(INPUT_POST,'lienant',FILTER_VALIDATE_INT))) {
        $this->globalEntities($valAntenne); 
      }
      elseif (filter_has_var(INPUT_GET,'lienant') && ($valAntenne = filter_input(INPUT_GET,'lienant',FILTER_VALIDATE_INT))) {
        $this->globalEntities($valAntenne);
      }   
      else { $this->globalEntities(); }
      if (! ($this->droits & MEMBRE)){ $this->vue->retour =  MBR_NO_ACTION ; }
      elseif (!(filter_has_var(INPUT_GET,'class') && ($class = filter_input(INPUT_GET,'class',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#')))))) {
        throw new MyPhpException('Membre/voir: var class invalide'); 
      }  
      else { 
        $this->visionTitleEntity();  
        if ($class == 'glb'){
          if (!empty($valAntenne)){ ;}          
          elseif (empty($valAntenne) && isset($_SESSION['tabMarq']['lienant'])) { $valAntenne = $_SESSION['tabMarq']['lienant']; }
          else { throw new MyPhpException('CtrlMembre/voir/glb: une clé d\'antenne est obligatoire'); }
          $tabMarq = array("lienant"=>$valAntenne);
          $tabCondi = array(T_CORE.".lienant = :lienant",T_CORE.".fiche = 'oui'");
          $queue = " ORDER BY nom ";
          $contenu = "mbr"; 
        }  //FIN POUR LES MEMBRES
        if (($this->droits & RESPONSABLE)) {               
          if ($class == 'ant'){  
            if (filter_has_var(INPUT_POST,'lienant')) { $valAntenne = filter_input(INPUT_POST,'lienant',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valAntenne = $_SESSION['tabMarq']['lienant']; }           
            $tabMarq = array("lienant"=>$valAntenne);
            $tabCondi = array(T_CORE.".lienant = :lienant");
            $queue = " ORDER BY nom ";
          }
          elseif ($class == 'numbr'){ 
            if (filter_has_var(INPUT_POST,'lienant')) { $valAntenne = filter_input(INPUT_POST,'lienant',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valAntenne = $_SESSION['tabMarq']['lienant']; }
            $tabMarq = array("lienant"=>$valAntenne);
            $tabCondi = array(T_CORE.".lienant = :lienant");
            $queue = " ORDER BY numbr ASC ";
          }
          elseif ($class == 'numbrinv'){  
            if (filter_has_var(INPUT_POST,'lienant')) { $valAntenne = filter_input(INPUT_POST,'lienant',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valAntenne = $_SESSION['tabMarq']['lienant']; }
            $tabMarq = array("lienant"=>$valAntenne);
            $tabCondi = array(T_CORE.".lienant = :lienant");
            $queue = " ORDER BY numbr DESC ";             
          }
          elseif ($class == 'new'){ 
            if ($this->statut == 'responsable') {
              $valAntenne = $_SESSION['idant']; 
              $tabMarq = array("lienant"=>$valAntenne);
              $tabCondi = array(T_CORE.".lienant = :lienant",T_CORE.".membre = '".NEWMBR."'");                   
            }
            else { $tabCondi = array(T_CORE.".membre = '".NEWMBR."'"); }
            $queue = " ORDER BY nom "; 
          }
          elseif ($class == 'nom'){
            if (filter_has_var(INPUT_POST,'MbrNom')) { $valNom = filter_input(INPUT_POST,'MbrNom',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valNom = $_SESSION['tabMarq']['nom']; }
            if ($this->statut == 'responsable') {
              $valAntenne = $_SESSION['idant']; 
              $tabMarq = array("lienant"=>$valAntenne,"nom"=>$valNom);
              $tabCondi = array(T_CORE.".lienant = :lienant",T_HUM.".nom LIKE :nom");
            }
            else { 
              $tabCondi = array(T_HUM.".nom LIKE :nom"); 
              $tabMarq = array("nom"=>$valNom);
            }
            $queue = " ORDER BY nom ";
          }
          elseif ($class == 'numero'){
            if (filter_has_var(INPUT_POST,'MbrParNum')) { $valMembre = filter_input(INPUT_POST,'MbrParNum',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valMembre = $_SESSION['tabMarq']['membre']; }
            if ($this->statut == 'responsable') {
              $valAntenne = $_SESSION['idant']; 
              $tabMarq = array("lienant"=>$valAntenne,"membre"=>$valMembre);
              $tabCondi = array(T_CORE.".lienant = :lienant",T_CORE.".membre = :membre");
            }
            else { 
              $tabCondi = array(T_CORE.".membre = :membre"); 
              $tabMarq = array("membre"=>$valMembre);
            }
          }
          elseif ($class == 'date'){
            if (filter_has_var(INPUT_POST,'MbrParDate')) { $valDate = filter_input(INPUT_POST,'MbrParDate',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else { $valDate = $_SESSION['tabMarq']['cachet']; }                
            if ($badate = $this->filtre->filtreDate($valDate)) { $this->vue->retour = $badate; }
            else {
              if ($this->statut == 'responsable') {
                $valAntenne = $_SESSION['idant']; 
                $tabMarq = array("lienant"=>$valAntenne,"cachet"=>$valDate);
                $tabCondi = array(T_CORE.".lienant = :lienant",T_CORE.".cachet BETWEEN :cachet AND NOW()");
              }
              else { 
                $tabCondi = array(T_CORE.".cachet BETWEEN :cachet AND NOW()"); 
                $tabMarq = array("cachet"=>$valDate);
              }
              $queue = " ORDER BY cachet ";                  
            }
          }
          elseif ($class == 'courrier') {
            if (filter_has_var(INPUT_POST,'mailMbr')) { $valMail = filter_input(INPUT_POST,'mailMbr',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            if ( ! $oldMail = $this->filtre->getOld('mail','mail',$valMail,$this->tableUn)) { $this->vue->retour = 'Mail non trouvé'; }
            else {
              $tabMarq = array("mail"=>$oldMail);
              $tabCondi = array(T_HUM.".mail = :mail");
            }
          }
        } //fin pour les RESPONSABLES
        if (($this->droits & ADMIN)) { 
          if ($class == 'ha'){ 
            if (filter_has_var(INPUT_POST,'MbrHorsAnt')) { $valMembre = filter_input(INPUT_POST,'MbrHorsAnt',FILTER_SANITIZE_SPECIAL_CHARS) ; }
            else  { $valMembre = $_SESSION['tabMarq']['ha']; }
            $tabMarq = array("lienant"=>$this->o_ant,"ha"=>$valMembre);
            $tabCondi = array(T_CORE.".lienant=:lienant",T_CORE.".membre LIKE :ha");
            $queue = " ORDER BY numbr ";              
          }
          elseif ($class == 'resp'){    
            $tabCondi = array(T_HUM.".statut = 'responsable'");
            $queue = " ORDER BY nom ";
            $contenu = "resp";
          }
        }
        if (($class != 'glb') && (!($this->droits & RESPONSABLE))) { throw new MyPhpException('Membre/Voir/class invalide'); }    
      } //conditions pour requetes construites        
      if (empty($this->vue->retour)) { 
        if (!empty($tabMarq)) { 
          $_SESSION['tabMarq'] = $tabMarq;  
          session_write_close();
        } // construire requete sql et recuperation de la requete['stdt'] et ['pagin']         
        $wheresql_stdt = $wheresql_pgin = StaticMembre::sqlMbr($tabCondi,$queue);            
        if (!empty($passiFrom)) { $sqlFull = $this->sqlFactory($wheresql_stdt,$wheresql_pgin,$passiFrom); }
        elseif ($class == 'glb') { $sqlFull = $this->sqlFactory($wheresql_stdt,$wheresql_pgin,NULL,1); }     
        else { $sqlFull = $this->sqlFactory($wheresql_stdt,$wheresql_pgin); }

        //pagination: recuperer le nombre de pages, recuperer page actuelle et elaboration de la pagination
        if (!empty($tabMarq)) { $nbrPages = $this->comptePages($sqlFull['pagin'],$tabMarq); }
        else { $nbrPages = $this->comptePages($sqlFull['pagin']); }     
        if (isset($_GET['page'])) {
          $pageActuelle = intval($_GET['page']);
          if ($pageActuelle > $nbrPages) {
            $pageActuelle = $nbrPages;
          }
        } 
        else { $pageActuelle = 1 ; }       
        $rsql = $sqlFull['stdt'];  
        $dataPagin = $this->pagination($nbrPages,$pageActuelle,$this->urlPages,$rsql,$class);
        $this->vue->pagination = $dataPagin['pages'] ;
        //executer requete sql :
        $fsql = $dataPagin['sql'];
        $dbh = $this->modele->getCnx(); 
        $stmt = $dbh->prepare($fsql);
        if (!empty($tabMarq)) {   //rem: meme code que $this->comptePages
          foreach ($tabMarq as $key=>$val){
            if ($key == "ha") { $tag = $val.'%'; }
            else { $tag = $val; }            
            if (in_array($key,$this->tabMarqI)){ $data_type = PDO::PARAM_INT; }
            elseif (in_array($key,$this->tabMarqS)) {  $data_type = PDO::PARAM_STR ; }
            $marqueur = ":".$key;            
            $stmt->bindValue($marqueur,$tag,$data_type);  
          } 
        }        
        $stmt->execute();
        $compteur = $stmt->rowCount();

        //traiter la Vue  
        if ($compteur == 0){ $this->vue->retour = MBR_NO_RESULT ; }
        else {
          if ($contenu == 'mbr') { $this->vue->setFile("contenu","voir_Membre-mbr.tpl"); }
          elseif ($contenu == 'resp'&& $this->statut == 'admin') { $this->vue->setFile("contenu","voir_Membre-admin.tpl"); }
          elseif ($contenu == 'resp' && $this->statut == 'responsable') { $this->vue->setFile("contenu","voir_Membre-responsable.tpl"); }
          $this->vue->setBlock("contenu","ligne","lignes");                  
          while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (($contenu == 'mbr') && ($result['iter_compta'] > 0)) { 
            //traitement exceptionnel pour iter_compta, (hors fichier Membre.ini)
            //pourrait etre resolu en modifiant dans le modele le tableau $this->dataVue (voir remarque class GererData-getVue)
              $urlIters = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;token=".$this->token."&amp;deal=non&amp;iterhum=".$result[$this->PPK];
              $this->vue->trajets = '<a href="'.$urlIters.'">'.MBR_CONSULT.'</a>';
            }
            elseif(($contenu == 'mbr') && ($result['iter_compta'] == 0)) { $this->vue->trajets = NIHIL; }
            if ($this->droits & RESPONSABLE){
              $this->vue->editer = BASEURL."index.php?ctrl=membre&amp;action=editer&amp;line=".$result[$this->PPK]."&amp;token=".$this->token;
              $this->vue->supprimer = BASEURL."index.php?ctrl=membre&amp;action=supprimer&amp;line=".$result[$this->PPK]."&amp;token=".$this->token;              
            }
            else { $this->vue->editer = $this->vue->supprimer = $this->vue->trajets = ''; }
            $y = 0;          
            $classe = "A".(($noligne++)%2);
            $this->visionEntity($result);  //gestion des entités de la vue (classe parente)
            $this->vue->cssline = $classe;            
            $this->vue->append("lignes","ligne");
          }
        }
      }        
      $this->vue->setFile("pied","pied.tpl"); 
      $out = $this->vue->render("page");
      $this->output($out);
      return true;
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
  public function supprimer() //protected car est commun
  {
    try { 
      $ok = false;
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }  
      if (! $ok = $this->validSess($token)){ $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko"; } 
      elseif (! $cle = $this->getPK('GET')) { throw new MyPhpException('Membre-Supprimer(), cle invalide'); }
      else {              
        $ok = $this->modele->supprimePlusLignes($cle,T_CHAT);
        $ok = $this->modele->supprimePlusLignes($cle,T_ROUTE);       
        foreach ($this->tabTables as $table){        
          $nomtable = strval($table);
          if (($nomtable == $this->liaisonTable) && ($ok = $this->modele->supprimePlusLignes($cle,$nomtable))){ ; }
          elseif ($ok = $this->modele->supprimeUneLigne($cle,$nomtable)){  ; }
          else { throw new MyPhpException('Membre-Supprimer(), erreur suppression membre'); }
        }
        if ($cle == $_SESSION['idhum']) { $loc = "Location: ".BASEURL."index.php?ctrl=auth&del=membre"; }  
        else { $loc = "Location: ".BASEURL."index.php?del=membre&token=".$token; }     
      }
      header($loc); 
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  } 
}



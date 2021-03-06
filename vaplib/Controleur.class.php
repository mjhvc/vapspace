<?php
/******************************************************************************
*    vapspace est un logiciel libre : vous pouvez le redistribuer ou le       *
*    modifier selon les termes de la GNU General Public Licence tels que      *
*    publi�s par la Free Software Foundation : � votre choix, soit la         *
*    version 3 de la licence, soit une version ult�rieure quelle qu'elle      *
*    soit.
*
*    vapspace est distribu� dans l'espoir qu'il sera utile, mais SANS AUCUNE  *
*    GARANTIE ; sans m�me la garantie implicite de QUALIT� MARCHANDE ou       *
*    D'AD�QUATION � UNE UTILISATION PARTICULI�RE. Pour plus de d�tails,       *
*    reportez-vous � la GNU General Public License.                           *
*
*    Vous devez avoir re�u une copie de la GNU General Public License         *
*    avec vapspace. Si ce n'est pas le cas, consultez                         *
*    <http://www.gnu.org/licenses/>                                           *
******************************************************************************** 
*/

/**
* class Controleur : controleur principal de l'application
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require_once('GererData.class.php');
require_once('FiltresData.class.php');
require_once('Template.php'); 
class Controleur 
{  
  protected $checkedForm = array();  
  private $contexte;
  protected $champPassif=array();
  private $dataContexte = array(); 
  protected $dataStructure = array();//utilis� par constructEntity()
  private $dataPassive=array();
  private $dataTable=array();
  private $droits;
  protected $erreurMasque = array();
  protected $erreurTaille = array();
  protected $erreurOblig ;
  protected $expedit;
  protected $filtre;
  private $filtreFrere;  
  protected $modele;
  private $modFrere ;
  protected $titleTab;
  private $select;
  protected $session;
  protected $sizeForm = array();  
  private $statut; 
  protected $tabmarqI;
  protected $tabMarqS;
  private $tabSelect=array();
  private $tablePassive; 
  protected $token;
  protected $tables = array();    
  protected $obligForm = array();
  protected $url_vapspace;
  protected $vue;
  protected $vueNameForm = array();
  protected $valueForm = array();
  const DUREE_SESSION = 2400; //30 minutes
/**
* Initialisation de la session PHP et par ce moyen, chargement du statut et des droits
* Initialisation de la classe 'GererData' par appel de :$this->chargerModele
* Initialisation  de la class FiltresData par appel � $this->chargerFiltre
* Si class:GereData est instanci�e '� vide', seule la connexion � la Database est initi�e ($this->modele->getCnx())
* Sinon, initiation des variables principales du modele
* initialisation de la 'Vue', par instanciation de la class:Template, chargement du template squelette.tpl dans l'entit� 'page'
*/
  public function __construct($contexte=NULL)
  { 
    session_start();
    $this->checkedForm = $this->valueForm = $this->sizeForm = array();
    $this->titleTab = $this->obligForm = $this->dataStructure = array();  
    $this->contexte = $contexte ;
    $this->url_vapspace = BASEURL."index.php?ctrl=membre&amp;token=";
    if (! empty($_SESSION['token']) && ($this->validSess($_SESSION['token']))) { $this->token = $_SESSION['token']; }
    else { $this->token = NULL; } 
    $this->statut = $this->sessionStatut();
    $this->droits = $this->sessionDroits();
   
    //Chargement du modele et du filtre (valeurs nulles possibles)
    $this->chargerModele($this->contexte,$this->statut);
    $this->chargerFiltre($this->contexte,$this->statut);            
    $this->tables = $this->modele->getTables();    
    $this->tablePassive = $this->modele->getPassiveTable();
    $this->expedit = $this->modele->getGeneral('mail'); 
   
    //Initier � vide la vue principale
    if (! $this->chargerVue()) { throw new MyPhpException('Impossible de charger le repertoire des vues'); }
    
    //Deux tableaux de champs sql li�s � methode comptePge()
    $this->tabMarqI = array('lienant','numbr','iterlieu','lipiter','antiter','anpiter','iterhum','hupiter');
    $this->tabMarqS = array('membre','nom','cachet','statut','ha','deal','mail');
  }  
  /**
  * obtenir un statut en fran�ais pour un temps limit�  via $this->validSess($_SESSION['token'])
  */
  private function sessionStatut()
  {
    try {
      $statutDecode =  'anonym';     
      if (($ok = $this->validSess($this->token)) && (!empty($_SESSION['statut']))) {  
        if ($_SESSION['statut'] == '6987'){ $statutDecode = 'membre'; }
        elseif ($_SESSION['statut'] == '8529'){ $statutDecode = 'responsable'; } 
        elseif ($_SESSION['statut'] == '3527'){ $statutDecode = 'admin'; }
      }
      return $statutDecode;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }          
  }
  /**
  * Obtenir les droits qui d�coulent du statut du client connect�
  */    
  private function sessionDroits()
  {
    try {
      $rights = 0;    
      $statut = $this->sessionStatut();
      if ($statut == 'anonym') { $rights |=  ANONYM ;}
      elseif ($statut == "membre"){ $rights |=  MEMBRE | ANONYM ; }     
      elseif ($statut == "responsable") { $rights |=  RESPONSABLE | MEMBRE | ANONYM ; }  
      elseif ($statut == "admin"){ $rights |= ADMIN | RESPONSABLE | MEMBRE | ANONYM ; }   
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
    return $rights;
  }
  /**
  * Charger le MODELE (li� aux fichiers vapdata/contextes)
  */
  protected function chargerModele($contexte=NULL,$statut=NULL) 
  {
    if ($this->modele = new GererData($contexte,$statut)) { return true; }
    else { return false; }
  }
  /**
  * Charger la class Filtres li�e au contexte et au statut
  */
  protected function chargerFiltre($contexte=NULL,$statut=NULL)
  {
    $this->filtre = new FiltresData($contexte,$statut);
    return true;  
  }
  /**
  * Charger la VUE li�e � variable $lang
  */
  protected function chargerVue()
  {
    if (filter_has_var(INPUT_GET,'lang') && ($lang = filter_input(INPUT_GET,'lang',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(fr|nl)$#'))))) {
      $dirVue = BASEPATH.'vapapi'.'/'.'vues_'.$lang.'/';
      $fileMsg = DIRLANG.$lang.'.php';
      $_SESSION['lang'] = $lang;
    } 
    elseif (! empty($_SESSION['lang'])) { 
      $dirVue = BASEPATH.'vapapi'.'/'.'vues_'.$_SESSION['lang'].'/'; 
      $fileMsg = DIRLANG.$_SESSION['lang'].'.php';
    }
    else {
      $dirVue = BASEPATH.'vapapi'.'/'.'vues_fr'.'/';
      $fileMsg = DIRLANG.'fr.php';
      $_SESSION['lang'] = 'fr'; 
    }
    $locale = $_SESSION['lang'].'_BE';
    setlocale(LC_TIME,$locale);
    include_once($fileMsg);
    if ($this->vue = new Template($dirVue)) { $back = true; }
    else { $back = false; }
    $this->vue->setFile("page","squelette.tpl");
    $this->vue->titre_head = "";
    $this->vue->titre_page = "";
    $this->vue->javascript = '';
    $this->vue->contact = 'mailto:'.$this->expedit;
    $this->vue->connecte = "";
    $this->vue->retour = '';
    $this->vue->contenu = '';
    $this->vue->pied = ''; 
    return $back;        
  }
  /** 
  * Appel statique encapsul� de Template::champSelect()
  */
  protected function getSelect($nom, $liste, $defaut=NULL, $required=NULL)
  {
    try {
      if(! is_array($liste)) { throw new MyPhpException('ControleurCtrl/getselect: parametre liste invalide'); }
      if (!empty($defaut)){ $formu_select = Template::champSelect($nom, $liste, $defaut,$required); }
      else { $formu_select = Template::champSelect($nom, $liste,NULL,$required); }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
    return $formu_select;
  }
  /**
  * Deux methodes pour l'encapsulation du statut et des droits 
  */
  protected function getStatut()
  {
    return $this->sessionStatut();
  }
  protected function getDroits()
  {
    return $this->sessionDroits();
  }
  /**
  * cette methode permet d'appeler la classe FiltresData dans un autre contexte
  */
  protected function changerFiltres($altcontexte)
  {
    try {
      $altFiltre = new FiltresData($altcontexte,$this->statut);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
    return $altFiltre;
  }

/**
* Une methode pour checker ou pas une entit� de la vue
* @param : $valide: bool indique si $nom doit etre check� ou non
* @param : $nom: string la valeur de l'entite � checker
*/
  protected function getChecked($valide,$nom)
  {
    $check = $nom;    
    $valcheck = $nom.'_val';     
    if ($valide){
      $this->vue->$check = "checked=";
      $this->vue->$valcheck = '"checked"';     
    }
    else {
      $this->vue->$check = "";
      $this->vue->$valcheck = '';  
    }
    return true;  
  }

/**
  * Une methode generale de controles avec 1 parametre: $tableau
  * Effectue systematiquement 3 controles : vacuum, taille et masques
  * return $error : string, la chaine avec une erreure ou une chaine vide
  */    
  protected function controler($tableau)
  {
    try {
      $ramasseur = array(); 
      $this->filtre->filtreCarMasq($tableau);    
      if ($this->filtre->filtreVacuum($tableau)) {
        $ramasseur[] = CONTROL_EMPTY;
      }
      elseif ($this->erreurTaille = $this->filtre->filtreTaille($tableau)){
        foreach ($this->erreurTaille as $valeur=>$taille){
          $ramasseur[] = $valeur.CONTROL_TOOBIG.$taille.'<br />' ;
        }
      }     
      elseif ($this->erreurMasque = $this->filtre->filtreMasques($tableau)){
        foreach ($this->erreurMasque as $formu=>$carac){ 
          if ($carac == 'nomail') { $ramasseur[] = CONTROL_RGX_MAIL; }     
          else { $ramasseur[] = CONTROL_REGEX.$formu.' : '.$carac.'<br />'; }
        }
      }
      else {  $error = ''; }

      if (! empty($ramasseur[0])){ 
        $error = $ramasseur[0]; 
        $ramasseur = array();
      }    
      return $error;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  //selectionne la valeur d'une colonne d'une table de la base selon son nom, sa cle de ligne et sa table
  protected function unoSelect($nom,$cle,$table)
  {
    try {
      $ligne = $this->modele->selectUneLigne($cle,$table);
      $valeur = $ligne[$nom];
      return $valeur;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  } 
/**
  * La methode qui construit DEPUIS LE MODELE les entites d'une vue  
  * @param: $tableau=array() facultatif � 1 dimmension l'entr�e des donn�es
  * @param: $table : string facultatif, nom d'une table sur laquelle travailler
  * @param: $flgstat : bool flagStatique facultatif non pris en charge ici mais sera surcharg� dans les controleurs sp�cifiques
  * charge $dataStructure avec $this->modele->getDataContexte() ou $this->modele->getDataTable($table)
  * Boucle foreach() parcourir $dataStructure  
  *   Si pas de flagStatique, les donnees proviennent d'un contexte standart (pas de liaison multi tables), 
  *     donnee obligatoire? : entity_o = '*' , charge entity_o dans $this->obligForm
  *     taille de la donnees  : entity_s = taille, charge entity_s dans $this->sizeForm
  *     valeur de la donn�e? : charge la valeur de la donn�e dans $this->valueForm
  *     en cas de negation des 3 donn�es ci-dessus, l'entite ad hoc est affect�e � vide
  *     si il y a $option['data'] ET il y a des valeurs 'statiques' :  
  *       - on charge  et compte le tableau des valeurs statiques 
  *       - boucle while: pour chaque valeurs statiques,
  *           si cette valeur est d�tect�e en entr�e ($tableau) on la charge, associ�e � son nom dans $this->checkedForm
  *       Nota : $this->checkedForm est array � 2 dimm car le mod�le permet deux valeurs statiques diff�rentes
  * Les 3 derni�res foreach puisent dans les 3 tableaux cr��s et assignent noms et valeurs dans la vue
*/
  protected function constructEntity($tableau=array(),$table=NULL,$flgStat=NULL)
  {  
    try { 
      if (empty($table)) { $this->dataStructure = $this->modele->getDataContexte(); } 
      else { $this->dataStructure = $this->modele->getDataTable($table); } 
      foreach ($this->dataStructure as $nom=>$option){         
        if(empty($flgStat)){ 
          $sizeName = $nom.'_s';
          $obligName = $nom.'_o';
          if (isset($option['longueur'])) {
            $this->sizeForm[$sizeName] = $option['longueur'];
          }          
          if (!empty($option['oblig'])) { $this->obligForm[$obligName] = '*'; }   
          else { $this->obligForm[$obligName] = ' '; }                         
          if (!empty($tableau[$nom])) { $this->valueForm[$nom] = $tableau[$nom]; } 
          else { $this->valueForm[$nom] = '';  }                                     
          if ( isset($option['data']) && $ok = $this->modele->getStatiqueValeurs() ) {
            $valStatiques = $this->modele->getStatiqueValeurs();
            $nbrValStat = count($valStatiques);
            $cpt = 0;
            while ($cpt < $nbrValStat) {
              $valueStat = $valStatiques[$cpt];  
              if (!empty($tableau[$nom]) && ($tableau[$nom] === $valStatiques[$cpt])){ 
                $this->checkedForm[$nom][$valueStat] = "checked"; 
              }
              else {  $this->checkedForm[$nom][$valueStat] = ""; }
              $cpt++;
            }
          }          
        }     
      }
      return true;    
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }     
  }
/**
* La methode qui cr�e des vues pour les titres des entit�s � visionner via voir() des classes enfants
* transforme la colonne 'cachet' en titre 'date'
* si droits de responsable, alloue l'entity 'manage' � editer supprimer
* cr�e $this->titleTab et $this->vue->manage
*/
  protected function visionTitleEntity()
  {
    try {
      //les colonnes � visionner sont encapsul�es dans modele->getVue()
      $vueTab = $this->modele->getVue();   
      foreach ($vueTab as $name){
        $title_entity = $name.'_t';
        if ($name == 'cachet'){ $this->titleTab[$title_entity] = ENT_CACHET ; }
        elseif ($name == 'inscrit'){ $this->titleTab[$title_entity] = ENT_INSCRIT ; }
        elseif ($name == 'code') { $this->titleTab[$title_entity] = ENT_ZIP ; }
        elseif ($name == 'lienant') { $this->titleTab[$title_entity] = ENT_ANT ; }
        else { $this->titleTab[$title_entity] = $name; }      
      }
      foreach ($this->titleTab as $entity=>$nom){ $this->vue->$entity = $nom;}
      if ($this->droits & RESPONSABLE){ $this->vue->manage = "<th>".ENT_EDIT.'</th><th>'.ENT_DEL."</th>"; }
      else { $this->vue->manage = ""; }
      return true;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
  }
/**
* Attribue ou pas les entit�s de la vue selon :
* - le tableau getVue() du mod�le 
* - un array fourni en parametre: $dataTab
*/
  protected function visionEntity(array $dataTab)
  {
    try {
      $vueTab = $this->modele->getVue();    
      foreach ($vueTab as $name){
        if (array_key_exists($name,$dataTab)) { 
          if ($name == 'inscrit') {
            switch ($dataTab['inscrit']) {
			        case 'deux' :	$this->vue->inscrit = '<img src="'.URLIMG.'deux.png" alt="pieton-automobiliste" />';break;
			        case 'pieton':	$this->vue->inscrit =	'<img src="'.URLIMG.'pieton3.png" alt="pi�ton" />';break;
			        case 'auto':	$this->vue->inscrit  = '<img src="'.URLIMG.'auto3.png" alt="automobiliste" />';break;
		        }
          }
          elseif (($name == 'mail') && ($dataTab['mail'] == UNI_NOMAIL)) { $this->vue->$name = MBR_NOMAIL; }
          elseif (($this->contexte == 'Membre') && ($name == 'lienant')) {
            $passiveLines = $this->valeursPassive(1);
            foreach($passiveLines as $cle=>$val) {
              if ($cle == $dataTab['lienant']) { $this->vue->Antenne = $val ; }
            }
          }  
          else { $this->vue->$name = $dataTab[$name]; }
        }
        else { $this->vue->$name = ''; }
      }
      return true;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
  }
  /**
  * calcul le nombre de lignes totales via la requete['pagin'] fournie par sqlFactory 
  * @param $sql:string, une requete sql
  * @param $tabmarq: array('marqueurSql',valeurMarqueur);
  * @param $stat: bool, permet si true une utilisation de cette fonction � des fins statistiques (la requ�te $sql est alors sp�cifique)
  * @return $nbrPages: int, le nombre de pages 
  */
  protected function comptePages($sql,$tabMarq=array(),$stat=NULL)
  {
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    if (!empty($tabMarq)) {   //traitement des marqueurs si pr�sents
      foreach ($tabMarq as $key=>$val){
        if ($key == 'ha') { $tag = $val.'%'; }
        else { $tag = $val; }
        if (in_array($key,$this->tabMarqI)){ $data_type = PDO::PARAM_INT; }
        elseif (in_array($key,$this->tabMarqS)) {  $data_type = PDO::PARAM_STR ; }
        $marqueur = ":".$key;            
        $stmt->bindValue($marqueur,$tag,$data_type);  
      } 
    } 
    $stmt->execute();  
    $result = $stmt->fetchAll();
    $total = $result[0]['total'];
    if (empty($stat)) { //utilisation 'basique': compter les pages 
      $calcul = $total / TAILLE;
      $nbrPages = ceil($calcul);
    }
    else { // utilisation d�tourn�e pour statistiques, on retourne simplement $total
      $nbrPages = $total;
    }
    return $nbrPages;
  } 
  /**
  * Calcul et retourne la liste des pages pour une vue quelconque
  * Return array avec 
  * -le requete $sql fournie ajust�e des limites de la pagination 
  * -la liste html des pages pour une vue quelconque
  * @param $nbrpages: integer, le nombre de pages, pre-calcul�es par $this->comptePages($sql)
  * @param $pageActuelle: integer, le chiffre de la page actuelle
  * @param $url : string, une url globale pour sortir variable 'href' de chaque pages
  * @param $rsql: string, la requete sql standart calcul�e par $this->sqlFactory($condition)
  * @param $class : le nom de la varaible $_GET qui regit l'appel de la methode voir() 
  */
  protected function pagination($nbrPages,$pageActuelle,$url,$rsql,$class=NULL)
  {
    try {
      $sortie = array();  
      $pagePre = $pageSui =  $classGet = $preFull = $nextFull = '';
      if ($pageActuelle > 1) { $pagePre = $pageActuelle - 1; }
      if ($pageActuelle < $nbrPages) { $pageSui = $pageActuelle + 1; }
      if (!empty($class)){ $classGet = '&amp;class='.$class; }
      if (!empty($pagePre)) {          
        $preUrl = $url.$pagePre.$classGet;
        $preFull = '<a href="'.$preUrl.'">'.PAGI_PREV.'</a> | ';
      }
      $vuePages = '<p class="pagi">'.$preFull;
      for ($i = 1;$i <= $nbrPages;$i++) {
        if ($i == $pageActuelle) { 
          $capsule =  ' ['.$i.'] ';           
          $vuePages .= $capsule ;
        }
        else {
          $capsule = '<a href="'.$url.$i.$classGet.'"> '.$i.' </a>';
          $vuePages .= $capsule ;
        }
      }
      if (!empty($pageSui)) {
        $nextUrl = $url.$pageSui.$classGet;
        $nextFull = ' | <a href="'.$nextUrl.'">'.PAGI_NEXT.'</a>';
      }
      $vuePages .= $nextFull.'</p>';
      $sortie['pages'] = $vuePages;
      //ajuster la reqete sql dans les bonnes limites:
      $premEntree = ($pageActuelle - 1) * TAILLE ;
      $sqlPaginee = $rsql." LIMIT ".$premEntree.",".TAILLE;
      $sortie['sql'] = $sqlPaginee ;
      return $sortie;    
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }        
  }
/**
  * preparation de la liste des attributs des tables du contexte � visionner
  * utilise $this->modele->getVue() qui defini les colonnes du contexte � visionner
  * utilise $this->modele->getPPK() qui retourne le nom de la cl� PK pricipale du contexte
  * @return $wagonVues: array() 1D avec comme valeurs le nom des colonnes � voir + nom de PPK
  */
  private function sqlVues()
  {
    try {
      $wagonVues = array();
      $champsVue = $this->modele->getVue();
      $idx = 0;
      $maxVues = count($champsVue);
      while ($idx < $maxVues){
        $wagonVues[$idx] = $champsVue[$idx];
        $idx++;
      } 
      $PPK = $this->modele->getPPK();  //important
      array_push($wagonVues,$PPK);
      return $wagonVues ;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
/**
  * sqlFactory(): la methode qui cree et retourne une requete sql de selection de contexte 
  * Cette requete affiche les donnees obligatoires d'un contexte dans le controleur ad hoc
  * cette requete re�oit une condition(WHERE)  et construit le FROM de la requete
  * @param: $condition_stdt (string) la condition WHERE standart de la requete (facultative) calculee depuis chaque contexte
  * @param: $condition_pgin: string, la condition ad hoc pour paginer la condition standart
  * @$from: integer, flag pour integrer ou pas une table passive dans la liste from
  * @return: un tableau de 2 requetes: la premiere est 'standart', la seconde est necessaire � la pagination.
  */    
  protected function sqlFactory($condition_stdt=NULL,$condition_pgin=NULL,$from=NULL,$iter=NULL) 
  {
    try {    
      $wagonsIn = $listeSql = $listeFrom = $listeFromDyn = array();
      $idx = 0; 
      $wagonSql =  $listeTables = $listeTablesDyn = '';
      $listeFrom =  $this->modele->getDynTables();  //$this->tables;    
      //le comptage des lignes ( de la requetes pgin ) ne se fait que sur les tables dynamiques du contexte      
      $listeFromDyn = $this->modele->getDynTables();
      //peut modifier $listeTables donc la requete stdt, par rajout de la table passive d'un contexte
      if (!empty($from)){ array_push($listeFrom,$this->tablePassive); }
           
      //creation de liste des champs, des tables, creation de la requete sql et ex�cution de celle-ci
      $wagonsIn = $this->sqlVues(); 
      if (!empty($iter)){ array_push($wagonsIn,"iter_compta"); } 
      $nbr = count($wagonsIn);
      $listeTables = implode(",",$listeFrom);
      $listeTablesDyn = implode(",",$listeFromDyn);
      
      if (!empty($condition_stdt)){ $condSql_stdt = " WHERE $condition_stdt "; }
      else { $condSql_stdt = ''; }      
      if (!empty($condition_pgin)){ $condSql_pgin = " WHERE $condition_pgin "; }
      else { $condSql_pgin = ''; }

      foreach ($wagonsIn as $nomVal){     
        ($idx < ($nbr-1))?  $Sep = ", ": $Sep = " "; 
        $wagonSql .= $nomVal.$Sep;
        $idx++; 
      }
      $sql_stdt = "SELECT DISTINCT $wagonSql FROM $listeTables $condSql_stdt";
      $sql_pagin = "SELECT COUNT(*) as total FROM  $listeTablesDyn $condSql_pgin"; 
      
      $listeSql['stdt'] = $sql_stdt;
      $listeSql['pagin'] = $sql_pagin; 
      return $listeSql;     
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
/**
*  construit un array bas� sur le tableau des valeurs 'Passive' fournies par le contexte (sp�cifique au contexte)   
*  cet array sera renvoy� � une methode de sortie : p.ex: getSelect() qui elle en fait un input type select
*  @param : $champ, string, le nom du futur formulaire � transmettre � la methode qui calculera c formulaire de sortie
*  @param: $idx, integer, l'index numerique pour choisir un des elements de la ligne passive
*  @param: $selected, integer , facultatif, la cl� d'un �lement de la ligne passive peut etre marqu� 'selected'
*  @param: $vue, string valeurs possibles: 
*   -'select': envoi la sortie dans $this->getSelect()
*   -'no': return de sortie simple, pas d'envoi dans une vue
*  @param: $altContexte, string, nom d'un autre contexte chargeable ici (facultatif)
*/
  protected function calculPassive($champ,$idx,$vue,$selected=NULL,$altContexte=NULL)
  {
    try {
      $idx = intval($idx);    
      if (empty($altContexte)){ $this->dataPassive = $this->modele->getSchemaDataPassive();}
      else {
        $newData = new GererData($altContexte,$this->statut);
        $this->dataPassive = $newData->getSchemaDataPassive(); 
      }   
      foreach ($this->dataPassive['Passive'] as $cle=>$strval){
        $this->tabSelect[] = explode(',',$strval);
      }           
      foreach ($this->tabSelect as $cle=>$tabVal){      
        $index = intval($tabVal[0]); //L'index est toujours [0]
        $this->listePassive[$index] = $tabVal[$idx]; 
      }
      asort($this->listePassive); //classer par ordre alpha sans changer les cl�s.
      if ($vue == 'select'){ $laSortie = $this->getSelect($champ,$this->listePassive,$selected); }
      elseif ($vue == 'no'){ $laSortie = $this->listePassive; }
      $newData = NULL;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
    return $laSortie;
  }
/**
* re ecriture de la methode precedente en plus efficace et simple....
* Plus de gestion de la Vue avec cette methode
* @param: $idxUn, integer, l'index du nom de la valeur � retourner parmis la ligne passive
* @param: $idxDeux, integer, facultatif, la valeur reelle d'un index passif propos�; permet de ne retourner que des valeurs li�es � cette valeur
* @altCont,string, le nom d'un contexte alternatif
* @return: un tableau de valeurs 'passives'
*/
  protected function valeursPassive($idxUn,$idxDeux=NULL,$altCont=NULL)
  {
    try {
      $idx = intval($idxUn);
      $listeValeurs = array();
      if (empty($altCont)){ $dataPassive = $this->modele->getSchemaDataPassive(); }
      else {
        $newData = new GererData($altCont,$this->statut);
        $dataPassive = $newData->getSchemaDataPassive();  
        unset($newData);
      } 
      foreach ($dataPassive['Passive'] as $cle=>$strval){
        $tabSelect[] = explode(',',$strval);  //$tabSelect devient un tableau � 2 dim
      }           
      foreach ($tabSelect as $cle=>$tabVal){  //$tabVal is_array()    
        $index = intval($tabVal[0]); //L'index du tableau des valeurs est toujours [0]
        if (!empty($idxDeux)) {
          if ($tabVal[2] == $idxDeux) { 
            $listeValeurs[$index] = $tabVal[$idxUn]; 
          }
        }
        else {  $listeValeurs[$index] = $tabVal[$idxUn]; }
      }
      asort($listeValeurs);
      return $listeValeurs;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }            
  }  
  /**
  * Idem que calculPassive mais ne retourne que un select pour une seule valeur: $valChamp
  */  
  protected function calculChampPassif($nomFormu,$valChamp)
  {
    try {
      $this->dataPassive = $this->modele->getSchemaDataPassive();
      foreach ($this->dataPassive['Passive'] as $cle=>$strval){
        $this->tabSelect[] = explode(',',$strval);
      } 
      foreach ($this->tabSelect as $cle=>$tabVal){        
        $index = intval($tabVal[0]);
        foreach ($tabVal as $nomVal){
          if ($nomVal == $valChamp){
            $this->champPassif = array($index=>$nomVal); 
          }
        }
      }    
      $leselect = $this->getSelect($nomFormu,$this->champPassif);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
    return $leselect;
  } 
  /**
  * Cr�ation de jetons al�atoires
  */
  protected function creerToken()
  {
    $token = md5(uniqid(rand(), TRUE));
    return $token;
  }
  /**
  * Createur de session  
  * controle des 2 parametres (prealablement filtr�s par controleur Auth):
  * @param: $mail : string le mail du client
  * @param $secret: string , le mot de passe en clair
  * Si mail existe et passe est correct, on creation de la session
  */
  protected function creerSession($mail,$secret)
  {
    $requete =  " SELECT idhum, prenom, nom, mail, passe, statut, idant, nomantenne, inscrit, membre, idreg, province, COUNT( DISTINCT idchat ) AS chats, COUNT( DISTINCT idtraj ) AS iters "
                ." FROM ".T_CORE." C "
                ."  LEFT OUTER JOIN ".T_HUM." H ON C.idvap = H.idhum "
                ."  LEFT OUTER JOIN ".T_CHAT." B ON B.lienhum = C.idvap "
                ."  LEFT OUTER JOIN ".T_ROUTE." T ON T.lienhum = C.idvap "
                ."  LEFT OUTER JOIN ".T_ANT." A ON A.idant = C.lienant "
                ."  LEFT OUTER JOIN ".T_REG." R ON H.lienreg = R.idreg "
                ." WHERE H.mail = :themail GROUP BY idhum ";
    $create = false; $c = 0;
    $dbh = $this->modele->getCnx(); 
    $stmt = $dbh->prepare($requete);
		$mailClean = htmlspecialchars($mail,ENT_QUOTES,'ISO-8859-1');
    $stmt->bindValue(':themail',$mailClean,PDO::PARAM_STR);
    $stmt->execute();
    while ($global = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $oldP = $global['passe'];      
      $crypte = crypt($secret,$oldP);
      if ($crypte === $oldP) {       
        session_regenerate_id();          
        $token = $this->creerToken();                 
        $tokenUser = md5($_SERVER['HTTP_USER_AGENT'].$token);
        $_SESSION['token'] = $token;           
        $_SESSION['tokenUser'] = $tokenUser;
        $_SESSION['validSess'] = time() + self::DUREE_SESSION;
        //creation de la session standart
        $_SESSION['idhum'] = $global['idhum']; $_SESSION['prenom'] = $global['prenom']; $_SESSION['nom'] = $global['nom'];
        $_SESSION['idant'] = $global['idant']; $_SESSION['nomantenne'] = html_entity_decode($global['nomantenne'],ENT_QUOTES,'ISO-8859-1'); 
        $_SESSION['province'] = $global['province'];
        $_SESSION['lienreg'] = $global['idreg']; $_SESSION['inscrit'] = $global['inscrit']; $_SESSION['membre'] = $global['membre'];
        $_SESSION['mail'] = html_entity_decode($global['mail'],ENT_QUOTES,'ISO-8859-1');  $_SESSION['chats'] = $global['chats']; $_SESSION['iters'] = $global['iters'];
  
        //petit codage perso de securit� pour le statut, attention � bien le d�coder par apr�s...		
        switch ($global['statut']) {
          case "membre":	$_SESSION['statut'] = "6987";break;
  			  case "admin" : $_SESSION['statut'] = "3527";break;
  			  case "responsable" : 	$_SESSION['statut'] = "8529";break;
        }
        $create = true; $c++;
        $this->token = $_SESSION['token']; 
        session_write_close(); 
      }
    }
    if ($c != 1) { $create = false; } //impossible de se connecter si plusieurs pass semblables sont associ�s � un mail.
    return $create;
  }    
   /**
   * V�rification qu'une session est valide, fait 2 controles:
   * la dur�e de la session est-elle correcte?
   * le client utilise-t-il le meme navigateur?
   * La session, si valide, est prolong�e de DUREE_SESSION 
   * @param token: string, le jeton � valider 
   */
  public function validSess($token=NULL)
  {
    $valid = true;    
    if (empty($token)) { $valid = false; }
    elseif ((!empty($_SESSION['token'])) && ($token === $_SESSION['token'])) {         
      $maintenant = time();
      $client = md5($_SERVER['HTTP_USER_AGENT'].$_SESSION['token']);
      if (($_SESSION['validSess'] < $maintenant) || ($_SESSION['tokenUser'] != $client)) 
      { $valid = false; }    
    }
    else { $valid = false; }        
    if ($valid) { $_SESSION['validSess'] = $maintenant + self::DUREE_SESSION; } 
    return $valid ;
  }
  /**
  * methode statutConnect: affiche un message personnalis� dans la vue selon la validation de session
  * @param $token, string le jeton de validation de session.
  * appell� par chaque methode globalEntities()
  */  
  public function statutConnect($token=NULL)
  {
    if ($this->validSess($token)) { 
      if ($this->droits & RESPONSABLE) { $ch_resp = '<li class="menuPerso"><a href="'.BASEURL.'index.php?token='.$token.'">'.MBR_ADMIN.'</a></li>'; }
      else { $ch_resp = ''; }
      $urlOut = BASEURL."index.php?ctrl=auth&amp;action=logout";
      $urlProfil = BASEURL."index.php?ctrl=membre&amp;action=editer&amp;line=".$_SESSION['idhum']."&amp;token=".$token ;
      $chaine = '<ul class="connect"><li><a href="#" id="Perso">'.$_SESSION['prenom']." ".$_SESSION['nom'].' <img src="photos/down2.gif" alt="down" /></a></li>'
                .'<li class="menuPerso"><a href="'.$urlProfil.'">'.CONNECT_PROFIL.'</a></li>'
                .'<li class="menuPerso">'.CONNECT_NUMBER.$_SESSION['membre']. '</li>';
      $ch_fin = '<li class="menuPerso"><a href="'.$urlOut.'">'.CONNECT_LOGOUT.'</a></li></ul>';
      $chaine_finale = $chaine.$ch_resp.$ch_fin;
    }
    else {
      $chaine_finale = "<p class=\"connect\">".CONNECT_WELKOM." <br />"
      .'<a href="http://'.$_SERVER["SERVER_NAME"].'">'.BACK2SITE.'</a></p>';
    }
    return $chaine_finale;
  }
  /**  
  * function qui g�re l'envoi du mail par appel de la classe phpmailer
  */
  protected function mailer($destina,$expedit,$sujet,$contenu,$nom ="l'�quipe des VAP")
  {
    require_once("class.phpmailer.php");	    
    $mail = new PHPMailer();
	  $mail->IsMail();
	  $mail->IsHTML(false);
    if (is_string($destina)) { $mail->AddAddress($destina); }    
    elseif (is_array($destina)) {
      foreach ($destina as $val) {
        $mail->AddAddress($val);
      }
    }
	  $mail->From = $expedit;
    $mail->Sender = $expedit;
	  $mail->FromName = $nom;
	  $mail->Subject = $sujet;
	  $mail->Body = $contenu;
	  $mail->WordWrap = 70;
	  if(!$mail->Send()) { $out = false; }
	  else {
		  $mail->ClearAddresses();		
		  $out = true;
	  }	 
    return $out;
  }
  protected function selectMailResp($idant)
  {    
    $retour = array();    
    $sql = "SELECT DISTINCT mail FROM ".T_HUM.",".T_CORE
          ." WHERE ".T_CORE.".lienant= :idant "
          ." AND ".T_HUM.".idhum = ".T_CORE.".lienperso"
          ." AND ".T_HUM.".statut = 'responsable' ";
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(':idant'=>$idant));
    while ($rslt = $stmt->fetch(PDO::FETCH_ASSOC)) { 
      $retour[] = $rslt['mail']; 
    }
    return $retour;
  }
  /**
  * creerCache() cr�e une structure de repertoires de cache  par 
  *   @param: $nom, str, le nom de la rubrique pricipale du cache
  *   @param: $tabDir, array, le tableau des id qui serviront � cr�er un repertoire par id
  */
  protected function creerCache($tabDir,$nom='antennes')
  {
    try { 
      if ($nom != 'antennes') { $path = DIRCACHE.$nom.'/'; }
      else { $path =  DIRCACHANT; }
      if (! is_dir($path)) {	mkdir($path); }
      if (is_array($tabDir)) {      
        foreach($tabDir as $cle=>$val) {
          $dir = strval($cle).'/';		
		      $path .= $dir; 		
		      if (! is_dir($path))	
		      {	mkdir($path); }
		      $path = str_replace($dir,'',$path);
        }
        $back = true;
      }
      else { $back = false; }
      return $back;
   } 
   catch(MyPhpException $e) {
     $msg = $e->getMessage();
     $e->alerte($msg);  
   }    
	}
   /**
  * fonction qui efface le(s) fichier(s) de cache identifi� par et $id(int)et $masq (str)
  */  
  protected function viderCache($id,$masq='./*.php',$nom='antennes')
  {
    if ($nom != 'antennes') { $path = DIRCACHE.$nom.'/'.$id.'/'; }
    else { $path =  DIRCACHANT.$id.'/'; }	  
	  $repertoire = getcwd();		
	  if (is_dir($path)) {	
		  chdir($path);
		  $fichier = glob($masq);
		  foreach ($fichier as $nom)
		  {	unlink($nom);}				
	  }
	  chdir($repertoire);
    return true;
  }
  /**
  * fonction qui re-initie un cache identifi� par $nom 
  * le cache $nom est vid�, effac� et recr�� avec la structure de repertoires : $tabDir 
  */
  protected function rstCache($tabDir,$nom='antennes')
  {
    try {  
      if ($nom != 'antennes') { $path = DIRCACHE.$nom.'/'; }
      else { $path =  DIRCACHANT; } 
      $repertoire = getcwd();     
      if (!empty($tabDir)) {  
        foreach ($tabDir as $cle=>$val) {
          $dir = strval($cle).'/';
          $path .= $dir;
          if (is_dir($path)) {
            $masq='./*';
            chdir($path);
		        $tabFiles = glob($masq);
		        foreach ($tabFiles as $file)
		        {	unlink($file);}
            chdir('..');
		        rmdir($path);
          }         
          $path = str_replace($dir,'',$path); 
        }
        $this->creerCache($tabDir,$nom);
        chdir($repertoire);
        $out = true;      
      }
      else { $out = false; }
      return $out;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }      
  }
  /**
  * Recopi� du livre 'PHP5 avanc�, ch gestion des caches, �Daspet & De Geyer, ed Eyrolles
  * d�termine si oui ou non (bool) il faut utiliser le cache
  * @param $path : string, un chemin de fichier cache
  * @param $delais :int le delais en secondes de validit� du fichier cache
  * retourne un booleen
  */   
  protected function useCache($path,$delais,$calc=NULL)
  {
    //a priori on utilise le cache
    $use_cache = true;
    //calculer le stamp du fichier cache, son delais depuis maintenant, et comparer avec delais fourni
    if (file_exists($path)) {
      $t = filemtime($path);
      $ledelais = (time() - $t);
      if (($ledelais < $delais) AND ($ledelais >= 0)){ $use_cache =true; }
    }
    else { $use_cache = false; }
    //Faut-il forcer un recalcul ?
    if ($calc) { $use_cache =false; }
    else { $use_cache = true; }
    return $use_cache;
  }

  /**
  * function ecrisCache : ecris $ligne dans le fichier $iDir.$masq
  * @param $ligne: string une chaine � ecrire
  * @param $iDir: int, identifie le numero de repertoire de cache dans DIRCACHANT
  * @masq: str, le nom du fichier dans lequel �crire
  * Cette fonction et la suivante generalise l'ecriture et la lecture (csv)   
  * de fichiers de cache selon la structure d'antenne
  */
  protected function ecrisCache($iDir,$masq,$ligne='',$nom='antennes')
  {
    if ($nom != 'antennes') { $path = DIRCACHE . $nom . '/' . $iDir . '/' ; }
    else { $path = DIRCACHANT . $iDir . '/' ; }     
    
    if (! is_dir($path)){ mkdir($path); }
    $fileCache = $path.strval($iDir).$masq;
    $point = strpos($masq,'.');
    $fileMasq = substr($masq,$point);
    $fileProtect = $path.'protect'.$fileMasq;
    if (!file_exists($fileProtect)) { touch($fileProtect); }
    $vp = fopen($fileProtect,'rb');						             
		flock($vp,LOCK_EX);		
    $fp = fopen($fileCache,'wb');
    if ($ok = file_put_contents($fileCache,$ligne)) { $ligne = ''; }
    fclose($fp);
    flock($vp,LOCK_UN);													               
		fclose($vp);
    return $ok; 
  }
  /**
  * function lisCache : lis $col dans le fichier $iDir.$masq selon $method et $car
  * @param $col, int, nombre de colonnes par ligne de fichier   
  * @param $masq: nom du fichier � lire
  * @param $iDir: int, identifie le numero de repertoire de cache dans DIRCACHANT
  * @param $car, int, nombre de caract�res par ligne de fichier  
  * @param $method, str : la methode de lecture par default est csv  
  * @return un array 2D � finaliser dans chaque contexte
  */
  protected function lisCache($iDir,$masq,$col,$car=128,$method='csv',$nom='antennes')
  {
    $x = 0;  $tab_2D = $tableau = array();  
    if ($nom != 'antennes') { $path = DIRCACHE . $nom . '/' . $iDir . '/' ; }
    else { $path = DIRCACHANT . $iDir . '/' ; }
    
    $fileCache = $path.strval($iDir).$masq;
    $point = strpos($masq,'.');
    $fileMasq = substr($masq,$point);
    $fileProtect = $path.'protect'.$fileMasq;
    if ($vp = @fopen($fileProtect,'rb')) {
      flock($vp,LOCK_SH);	
      if ($fp = @fopen($fileCache,'rb')) {
        if ($method == 'csv') {
          while ($data = fgetcsv($fp,$car)) {
            for ($c = 0; $c < $col; $c++) {
              $tab_2D[$x][$c] = $data[$c];
            }
            $x++; //compte les lignes
          }
        }
        //d'autres methodes de capture de cache ici, dans ce cas attention au return final
        fclose($fp);
      }
      flock($vp,LOCK_UN);													               
		  fclose($vp);
    }
    return $tab_2D;
  }
	/**
  * factorisation de l'action download
  */
  protected function downLoad($file,$pathFull)
  {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-disposition: attachment; filename=$file");            
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($pathFull));
    if (ob_get_length() > 0) { ob_clean(); }
    flush();
    readfile($pathFull);
    if ($final = @readfile($pathFull)) { return true; }
    else { return false; }
  }
}

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
* Class RegionCtrl: gère le contexte Region.ini table: T_REG
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require_once("Controleur.class.php");

class RegionCtrl extends Controleur
{ 
  protected $contexte;
  private $statut;
  private $droits;
  private $action;
  private $table;
  private $tabindex;
  private $tabTables = array();
  private $urlPages;
  private $valppk;

/**
* methode fille __construct()  necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appell de parent:: __construct($contexte) qui charge statut,modele et vue selon session et contexte 
 */
  public function __construct()
  {
    $this->contexte = "Region";
    parent::__construct($this->contexte);    
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();    
    $this->tabTables = $this->modele->getTables();    
    $this->table = $this->tabTables[0];
    $this->action = '';
    $this->tabindex = array();
    $this->urlPages = BASEURL."index.php?ctrl=region&amp;action=voir&amp;token=".$this->token."&amp;page=";
    $this->valppk = '';
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
  * Des entités de la vue du contexte à appeler à chaque fois
  * L'entité index_admin est dans pied.tpl
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head = ANT_S_TIT;
    $this->vue->titre_page = ANT_S_TIT;
    $this->vue->vapspace =  $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token);   
    if (! $this->droits & RESPONSABLE){ 
      $this->vue->index_admin = ''; 
    }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin =  MBR_ADMIN;
    }      
    return true;
  }
  /**
  * controler() surcharge de
  * appel de parent::controler() puis fait des controles specifiques au contexte
  * retourne $this->vue->retour
  */ 
  protected function controler($tableau)
  {
    $ramasseError = array();
    try {  
      //faire d'abord les filtres génériques du controleur principal
      $this->vue->retour = parent::controler($tableau);
      if (empty($this->vue->retour)){    
        if ($testOblig = $this->filtre->filtreOblig($tableau)){  
            $ramasseError[] = CONTROL_OBLIG.$testOblig;
        }                  
        if ($this->action == 'insert') { 
          if ($ctldbl = $this->filtre->doublon('province',$tableau['province'],$this->table)){
            $ramasseError[]  = $tableau['province']. THIS_EXIST; 
          }
          elseif ($postCorrupt = $this->filtre->postInattendu($tableau)){
            throw new MyPhpException('formulaire du contexte: Region corrompu');
          }
        }
        elseif ($this->action == 'update'){
          //pas de filtre doublon pour un update en base pour ce contexte        
          $hidden = array('ppk','itoken');
          if ($postCorrupt = $this->filtre->postInattendu($tableau,$hidden)){
            throw new MyPhpException('formulaire du contexte: Region corrompu');
          }
          elseif ($tableau['itoken'] != $this->token) {
            throw new MyPhpException('jeton du formulaire du contexte: '.$this->contexte.' corrompu');
          }
        }
        if (! empty($ramasseError[0])){  
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
* Regroupement des opérations sur la Vue pour les actions update et inscrire
*/
  private function vueRegion($action,$valppk=NULL,$tableau=array())
  {
    $out = ''; 
    $this->globalEntities();
    if ($action == 'insert') { $this->vue->script_formu = BASEURL."index.php?ctrl=region&amp;action=inscrire&amp;token=".$this->token; }
    elseif ($action == 'update') { $this->vue->script_formu = BASEURL."index.php?ctrl=region&amp;action=update&amp;token=".$this->token; }  
  
    if (empty($tableau)) { $this->constructEntity(); }
    else { $this->constructEntity($tableau); }
   
    if ($action == 'insert'){ $this->vue->cachez = ""; }
    elseif ($action == 'update'){ 
      $this->vue->setFile("cachez","multi_Membre_ValPPK.tpl");      
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;      
    }
    $this->vue->setFile("contenu","inscrire_Region.tpl");
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;        
  }

/** 
  * methode inscrire:
  * gère : un formulaire d'inscription à vide (si pas de $_POST) par parent::constructEntity()
  * gère : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire()
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
        $out =  MBR_NO_ACTION;
        $this->output($out);  
      }
      else {
        $this->action = 'insert'; 
        if (empty($_POST)) {          
          $page = $this->vueRegion($this->action);
          $this->output($page);      
        }
        else {    
          $this->vue->retour = $this->controler($_POST);   
          if (empty($this->vue->retour)){      
            if ($this->valppk = $this->modele->inscrire($_POST)){
              $this->modele->resetContexte($this->contexte,$this->statut);
              $loc =  "Location: ".BASEURL."index.php?in=region&token=".$token;
              header($loc);
            }
            else  { throw new MyPhpException('Impossible d\'insérer une antenne');}
          }          
          else { // des erreurs du client?: on retourne les données dans l'entité contenu de la vue
            $page = $this->vueRegion($this->action,NULL,$_POST);
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
        //usinage sql et pagination            
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
        $fsql = $dataPagin['sql']; //sql avec LIMIT
 
        //traiter sql
        $dbh = $this->modele->getCnx();
        $stmt = $dbh->prepare($fsql);
        $stmt->execute();
        $compteur = $stmt->rowCount();      
        if ($compteur == 0){ $this->vue->retour = 'Pas de résultats'; }
        else {         
          $this->vue->setFile("contenu","voir_Region.tpl");
          $this->vue->setBlock("contenu","ligne","lignes");
          while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            $classe = "A".(($noligne++)%2);
            $ppk = $result['idreg'];
            $this->vue->cssline = $classe;
            $this->vue->nom_province = $result["province"];
            $this->vue->nom_region = $result["nomregion"];
            $this->vue->editer  = BASEURL."index.php?ctrl=region&amp;action=editer&amp;line=".$ppk."&amp;token=".$this->token;
            $this->vue->supprimer = BASEURL."index.php?ctrl=region&amp;action=supprimer&amp;line=".$ppk."&amp;token=".$this->token;
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
    $tableauVue = $data=array();
    $page='';    
    try {
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      elseif (! ($this->droits & ADMIN)){
        $out = MBR_NO_ACTION;
        $this->output($out);
      }
      else { 
        $this->action = 'update';     
        if ($cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT))
        {
          $data = $this->modele->selection($cle); //attention retourne un tableau à 2dim: echo '<pre>'; print_r($tableau);echo '</pre>';
          $tableauVue = $data[$this->table];   
          $page = $this->vueRegion($this->action,$cle,$tableauVue);
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
          if (empty($this->vue->retour)){  
            if (! $this->filtre->filtreClePPK($cle)){
              throw new MyPhpException('le parametre: ppk dans le contexte: region est non valide');
            }
            elseif ($ok = $this->modele->mettreajour($_POST,$cle)){
              $this->modele->resetContexte($this->contexte,$this->statut);
              $loc =  "Location: ".BASEURL."index.php?up=region&token=$token";
              header($loc);                           
            }
          }
          else { //retour des erreurs clients
            $page = $this->vueRegion($this->action,$cle,$_POST);
            $this->output($page);
          }        
        }
        else { throw new MyPhpException('le parametre: ppk dans le contexte: region est non valide');}        
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
      if (! ($this->droits & ADMIN)){
        $out =  MBR_NO_ACTION;
        $this->output($out);  
      }
      else {           
        if ( $cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT )) {
          if (!($ok = $this->filtre->getOld('idant','lienreg',$cle,T_ANT,1))){
            if ( $ok = $this->modele->supprimeUneLigne($cle,$this->table )){
              $this->modele->resetContexte($this->contexte,$this->statut);
              $loc = "Location: ".BASEURL."index.php?del=region&token=$token";  
            } 
          }
          else { $loc = "Location: ".BASEURL."index.php?delko=region"; } 
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
*/  
  public function index()
  {
    try {    
      $this->globalEntities();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }   
      if (! ($this->droits & ADMIN)){ $this->vue->retour =  MBR_NO_ACTION;  }
      else {  $this->vue->retour = '<p><a href="'.BASEURL.'">'.ENT_REGIOS.'</p>'; }      
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
  * Affichage à l'ecran
  */
  protected function output($var)
  { echo $var; }
}



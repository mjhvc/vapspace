<?php

/** 
	@class AuthCtrl
	@brief Gestion de l'identification à l'application via table sql: T_HUM 

	- Fichiers utilisés par la Vue :
		+ squelette.tpl
		+ stdt_connect.tpl
		+ pied.tpl 
	
	@author marcvancraesbeck@scarlet.be
	@copyright [GNU Public License](@ref licence.dox)
*/

include("Controleur.class.php");

class AuthCtrl extends Controleur
{ 
  private $contexte;                /**< string nom du contexte*/
  private $tabTables = array();     /**< array le tableau avec le nom de(s) tables sql utilisées */
  private $table;                   /**< string nom de la table sql principale du contexte */
  private $dataContexte = array();  /**< array vide */
  private $back;                    /**< string message html d'erreur du contexte */
  private $statut;                  /**< string le nom du statut */
  private $droits;                  /**< integer codage simple du droit de l'utilisateur */
  public function __construct()
  {
    $this->contexte = "Auth";
    parent::__construct($this->contexte);   
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits(); 
    $this->tabTables = $this->modele->getTables();
    $this->table = $this->tabTables[0];
    $this->back =  '<span style="color:red">'.AUTH_BACK_ERROR.'</span>'; 
  }

  /** Les entités de la vue du contexte à appeler à chaque fois
  L'entité index_contexte est dans pied.tpl
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head = AUTH_TITLE_HEAD;
    $this->vue->titre_page = AUTH_TITLE_PAGE;
    $this->vue->connecte = $this->statutConnect();
  }
  /** constructEntity cote enfant: on appelle parent:: 
  on récupère les variables du contexte enfant pour la vue
  */  
  protected function constructEntity($tableau=array(),$table=NULL,$flgStat=NULL)
  {  
    try { 
      parent::constructEntity();   
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

   /** controler() surcharge de parent::controler
  appel de parent::controler() puis fait des controles specifiques au contexte
  retourne $this->vue->retour
  */ 
  protected function controler($tableau)
  {  
    try { 
      $this->vue->retour = parent::controler($tableau);
      if (empty($this->vue->retour)){  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){
          $ramasseError[] = $testOblig;
        }
        elseif (! $ctldns = $this->filtre->filtreDns($tableau['mail'])){
        $ramasseError[] =  $tableau['mail'].AUTH_MAIL_ERROR;
        }
        elseif ( $tableau['mail'] == MBR_NOMAIL ) {
          $ramasseError[] =  $tableau['mail'].AUTH_MAIL_PERMIS;
        }
        elseif ($postCorrupt = $this->filtre->postInattendu($tableau)){
          throw new MyPhpException('formulaire du contexte:'.$this->contexte.' corrompu');
        }
        if (! empty($ramasseError[0])){ 
          $this->vue->retour = '<span style="color:red">'.$ramasseError[0].'</span>'; 
          $ramasseError = array();
        }
      }
      else { $this->vue->retour = '<span style="color:red">'.$this->vue->retour.'</span>' ; } 
      return $this->vue->retour;  
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }
  }  
  protected function vueAuth($strLang=NULL)
  {
    $this->globalEntities();
    $this->vue->url_auth = BASEURL."index.php?ctrl=auth&amp;action=login".$strLang ;
    $url_subscribe = BASEURL."index.php?ctrl=membre&amp;action=inscrire".$strLang ;
    $url_help = BASEURL."index.php?ctrl=help";
    $this->constructEntity();
    $this->vue->hreFR = BASEURL."index.php?ctrl=auth&amp;lang=fr";
    $this->vue->hreNL  = BASEURL."index.php?ctrl=auth&amp;lang=nl";
    $this->vue->setFile("contenu","stdt_connect.tpl");
    $this->vue->pied = '<p class="piedleft"><a href="'.$url_subscribe.'">'.AUTH_SUBSCRIBE.'</a></p>'.
                         '<p class="piedleft"><a href="'.$url_help.'">'.AUTH_HELP.'</a></p>';
    $out = $this->vue->render("page");
    return $out;
  }     
  /** lancer controler()
     - si controles ok, et si session créée:
     - on recalcule les droits, on redirige en conséquence :  
     - un membre va dans son vapspace, un admin dans le coin des admin 
  */
  public function login()
  {
    try {  
      $loc = '';  
      if (filter_has_var(INPUT_GET,'lang') && ($lang = filter_input(INPUT_GET,'lang',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(fr|nl)$#'))))) {
        $getLang = '&lang='.$lang; 
      }
      else { $getLang = ''; } 
      $this->vue->retour = $this->controler($_POST); 
      if (empty($this->vue->retour)) {
        $mail = filter_input(INPUT_POST,'mail',FILTER_VALIDATE_EMAIL);
        $passe = $_POST['passe'];
        if ($sess = $this->creerSession($mail,$passe)){ 
          $this->droits = $this->getDroits();
          $this->statut = $this->getStatut();
          if (($this->droits & RESPONSABLE)) { $loc = "Location: ".BASEURL."index.php?token=".$_SESSION['token'].$getLang; }
          else { $loc = "Location: ".BASEURL."index.php?ctrl=membre&token=".$_SESSION['token'].$getLang; }
        }
        else { $loc = "Location: ".BASEURL."index.php?ctrl=auth&retour=1".$getLang; }
        header($loc);      
      }
      else {
        $page = $this->vueAuth($getLang);
        echo $page; 
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    } 
  }
  /** methode logout: on detruit la session et le cookie, on redirige sur site public
  */
  public function logout()
  {
    $loc = "Location: http://".$_SERVER["SERVER_NAME"];
    unset($_SESSION);
    if ($getid = session_id()) { session_destroy(); }
    header($loc);
  }
  
  /** methode index appellés si aucunes autres, traitement des réponses à 3 variables GET : retour,sess et del
  
  */  
  public function index()
  {
    try {  
      $lang = '';           
      if (filter_has_var(INPUT_GET,'retour') && ($msg  = filter_input(INPUT_GET,'retour',FILTER_VALIDATE_INT,array('options'=>array('min_range'=>1,'max_range'=>1))))) {
        $this->vue->retour = $this->back; 
      }
      if (filter_has_var(INPUT_GET,'sess') && ($msg  = filter_input(INPUT_GET,'sess',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^ko$#'))))) {
        $this->vue->retour = SESSION_ERROR ;
        $lang = $_SESSION['lang'];
        unset($_SESSION);
        if ($getid = session_id()) { session_destroy(); } 
      }
      if (filter_has_var(INPUT_GET,'del') && ($msg = filter_input(INPUT_GET,'del',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^membre$#'))))){
        $this->vue->retour = UNSUBSCRIBE;
      } 
      if (!empty($lang)) { $getLang = '&lang='.$lang; }
      else { $getLang = ''; }
      $page = $this->vueAuth($getLang);
      echo $page;
    } 
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
}


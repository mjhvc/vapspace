<?php
/**
* @category VAP
* @copyright Marc Van Craesbeeck, 2012
* @license GPL
* @package controleur
* Classe MembreCtrl, finalisation de la classe abstraite Ab_Membre
*/
require_once('ABS_Iter.php');
class PassCtrl extends ABS_Iter
{
  private $contexte;
  protected $action; 
  protected $urlPages;

  public function __construct($contexte=NULL)
  {
    $this->contexte = 'Passage';
    $this->urlPages = BASEURL."index.php?ctrl=pass&amp;action=voir&amp;token=".$this->token."&amp;page=";
    parent::__construct($this->contexte); 
    $this->table = $this->tables[0];
  }  
   
   /**
  * les operations d'attribution à la vue pour construire la page d'inscrption et update du contexte 'passage'
  * utilise $this->constructEntity() et $this->calculPassive()  
  */
  protected function vuePassage($action,$valppk=NULL,$tableau=array())
  {
    $out = '';    
    $this->globalEntities($this->passage);
    if ($action == 'insert'){ 
      $this->vue->script_formu = BASEURL."index.php?ctrl=pass&amp;action=inscrire&amp;token=".$this->token; 
      $this->vue->submit = SUBMIT_SUBSCRIBE;
    }
    elseif ($action == 'update'){ 
      $this->vue->script_formu = BASEURL."index.php?ctrl=pass&amp;action=update"; 
      $this->vue->submit = SUBMIT_UPDATE;
    }   
    if (empty($tableau['lieu'])) { $this->vue->lieu = ''; }
    else { $this->vue->lieu = $tableau['lieu']; }
    if ($this->droits & ADMIN) {
      if (empty($tableau['lienant'])) { $this->vue->select = $this->getSelect('lienant',$this->tabAnten); }
      else { $this->vue->select = $this->getSelect('lienant',$this->tabAnten,$tableau['lienant']); }
    }
    else { $this->vue->select =  $this->calculChampPassif('lienant',$_SESSION['nomantenne']); }
      
    if ($action == 'update'){ 
      $this->vue->setFile("cachez","multi_Membre_ValPPK.tpl");      
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;    
    }
    else { $this->vue->cachez = ''; } 
    $this->vue->setFile("contenu","vuePassage.tpl");  
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;        
  }
  
  /** 
  * methode inscrire , publique pour inserer un lieu dans ".T_MEET." par http. 
  * gère : un formulaire d'inscription à vide (si pas de $_POST) par parent::constructEntity()
  * gère : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire()
  * gère : creation d'un fichier de cache $idant.'_lieu.csv' dans le cache de l'antenne
  * fais l'insertioN ET NE TOUCHE PAS AU CACHE
  */ 
  public function inscrire() {
    try {      
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }       
      elseif (! ($this->droits & RESPONSABLE)){
        $out =  MBR_NO_ACTION;
        $this->output($out); ;
      }
      else { 
        $this->basicData($this->passage,$this->statut);                        
        if (empty($_POST)) {
          $this->action = 'insert';    
          $page = $this->vuePassage($this->action);
          $this->output($page);      
        }
        else {        
          $this->vue->retour = $this->controler($_POST);
          if (empty($this->vue->retour)){       
            if ($pass = $this->modele->inscrire($_POST)){ 
              $loc = "Location: ".BASEURL."index.php?in=lieu&token=".$token;
              header($loc); exit(0);  
            }
            else  { throw new MyPhpException('Impossible d\'insérer un lieu'); }
          }          
          else {    
            $page = $this->vuePassage($this->action,NULL,$_POST);
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
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      } 
      elseif (! ($this->droits & RESPONSABLE)){
        $this->vue->retour =  MBR_NO_ACTION;
      }
      else {  
        if (filter_has_var(INPUT_POST,'antenne')  && ($getIdant = filter_input(INPUT_POST,'antenne',FILTER_VALIDATE_INT)) && (array_key_exists($getIdant,$this->tabAnten))) { 
          $idant = intval($getIdant);
        } 
        else { $idant = intval($_SESSION['idant']); }      
        //sql et pagination : preparation 
        $condi = T_MEET.".lienant = :lienant";
        $tabMarq = array('lienant'=>$idant);           
        $sqlFull = $this->sqlFactory($condi,$condi);
        $nbrPages = $this->comptePages($sqlFull['pagin'],$tabMarq);       
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
        
        $stmt->execute(array(':lienant'=>$idant));
        $compteur = $stmt->rowCount();      
        if ($compteur == 0){ $this->vue->retour = MBR_NO_RESULT; }
        else { $this->vue->retour = ''; }
        $this->globalEntities(NULL,$idant);
        $this->vue->setFile("contenu","voir_Passage.tpl");
        $this->vue->setBlock("contenu","ligne","lignes"); 
        $this->vue->anten = $this->tabAnten[$idant];       
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
          $classe = "A".(($noligne++)%2);
          $ppk = $result['idpass'];
          $this->vue->cssline = $classe;
          $this->vue->lieu = $result["lieu"];
          $this->vue->editer  = BASEURL."index.php?ctrl=pass&amp;action=editer&amp;line=".$ppk."&amp;token=".$token;
          $this->vue->supprimer = BASEURL."index.php?ctrl=pass&amp;action=supprimer&amp;line=".$ppk."&amp;token=".$token; 
          $this->vue->append("lignes","ligne");
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
      elseif (! ($this->droits & RESPONSABLE)){
        $out =  MBR_NO_ACTION;
        $this->output($out); 
      }       
      else {
        $this->action = 'update';      
        if ($cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT ))
        {          
          $data = $this->modele->selection($cle);  
          $tableauVue = $data[$this->table]; //translation de $data en array à 1 dimmension        
          $page = $this->vuePassage($this->action,$cle,$tableauVue);
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
      elseif (! ($this->droits & RESPONSABLE)){
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
            if (! $this->filtre->filtreClePPK($cle)){
              throw new MyPhpException('le parametre: ppk dans le contexte: '.$this->contexte.' est non valide');
            }            
            elseif ($tabout = $this->modele->mettreajour($_POST,$cle)){
              $locLieu = $_POST['lienant'].$this->masqLieu;
              $this->viderCache($_POST['lienant'],$locLieu);
              if ($ok = $this->ecrisCache($_POST['lienant'],$this->masqLieu)) { //appel à la class surchargée d'abord 
                $loc =  "Location: ".BASEURL.'index.php?up=passage&token='.$token; 
                header($loc);
              }                       
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
      $data = array();
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){ 
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko"; 
      }  
      elseif (! ($this->droits & RESPONSABLE)){
        $out =  MBR_NO_ACTION;
        $this->output($out); ;  
      }
      elseif ( $cle = filter_input(INPUT_GET,'line',FILTER_VALIDATE_INT )) {
        if ( $ok = $this->filtre->getOld('idtraj','lienpass',$cle,T_ROUTE,1)){ //protection referentielle             
          $loc = "Location: ".BASEURL."index.php?delko=lieu&token=".$token;
        }          
        else {
          $champ = array('lienant'); //extraction de lienant obligatoire pour calculer les caches (basés sur lienant)
          $data = $this->modele->ligneParValeurs($champ,$this->table,'idpass',$cle);
          $lienant = $data['lienant'];
          if ($ok = $this->modele->supprimeUneLigne($cle,$this->table)){
            $locLieu = $lienant.$this->masqLieu;
            $this->viderCache($lienant,$locLieu);
            if ($ok = $this->ecrisCache($lienant,$this->masqLieu)) { //appel à la class surchargée d'abord 
              $loc =  "Location: ".BASEURL.'index.php?del=passage&token='.$token; 
            } 
            else {  $loc = "Location: ".BASEURL."index.php?delko=lieu&token=".$token; }                    
          }
          else {  $loc = "Location: ".BASEURL."index.php?delko=lieu&token=".$token; }
        }
      }
      else { throw new MyPhpException('le parametre: line dans le contexte: '.$this->contexte.' est non valide'); }          
      header($loc);       
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
    if (is_array($data)) { print_r($data); }
    else {    echo $data; }
  }
}  

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
* Class IterCtrl gestion des contextes 
*   Passage.ini table: ".T_MEET."
*   Trajet.ini  table: ".T_ROUTE."
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require_once("ABS_Iter.php");
include_once("StaticMailLang.class.php");
class IterCtrl extends ABS_Iter
{
  private $contexte;
  protected $action; 
  protected $urlPages;
  const KEYANT = 1; 

  public function __construct($contexte=NULL)
  {
    $this->contexte = 'Trajet';
    $this->urlPages = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;token=".$this->token."&amp;page=";
    parent::__construct($this->contexte);
    $this->table = $this->tables[0];  
  }  
  
  /**
  * La vue pour les operations d'insert et update contexte 'Trajet' 
  */
  protected function vueTrajet($action,$valppk=NULL,$tableau=array())
  {
    $out = '';    
    if ($action == 'insert') {
      $this->globalEntities($this->trajet);
      $this->vue->script_formu = BASEURL."index.php?ctrl=iter&amp;action=inscrire&amp;iterant=".$_SESSION['idant']."&amp;token=".$this->token; 
      $tabLieu = $this->recupLieu(); 
      $this->vue->anten = $_SESSION['nomantenne'];
      $this->vue->it_pre = $_SESSION['prenom'];
      $this->vue->it_nom = $_SESSION['nom'];
      if ($_SESSION['inscrit'] == 'deux') { 
        $this->vue->setFile("deal","vueDeal.tpl"); 
        if (empty($tableau['deal'])) {
          $this->vue->offreCheck = $this->vue->demandeCheck = $this->vue->offreCheck_val = $this->vue->demandeCheck_val = "";
        }
        elseif (isset($tableau['deal']) && ($tableau['deal'] == 'offre')) { $this->getChecked(true,'offreCheck'); $this->getChecked(false,'demandeCheck'); }
        elseif (isset($tableau['deal']) && ($tableau['deal'] == 'demande')) { $this->getChecked(false,'offreCheck'); $this->getChecked(true,'demandeCheck'); }
      }
      elseif ($_SESSION['inscrit'] == 'auto') { $this->vue->deal = '<p>'.ITER_AUTO_DEAL.'</p>'; }
      elseif ($_SESSION['inscrit'] == 'pieton'){ $this->vue->deal = '<p>'.ITER_PEDESTRIAN_DEAL.'</p>'; }  
      $this->vue->cachez = ''; 
    }
    elseif ($action == 'update') {
      $this->globalEntities($this->trajet,$tableau['lienant']);
      $this->vue->script_formu = BASEURL."index.php?ctrl=iter&amp;action=update"; 
      $tabLieu = $this->recupLieu($tableau['lienant']);   
      $cleAnt = $tableau['lienant'];     
      $this->vue->anten = $this->tabAnten[$cleAnt];
      $this->vue->it_pre = '';
      $this->vue->it_nom = '';
      $this->vue->setFile("deal","vueDeal.tpl");
      if ($tableau['deal'] == 'offre') { $this->getChecked(true,'offreCheck'); $this->getChecked(false,'demandeCheck'); }
      elseif ($tableau['deal'] == 'demande') { $this->getChecked(false,'offreCheck'); $this->getChecked(true,'demandeCheck'); }
      $this->vue->setFile("cachez","vueTrajet_hidden.tpl");      
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;
      $this->vue->lienpass = $tableau['lienpass'] ; 
      $this->vue->cleHum = $tableau['lienhum'];
      $this->vue->cleAnt = $tableau['lienant'];  
    }
     
    if (empty($tableau['commune'])) { $this->vue->commune = '' ; }
    else  { $this->vue->commune = $tableau['commune'] ; }
    if (empty($tableau['passage'])) { $this->vue->passage = ''; }
    else { $this->vue->passage = $tableau['passage']; }
    if (empty($tableau['destination'])) { $this->vue->destination = ''; }
    else { $this->vue->destination = $tableau['destination']; }
    if (empty($tabLieu)) { 
      $this->vue->select = $this->vue->preSelect = ""; 
      $this->vue->labPass = ITER_MEET;
    }  
    else {
      $this->vue->preSelect = '<label for="lienpass"><strong>'.ITER_STDT_MEET.'</strong></label><br />';
      $this->vue->labPass = ITER_NEW_MEET;
      if (empty($tableau['lienpass'])) { $this->vue->select = $this->getSelect('lienpass',$tabLieu,$tabLieu[0]); }  
      else { $this->vue->select = $this->getSelect('lienpass',$tabLieu,$tableau['lienpass']); }       
    }
    if (empty($tableau['periode'])) { $this->vue->periode = ""; }
    else { $this->vue->periode = $tableau['periode']; }
 
    $this->vue->setFile("contenu","vueTrajet.tpl");  
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;        
  }
  /**
  * la methode privée qui inscrit un lieu pour les membres qui ont rempli le formu de iter
  * vérifie que lieu n'est pas déjà présent 
  * si non, fais l'insertion en base, vide et recree le cache des lieux
  * doit rester dans la classe abstraite
  */
  protected function inscrireLieu($tableau)
  {
    $lienpass = NULL; $taBack = $tabLieux = array();
    $this->basicData($this->passage,$this->statut);   //charger contexte minCont   
    $tabLieux = $this->recupLieu(); 
    if (in_array($tableau['lieu'],$tabLieux)) { $taBack['error'] = ITER_ALLREADY; }
    elseif ($this->lienpass = $this->modele->inscrire($tableau)) {
      $locLieu = $tableau['lienant'].$this->masqLieu;
      $this->viderCache($tableau['lienant'],$locLieu);
      if ($ok = $this->ecrisCache($tableau['lienant'],$this->masqLieu)) { 
        $taBack['lienpass'] = $this->lienpass; 
      }
    }
    else { $taBack['error'] = ITER_IN_ERROR;  } 
    return $taBack;
  } 
      
 
  /** 
  * methode inscrire, publique pour inserer contexte 'trajet' dans ".T_ROUTE." par http. 
  * gère : un formulaire d'inscription à vide (si pas de $_POST) par parent::constructEntity()
  * gère : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire() 
  * avec pre-inscription  d'un 'passage' dans le contexte 'passage' par appel de $this->inscrireLieu
  * si ok, $this->lienpass y est affecté
  * fait l'insertion en base et :
  * - comptabilise l'offre ou la demande dans T_MEET
  * - ecris le cache noeud via $this->noeudcache()
  */ 
  public function inscrire() 
  {
    try {      
      $tabPassage = $iter = $preInPassage = $nomInject = $valInject = array();
      $this->action = 'insert';   
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc); exit(0);
      }       
      elseif (! ($this->droits & MEMBRE)){ $page = MBR_NO_ACTION ; }
      elseif ((!filter_has_var(INPUT_GET,'iterant')) || ($_GET['iterant'] != $_SESSION['idant'])) { $page = ITER_OUTANT; }                
      else { 
        $this->basicData($this->trajet,$this->statut);                  
        if (empty($_POST)) { $page = $this->vueTrajet($this->action); }
        else { 
          $_POST['lienhum'] = $_SESSION['idhum'];
          $_POST['lienant'] = $_SESSION['idant'];
          if (empty($_POST['deal']) && ($_SESSION['inscrit'] == 'auto')) { $_POST['deal'] = 'offre'; }
          elseif (empty($_POST['deal']) && ($_SESSION['inscrit'] == 'pieton')){ $_POST['deal'] = 'demande'; }
          
          foreach ($_POST as $key=>$val) { //copier $_POST sans 'passage' 
            if ($key == 'passage') { ;}
            else { $iter[$key] = $val; }
          }
          $this->vue->retour = $this->controler($iter); //pour un controle contexte trajet seul
          if (empty($this->vue->retour)) {
            if (!empty($_POST['passage']) && (trim($_POST['passage']) != '')) { //si 'passage' est présent dans $_POST
              $tabPassage = array('lienant'=>$_SESSION['idant'],'lieu'=>$_POST['passage']);
              $this->basicData($this->passage,$this->statut);   //charger contexte minCont : passage  
              $this->vue->retour = $this->controler($tabPassage); //controler le contexte passage seul
              if (empty($this->vue->retour)) { //inscrire le lieu après tous les controles
                $preInPassage = $this->inscrireLieu($tabPassage);            
                if (array_key_exists('error',$preInPassage)) { $this->vue->retour = $preInPassage['error']; }
                elseif (array_key_exists('lienpass',$preInPassage)) { $iter['lienpass'] = $preInPassage['lienpass']; }//recup du bon lienpass   
              }
            }//si pas de $_POST['passage'], $_POST['lienpass'] DOIT être > 0    
            elseif (empty($_POST['lienpass']) || (intval($_POST['lienpass']) === 0)) {
              $this->vue->retour = '<span style="color:red">'.ITER_NO_CHOICE.'</span>'; } 
          }
          if (empty($this->vue->retour)) { //controles et (pre)inscription du lieu sont ok
            $this->basicData($this->trajet,$this->statut);  //retour contexte trajet
            $nomInject[] = 'deal';
            $valInject[] = $iter['deal'];
            if (empty($this->lienpass)) { $this->lienpass = $iter['lienpass']; }          
            $injection = array(T_ROUTE,$nomInject,$valInject);
            $this->valppk = $this->modele->inscrire($iter,$injection);  
            if (!empty($this->valppk)) { 
              $this->comptabilise($this->lienpass,'idpass',T_MEET,$_POST['deal']);
              $this->comptabilise($_SESSION['idhum'],'lienperso',T_CORE);
              $this->viderCache($_POST['lienant'],'./*_noeud.csv');
              $this->noeudCache($_POST['lienant'],'_noeud.csv');
              $loc = "Location: ".BASEURL.'index.php?ctrl=membre&new=trajet&token='.$token;
              header($loc);exit(0); 
            } 
            else { throw new MyPhpException('Impossible d\'insérer un iter'); }
          }  //retour des erreurs         
          else { $page = $this->vueTrajet($this->action,NULL,$_POST); }      
        } 
      }
      $this->output($page);
    } 
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
  * Un membre peut editer ses trajets et les modifier (update)
  */  
  public function editer()
  {
    try {  
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }  
      else {
        $this->basicData($this->trajet,$this->statut);   //charger contexte 
        if (filter_has_var(INPUT_GET,'iteredit') && ($getIter = filter_input(INPUT_GET,'iteredit', FILTER_VALIDATE_INT)) && ($this->filtre->filtreClePPK($getIter))) {
          $lienhum = $this->filtre->getOld('lienhum','idtraj',$getIter,$this->table);
          if (($_SESSION['idhum'] == $lienhum) || ($this->droits & RESPONSABLE)) {         
            $data = $this->modele->selection($getIter);
            $tabTrajet = $data[$this->table];
            $tabTrajet['idtraj'] = $data['valppk'];
            $page = $this->vuetrajet('update',$getIter,$tabTrajet); 
            $this->output($page);
          }
          else   { throw new MyPhpException('Iter/Editer, propriétaire de l\'iter incorrect'); }
        } 
        else { throw new MyPhpException('Iter/editer: iteredit invalide'); }   
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
  * Mise à jour du trajet
  * fait l'insertion en base et :
  * - comptabilise l'offre ou la demande dans T_MEET
  * - ecris cache de noeud via $this->noeudCache()
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
      elseif (! ($this->droits & MEMBRE)){ $page =  MBR_NO_ACTION; }
      else {  
        $tabPassage = $iter = $preInPassage = $nomInject = $valInject = $old = array();    
        $this->action = 'update';
        $this->basicData($this->trajet,$this->statut); 
        if ($cle = filter_input(INPUT_POST,'ppk',FILTER_VALIDATE_INT)) {
          foreach ($_POST as $key=>$val) { //copier $_POST sans 'passage' 
            if ($key == 'passage') { ;}
            else { $iter[$key] = $val; }
          }
          $this->vue->retour = $this->controler($iter); //pour un controle contexte trajet seul
          if (empty($this->vue->retour)) {
            if (!empty($_POST['passage']) && (trim($_POST['passage']) != '')) { 
              $tabPassage = array('lienant'=>$_SESSION['idant'],'lieu'=>$_POST['passage'],'itoken'=>$_POST['itoken']);
              $this->basicData($this->passage,$this->statut);   //charger contexte minCont : passage  
              $this->vue->retour = $this->controler($tabPassage); //controler le contexte passage seul
              if (empty($this->vue->retour)) { //inscrire le lieu après tous les controles
                $preInPassage = $this->inscrireLieu($tabPassage);            
                if (array_key_exists('error',$preInPassage)) { $this->vue->retour = $preInPassage['error']; }
                elseif (array_key_exists('lienpass',$preInPassage)) { $iter['lienpass'] = $preInPassage['lienpass']; } 
              }
            }//si pas de $_POST['passage'], $_POST['lienpass'] DOIT être > 0    
            elseif (!(intval($_POST['lienpass']) > 0)) {$this->vue->retour = '<span style="color:red"'.ITER_NO_CHOICE.'</span>'; } 
          } 
          if (empty($this->vue->retour)) {
            if (empty($this->lienpass)) { $this->lienpass = $iter['lienpass']; }
            $this->basicData($this->trajet,$this->statut);
            $col = array('lienpass','deal');
            $old = $this->modele->ligneParValeurs($col,$this->table,'idtraj',$cle); //selection ancien lienpass = idpass et ancien deal         
            $this->decomptabilise($old['lienpass'],'idpass',T_MEET,$old['deal']); //decomptabiliser ancien idpass selon ancien deal             
            $masq = $iter['lienant'].'_noeud.csv';  
            $nomInject[] = 'deal';
            $valInject[] = $iter['deal'];
            $injection = array(T_ROUTE,$nomInject,$valInject); 
            if ($ok = $this->modele->mettreajour($iter,$cle,$injection)) { 
              $this->comptabilise($this->lienpass,'idpass',T_MEET,$_POST['deal']); //comptabilise le nouveau trajet
              $this->viderCache($iter['lienant'],$masq);
              $this->noeudCache($iter['lienant'],'_noeud.csv');
              $loc = "Location: ".BASEURL.'index.php?ctrl=membre&upmbr=trajet&token='.$token;
              header($loc);exit(0); 
            }
          } //retour des erreurs
          else { $page = $this->vueTrajet($this->action,$cle,$_POST); }
        }
        else { throw MyPhpException('Contexte Iter/update: cle ppk invalide'); }
      }
      $this->output($page);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }   
  }
  /**
  * methode voir(), n'utilise pas $this->sqlFactory car iter est multi controleurs
  * 3 visions sont possibles: selon le lieu ('iterlieu'), selon l'antenne ('antiter'), selon le membre ('iterhum')
  * s'y ajoutent: le deal (offre ou demande) et la pagination 
  * pour chaque situation, un array $transIter est créé qui véhicule les paramètres afin de construire la requete 
  */  
  public function voir()
  {
    try {  
      $page = $noligne = 0; $transIter = $tabLieux = array(); $getIter= NULL; $edit = false; $spaceAnten = NULL;
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }
      else {
        $this->basicData($this->trajet,$this->statut); 
        //filtrage des paramètres
        if (filter_has_var(INPUT_GET,'deal') && ($deal = filter_input(INPUT_GET,'deal',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(offre|demande|non)$#'))))) { ; }
        else { throw new MyPhpException('Contexte Iter/voirIter, deal non valide'); } 
        if (filter_has_var(INPUT_GET,'iterlieu') && ($getIter = filter_input(INPUT_GET,'iterlieu', FILTER_VALIDATE_INT))) {
          if (filter_has_var(INPUT_GET,'antiterlieu') && ($getAnt = filter_input(INPUT_GET,'antiterlieu',FILTER_VALIDATE_INT)) && (array_key_exists($getAnt,$this->tabAnten))) { 
            $spaceAnten = $getAnt;
          }
          else { throw new MyPhpException('Contexte Iter/voirIter, idant obligatoire'); } //un lieu depend de son antenne
          if (! $tabLieux = $this->recupLieu($getAnt)) { 
            throw new MyPhpException('Contexte Iter/voirIter, idant ou lieu inexistant');
          }
          if (filter_has_var(INPUT_GET,'lipiter') && ($page = filter_input(INPUT_GET,'lipiter',FILTER_VALIDATE_INT))) { ; }         
          $transIter = array('T.lienpass','iterlieu',$getIter,'lipiter',$deal);         
        }
        elseif (filter_has_var(INPUT_GET,'antiter') && ($getIter = filter_input(INPUT_GET,'antiter', FILTER_VALIDATE_INT)) && (array_key_exists($getIter,$this->tabAnten))) {
          $spaceAnten = $getIter;          
          if (filter_has_var(INPUT_GET,'anpiter') && ($page = filter_input(INPUT_GET,'anpiter',FILTER_VALIDATE_INT))) { ; }          
          if ($this->droits & RESPONSABLE) { $edit = true; }
          $transIter = array('T.lienant','antiter',$getIter,'anpiter',$deal); 
        }  
        elseif (filter_has_var(INPUT_GET,'iterhum') && ($getIter = filter_input(INPUT_GET,'iterhum', FILTER_VALIDATE_INT)) && ($this->filtre->getOld('idhum','idhum',$getIter,T_HUM))) {          
          if (filter_has_var(INPUT_GET,'hupiter') && ($page = filter_input(INPUT_GET,'hupiter',FILTER_VALIDATE_INT))) { ; }  
          if ($getIter == $_SESSION['idhum']) { $edit = true; }          
          $transIter = array('T.lienhum','iterhum',$getIter,'hupiter');
        }
        else { throw new MyPhpException('Contexte Iter/voirIter, cle d\'acces non valide'); }
        
        //preparer datas variables        
        $cpt = count($transIter);
        $amp = '&amp;';
        if ($cpt > 4) {
          $url = $this->urlPagiter.$transIter[1].'='.$getIter.$amp.'deal='.$transIter[4].$amp.$transIter[3].'=';
          $varCondi =  " AND ".$transIter[0].' =  :'.$transIter[1]." AND T.deal= :deal ";
          $tabMarq = array($transIter[1]=>$transIter[2],'deal'=>$transIter[4]);        
        }
        else {
          $url = $this->urlPagiter.$transIter[1].'='.$getIter.$amp.$transIter[3].'=';        
          $varCondi =  " AND ".$transIter[0].' =  :'.$transIter[1];
          $tabMarq = array($transIter[1]=>$transIter[2]);
        }
        //consruire requetes sql
        $fixeCondi = " WHERE  P.idpass = T.lienpass  AND H.idhum = T.lienhum ";
        $condIter = $fixeCondi.$varCondi;
        $stdSql = "SELECT DISTINCT idtraj,commune,destination,periode,lieu,nom,deal ".
                   " FROM ".T_ROUTE." AS T,".T_MEET." AS P,".T_HUM." as H ".$condIter;
        $pageSql = "SELECT COUNT(*) as total FROM ".T_ROUTE." AS T, ".T_MEET." AS P, ".T_HUM." as H ".$condIter;
        
        //pagination        
        $nbrPages = $this->comptePages($pageSql,$tabMarq);
        if (!empty($page)) {
          $pageActuelle = $page;
          if ($page > $nbrPages) {
            $pageActuelle = $nbrPages;
          }
        } 
        else { $pageActuelle = 1 ; }
        $dataPagin = $this->pagination($nbrPages,$pageActuelle,$url,$stdSql);
        //execution de la requete
        $fsql = $dataPagin['sql'];
        $dbh = $this->modele->getCnx(); 
        $stmt = $dbh->prepare($fsql);
        foreach($tabMarq as $key=>$val) {
          if (in_array($key,$this->tabMarqI)){ $data_type = PDO::PARAM_INT; }
          elseif (in_array($key,$this->tabMarqS)) { $data_type = PDO::PARAM_STR ; }
          $marqueur = ":".$key;            
          $stmt->bindValue($marqueur,$val,$data_type);  
        }   
        $stmt->execute();
        $compteur = $stmt->rowCount();
        //traitement du resultat par la vue
        if (!empty($spaceAnten)) { $this->globalEntities(NULL,$spaceAnten); }
        else { $this->globalEntities(); }       
        $this->vue->pagination = $dataPagin['pages'] ;
        if ($compteur == 0){ $this->vue->retour = MBR_NO_RESULT ; }
        else {
          if (!$edit) { $this->vue->setFile("contenu","voir_Iter.tpl"); }
          else { $this->vue->setFile("contenu","voir_Iteredit.tpl"); }
          $this->vue->setBlock("contenu","vapiters","iters");
          while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $classe = "A".(($noligne++)%2); 
            $this->vue->cssline = $classe;
            $this->vue->nom = $result['nom'];
            if ($result['deal'] == 'offre') { $this->vue->deal = ITER_OFFER ; }
            elseif ($result['deal'] == 'demande') { $this->vue->deal = ITER_DEMAND ; }
            $this->vue->commune = $result['commune']; 
            $this->vue->destination = $result['destination'];
            $this->vue->periode = $result['periode'];
            $this->vue->lieu = $result['lieu'];
            $this->vue->urlitercnx = $this->urlIterCnx.$amp.'iter='.$result['idtraj'];
            if (!$edit) { $this->vue->editer = $this->vue->supprimer = ''; }
            else { 
              $this->vue->editer = BASEURL."index.php?ctrl=iter&amp;action=editer&amp;iteredit=".$result['idtraj']."&amp;token=".$token ; 
              $this->vue->supprimer =  BASEURL."index.php?ctrl=iter&amp;action=supprimer&amp;iterdel=".$result['idtraj']."&amp;token=".$token ;           
            }            
            $this->vue->append("iters","vapiters");
          }     
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
  * methode mailiter, reçoit par $_GET une PK (idtraj), la filtre et renvoi de mail  
  * au membre connecté avec le mail du membre initiateur
  * comptabilise l'envoi via champ compta de T_ROUTE
  */
  public function mailiter()
  {
    try {
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){        
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      } 
      else {
        $this->basicData($this->trajet,$this->statut);   //charger du bon contexte
        if (!(filter_has_var(INPUT_GET,'iter') && ($idtraj = filter_input(INPUT_GET,'iter', FILTER_VALIDATE_INT)) && ($ok = $this->filtre->filtreClePPK($idtraj)))) {
          throw new MyPhpException('Iter/mailiter: ppk invalide');
        }
        else {
          //confection requete sql
          $sql = " SELECT nom, mail,membre FROM ".T_HUM." AS H, ".T_CORE." as C, ".T_ROUTE." AS T  WHERE  "
                  ."  T.idtraj = :idtraj  AND T.lienhum = H.idhum  AND  T.lienhum = C.idvap ";
          $dbh = $this->modele->getCnx();
          $stmt = $dbh->prepare($sql);
          $stmt->bindValue(':idtraj',$idtraj,PDO::PARAM_INT);
          $stmt->execute();
          $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
          //confection du mail
          $sujet = ITER_CONTACT ;
          $contenu = MailLang::textIterContact($rslt['nom'],$rslt['membre'],$rslt['mail'],$_SESSION['nom'],$_SESSION['prenom'],$_SESSION['lang']);
          if ($this->mailer($_SESSION['mail'],'info@vap-vap.be',$sujet,$contenu)) {  //garder une trace de la conclusion          
            $this->comptabilise($idtraj,'idtraj',T_ROUTE); 
            $loca = "Location: ".BASEURL."index.php?ctrl=membre&action=index&token=".$this->token."&trajok=ok" ;
            header($loca);
          }
          else  {
            $this->globalEntities();
            $this->vue->retour = ENT_ERROR;
            $this->vue->setFile("pied","pied.tpl"); 
            $out = $this->vue->render("page");
            $this->output($out);             
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
  * decomptabilise un iter puis supprime cet iter
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
      else {      
        $this->basicData($this->trajet,$this->statut);   //charger contexte 
        if (filter_has_var(INPUT_GET,'iterdel') && ($getIter = filter_input(INPUT_GET,'iterdel', FILTER_VALIDATE_INT)) && ($this->filtre->filtreClePPK($getIter))) {
          $lienhum = $this->filtre->getOld('lienhum','idtraj',$getIter,$this->table);
          if ((!($this->droits & RESPONSABLE)) && ($_SESSION['idhum'] != $lienhum)) {
            throw new MyPhpException('Iter/supprimer, propriétaire de l\'iter incorrect');
          }
          else {
            $champ = array('deal','lienpass','lienant');
            $data = $this->modele->ligneParValeurs($champ,$this->table,'idtraj',$getIter);
            $deal = $data['deal']; 
            $lienpass = $data['lienpass']; 
            $lienant = $data['lienant'];
            $masq = './'.$lienant.'_noeud.csv';
            $this->decomptabilise($lienpass,'idpass',T_MEET,$deal);
            $this->decomptabilise($lienhum,'idvap',T_CORE);       
            $this->modele->supprimeUneLigne($getIter,$this->table) ;
            $this->viderCache($lienant,$masq);
            $loc = "Location: ".BASEURL."index.php?ctrl=membre&del=iter&token=".$token;
            header($loc); exit(0);
          }    
        }
        else { throw new MyPhpException('Iter/supprimer, trajet incorrect'); }
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    

  }
  /**
  * La methode index() est une page statique de documentation
  */
  public function index()
  {
    try {    
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){ 
        $this->vue->urlTrajet = ""; 
        $this->globalEntities();
      }  
      else { 
        $this->vue->urlTrajet = BASEURL."index.php?ctrl=iter&amp;action=inscrire&amp;iterant=".$_SESSION['idant']."&amp;token=".$token; 
        $this->globalEntities(NULL,NULL,1);
      }
      $this->vue->setFile("contenu","index_Trajet.tpl");  
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
  * la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
  */
  protected function output($data)
  {
    if (is_array($data)) { print_r($data); }
    else {    echo $data; }
  }
}

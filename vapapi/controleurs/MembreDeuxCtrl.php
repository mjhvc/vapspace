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
* Class MembreDeuxCtrl, finalisation de la classe abstraite ABS_Membre 
* avec gestion de liste antennes imbriquées dans liste de régions
* @category VAP
* @copyright Marc Van Craesbeeck, 2012
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require('ABS_Membre.php');
class MembreCtrl extends ABS_Membre
{
  /**
  * controler() surcharge de parent::controler
  * appel de parent::controler() puis fait des controles specifiques au contexte
  * utilise $this->filtre : une instance de la classe FiltreData appellé dans parent
  * utilise parent::changerFiltres()
  * retourne $this->vue->retour
  * prudence avec les elseif et des conditions toujours vraies...
  */ 
  protected function control($tableau,$lang) 
  {  
    try {     
      $this->vue->retour = $this->preCtrl($tableau);
      if (empty($this->vue->retour)){         
        $ramasseError = array(); 
        //$hidden permet d'inclure des formulaires 'cachez' dans la liste des formulaires attendus par $this->filtre->postInattendu($tableau)    
        $hidden = array('ppk','itoken');
        if ($this->action == 'update') { 
          if (! $ok = $this->getOld($tableau['ppk'])){ throw new MyPhpException('contexte editer Membre, cle invalide'); } 
        }
        if ($this->phase == 'un') {        
          if ($postCorrupt = $this->filtre->postInattendu($tableau,$hidden)){  
            throw new MyPhpException('formulaire du contexte:'.$this->contexte.' corrompu par:'.$postCorrupt);
          }          
          elseif ($testOblig = $this->filtre->filtreOblig($tableau,'nopass',$this->tableUn)){
            $ramasseError[] = CONTROL_OBLIG.$testOblig;            
          }
          elseif (($tableau['mail'] != MBR_NOMAIL) && (! ($ctldns = $this->filtre->filtreDns($tableau['mail'])))) {  
            $ramasseError[] =   $tableau['mail'].AUTH_MAIL_ERROR;
          }
          elseif ((!empty($tableau['naissance'])) && ($badnaiss = $this->filtre->naissance($tableau['naissance']))) {
            $ramasseError[] =  DATE_ERROR;
          } /*                    
          elseif (($this->action == 'inscription') && ($tableau['mail'] != 'PasDeMail') && ($dblMail = $this->filtre->doublon('mail',$tableau['mail'],$this->tableUn))) {   
            $ramasseError[] = $tableau['mail'].THIS_EXIST;  
          }
          elseif (($this->action == 'update')  && ($tableau['mail'] != 'PasDeMail') && ($this->oldMail != $tableau['mail']) && ($dblMail = $this->filtre->doublon('mail',$tableau['mail'],$this->tableUn))) {   //($this->oldMail != 'PasDeMail')
            $ramasseError[] =  $tableau['mail'].THIS_EXIST;            
          } */                            
        }
        elseif ($this->phase == 'deux') {
          $this->filtreRegion = $this->changerFiltres('Region');
          if (! $this->filtreRegion->filtreClePPK($tableau['lienreg'])) {
            throw new MyPhpException("Membre phase deux: cle regionale d'inscription non valide");
          }
          elseif ($hiddenOblig = $this->filtre->filtreOblig($tableau,'nopass',$this->tableUn)) {  //Les donnees de phase Un doivent etre presentes
            throw new MyPhpException("Membre Phase Deux: donnees incompletes");
          }
          elseif (($this->action == 'update') && ($tableau['itoken'] != $this->token)) {
            throw new MyPhpException("Membre Phase Deux update, tableau corrompu");
          }   
          elseif (!empty($tableau['membre']) && ($tableau['membre'] != $this->oldMbr) && ($dblMbr = $this->filtre->doublon('membre',$tableau['membre'],$this->tableDeux))) { 
            $ramasseError[] = $tableau['membre'].THIS_EXIST; 
          }
          elseif (($this->statut == 'responsable') && ($_SESSION['idant'] != $tableau['lienant'])) {
            $ramasseError[] = ANT_BAD . " : ". $this->allAntennes[$tableau['lienant']] ;
          }
          elseif ((!empty($tableau['cachet'])) && ($badate = $this->filtre->filtreDate($tableau['cachet']))) {  
            if ($badate == 1) { $ramasseError[] =  DATE_FORMAT ; }
            elseif ($badate == 2) { $ramasseError[] =  DATE_ERROR ; }
          }
          elseif ((!($this->droits & MEMBRE)) && (!empty($tableau['spam'])) && (! $spam = $this->filtre->antiSpam($tableau['spam'],$lang))){   
            $ramasseError[] = MBR_ANTISPAM ;
          }
          elseif ((!empty($tableau['passe'])) && ($tableau['passe'] != $tableau['confirmation'])){  
            $ramasseError[] =  PASS_CONFIRM ;  
          }
          elseif (($this->action == "update") && ($testOblig = $this->filtre->filtreOblig($tableau,'nopass'))){
            $ramasseError[] = CONTROL_OBLIG.$testOblig;           
          }
          elseif (($this->action == "inscription") && ($testOblig = $this->filtre->filtreOblig($tableau))) { 
            $ramasseError[] = CONTROL_OBLIG.$testOblig; 
          } 
          elseif (($this->action == "inscription") && ($tableau['mail'] != MBR_NOMAIL) && ($doublonPass =  $this->ctlPass($tableau['mail'],$tableau['passe'],$this->action))) {
            $ramasseError[] = PASS_EXIST;
          } 
          elseif (($this->action == "update") && ($tableau['mail'] != MBR_NOMAIL) && (!empty($tableau['passe'])) && ($doublonPass =  $this->ctlPass($tableau['mail'],$tableau['passe'],$this->action))) {
            $ramasseError[] = PASS_EXIST;
          }                                           
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
  /**
  * Les operations de la vue pour la partie 1 du formulaire d'inscription
  * @param $action: string, determine l'action sql qui sera effectuée 
  * @param $valPPK: int, la cle PK qui identifie la ligne du membre
  * @param $tableau: array, un tableau de donnee à transformer en entités de la vue
  * fichier tpl appellés : inscrire_Membre-t.tpl, multi_Membre_ValPPK.tpl, pied.tpl
  */
  private function vuePhaseUn($action,$valppk=NULL,$tableau=array()) //propre à un descendant
  {
    $out = false;
    $this->globalEntities();               
    if (empty($tableau["genre"])) { $this->vue->civil = $this->getSelect("genre",$this->civilite);}
    else { $this->vue->civil = $this->getSelect("genre",$this->civilite,$tableau["genre"]);}
    if ($action == 'inscription' || $this->action == 'install') { 
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=inscrire&amp;phase=un";        
      $this->vue->cachez = ''; 
      $this->vue->introReg = INTRO_REG; 
      $this->vue->intro =  INTRO_SUBSCRIBE; 
      if ($this->action == 'install') { $this->vue->intro =  INTRO_INSTALL; }    
    }
    elseif ($action == 'sansMail') {
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=inscrire&amp;phase=un";        
      $this->vue->cachez = ''; 
      $this->vue->introReg =  INTRO_REG; 
      $this->vue->intro =  SANSMAILS_ALERT;
    }
    elseif ($action == 'update') {
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=update&amp;phase=un";  
      $this->vue->intro =  INTRO_ONE;     
      $this->vue->introReg = INTRO_UP_REG;      
      $this->vue->setFile("cachez",'multi_Membre_ValPPK.tpl');        
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;
    }
    if (empty($tableau)) { $this->constructEntity(NULL,$this->tableUn); }
    else { $this->constructEntity($tableau,$this->tableUn); }
    $this->vueCheck();
    if ($action == 'sansMail') { $this->vue->mail = MBR_NOMAIL; }
    $this->vue->type = MBR_SUB_NEXT;
    $this->vue->setFile('contenu','inscrire_Membre_un.tpl'); 
    $this->vue->setBlock("contenu","choixReg","provinces"); 
    foreach ($this->tabRegions as $cle=>$nomRegion) {
      if (!empty($tableau['lienreg']) && $cle == $tableau['lienreg']){ $this->getChecked(true,'regionCheck'); }  
      else { $this->getChecked(false,'regionCheck'); }  
      $this->vue->nomreg = $nomRegion ;
      $this->vue->clereg = $cle ;            
      $this->vue->append('provinces','choixReg');   
    }
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;
  }
  /**
  * Les operations de la vue pour la partie 2 du formulaire d'inscription
  * @param $action: string, determine l'action sql qui sera effectuée 
  * @param $valPPK: int, la cle PK qui identifie la ligne du membre
  * @param $tableau: array, un tableau de donnee à transformer en entités de la vue
  * @param $mode:string,  informe si le param $tableau est à 1D ou à 2D
  * fichier tpl appellés : inscrire_Membre-deux.tpl, multi_Membre_ValPPK.tpl, pied.tpl + parent::vueStatut() + parent::vueStatique()
  */
  private function vuePhaseDeux($action,$valPPK=NULL,$tableau=array(),$mode=NULL) //propre à un descendant
  { 
    $out = false;
    $this->globalEntities();
    if (empty($mode)) { //On est avec un tableau extrait de la base en 2 dimmensions
      $clereg = $tableau[T_HUM]['lienreg']; 
      $cleant = $tableau[T_CORE]['lienant'];
    }
    else { 
      $clereg = $tableau['lienreg']; 
      if (!empty($tableau['lienant'])) { $cleant = $tableau['lienant']; }
    }
    $listeAntennes = $this->valeursPassive($this->idxPassif,$clereg);
    //rajouter éventuellement un 'hors-antenne' à chaque liste d'antenne, ce qui crée une antenne hors-antenne spécifique à chaque région 
    if ((!empty($this->o_ant)) && ($clereg != $this->o_reg)) { $listeAntennes[$this->o_ant] = $this->o_nomant; }                     
    $this->vueStatut($this->statut);
    $this->vueRecommand(); // appel (dans classe parente) du chargement de entite multiRecomm
    if ($action == 'update') {
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=update&amp;phase=deux";
      $this->vue->intro_passe =  PASS_UPDATE; 
      $this->vue->spam = ''; 
      $this->vue->type = SUBMIT_UPDATE; 
    }
    elseif ($action == 'inscription') {
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=inscrire&amp;phase=deux"; 
      $this->vueSpam($this->statut);
      if (!empty($tableau['spam'])) { $this->vue->spam_code = $tableau['spam']; }
      else { $this->vue->spam_code = ''; }
      $this->vue->cachez = ''; 
      $this->vue->type = SUBMIT_SUBSCRIBE;
      $this->vue->intro_passe = PASS_SUBSCRIBE;
    }
    if (! empty($tableau['confirmation'])) { $this->vue->confirmation = $tableau['confirmation']; }
    else { $this->vue->confirmation = ''; } 
    if (empty($cleant)) { $this->vue->select = $this->getSelect($this->champPassif,$listeAntennes); }
    else {  $this->vue->select = $this->getSelect($this->champPassif,$listeAntennes,$cleant); }
    if ($action == 'inscription' || $mode == 'error') { $this->constructEntity($tableau);}
    elseif ($action == 'update' && empty($mode)){ //$tableau est à 2D, sauf $tableau['inliaison']
      $maxStat = intval($tableau['inliaison']);   
      foreach ($this->tabTables as $table){        
        $nomtable = strval($table); 
        if ($table == $this->liaisonTable){
          for ($x=0;$x<$maxStat;$x++){
            $this->constructEntity($tableau[$x],$nomtable,'flag');
          }
        }
        else { $this->constructEntity($tableau[$table],$nomtable); }   
      }
    }        
    $this->vueCheck(); 
    $this->vue->setFile("contenu","inscrire_Membre_deux.tpl"); 
    $this->vueStatique('I');
    if ($action == 'update'){
      $this->vue->passe = ''; $this->vue->passe_o = '';
      $this->vue->confirmation = ''; 
      $this->vue->setFile("cachez",'multi_Membre_ValPPK.tpl');        
      $this->vue->ppk = $valPPK;
      $this->vue->itoken = $tableau['itoken'];    
    } 
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;   
  }
   /** 
  * methode inscrire:
  * gère : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire()
  * gère la vue par appel de $this->vuePhaseUn et $this->vuePhaseDeux
  * gère : un re-affichage du formulaire rempli si erreurs du client par parent::constructEntity($_POST)
  * les erreurs clientes sont traitées, les erreurs d'ecriture lancent une exception
  */ 
  public function inscrire() 
  {
    $dataMbr = $nomChamp = $valChamp = $injection = $mbrTab = array(); 
    $page = '';
    try { 
      if (filter_has_var(INPUT_GET,'membre') && $_GET['membre'] == 'sans') { $this->action = 'sansMail'; }
      else { $this->action = 'inscription'; } 
      if (filter_has_var(INPUT_GET,'phase')) { $this->phase = filter_input(INPUT_GET,'phase',FILTER_SANITIZE_SPECIAL_CHARS) ; }
      else { $this_phase = "un"; } 
      if (filter_has_var(INPUT_GET,'install') && $_GET['install'] == 'oui') { 
        if (!($back = $this->filtre->calculKeyMax('idhum',T_HUM))) { $this->action = 'install'; }
        else { $this->action = 'inscription'; }
      } 
      if (filter_has_var(INPUT_GET,'lang')) { $lang = filter_input(INPUT_GET,'lang',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(fr|nl)$#'))); }
      else { $lang = $_SESSION['lang']; }
      if (empty($_POST)){ 
        $mbrTab['lang'] = $lang; 
        if ($this->statut == 'responsable') { $mbrTab['lienreg'] = $_SESSION['lienreg']; }        
        $page = $this->vuePhaseUn($this->action,NULL,$mbrTab); //injection du parametre de langue dans la vue ici
      }     

      elseif ($this->phase == 'un') {
        $this->vue->retour = $this->control($_POST,$lang);
        if (empty($this->vue->retour)) { //pas d'erreurs du client? on lance phase deux 
          if ($this->statut == 'responsable') { $_POST['lienant'] = $_SESSION['idant']; }                             
          $page = $this->vuePhaseDeux($this->action,NULL,$_POST,true);
        }
        else {  //traiter les erreurs clients 
          $page = $this->vuePhaseUn($this->action,NULL,$_POST); 
        } 
      }
      elseif ($this->phase == 'deux') {
        $this->vue->retour = $this->control($_POST,$lang);          
        if (!empty($this->vue->retour)) { //traitement des erreurs clients
          $page = $this->vuePhaseDeux($this->action,NULL,$_POST,'error');  
        }
        else { //pas d'erreurs du client? on lance l'inscription     
          $dataMbr = $_POST;            
          $dataMbr['passe'] = crypt($_POST['passe']);              
          if (!empty($_POST['membre'])) { //classer le numero de membre    
            $decimal = StaticMembre::classmbr($_POST['membre']);
            $nomChamp[] = 'numbr';
            $valChamp[] = $decimal ;            
          }
          if (empty($_POST['cachet'])) { //repechage du cachet (date) d'inscription invisible
            $leCachet = date('Y-m-d');
            $nomChamp[] = 'cachet' ;
            $valChamp[] = $leCachet ;
          }
          if (empty($dataMbr['plaque'])) { $dataMbr['plaque'] = MBR_NO; }
          if (empty($dataMbr['connu'])) { $dataMbr['connu'] = MBR_NOCONNU; }
          if (empty($dataMbr['tel'])) { $dataMbr['tel'] =  MBR_NO; }
          if (empty($dataMbr['pays'])) { $dataMbr['pays'] =  MBR_LAND; }
          if ($dataMbr['mail'] == MBR_NOMAIL) { $dataMbr['mail'] = UNI_NOMAIL; }
          if (!empty($nomChamp)) { $injection = array(T_CORE,$nomChamp,$valChamp);}
          if ($this->valppk = $this->modele->inscrire($dataMbr,$injection)){
            if (intval($this->valppk) === 1) { //installation du premier membre : un admin 
              $firstData = array('statut'=>'admin');
              $this->modele->mettreajour($firstData,$this->valppk);
              $this->statut == 'install';    
            }
            if (($this->statut == 'install') || ($this->statut == 'anonym')) { 
              $this->creerSession($dataMbr['mail'],$_POST['passe']); 
              $loc = "Location: ".BASEURL."index.php?ctrl=membre&new=membre&token=".$_SESSION['token'];
            }             
            else { $loc =  "Location: ".BASEURL."index.php?in=membre&token=".$this->token;  } 
            if ($dataMbr['mail'] != UNI_NOMAIL) {  //gestion du mailing       
              $cleant = $dataMbr['lienant'];  $clereg = $dataMbr['lienreg'];              
              $dataMbr['nomantenne'] = $this->allAntennes[$cleant];
              $dataMbr['province'] = $this->tabRegions[$clereg];
              $dataMbr['passe'] = $_POST['passe'];
              $textMbr = StaticMembre::textToMail($dataMbr,$dataMbr['lang']);
              $sujet =  StaticMembre::sujeToMail($dataMbr['lang']); 
              $this->mailer($dataMbr['mail'],$this->expedit,$sujet,$textMbr);   
              if ($this->statut == 'anonym') {
                $tabExpResp = $this->selectMailResp($_SESSION['idant']);
                if (!empty($tabExpResp)) {
                  $sujetResp =   StaticMembre::sujeToResp($dataMbr['lang']);
                  $textResp =  StaticMembre::textToResp($dataMbr['nom'],$dataMbr['prenom'],$dataMbr['nomantenne'],$dataMbr['lang']);
                  foreach ($tabExpResp as $dest) {
                    $this->mailer($dest,$this->expedit,$sujetResp,$textResp);
                  }
                }
              }
              if (!empty($_POST['promoMsg'])) { $promoText = strip_tags($_POST['promoMsg']); }
              else { $promoText = ''; } 
              $sujetPromo =   $dataMbr['prenom'].' '.$dataMbr['nom']. MAIL_PROMO_SUJET;       
              foreach ($this->promoMail as $Mail) {
                if ((!empty($_POST[$Mail])) && ($this->filtre->matchMail($_POST[$Mail]))) {
                  $promoFinTxt = StaticMembre::textPromo($dataMbr['nom'],$dataMbr['prenom'],$_POST[$Mail],$dataMbr['lang'],$promoText);
                  $this->mailer($_POST[$Mail],$this->expedit,$sujetPromo,$promoFinTxt);                  
                }
              }                
            }
            header($loc);exit(0);
          }
          else { throw new MyPhpException('Impossible d\'insérer un Membre'); } 
        } //fin inscription 
      }//fin de phase deux
      if (!empty($page)) { $this->output($page); } 
      else { throw new MyPhpException('Inscrire un membre, variable GET:phase  incorrecte'); }   
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }           
  }
   /**
  * la fonction qui lance la phase un de miseàjour(update) d'un membre depuis la base de donnee
  * utilise $this->getPK() pour filtrer $_GET['line']
  * utilise $this->modele->selection($cle) pour selectionner la ligne du contexte en base  
  * utilise $this->vuePhaseUn($action,$valppk,$tableau) pour sortir la vue
  * utilise parent::validSess() en controle de seesion  
  */
  public function editer() //propre à un descendant
  {
    $data = $dataPhaseUn=array();    
    try {
      $this->action = 'update';
      $cle = $this->getPK('GET'); 
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }    
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }      
      elseif (! empty($cle)) { //extraction des donnees  de la database (array à 2 dimm)         
        $data = $this->modele->selection($cle);
        if ($data[T_HUM]['mail'] == UNI_NOMAIL) { $data[T_HUM]['mail'] = MBR_NOMAIL; }        
        $dataPhaseUn = $data[T_HUM];
        $page = $this->vuePhaseUn($this->action,$cle,$dataPhaseUn);
        $this->output($page);
      }       
      else { throw new MyPhpException('le parametre: line dans le contexte:'.$this->contexte.' est requis'); }    
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }     
   /**
  * methode update()
  * appel $this->getPK('POST)
  * appel $this->modele->mettreajour($tableau,$cle) qui réalise un update en base pour la ligne concernee 
  * si erreur du client suites aux filtres : lance $this->vueMbr()
  * les erreurs clientes sont traitées, les erreurs d'ecriture lancent une exception
  */
  public function update()  //abstract
  {
    $injection = $dataPhaseDeux = $listePhaseUn = array();
    $dataMbr = $data = $nomChamp = $valChamp = array();
    try {      
      $this->globalEntities();        
      if (! ($this->droits & MEMBRE)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko"; header($loc);
      }
      else { 
        $this->action = 'update';
        $this->phase = filter_input(INPUT_GET,'phase',FILTER_SANITIZE_SPECIAL_CHARS) ;
        $cle = $this->getPK('POST'); 
        if (empty($cle)) { throw new MyPhpException('le parametre: ppk dans le contexte: '.$this->contexte.' est non valide'); }       
        elseif ($this->phase == 'un') { 
          $this->vue->retour = $this->control($_POST,$_SESSION['lang']);
          if (empty($this->vue->retour)) { //pas d'erreurs du client?  extraction des donnees  de la database (array à 2 D)         
            $dataPhaseDeux = $this->modele->selection($cle);       
            $listePhaseUn = $this->modele->getListeAttr();
            foreach ($listePhaseUn as $val) { //prise en compte des nouvelles donnees clients de la phase un
              if (!empty($_POST[$val])) { $dataPhaseDeux[T_HUM][$val] = $_POST[$val]; }
            }
            $dataPhaseDeux['itoken'] = $_POST['itoken'];
            $page = $this->vuePhaseDeux($this->action,$cle,$dataPhaseDeux);
          }
          else { $page = $this->vuePhaseUn($this->action,$cle,$_POST); } 
          $this->output($page);
        }
        elseif ($this->phase == 'deux') {
          $this->vue->retour = $this->control($_POST,$_SESSION['lang']);
          if (!empty($this->vue->retour)) { //traiter erreurs clients
            $page = $this->vuePhaseDeux($this->action,$cle,$_POST,'error');
            $this->output($page);
          }
          else {  //lancer l'update
            $dataMbr = $_POST;                                           
            if (!empty($_POST['passe'])){ $dataMbr['passe'] = crypt($_POST['passe']); }          
            if (!empty($dataMbr['membre']) && ($this->oldMbr != $dataMbr['membre'])) {
              $decimal = StaticMembre::classmbr($dataMbr['membre']);
              $nomChamp[] = 'numbr' ;
              $valChamp[] = $decimal ;            
            }
            if (empty($_POST['fiche'])) {
              $nomChamp[] = 'fiche' ;
              $valChamp[] = 'non';
            }
            if (empty($_POST['news'])) {
              $nomChamp[] = 'news' ;
              $valChamp[] = 'non';
            }
            if (empty($dataMbr['plaque'])) { $dataMbr['plaque'] = MBR_NO; }
            if (empty($dataMbr['connu'])) { $dataMbr['connu'] = MBR_NOCONNU; }
            if (empty($dataMbr['tel'])) { $dataMbr['tel'] =  MBR_NO; }
            if (empty($dataMbr['pays'])) { $dataMbr['pays'] =  MBR_LAND; }
            if ($dataMbr['mail'] == MBR_NOMAIL) { $dataMbr['mail'] = UNI_NOMAIL; }
            if (!empty($nomChamp)) { $injection = array(T_CORE,$nomChamp,$valChamp);}            
            if ($valid = $this->validSess($dataMbr['itoken'])) {              
              $this->modele->mettreajour($dataMbr,$cle,$injection); 
              if (!($this->droits & RESPONSABLE)) { $loc = "Location: ".BASEURL."index.php?ctrl=membre&upmbr=ok&token=".$dataMbr['itoken']; }
              else { $loc = "Location: ".BASEURL."index.php?&up=membre&token=".$dataMbr['itoken']; }   
            }
            else { $loc = "Location: ".BASEURL."index.php?ctl=auth&action=index&sess=ko"; }
            header($loc);
          }
        }
        else { throw new MyPhpException('Contexte Membre->update:  variable GET:phase  incorrecte'); }       
      }    
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
* methode index() liste des actions possibles pour tous les membres : vapspace
*/  
  public function index() 
  {
    try { 
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; }
      else { $token = NULL; }
      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }
      else {
        if (filter_has_var(INPUT_GET,'region') && ($getIdreg = filter_input(INPUT_GET,'region', FILTER_VALIDATE_INT)) && (array_key_exists($getIdreg,$this->tabRegions))) {
          $clereg = $getIdreg;        
        }
        else { $clereg = intval($_SESSION['lienreg']); }
        if (filter_has_var(INPUT_GET,'antenne')  && ($getIdant = filter_input(INPUT_GET,'antenne',FILTER_VALIDATE_INT)) && (array_key_exists($getIdant,$this->allAntennes))) { 
          $idant = $getIdant;
        } 
        else { $idant = $_SESSION['idant']; }
        if (filter_has_var(INPUT_GET,'new')){
          if ($_GET['new']=='membre') { 
            $this->vue->retour = MBR_WELKOM; 
          }
          elseif ($_GET['new']=='trajet') { $this->vue->retour =  MBR_ROUTE_SUBSC; }
        }
        if (filter_has_var(INPUT_GET,'upmbr') && ($up = filter_input(INPUT_GET,'upmbr',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour =  MBR_UPDATE;
        } 
        if (filter_has_var(INPUT_GET,'trajok') && ($con = filter_input(INPUT_GET,'trajok',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour =  MAIL_CONTACT_MBR;
        }
        if (filter_has_var(INPUT_GET,'del') && ($del = filter_input(INPUT_GET,'del',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour =   $del.ENT_DELETED;
        }             
        $cle_mbr = intval($_SESSION['idhum']);               
        $nomAntenne = $this->allAntennes[$idant];
         
        $urlIterOffre = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=offre&amp;token=".$token."&amp;antiterlieu=".$idant."&amp;iterlieu=";
        $urlIterDemande = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=demande&amp;token=".$token."&amp;antiterlieu=".$idant."&amp;iterlieu=";
        $urlRegStats = BASEURL."index.php?ctrl=membre&amp;action=stats&amp;token=".$token."&amp;regstat=";     
        
        $this->globalEntities($idant);         
        $this->vue->url_chatPost = BASEURL."index.php?ctrl=chat&amp;action=poster&amp;chatidant=".$idant."&amp;token=".$token; 
        $this->vue->url_chatGlb =  BASEURL."index.php?ctrl=chat&amp;action=voirGlob&amp;chatidant=".$idant."&amp;token=".$token;        
        $this->vue->url_chatMens = BASEURL."index.php?ctrl=chat&amp;action=voirMens&amp;chatidant=".$idant."&amp;token=".$token;
        $this->vue->urlIter = BASEURL."index.php?ctrl=iter&amp;token=".$token; 
        $this->vue->urlTrajet = BASEURL."index.php?ctrl=iter&amp;action=inscrire&amp;iterant=".$_SESSION['idant']."&amp;token=".$token;          
        $this->vue->urlProfil = $urlProfil = BASEURL."index.php?ctrl=membre&amp;action=editer&amp;line=".$_SESSION['idhum']."&amp;token=".$token ;       
        $this->vue->nomantenne =  $nomAntenne;                    
        $this->vue->vue_mbr =  BASEURL."index.php?ctrl=membre&amp;action=voir&amp;class=glb&amp;lienant=".$idant;  
        $this->vue->urlCarte = BASEURL."index.php?ctrl=cartes&amp;token=".$token;     
        $this->vue->chatGlob =  CHAT_ALL.$nomAntenne;                            
        $this->vue->chatMens =  CHAT_MONTH;
        $this->vue->urlDelMbr = BASEURL."index.php?ctrl=membre&amp;action=supprimer&amp;token=".$token."&amp;line=".$cle_mbr;
        //$this->vue->ensRegions = parent::getSelect('regstat',$this->allRegions);
        //$this->vue->url_stat = $urlMbrStats;      
        
        if ($_SESSION['iters'] > 0) {                          
          $this->vue->iterperso = MBR_TRIPS;  
          $this->vue->urliterperso = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=non&amp;iterhum=".$_SESSION['idhum']."&amp;token=".$token;        
        }        
        else { $this->vue->urliterperso = $this->vue->iterperso = ''; } 
        if ($_SESSION['chats'] > 0) {
          $this->vue->urlchatperso = BASEURL."index.php?ctrl=chat&amp;action=voir&amp;chathum=".$_SESSION['idhum']."&amp;token=".$token;
          $this->vue->chatperso = MBR_CHATS;
        }
        else { $this->vue->urlchatperso = $this->vue->chatperso = ''; }       
        $this->vue->setFile("contenu","index_Membre_RegAnt.tpl");
        $this->vue->setBlock("contenu","regions","province");
        $this->vue->setFile("Ant","s_listeAnt.tpl");
        foreach($this->tabRegions as $cle=>$val) {
          $this->vue->listeregion = $val;
          $regListeAnt = $this->valeursPassive($this->idxPassif,$cle);
          $this->vue->setVar($cle,"");       
          foreach ($regListeAnt as $newCle=>$newAnt) {
            $this->vue->urlRegAnt = BASEURL."index.php?ctrl=membre&amp;antenne=".$newCle."&amp;region=".$cle."&amp;token=".$token;
            $this->vue->regAnt = $newAnt;
            $this->vue->append($cle,"Ant");
          }
          $this->vue->newListeAnt = $this->vue->getVar($cle);
          $this->vue->append('province','regions');
        }
        $this->vue->setBlock("contenu","STATISTIQUES","statReg");
        foreach ($this->allRegions as $cle=>$val) {
          $this->vue->nomreg = $val;
          $this->vue->urlRegStats = $urlRegStats.$cle;
          $this->vue->append("statReg","STATISTIQUES");
        }
         
        if ($cacheChat = $this->recupData($idant,'Chat')) { 
          $this->vue->setBlock("contenu","vapchats","chat");
          foreach($cacheChat as $id=>$chat) {  
            $this->vue->urlchat =  BASEURL."index.php?ctrl=chat&amp;action=editer&amp;chatppk=".$chat[0]."&amp;token=".$token."&amp;mode=single" ;
            $this->vue->sujetchat = $chat[1];
            $this->vue->append("chat","vapchats");
          } 
        }
        else { 
          $this->vue->urlchat = $this->vue->url_chatMens = $this->vue->url_chatGlb = "#"; 
          $this->vue->sujetchat = $this->vue->chatMens = $this->vue->chatGlob = $nomAntenne.MBR_NOCHATS;     
        }
        if ($tabLieu = $this->recupData($idant,'Passage')) { //echo '<pre>';print_r($tabLieu);echo '</pre>';
          $numli = 0;
          $this->vue->setFile("tabIter","index_Membre_Vapspace.tpl"); 
          $this->vue->setBlock('tabIter',"spaciter","spiter");
          foreach($tabLieu as $num=>$lieu) {
            $classe = "A".(($numli++)%2);
            $this->vue->cssline = $classe;
            $this->vue->lieu = $lieu[1];
            $this->vue->urlOffre = $urlIterOffre.$lieu[0];
            $this->vue->offre = $lieu[2];
            $this->vue->urlDemande = $urlIterDemande.$lieu[0];
            $this->vue->demande = $lieu[3];            
            $this->vue->append("spiter","spaciter");
          }        
          $this->vue->vapant = $nomAntenne;
          $this->vue->O_urliterant = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=offre&amp;antiter=".$idant."&amp;token=".$token;
          $this->vue->D_urliterant = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=demande&amp;antiter=".$idant."&amp;token=".$token;         
        }
        else { $this->vue->tabIter =  "<p>".$nomAntenne.MBR_NOTRIPS ."</p>\n" ; }                 
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
  * Appelé par le moteur ajax du script js/scriptVap2.js
  */  
  public function promo()
  {
    header("Content-Type: text/plain ; charset=iso-8859-1");  //indique que le type de la réponse renvoyée au client sera du Texte
    header("Cache-Control: no-cache , private");              //anti Cache pour HTTP/1.1  
    header("Pragma: no-cache");                               //anti Cache pour HTTP/1.0
                                                                 
    $cpt = count($this->promoMail);
    $tabRetour = array();
    $x = 0;     

    if (!empty($_POST['promoMsg']) && (trim($_POST['promoMsg']) != '')) { $promoText = strip_tags($_POST['promoMsg']); }
    else { $promoText = ''; }
    $message = iconv('UTF-8','ISO-8859-1',$promoText); 
    $sujetPromo =  $_SESSION['prenom'].' '.$_SESSION['nom']. MAIL_PROMO_SUJET;
   
    foreach ($this->promoMail as $Mail) {
      if (!empty($_POST[$Mail])) {
        $courriel = iconv('UTF-8','ISO-8859-1',$_POST[$Mail]);        
        if ($this->filtre->matchMail($courriel)) {
          $promoFinTxt = StaticMembre::textPromo($_SESSION['nom'],$_SESSION['prenom'],$courriel,$_SESSION['lang'],$message);
          if ($this->mailer($courriel,$this->expedit,$sujetPromo,$promoFinTxt)) { ++$x; }
        }
      }
    }                 
    if (empty($x)) { $retour =  MAIL_PROMO_ERROR.$courriel; }
    else { $retour = MAIL_PROMO_OK ; }
    $this->output($retour);
  }
  /**
* La methode de gestion des statistiques membres
*/
  public function stats()
{
try {
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }      
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko";
        header($loc);
      }      
      elseif (! ($this->droits & MEMBRE)){ $this->vue->retour = MBR_NO_ACTION; }
      else { 
        if ((filter_has_var(INPUT_POST,'regstat')) && ($regstat = filter_input(INPUT_POST,'regstat',FILTER_VALIDATE_INT))) {
          $condiReg = " AND H.lienreg='".$regstat."' ";
          $this->vue->nomRegion = $this->allRegions[$regstat];
        }
        elseif ((filter_has_var(INPUT_GET,'regstat')) && ($regstat = filter_input(INPUT_GET,'regstat',FILTER_VALIDATE_INT))) {
          $condiReg = " AND H.lienreg='".$regstat."' ";
          $this->vue->nomRegion = $this->allRegions[$regstat];
        }
        else { 
          $condiReg = ""; 
          $this->vue->nomRegion = ENT_STATS_GLOB; 
          $regstat = '0';
        }
        if (filter_has_var(INPUT_GET,'recalcul')) { $recalcul = true; }   
        else { $recalcul = false; }       

        //Les 4 requètes sql        
        $genUtilStat = "SELECT COUNT(idvap) AS RST "
          ." FROM ".T_CORE." C JOIN ".T_HUM." H ON C.idvap=H.idhum "
          ." WHERE C.inscrit=:utilise and H.genre=:sex ". $condiReg;
        $genMobStat = "SELECT COUNT(lienhum) as RST  "
          ." FROM ".T_HUM_SOC." M JOIN ".T_HUM." H ON M.lienhum=H.idhum  "
          ." WHERE M.lientrans=:idtrans "
          ." AND M.utilisation='oui' "          
          . $condiReg;
        $antUtilStat = "SELECT COUNT(idvap) AS RST "
          ." FROM ".T_CORE." C JOIN ".T_HUM." H ON C.idvap=H.idhum "
          ." WHERE C.inscrit=:utilise and H.genre=:sex and C.lienant=:antenne";
        $antMobStat = "SELECT COUNT(lienhum) as RST "  
          ." FROM ".T_HUM_SOC." M, ".T_CORE." C, ".T_HUM." H "
          ." WHERE M.lientrans=:idtrans "
          ." AND C.lienant = :lienant "            
          ." AND M.lienhum=H.idhum "
          ." AND C.idvap = H.idhum " ;

        //Les variables
        $userVap = array('pieton','auto','deux');
        $collect = $genCollect = array();
        $collect[MBR_GLB_REG] = array(MBR_FEM=>0,MBR_MAL=>0,MBR_GLB_GLB=>0);
        $collect[MBR_GLB_MOB] = array();
        $moduo = $this->modele->getContexte($this->contexte,$this->statut);
        $schema = $this->modele->getSchemaDataStatique();  
        $dbh = $this->modele->getCnx();

        //Calcul des statistiques de l'utilisation des VAP
        $stmt = $dbh->prepare($genUtilStat);
        foreach ($userVap as $util) {  
          $stmt->bindParam(':utilise',$util,PDO::PARAM_STR);
          $stmt->bindValue(':sex','F',PDO::PARAM_STR);
          $stmt->execute();
          $result = $stmt->fetch(PDO::FETCH_ASSOC);            
          $collect[MBR_FEM][$util]  = $result['RST'];
          $stmt->bindValue(':sex','M',PDO::PARAM_STR);
          $stmt->execute();
          $result = $stmt->fetch(PDO::FETCH_ASSOC); 
          $collect[MBR_MAL][$util]  = $result['RST'];
        }
        // Calcul des statitstiques de mobilités (sociétés et abonnements)
        $genMobStatUn = $genMobStat." AND M.abonne='non' ";
        $stmt = $dbh->prepare($genMobStatUn);
        foreach ($schema as $nom=>$opt) { $antMob[$nom] = $opt['valCleLiaison']; }
        foreach ($antMob as $soc=>$idtrans){
          $stmt->execute(array(':idtrans'=>$idtrans));
          $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
          $collect[SPONS_SANS][$soc] = $rslt['RST'];      
        }
        $genMobStatDeux =  $genMobStat. " AND M.abonne='oui' ";
        $stmt = $dbh->prepare($genMobStatDeux);
        $antMob = array();
        foreach ($schema as $nom=>$opt) { $antMob[$nom] = $opt['valCleLiaison']; }
        foreach ($antMob as $soc=>$idtrans){
          $stmt->execute(array(':idtrans'=>$idtrans));
          $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
          $collect[SPONS_AVEC][$soc] = $rslt['RST'];      
        }
        //calcul des statistiqes globales
        foreach ($collect[MBR_FEM] as $cle=>$cpt) { $collect[MBR_GLB_REG][MBR_FEM] += $cpt; }
        foreach ($collect[MBR_MAL] as $key=>$xpt) { $collect[MBR_GLB_REG][MBR_MAL] += $xpt; }
        $dividende = $collect[MBR_GLB_REG][MBR_GLB_GLB] = $collect[MBR_GLB_REG][MBR_FEM] + $collect[MBR_GLB_REG][MBR_MAL] ;
        foreach ($antMob as $soc=>$idtrans) {
          $collect[MBR_GLB_MOB][$soc] = $collect[SPONS_AVEC][$soc] + $collect[SPONS_SANS][$soc];  
        }
        ksort($collect);              
        //mise en place des données.     
        foreach ($collect as $lettre=>$tableau) { 
          $typoTab = array();  $cleTypo = '';        
          foreach ($tableau as $cle=>$cpt) {
            if ($cle == 'pieton'){ $cleTypo = MBR_PIEDS; }
            elseif ($cle == 'auto'){ $cleTypo = MBR_AUTOS; }
            elseif ($cle == 'deux'){ $cleTypo = MBR_DEUX; }
            else { $cleTypo = $cle; }
            $valpourcent = $this->pourcentage($cpt,$dividende);
            $typoTab[$cleTypo] = $cpt.','.$valpourcent;
            $genCollect[$lettre] = $typoTab;  
          } 
        } 
        // Gestion du cache et de la vue
        $this->globalEntities();                
        $masq = $regstat.'_stats.html';
        $cache = DIRCACHE.'regions/'.$regstat.'/'.$masq;
        $delais = 86400;
        $this->creerCache($this->allRegions,$nom='regions');   
        if ($this->useCache($cache,$delais,$recalcul)) {
          $this->vue->liste_cache = file_get_contents($cache);
          $this->vue->setFile("contenu","stats_Membre_cache.tpl");   
        }       
        else {
          $this->vue->setFile("contenu","voir_Membre_stats.tpl"); 
          $this->vue->setBlock("contenu","genCollect","collect");
          $this->vue->setFile("leStat","s_stat.tpl");
          foreach($genCollect as $letter=>$data) {
            $this->vue->genre = $letter;
            $this->vue->setVar($letter,"");        
            foreach ($data as $cle=>$val) { 
              $pos = strpos($val,',');        
              $this->vue->pourcent = $pcent = substr($val,($pos+1)); 
              $this->vue->stat = $chiffre = substr($val,0,$pos) ; 
              $this->vue->nomStat = $cle;
              $this->vue->append($letter,"leStat");
            }                      
            $this->vue->li_stat = $this->vue->getVar($letter);
            $this->vue->append('collect','genCollect');
          }
          $outCache = $this->vue->collect;
          $this->ecrisCache($regstat,'_stats.html',$outCache,'regions');
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
* la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
*/
  public function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}

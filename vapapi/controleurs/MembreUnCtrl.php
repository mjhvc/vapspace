<?php
/**
* @category VAP
* @copyright Marc Van Craesbeeck, 2012
* @license GPL
* @package controleur
* Classe MembreCtrl, finalisation de la classe abstraite Ab_Membre
*/
require_once('ABS_Membre.php');
class MembreCtrl extends ABS_Membre
{
  /**
  * controler() surcharge de parent::controler
  * appel de parent::controler() puis fait des controles specifiques au contexte
  * utilise $this->filtre : une instance de la classe FiltreData appell� dans parent
  * utilise parent::changerFiltres()
  * retourne $this->vue->retour
  * si action = 'update' appel $this->getOld($tableau['ppk']) dans classe parente
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
        elseif (($this->action == "inscription") && ($testOblig = $this->filtre->filtreOblig($tableau))) {
          $ramasseError[] = CONTROL_OBLIG.$testOblig;             
        }
        elseif (($this->action == "update") && ($testOblig = $this->filtre->filtreOblig($tableau,'nopass'))){
          $ramasseError[] = CONTROL_OBLIG.$testOblig;           
        }           
        elseif (($tableau['mail'] != 'PasDeMail') && (! ($ctldns = $this->filtre->filtreDns($tableau['mail'])))) {  
          $ramasseError[] =  $tableau['mail'].AUTH_MAIL_ERROR;
        }
        elseif ((!empty($tableau['naissance'])) && ($badnaiss = $this->filtre->naissance($tableau['naissance'],1910,2005))) {
          $ramasseError[] =  DATE_ERROR;
        }     
        elseif (($this->action == 'update') && ($tableau['itoken'] != $this->token)) {
          throw new MyPhpException("Membre update, tableau corrompu");
        }   
        elseif (!empty($tableau['membre']) && ($tableau['membre'] != $this->oldMbr) && ($dblMbr = $this->filtre->doublon('membre',$tableau['membre'],$this->tableDeux))) { 
          $ramasseError[] =   $tableau['membre'].THIS_EXIST; 
        }
        elseif ((!empty($tableau['cachet'])) && ($badate = $this->filtre->filtreDate($tableau['cachet']))) {  
          if ($badate == 1) { $ramasseError[] =  DATE_FORMAT ; }
          elseif ($badate == 2) { $ramasseError[] =  DATE_ERROR ; }
        }
        elseif ((!($this->droits & MEMBRE)) && (! $spam = $this->filtre->antiSpam($tableau['spam']))){   
          $ramasseError[] =  MBR_ANTISPAM ;
        }
        elseif ((!empty($tableau['passe'])) && ($tableau['passe'] != $tableau['confirmation'])){  
          $ramasseError[] =  PASS_CONFIRM ;
        }
        elseif (($this->action == "inscription") && ($tableau['mail'] != MBR_NOMAIL) && ($doublonPass =  $this->ctlPass($tableau['mail'],$tableau['passe'],$this->action))) {
          $ramasseError[] = PASS_EXIST;
        } 
        elseif (($this->action == "update") && ($tableau['mail'] != MBR_NOMAIL) && (!empty($tableau['passe'])) && ($doublonPass =  $this->ctlPass($tableau['mail'],$tableau['passe'],$this->action))) {
          $ramasseError[] = PASS_EXIST;
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
  * $mode pour savoir si le tableau vient de la database (2D) ou d'un retour d'erreurs (1D)
  * fichiers tpl appell�s : inscrire_Membre.tpl, multi_Membre_ValPPK.tpl, pied.tpl + parent::vueStatut() + parent::vueStatique()
  */
  private function vueFormulaire($action,$valppk=NULL,$tableau=array(),$mode=NULL)
  {
    $out = false; $genre='';
    $this->globalEntities();
    $this->vueStatut($this->statut);
    $this->vueRecommand(); // appel (dans classe parente) du chargement de entite multiRecomm
    if (empty($mode)) { //On est avec un tableau 2D extrait de la base
      $clereg = $tableau[T_HUM]['lienreg']; 
      $genre = $tableau[T_HUM]['genre'];
      $cleant = $tableau[T_CORE]['lienant'];
    }
    else { //retour client � 1D
      if (!empty($tableau['lienreg'])) { $clereg = $tableau['lienreg']; } 
      if (!empty($tableau['lienant'])) { $cleant = $tableau['lienant']; }
      if (!empty($tableau['genre'])) { $genre =$tableau['genre']; } 
    }
    if (! empty($tableau['confirmation'])) { $this->vue->confirmation = $tableau['confirmation']; }
    else { $this->vue->confirmation = ''; } 
    if (empty($cleant)) { $this->vue->select = $this->getSelect($this->champPassif,$this->allAntennes); }
    else {  $this->vue->select = $this->getSelect($this->champPassif,$this->allAntennes,$cleant); } 
    if (empty($genre)) { $this->vue->civil = $this->getSelect("genre",$this->civilite);}
    else { $this->vue->civil = $this->getSelect("genre",$this->civilite,$genre); }
    if (($action == 'inscription') || ($action == 'sansMail') || ($action == 'install')) { 
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=inscrire";        
      $this->vueSpam($this->statut);
      if (!empty($tableau['spam'])) { $this->vue->spam_code = $tableau['spam']; }
      else { $this->vue->spam_code = ''; }
      $this->vue->cachez = ''; 
      $this->vue->type =  SUBMIT_SUBSCRIBE;
      $this->vue->intro_passe = PASS_SUBSCRIBE;
      $this->vue->intro =  INTRO_SUBSCRIBE;       
      if ($action == 'sansMail') { $this->vue->intro =  SANSMAILS_ALERT;}
      elseif ($action == 'install') { $this->vue->intro =   INTRO_INSTALL; }
      if (empty($tableau)) { $this->constructEntity(); }
      else { $this->constructEntity($tableau); }
      if ($action == 'sansMail') { $this->vue->mail = 'PasDeMail'; }
      $this->vueCheck();
      $this->vue->lienreg = 1;                            //1 car c'est l'idreg de region neutre
      $this->vue->setFile("contenu","inscrire_Membre.tpl");
      if (empty($tableau)) { $this->vueStatique(); }
      else { $this->vueStatique('I'); }  
    } 
    elseif ($action == 'update') {
      $this->vue->choix_script = BASEURL."index.php?ctrl=membre&amp;action=update";  
      $this->vue->intro =   INTRO_ONE;       
      $this->vue->intro_passe =  PASS_UPDATE; 
      $this->vue->type =   SUBMIT_UPDATE;
      $this->vue->passe =  $this->vue->passe_o  = $this->vue->confirmation = $this->vue->spam = '';     
      $this->vue->setFile("cachez",'multi_Membre_ValPPK.tpl');        
      $this->vue->ppk = $valppk;
      $this->vue->itoken = $this->token;
      if (!empty($mode)) { $this->constructEntity($tableau); } //mode == "ERROR' p.ex 
      else {
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
      $this->vueStatique('I');
      $this->vue->lienreg = 1;                            //1 car c'est l'idreg de region neutre       
      $this->vue->setFile("contenu","inscrire_Membre.tpl");      
    } 
    $this->vue->setFile("pied","pied.tpl"); 
    $out = $this->vue->render("page");
    return $out;      
  } 
    
   /** 
  * methode inscrire:
  * g�re : l'insertion en base d'une ligne ad hoc par $this->modele->inscrire()
  * g�re la vue par appel de $this->vuePhaseUn et $this->vuePhaseDeux
  * g�re : un re-affichage du formulaire rempli si erreurs du client par parent::constructEntity($_POST)
  * les erreurs clientes sont trait�es, les erreurs d'ecriture lancent une exception
  */ 
  public function inscrire() 
  {
    $dataMbr = $nomChamp = $valChamp = $mbrTab = array(); 
    $page = '';
    try { 
      if (filter_has_var(INPUT_GET,'membre') && $_GET['membre'] == 'sans') { $this->action = 'sansMail'; }
      elseif (filter_has_var(INPUT_GET,'install') && $_GET['install'] == 'oui') { 
        if (! $back = $this->filtre->calculKeyMax('idhum',T_HUM)) { $this->action = 'install'; }
        else { $this->action = 'inscription'; }
      }  
      else { $this->action = 'inscription'; }
      if (filter_has_var(INPUT_GET,'lang')) { $lang = filter_input(INPUT_GET,'lang',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^(fr|nl)$#'))); }
      else { $lang = $_SESSION['lang']; } 
      if (empty($_POST)){ 
        $mbrTab['lang'] = $lang; 
        if ($this->statut == 'responsable') { 
          $mbrTab['lienreg'] = $_SESSION['lienreg']; 
          $mbrTab['lienant'] = $_SESSION['idant']; 
        }   
        $page = $this->vueFormulaire($this->action,NULL,$mbrTab,true); 
      }
      else {
        $this->vue->retour = $this->control($_POST,$lang);     
        if (!empty($this->vue->retour)) { //traitement des erreurs clients
          $page = $this->vueFormulaire($this->action,NULL,$_POST,'error');  
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
          if (!empty($nomChamp)) { $injection = array(T_CORE,$nomChamp,$valChamp);}
          else { $injection = array(); } 
          if ($this->valppk = $this->modele->inscrire($dataMbr,$injection)) {
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
            if ($dataMbr['mail'] != 'PasDeMail') {  //gestion du mailing       
              $cleant = $dataMbr['lienant'];  $clereg = $dataMbr['lienreg'];              
              $dataMbr['nomantenne'] = $this->allAntennes[$cleant];
              $dataMbr['passe'] = $_POST['passe'];
              $textMbr = StaticMembre::textToMail($dataMbr, $_SESSION['lang']);
              $sujet =  StaticMembre::sujeToMail( $_SESSION['lang']); 
              $this->mailer($dataMbr['mail'],$this->expedit,$sujet,$textMbr);   
              if ($this->statut == 'anonym') {
                $tabExpResp = $this->selectMailResp($_SESSION['idant']);
                if (!empty($tabExpResp)) {
                  $sujetResp =   StaticMembre::sujeToResp($_SESSION['lang']);
                  $textResp =  StaticMembre::textToResp($dataMbr['nom'],$dataMbr['prenom'],$dataMbr['nomantenne'],$_SESSION['lang']);
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
                  $promoFinTxt = StaticMembre::textPromo($dataMbr['nom'],$dataMbr['prenom'],$_POST[$Mail],$_SESSION['lang'],$promoText);
                  $this->mailer($_POST[$Mail],$this->expedit,$sujetPromo,$promoFinTxt);                  
                }
              }                
            }
            header($loc);exit(0);
          }
          else { throw new MyPhpException('Impossible d\'ins�rer un Membre'); } 
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
  * la fonction qui lance la phase un de mise�jour(update) d'un membre depuis la base de donnee
  * utilise $this->getPK() pour filtrer $_GET['line']
  * utilise $this->modele->selection($cle) pour selectionner la ligne du contexte en base  
  * utilise $this->vuePhaseUn($action,$valppk,$tableau) pour sortir la vue
  * utilise parent::validSess() en controle de seesion  
  */
  public function editer() //propre � un descendant
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
      elseif (! empty($cle)) { //extraction des donnees  de la database (array � 2 dimm)         
        $data = $this->modele->selection($cle);        
        $page = $this->vueFormulaire($this->action,$cle,$data);
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
  * appel $this->modele->mettreajour($tableau,$cle) qui r�alise un update en base pour la ligne concernee 
  * si erreur du client suites aux filtres : lance $this->vueMbr()
  * les erreurs clientes sont trait�es, les erreurs d'ecriture lancent une exception
  */
  public function update()  //abstract
  {
    $injection = $dataMbr = $nomChamp = $valChamp = array();
    try {            
      if (! ($this->droits & MEMBRE)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&action=index&sess=ko"; 
        header($loc);
      }
      else { 
        $this->action = 'update';
        if (!($cle = $this->getPK('POST'))) { throw new MyPhpException('le parametre: ppk dans le contexte: '.$this->contexte.' est non valide'); }       
        $this->vue->retour = $this->control($_POST,$_SESSION['lang']);
        if (!empty($this->vue->retour)) { //traiter erreurs clients          
          $page = $this->vueFormulaire($this->action,$cle,$_POST,'error');
          $this->output($page);
        }        
        else {  //pas d'erreur? lancer l'update
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
          // on ne modifie plus le cachet d'un inscrit....if (empty($_POST['cachet'])) {$leCachet = date('Y-m-d'); $nomChamp[] = 'cachet' ;$valChamp[] = $leCachet ;}  
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
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }    
  }
  /**
* methode index() liste des actions possibles pour tous les ffmembres : vapspace
* la variante est standart : une liste d'antenne
* le menu jquery (menus.js) doit s'adapter � cette variante.
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
        $tabant = array();
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
          elseif ($_GET['new']=='trajet') { $this->vue->retour =   MBR_ROUTE_SUBSC; }
        }
        if (filter_has_var(INPUT_GET,'upmbr') && ($up = filter_input(INPUT_GET,'upmbr',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour =   MBR_UPDATE;
        } 
        if (filter_has_var(INPUT_GET,'trajok') && ($con = filter_input(INPUT_GET,'trajok',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour =   MAIL_CONTACT_MBR;
        }
        if (filter_has_var(INPUT_GET,'del') && ($del = filter_input(INPUT_GET,'del',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]+$#'))))) {
          $this->vue->retour = $del. ENT_DELETED;
        }             
        $cle_mbr = intval($_SESSION['idhum']);               
        $nomAntenne = $this->allAntennes[$idant];
        $urlIterOffre = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=offre&amp;token=".$token."&amp;antiterlieu=".$idant."&amp;iterlieu=";
        $urlIterDemande = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=demande&amp;token=".$token."&amp;antiterlieu=".$idant."&amp;iterlieu=";
        $urlRegStats = BASEURL."index.php?ctrl=membre&amp;action=stats&amp;token=".$token; 
        
        $this->globalEntities($idant);  
        $this->vue->urlCarte = BASEURL."index.php?ctrl=cartes&amp;token=".$token;         
        $this->vue->url_chatPost = BASEURL."index.php?ctrl=chat&amp;action=poster&amp;chatidant=".$idant."&amp;token=".$token; 
        $this->vue->url_chatGlb =  BASEURL."index.php?ctrl=chat&amp;action=voirGlob&amp;chatidant=".$idant."&amp;token=".$token;        
        $this->vue->url_chatMens = BASEURL."index.php?ctrl=chat&amp;action=voirMens&amp;chatidant=".$idant."&amp;token=".$token;
        $this->vue->urlIter = BASEURL."index.php?ctrl=iter&amp;token=".$token; 
        $this->vue->urlTrajet = BASEURL."index.php?ctrl=iter&amp;action=inscrire&amp;iterant=".$_SESSION['idant']."&amp;token=".$token;          
        $this->vue->urlProfil = $urlProfil = BASEURL."index.php?ctrl=membre&amp;action=editer&amp;line=".$_SESSION['idhum']."&amp;token=".$token ;       
        $this->vue->nomantenne =  $nomAntenne;                    
        $this->vue->vue_mbr =  BASEURL."index.php?ctrl=membre&amp;action=voir&amp;class=glb&amp;lienant=".$idant;      
        $this->vue->chatGlob =   CHAT_ALL.$nomAntenne;                           
        $this->vue->chatMens = CHAT_MONTH;
        $this->vue->urlDelMbr = BASEURL."index.php?ctrl=membre&amp;action=supprimer&amp;token=".$token."&amp;line=".$cle_mbr;
        
        if ($_SESSION['iters'] > 0) {                          
          $this->vue->iterperso =  MBR_TRIPS;  
          $this->vue->urliterperso = BASEURL."index.php?ctrl=iter&amp;action=voir&amp;deal=non&amp;iterhum=".$_SESSION['idhum']."&amp;token=".$token;        
        }        
        else { $this->vue->urliterperso = $this->vue->iterperso = ''; } 
        if ($_SESSION['chats'] > 0) {
          $this->vue->urlchatperso = BASEURL."index.php?ctrl=chat&amp;action=voir&amp;chathum=".$_SESSION['idhum']."&amp;token=".$token;
          $this->vue->chatperso =   MBR_CHATS;
        }
        else { $this->vue->urlchatperso = $this->vue->chatperso = ''; }
      
        foreach($this->allAntennes as $idx=>$ant) {
          if ($idx != $idant) { $tabant[$idx] = $ant; }
        }
        $cptAnt = count($tabant);
        if ($cptAnt == 0 ) { $this->vue->listeAntennes = ''; }
        else { 
          $this->vue->setFile('listeAntennes','index_Membre_Ant.tpl'); 
          $this->vue->setBlock("listeAntennes","liAntennes","ant");
          foreach($tabant as $cle=>$val) {
            $this->vue->urlRegAnt = BASEURL."index.php?ctrl=membre&amp;antenne=".$cle."&amp;token=".$token;
            $this->vue->regAnt = $val;
            $this->vue->append('ant','liAntennes');
          }
        } 
        $this->vue->setFile("contenu","index_Membre_stAnt.tpl");
        $this->vue->setBlock("contenu","STATISTIQUES","statReg");
        foreach ($this->tabRegions as $cle=>$val) {
          $this->vue->nomreg = $val;
          $this->vue->urlRegStats = $urlRegStats;
          $this->vue->append("statReg","STATISTIQUES");
        }
        
        $cacheChat = $this->recupData($idant,'Chat'); 
        if (!($ok = $this->filtre->doublon('idchat',$idant,T_CHAT,'lienant'))) { 
          $this->vue->urlchat = $this->vue->url_chatMens = $this->vue->url_chatGlb = "#"; 
          $this->vue->sujetchat = $this->vue->chatMens = $this->vue->chatGlob =   $nomAntenne.MBR_NOCHATS;            
        }
        else { 
          $this->vue->setBlock("contenu","vapchats","chat");
          foreach($cacheChat as $idChat=>$suChat) {
            $this->vue->urlchat =  BASEURL."index.php?ctrl=chat&amp;action=editer&amp;chatppk=".$idChat."&amp;token=".$token."&amp;mode=single" ;
            $this->vue->sujetchat = $suChat;
            $this->vue->append("chat","vapchats");
          }
        }
        if ($tabLieu = $this->recupData($idant,'Passage')) {
          $numli = 0;
          $this->vue->setFile("tabIter","index_Membre_Vapspace.tpl"); 
          $this->vue->setBlock('tabIter',"spaciter","spiter");
          foreach($tabLieu as $cle=>$val) {
            $classe = "A".(($numli++)%2);
            $this->vue->cssline = $classe;
            $this->vue->lieu = $val[0];
            $this->vue->urlOffre = $urlIterOffre.$cle;
            $this->vue->offre = $val[1];
            $this->vue->urlDemande = $urlIterDemande.$cle;
            $this->vue->demande = $val[2];            
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
  * Appel� par le moteur ajax du script js/scriptVap2.js
  */  
  public function promo()
  {
    header("Content-Type: text/plain ; charset=iso-8859-1");  //indique que le type de la r�ponse renvoy�e au client sera du Texte
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
          $promoFinTxt = StaticMembre::textPromo($_SESSION['nom'],$_SESSION['prenom'],$courriel, $_SESSION['lang'],$message);
          if ($this->mailer($courriel,$this->expediteur,$sujetPromo,$promoFinTxt)) { ++$x; }
        }
      }
    }                 
    if (empty($x)) {  $retour =  MAIL_PROMO_ERROR.$courriel; }
    else { $retour =  MAIL_PROMO_OK ; }
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
        if (filter_has_var(INPUT_GET,'recalcul')) { $recalcul =  true; }
        else { $recalcul = false; }       

        //Les 4 requ�tes sql        
        $genUtilStat = "SELECT COUNT(idvap) AS RST "
          ." FROM ".T_CORE." C JOIN ".T_HUM." H ON C.idvap=H.idhum "
          ." WHERE C.inscrit=:utilise and H.genre=:sex ";
        $genMobStat = "SELECT COUNT(lienhum) as RST  "
          ." FROM ".T_HUM_SOC." M JOIN ".T_HUM." H ON M.lienhum=H.idhum  "
          ." WHERE M.lientrans=:idtrans "
          ." AND M.utilisation='oui' ";
        $antUtilStat = "SELECT COUNT(idvap) AS RST "
          ." FROM ".T_CORE." C JOIN ".T_HUM." H ON C.idvap=H.idhum "
          ." WHERE C.inscrit=:utilise and C.lienant=:antenne";
        $antMobStat = "SELECT COUNT(lienhum) as RST "  
          ." FROM ".T_HUM_SOC." M, ".T_CORE." C, ".T_HUM." H "
          ." WHERE M.lientrans=:idtrans "
          ." AND C.lienant = :lienant "            
          ." AND M.lienhum=H.idhum "
          ." AND C.idvap = H.idhum " ;

        //Les variables
        $userVap = array('pieton','auto','deux');       
        $collect = $finCollect = $genCollect = $genFinCollect = array();
        $diReg = array_keys($this->tabRegions);        
        $masqUn = '_unStatBloc.html';
        $masqDeux = '_deuxStatBloc.html';
        $fileUn = $diReg[0].$masqUn;
        $fileDeux = $diReg[0].$masqDeux;        
        $cacheUn = DIRCACHE.'regions/'.$diReg[0].'/'.$fileUn;
        $cacheDeux = DIRCACHE.'regions/'.$diReg[0].'/'.$fileDeux;
        $delais = 86400;       
        $moduo = $this->modele->getContexte($this->contexte,$this->statut);
        $schema = $this->modele->getSchemaDataStatique();  
        $dbh = $this->modele->getCnx();

        //Calcul des statistiques de l'utilisation des VAP global website
        $stmt = $dbh->prepare($genUtilStat);
        foreach ($userVap as $util) {  
          $stmt->bindParam(':utilise',$util,PDO::PARAM_STR);
          $stmt->bindValue(':sex','F',PDO::PARAM_STR);
          $stmt->execute();
          $result = $stmt->fetch(PDO::FETCH_ASSOC);            
          $genCollect[MBR_FEM][$util]  = $result['RST'];
          $stmt->bindValue(':sex','M',PDO::PARAM_STR);
          $stmt->execute();
          $result = $stmt->fetch(PDO::FETCH_ASSOC); 
          $genCollect[MBR_MAL][$util]  = $result['RST'];
        }
        // Calcul des statitstiques de mobilit�s (soci�t�s et abonnements)
        if ($schema) {
          $genMobStatUn = $genMobStat." AND M.abonne='non' ";
          $stmt = $dbh->prepare($genMobStatUn);
          $antMob =array();          
          foreach ($schema as $nom=>$opt) { $antMob[$nom] = $opt['valCleLiaison']; }
          foreach ($antMob as $soc=>$idtrans){
            $stmt->execute(array(':idtrans'=>$idtrans));
            $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
            $genCollect['Utilisent-sans-abonnement:'][$soc] = $rslt['RST'];      
          }
          $genMobStatDeux =  $genMobStat. " AND M.abonne='oui' ";
          $stmt = $dbh->prepare($genMobStatDeux);
          foreach ($schema as $nom=>$opt) { $antMob[$nom] = $opt['valCleLiaison']; }
          foreach ($antMob as $soc=>$idtrans){
            $stmt->execute(array(':idtrans'=>$idtrans));
            $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
            $genCollect['Utilisent-avec-abonnement:'][$soc] = $rslt['RST'];      
          }
        }
        //calcul des statistiqes globales
        $genCollect[MBR_GLB_REG]=array(MBR_FEM=>0,MBR_MAL=>0);
        foreach ($genCollect[MBR_FEM] as $cle=>$cpt) { $genCollect[MBR_GLB_REG][MBR_FEM] += $cpt; }
        foreach ($genCollect[MBR_MAL] as $key=>$xpt) { $genCollect[MBR_GLB_REG][MBR_MAL] += $xpt; }
        $dividende = $genCollect[MBR_GLB_REG][MBR_GLB_GLB] = $genCollect[MBR_GLB_REG][MBR_FEM] + $genCollect[MBR_GLB_REG][MBR_MAL] ;
       
        //calcul des statistiques pour chaque antennes:
        $stmt = $dbh->prepare($antUtilStat);        
        foreach ($this->allAntennes as $id=>$nom) {    
          $collect[$nom] =  array();
          foreach ($userVap as $util) {  
            $stmt->bindParam(':utilise',$util,PDO::PARAM_STR);
            $stmt->bindParam(':antenne',$id,PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);            
            $collect[$nom][$util]  = $result['RST'];
          }
        }  
        //mise en place des donn�es. 
        foreach ($genCollect as $relai=>$tableau) { 
          $typoTab = array(); $cleTypo = '';      
          foreach ($tableau as $nom=>$valstat) { 
            if ($nom == 'pieton'){ $cleTypo = MBR_PIEDS; }
            elseif ($nom == 'auto'){ $cleTypo = MBR_AUTOS; }
            elseif ($nom == 'deux'){ $cleTypo = MBR_DEUX; }
            else { $cleTypo = $nom; }
            $valpourcent = $this->pourcentage($valstat,$dividende);
            $typoTab[$cleTypo] = $valstat.','.$valpourcent; 
          }
          $genFinCollect[$relai] = $typoTab; 
        } 
        foreach ($collect as $relai=>$tableau) { 
          $typoTab = array(); $cpt = 0;         
          foreach ($tableau as $nom=>$valstat) { 
            if ($nom == 'pieton'){ $cleTypo = MBR_PIEDS; }
            elseif ($nom == 'auto'){ $cleTypo = MBR_AUTOS; }
            elseif ($nom == 'deux'){ $cleTypo = MBR_DEUX; }
            else { $cleTypo = $nom; }
            $cpt += $valstat; 
            $valpourcent = $this->pourcentage($cpt,$dividende);
            $typoTab['Total'] = $cpt.','.$valpourcent; 
            $typoTab[$cleTypo] = $valstat;
            $finCollect[$relai] = $typoTab; 
         } 
        } 
       
        // Gestion du cache et de la vue
        $this->globalEntities();                
        $this->creerCache($this->tabRegions,$nom='regions'); 
        if ($this->useCache($cacheUn,$delais,$recalcul)) { 
          $this->vue->liste_cacheUn = file_get_contents($cacheUn);
           $this->vue->liste_cacheDeux = file_get_contents($cacheDeux);
          $this->vue->setFile("contenu","stats_Membre_cache_deux.tpl");   
        }       
        else {
          $this->vue->setFile("contenu","voir_Membre_stats_deux.tpl"); 
          $this->vue->setBlock("contenu","genCollect","collect");
          $this->vue->setBlock("contenu","antCollect","antenne");
          $this->vue->setFile("leStat","s_stat.tpl");
          foreach ($genFinCollect as $relai=>$data) {
            $this->vue->parGenre = $relai;            
            $this->vue->setVar($relai,'');        
            foreach ($data as $cle=>$val) { 
              if ($pos = strpos($val,',')) {       
                $this->vue->pourcent =  $pcent = substr($val,($pos+1)); 
                $this->vue->pourcentGras = $pcent.'%';
                $this->vue->stat = $chiffre = substr($val,0,$pos) ; 
              }  
              else {               
                $this->vue->pourcent = $this->vue->pourcentGras =  ''; 
                $this->vue->stat = $chiffre = $val ;
              } 
              $this->vue->nomStat = $cle;
              $this->vue->append($relai,"leStat");
            }
            $this->vue->li_genStat = $this->vue->getVar($relai);                                         
            $this->vue->append('collect','genCollect');            
          }   
          foreach ($finCollect as $relai=>$data) {
            $this->vue->parNom = $relai;            
            $this->vue->setVar($relai,'');        
            foreach ($data as $cle=>$val) { 
              if ($pos = strpos($val,',')) {       
                $this->vue->pourcent =  $pcent = substr($val,($pos+1)); 
                $this->vue->pourcentGras = $pcent.'%';
                $this->vue->stat = $chiffre = substr($val,0,$pos) ; 
              }  
              else {               
                $this->vue->pourcent = $this->vue->pourcentGras =  ''; 
                $this->vue->stat = $chiffre = $val ;
              } 
              $this->vue->nomStat = $cle;
              $this->vue->append($relai,"leStat");
            }
            $this->vue->li_antStat = $this->vue->getVar($relai);                                         
            $this->vue->append('antenne','antCollect');            
          }   
          $outCache = $this->vue->collect ;
          $this->ecrisCache($diReg[0],$masqUn,$outCache,'regions');
          $outCache = $this->vue->antenne ;
          $this->ecrisCache($diReg[0],$masqDeux,$outCache,'regions');
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
* la derni�re methode lance la sortie: echo, servira  + tard pour une mise en cache
*/
  public function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}

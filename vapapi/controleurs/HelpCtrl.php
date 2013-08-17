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
* class HelpCtrl gestion de l'identification secondaire sans mot de passe, table T_HUM
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include_once("Controleur.class.php");
include_once("StaticMailLang.class.php");

/**
* Classe HelpCtrl, la classe controleur du Contexte Help.ini
*/
class HelpCtrl extends Controleur
{ 
  private $contexte;
  private $statut;
  private $droits; 
  private $dataContexte;
  private $table;
  private $nomPK;
  private $tabTable = array();
  /**
* methode fille __construct()  
* necessaire pour initier en premier lieu le nom du contexte = nom du controleur
* ensuite appelle de parent:: __construct($contexte) qui charge 'le gros' 
 */
  public function __construct()
  {
    $this->contexte = "Help";
    parent::__construct($this->contexte);  
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits(); 
    $this->dataContexte = array();
    $this->tabTable = $this->modele->getTables();
    $this->table = $this->tabTable[0];
    $this->nomPK = $this->modele->getPPK(); //le nom de la cle PK du contenxte
  }
  /**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_contexte est dans pied.tpl
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head =  HELP_TITLE_HEAD;
    $this->vue->titre_page =  HELP_TITLE_PAGE;
    $this->vue->connecte = $this->statutConnect($this->token);  
    $this->vue->pied = '<p class="piedleft"><a href="'.BASEURL.'index.php?ctrl=membre&amp;action=inscrire">'.AUTH_SUBSCRIBE.'</a></p>';
    return true;
  }
  /**
  * methode qui cree un mot de passe de 8 caractères lettres et chiffres.
  */  
  private function creePasse()
  {
	  //Variables:
	  $passe = $mot = "";
    $compteur = 1;
	
	  //boucle 
	  while ($compteur <= 8)
	  {
	  	$nb = mt_rand(1,3);			//genere un int aleatoire entre 1 et 3
	  	if ($nb == 1)
	  	{	$nombre = mt_rand(48,57);
	  		$passe= chr($nombre);				
	  	}	// un chiffre (code ascii) de 0 à 9
	  	elseif ($nb == 2)
	  	{	$nombre = mt_rand(65,90); //codage ascii des minuscules 
	  		 $passe= chr($nombre);			
	  	}
	  	elseif ($nb == 3)
	  	{	$nombre = mt_rand(97,122); // codage ascii des majuscules 
	  		$passe= chr($nombre);
	  	}
	  	$mot=$mot . $passe ;
	  	$compteur++;	
	  }
	  //sortie
	  return $mot;
  }
  /**
  * ppkParMail : methode qui selectionne ppk en base en fonction du mail client
  * - on vérifie que $mail est bien stoqué en base
  * - on retourne valppk
  * @param $mail string, le mail à tester
  * @return $rps qui vaut soit: 
  * - integer avec la valeur de ppk : succes
  * - bool false : echec;
  */
  private function ppkParMail($mail,$nom,$prenom)
  {
    $rps = array();
    $back = '';    
    $sql = "SELECT idhum FROM ".T_HUM." WHERE nom LIKE :nom AND prenom LIKE :prenom AND mail LIKE :mail ";
    $marq = array(':mail'=>$mail,':nom'=>$nom,':prenom'=>$prenom);
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    foreach ($marq as $key=>$val) {
      $stmt->bindValue($key,$val,PDO::PARAM_STR);
    } 
    if ($ok = $stmt->execute()) {
      $rslt = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }  
    foreach ($rslt as $row) {
        $rps[] = $row['idhum'];
    } 
    $c = count($rps);
    if ($c == 1) { $back = intval($rps[0]); }
    elseif ($c === 0) { $back = strval('no'); }
    else { $back = true; } 
    return $back;  
  }
  /** helpMail fonction de gestion du mailing 
    @param $valppk = bool ? alors on envoi un mail à $this->expedit
    @param $valppk = integer ? alors on envoi le mail à $_POST['mail']   
  */
  private function helpMail($valppk,$newPasse=NULL) {
    if (is_int($valppk)) {
      $tabHum = $this->modele->selectUneLigne($valppk,$this->table);
      $nom = $tabHum['prenom'];
		  $prenom = $tabHum['nom'];
	    $mailvap = $tabHum['mail'];
      $sujet = HELP_MAIL_SUJET;
		  $texte =  MailLang::textHelpCtrl($prenom,$nom,$newPasse,$mailvap,$_SESSION['lang']);
      $retour = HELP_SENT_PASS.$mailvap;
    }
    elseif (is_bool($valppk)) {
       $sujet = HELP_MAIL_VAP;
       $mailvap = $this->expedit;
       $texte = MailLang::textHelpImp($_POST['mail'],$_SESSION['lang']);
       $retour = HELP_IMP_PASS;
    }
    if ( ! $ok = $this->mailer($mailvap,$this->expedit,$sujet,$texte)) {
      $retour = '<span style="color:red">'.HELP_ERROR_PASS.'</span>';          
    }
    return $retour;
  }
  
   /**
  * controler() surcharge de parent::controler
  * appel de parent::controler() puis fait des controles specifiques au contexte
  * retourne $this->vue->retour
  */ 
  protected function controler($tableau)
  {  
    try {     
      //appel des filtres génériques du controleur principal:
      $this->vue->retour = parent::controler($tableau);
      if (empty($this->vue->retour)){  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){
          $ramasseError[] = CONTROL_OBLIG.$testOblig;
        }
        //filtreDns sur $_POST['mail'] 
        elseif (! $ctldns = $this->filtre->filtreDns($tableau['mail'])){
          $ramasseError[] =  $tableau['mail'].AUTH_MAIL_ERROR;
        }
        elseif ( $tableau['mail'] == MBR_NOMAIL ) {
          $ramasseError[] =  $tableau['mail'].AUTH_MAIL_PERMIS;
        }
        //filtre:postInattendu()
        elseif ($postCorrupt = $this->filtre->postInattendu($tableau)){
          throw new MyPhpException('formulaire du contexte:'.$this->contexte.' corrompu');
        }
        // preparation de la chaine de sortie des erreurs :
        if (! empty($ramasseError[0])){ 
          $this->vue->retour = '<span style="color:red">'.$ramasseError[0].'</span>'; 
          $ramasseError = array();
        }
        else { $this->vue->retour = ''; }          
      }
      return $this->vue->retour;  
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }
  }       
  /**
  * verif la methode alternative de verification du mail
  * verifier via appel à controler()
  * si ok, cree un nouveau mot de passe
  * met à jour la table du contexte
  * envoi le pass par mail
  */
  public function verif()
  {
    try {
      $tabUp = $tabHum = array();
      $this->globalEntities();
      $this->vue->retour = $this->controler($_POST);
      if (empty($this->vue->retour)) {
        $newPasse = $this->creePasse();
        $newPasseCrypte = crypt($newPasse);
        $tabUp['passe'] = $newPasseCrypte; 
        $valppk = $this->ppkParMail($_POST['mail'],$_POST['nom'],$_POST['prenom']);
        if (is_string($valppk)) { $this->vue->retour =  VAP_UNKNOWN ; } 
        elseif(is_bool($valppk)) { $this->vue->retour = $this->helpMail($valppk); }
        elseif (is_int($valppk) && ($ok = $this->modele->mettreajour($tabUp,$valppk))) {
          $this->vue->retour = $this->helpMail($valppk,$newPasse);
        }
      }
      $page = $this->vue->render('page');
      $this->output($page);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }
  }        

  public function index()
  {
    $this->globalEntities();
    $this->vue->url_contexte = BASEURL.'index.php?ctrl=help&amp;action=verif';
    $this->vue->setFile('contenu','index_Help.tpl'); 
    $out = $this->vue->render("page");
    $this->output($out);
  }
    
  /**
* la dernière methode lance la sortie: echo, servira  + tard pour une mise en cache
*/
  protected function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}


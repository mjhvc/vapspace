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

require_once("Controleur.class.php");
class ABS_Iter extends Controleur
{
   
  protected $dirCache;  
  protected $droits;  
  protected $lienpass;  
  protected $masq;
  protected $masqLieu;
  protected $trajet;
  protected $passage;   
  protected $statut;    
  protected $table;  
  protected $tabAnten = array();
  protected $tabTables = array(); 
  protected $urlPagiter;
  protected $urlIterCnx;
  protected $valppk;  
  const KEYANT = 1; 
  /**
  * methode fille __construct() :appel du constructeur parent   
  */
  public function __construct($contexte=NULL)
  {
    $this->trajet = 'Trajet';
    $this->passage = 'Passage';
    $this->masq = './*.csv'; 
    $this->masqLieu = '_lieu.csv'; 
    $this->lienpass = '';       
    $this->valppk = '';       
    parent::__construct($contexte); //appel de Controleur sans arguments!
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();
    $this->tabAnten = $this->valeursPassive(self::KEYANT,NULL,'Passage');
    $this->urlPagiter = BASEURL.'index.php?ctrl=iter&amp;action=voir&amp;token='.$this->token.'&amp;';
    $this->urlIterCnx = BASEURL.'index.php?ctrl=iter&amp;action=mailiter&amp;token='.$this->token ;
  }
  
  /**
  * basicdata() initie les donnees du Modele selon 1 contexte 
  * contexte appelé via $this->chargerModele($contexte,$filtre) du Controleur Principal
  */  
  protected function basicData($contexte)
  {
    $ok = null;    
    if ($ok = $this->chargerModele($contexte,$this->statut)) {    
      $this->tabTable = $this->modele->getTables();
      $this->table = $this->tabTable[0];
      $this->chargerFiltre($contexte,$this->statut);
      return $ok;
    }
    else { throw new MyPhpException('ABS_Iter: Impossible de charger le contexte :'.$contexte); }  
  }
  /**
  * Les entités des vues principale(squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_admin est dans pied.tpl
  */ 
  protected function globalEntities($cont=NULL,$anten=NULL,$nom=NULL)
  {
    if (empty($_SESSION['nomantenne'])) { $titre = ITER_TIT; }    
    elseif(!empty($nom)) { $titre = MBR_SPACE.$_SESSION['nomantenne']; }    
    elseif (empty($anten)) { $titre =  ITER_TIT.$_SESSION['nomantenne']; }
    elseif (array_key_exists($anten,$this->tabAnten)) { $titre = ITER_TIT.$this->tabAnten[$anten]; }      
    else { $titre = ITER_TIT.$cont ; }    
    $this->vue->titre_head =  $titre ;
    $this->vue->titre_page =  $titre ;
    $this->vue->vapspace =  $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token) ;      
    if (! ((int)$this->droits & MEMBRE)){  $this->vue->index_admin = '<p class="piedleft"><a href="index.php?ctrl=membre&amp;action=inscrire">Inscription</a></p>'; }
    elseif (! ((int)$this->droits & RESPONSABLE)) {  $this->vue->index_admin = ''; }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin =  MBR_ADMIN;
    }      
    return true;
  }
  /**
  * Une methode qui incrémente le champ compta,demande et offre d'une table (vap_passage ou vap_trajet)
  */
  protected function comptabilise($id,$champ,$table,$deal=NULL)
  {
    $ok = NULL;   
    if ($table ==  T_MEET) {
      $compta = 'compta';
      $busi = array('offre','demande');
      if (! in_array($deal,$busi)) { throw new MyPhpException('Iter/comptabilise, deal incorrect'); }
      $sql = "UPDATE $table SET $compta = $compta + 1, $deal = $deal + 1  WHERE $champ = :noeud ";
    }
    elseif ($table == T_CORE) {$sql = "UPDATE $table SET iter_compta = iter_compta + 1 WHERE $champ = :noeud "; }  
    else { $sql = "UPDATE $table SET compta = compta + 1 WHERE $champ = :noeud "; }    
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    $ok = $stmt->execute(array(':noeud'=>$id)); 
    return $ok;
  }
  protected function decomptabilise($id,$champ,$table=T_MEET,$deal=NULL)
  {
    $ok = NULL; 
    if ($table == T_MEET) {
      $busi = array('offre','demande');
      if (! in_array($deal,$busi)) { throw new MyPhpException('Iter/decomptabilise, deal incorrect'); }   
      $sql = "UPDATE $table SET compta = compta - 1, $deal = $deal - 1  WHERE $champ = :noeud"; 
    }
    elseif ($table == T_CORE) {$sql = "UPDATE $table SET iter_compta = iter_compta - 1 WHERE $champ = :noeud"; }  
    else { $sql = "UPDATE $table SET compta = compta - 1 WHERE $champ = :noeud"; }   
    $dbh = $this->modele->getCnx();
    $stmt = $dbh->prepare($sql);
    $ok = $stmt->execute(array(':noeud'=>$id)); 
    return $ok;
  }
  /**
  * ecrisCache : selectionne toutes les lignes du contexte 'passage' par cle d'antenne
  * Reforme chaque ligne en csv avec idpass,lieu 
  * appel parent ecrisCache() qui lui fait l'ecriture d'un fichier de cache csv par antenne avec tous les lieux 
  * les 2 derniers arguments sont inutiles mais obligatoires ici pour respecter le modele d'heritage php  
  */  
  protected function ecrisCache($lienant,$masq,$lignes='',$nom='antennes')
  {
    $ok = NULL;    
    $colonnes = array('idpass','lieu');
    $this->basicData($this->passage); 
    $tabExtract = $this->modele->ligneParValeurs($colonnes,$this->table,"lienant",$lienant,'all');
    foreach($tabExtract as $key=>$tableau) {
      $lignes .= $tableau['idpass'].',"'.$tableau['lieu'].'"'."\n";
    }
    $ok = parent::ecrisCache($lienant,$masq,$lignes);
    return $ok;
  }
  /**
  * la methode qui ecris le cache des noeuds de proxi AVEC comptabilisation 
  * ce cache sera affiché dans le vapspace
  */  
  public function noeudCache($lienant,$masq)
  {
    $ok = NULL; $lignes = '';
    $champs = array('idpass','lieu','offre','demande');
    $condi = " AND (offre > 0 OR demande > 0) ORDER BY compta DESC LIMIT 0,10 ";
    $tabExtract = $this->modele->ligneParValeurs($champs,T_MEET,"lienant",$lienant,'all',$condi);
    foreach ($tabExtract as $key=>$tableau) {
      $lignes .= $tableau['idpass'].',"'.$tableau['lieu'].'",'.$tableau['offre'].','.$tableau['demande']."\n";
    }
    $ok = parent::ecrisCache($lienant,$masq,$lignes);
    return $ok;
  } 
  /**
  * La methode qui lit dans le cache $masq d'une antenne $lienant,  
  * utilise parent::lisCache() qui retourne un tableau 2D qui est transformé en tableau ad hoc 1D
  */  
  protected function lisCache($lienant,$masq,$col,$car=128,$method='csv',$nom='antennes')
  {
    $tabCache = $tableau = array();    
    if ($tabCache = parent::lisCache($lienant,$masq,$col,$car,$method)) {
      if ($col == 2) {
        foreach ($tabCache as $key=>$tabcsv) {
          $idpass = intval($tabcsv[0]);
          $tableau[$idpass] = $tabcsv[1];
        }  
       $tableau[0] = ITER_MEET;
      }
      elseif ($col == 4) {
        foreach ($tabCache as $key=>$tabcsv) {
           $idpass = intval($tabcsv[0]);
           $tableau[$idpass] = array($tabcsv[1],$tabcsv[2],$tabcsv[3]);
        }
      }    
    }   
    return $tableau;
  }
    /**
  * une methode pour recuperer ou pas un tableau des lieux soit par cache soit par database
  * la cle [0] de $tabLieux est necessaire pour afficher une ligne neutre de $tabLieux 
  * cette methode retourne soit un array[cle]=>[lieu] soit false 
  * recupLieu() utilise DABORD LE CACHE ecrit aussi le cache Lieu 
  */
  protected function recupLieu($lienant=NULL)
  {
    $tabLieux = array(); $ok = NULL; $lignes = '';
    $condi = " ORDER BY compta ";
    if (empty($lienant)) { $lienant = $_SESSION['idant']; }
    $colonnes = array('idpass','lieu');
    $this->basicData($this->passage); 
    if ($tabLieux = $this->lisCache($lienant,$this->masqLieu,2)) {  ;}
    elseif ( $tabLieux = $this->modele->ligneParValeurs($colonnes,$this->table,"lienant",$lienant,'all',$condi)) {
      foreach($tabLieux as $key=>$tableau) {
        $lignes .= $tableau['idpass'].',"'.$tableau['lieu'].'"'."\n";
      }
      $ok = parent::ecrisCache($lienant,$this->masqLieu,$lignes);
      $tabLieux[0] =  ITER_MEET ;
    }  
    return $tabLieux;
  }  
   /**
  * controler() surcharge de parent::controler
  * retourne $this->vue->retour
  */ 
  protected function controler($tableau)
  {     
    try {
      $ramasseError = array();
      $altern = array('lienpass','passage'); 
      $hidden = array('ppk','itoken','oldPass');
      $this->vue->retour = parent::controler($tableau);   //definir le bon contexte avant cet appel !!
      if (empty($this->vue->retour)){                  
        if ($testOblig = $this->filtre->filtreOblig($tableau)){ $ramasseError[] = $testOblig; }
        elseif (($this->action == 'insert') && ($postCorrupt = $this->filtre->postInattendu($tableau))) { throw new MyPhpException('formulaire : '.$this->contexte.' corrompu'); }
        elseif (($this->action == 'update') && ($postCorrupt = $this->filtre->postInattendu($tableau,$hidden))) { throw new MyPhpException('formulaire : '.$this->contexte.' corrompu'); } 
        elseif (($this->action == 'update') && ($tableau['itoken'] != $this->token)) { throw new MyPhpException('controles trajet et passage: jeton du formulaire corrompu'); }
  
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
}

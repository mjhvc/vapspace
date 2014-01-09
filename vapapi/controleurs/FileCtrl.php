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
* Class FileCtrl : gestion de l'upload de fichiers dans le vapspace table: T_LOAD
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

require_once("Fichier.class.php");
require_once("Controleur.class.php");

class FileCtrl extends Controleur
{ 
  private $contexte;
  private $statut;
  private $droits;
  private $dbh;
  private $tabAntennes = array();
  /**
  * methode fille __construct()  necessaire pour initier en premier lieu le nom du contexte = nom du controleur
  * ensuite appell de parent:: __construct($contexte) qui charge statut,modele et vue selon session et contexte 
  */
  public function __construct()
  {
    $this->contexte = "File";
    parent::__construct($this->contexte);
    $this->statut = $this->getStatut(); 
    $this->droits = $this->getDroits();
    $this->dbh = $this->modele->getCnx();
    $this->tabAntennes = $this->valeursPassive(1);     
  }
/**
  * Des entités de la vue (squelette.tpl et pied.tpl) uniques au contexte
  * L'entité index_admin est dans pied.tpl
  */ 
  private function globalEntities($titre=NULL)
  {
    $this->vue->titre_head =  FILE_TIT  ;
    $this->vue->titre_page =  FILE_TIT ;
    if (!empty($titre)) { $this->vue->tabTitre = $titre; }
    else { $this->vue->tabTitre = FILE_LS; }
    $this->vue->vapspace =  $this->url_vapspace.$this->token ; 
    $this->vue->connecte = $this->statutConnect($this->token);      
    if (! ((int)$this->droits & RESPONSABLE)){ $this->vue->index_admin = ''; }
    else {
      $this->vue->setFile('index_admin','pied_admin.tpl'); 
      $this->vue->url_admin = BASEURL."index.php?token=".$this->token ; 
      $this->vue->admin = MBR_ADMIN;
    }      
    return true;
  }
  
  /**
  * factorisation des operations sur la vue
  */
  private function vueMng($token,$mode,$titre=NULL,$back=NULL,$tableau=array())
  {
    $this->globalEntities($titre);
    if (!empty($back)) { $this->vue->retour = '<span style="color:red">'.$back.'</span>'; }
    if (!empty($tableau)) {
      $this->vue->setFile("contenu","voir_File.tpl");
      $this->vue->setBlock("contenu","listeFile","file");
      foreach ($tableau as $cpt=>$ligne) {
        if (($cpt%2) == 0) { $this->vue->cssligne = 'even'; }
        else { $this->vue->cssligne = 'odd'; }

        if ($mode == 'extract') { 
          $down = BASEURL.'index.php?ctrl=file&amp;action=extract&amp;antFile='.$ligne['keyAnt'].'&amp;loadcsv='.$ligne['name'].'&amp;token='.$token;
          $del = BASEURL.'index.php?ctrl=file&amp;action=extract&amp;antFile='.$ligne['keyAnt'].'&amp;delcsv='.$ligne['name'].'&amp;token='.$token;
          $date = $ligne['date'];
          if ($this->droits & ADMIN) { $this->vue->file_del = "<td><a href=\"".$del."\" onclick=\"return confirm('".ENT_DEL_CONFIRM." ?');\">".ENT_DEL."</a></td>"; }
          else { $this->vue->file_del = '<td>'.NIHIL.'</td>'; }
        }
        elseif ($mode == "voir") { 
          $down = BASEURL.'index.php?ctrl=file&amp;action=voir&amp;loadfile='.$ligne['name'].'&amp;token='.$token ; 
          $del = BASEURL.'index.php?ctrl=file&amp;action=voir&amp;delfile='.$ligne['id'].'&amp;token='.$token ;
          $date = strftime('%A %d %B %Y',strtotime($ligne['date'])); 
          if ($this->droits & ADMIN) { $this->vue->file_del = "<td><a href=\"".$del."\" onclick=\"return confirm('".ENT_DEL_CONFIRM." ?');\">".ENT_DEL."</a></td>"; }
          else { $this->vue->file_del = '<td>'.NIHIL.'</td>'; }      
        }
        $this->vue->picture = $ligne['urlImg'];
        $this->vue->url_down = $down; 
        $this->vue->timestamp = $date;
        if ($ligne['desc'] == 'Aucune') { $this->vue->description = ENT_UNDEFINED; }
        else { $this->vue->description = $ligne['desc']; } 
        $this->vue->taille = $ligne['size'];
        $this->vue->append('file','listeFile');
      }
    }
    else { $this->vue->retour =  MBR_NO_RESULT; }
    $this->vue->setFile("pied","pied.tpl"); 
    $pageFile = $this->vue->render("page");
    return $pageFile;
  }        
  /**
  * un admin peut déposer un fichier dans la table 'spip_vap_files'
  * l'appel de $file->sauvegarder est négatif 
  * car cette methode retourne soit une erreur soit null
  */
  public function index()
  {
    try { 
      $out = '';   
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }   
      if (! ($this->droits & ADMIN)){ $out =  MBR_NO_ACTION; }
      elseif (isset($_FILES['nomFile'])) {
        $file = new Fichier();
        if (!empty($_POST['descFile'])) { $desc = strval($_POST['descFile']); }
        else { $desc = ENT_UNDEFINED; }
        if (! $out = $file->sauvegarder($_FILES['nomFile'],$desc)){  
          $loc = "Location: ".BASEURL."index.php?load=fichier&token=".$token ;
          header($loc);
        }        
      }
      else { 
        $loc = "Location: ".BASEURL."index.php?ctrl=file&action=voir&token=".$token; 
        header($loc);  
      } 
      if (! empty($out)) {
        $page = $this->vueMng($token,'error',NULL,$out);
        $this->output($page);
      }
      else { return ; }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }           
  }
  /**
  * extract gère la creation, le telechargement et la suppression d'un fichier csv des membres d'une antene
  */
  public function extract()
  {
    try {
      $retour = ''; $tabVue = array(); $titre = $idAnt = NULL; 
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }
      if (filter_has_var(INPUT_GET,'antFile')) { $idAnt = filter_input(INPUT_GET,'antFile',FILTER_VALIDATE_INT); }
      elseif (filter_has_var(INPUT_POST,'lienant')) { $idAnt = filter_input(INPUT_POST,'lienant',FILTER_VALIDATE_INT); } 
            
      if (! isset($this->tabAntennes[$idAnt])) { throw new MyPhpException('File/extract: cle invalide'); }
      elseif (! ($this->droits & RESPONSABLE)){ $retour =  MBR_NO_ACTION; }
      elseif (($this->statut == 'responsable') && ($idAnt != $_SESSION['idant'])) { $retour = ANT_BAD; }
      
      if ( filter_has_var(INPUT_GET,'loadcsv') && ($file = filter_input(INPUT_GET,'loadcsv',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[0-9]+_extract\.csv$#'))))) {        
        $monFichier = DIRCACHANT.$idAnt.DIRECTORY_SEPARATOR.$file; 
        if (file_exists($monFichier)){
          if ($ok = $this->downLoad($file,$monFichier)) { exit(0); }
          else { $retour = FILE_DOWN_ERR; } 
        }
        else { throw new MyPhpException("Fichier incorrect"); }
      } 
      elseif (filter_has_var(INPUT_GET,'delcsv') && ($file = filter_input(INPUT_GET,'delcsv',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[0-9]+_extract\.csv$#'))))) {
        $monFichier = DIRCACHANT.$idAnt.DIRECTORY_SEPARATOR.$file;
        if (file_exists($monFichier)) { 
          unlink($monFichier); 
          $loc = "Location: ".BASEURL."index.php?del=fichier&token=".$token ;
          header($loc);
        }
        else { throw new MyPhpException("Fichier incorrect"); }  
      }     
      else {
        $cpt = 1; $ligne = ''; $comasep = ',"'; $sep = '","'; $final = '"';
        $masq = '_extract.csv';
        $colonnes = array("mail","nom","prenom","membre","inscrit","adresse","code","ville","tel","cachet");
        $sql = "SELECT DISTINCT mail,nom,prenom,membre,inscrit,code,adresse,ville,tel,cachet FROM ".T_HUM." AS H, ".T_CORE." as C "
        ." WHERE C.lienant = :idant AND H.idhum=C.idvap ORDER BY nom";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":idant",$idAnt,PDO::PARAM_INT);
        if ($ok=$stmt->execute()) {
          while ($rslt = $stmt->fetch(PDO::FETCH_ASSOC)){
            $ligne .= $cpt.$comasep; 
            foreach ($rslt as $cle=>$val) {
              if ($cle == 'cachet') { $ligne .= $val.$final."\n"; }
              else { $ligne .=  $val.$sep; }
            }
            $cpt++;
          }
          if ($good = $this->ecrisCache($idAnt,$masq,$ligne)) {
            $fichier = $idAnt.$masq;
            $pathFile = DIRCACHANT.$idAnt.DIRECTORY_SEPARATOR.$fichier; 
            $tabVue[0]['urlImg'] = '<img src="'.URLIMG.'csv.png" title="csv '.$this->tabAntennes[$idAnt].'" alt="csv '.$this->tabAntennes[$idAnt].'" />';;
            $tabVue[0]['id'] = $fichier;
            $tabVue[0]['name'] = urlencode($fichier);
            $tabVue[0]['desc'] = FILE_CSV_DESC ;
            $tabVue[0]['size'] = filesize($pathFile);
            $tabVue[0]['date'] = date('d-m-Y');
            $tabVue[0]['keyAnt'] = $idAnt;  
            $titre = FILE_CSV_TIT . $this->tabAntennes[$idAnt];           
          }
        }
        else { $retour = "echec création du fichier"; }
      }
      $page = $this->vueMng($token,'extract',$titre,$retour,$tabVue);
      $this->output($page);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }           
  }
  /**
  *  telecharger, supprimer et voir les fichiers renseignés dans la tables 'spip_vap_files'
  *  le contenu du répertoire FILEPATH lui n'est pas affecté par les suppressions. 
  */
  public function voir()
  {
    try { 
      $retour = ''; $tabVue = array(); $i = 0;  
      if (filter_has_var(INPUT_GET,'token')) { $token = $_GET['token']; } 
      else { $token = NULL; }
      if (! $ok = $this->validSess($token)){
        $loc = "Location: ".BASEURL."index.php?ctrl=auth&sess=ko";
        header($loc);
      }  
      if (! ($this->droits & RESPONSABLE)){ $retour =  MBR_NO_ACTION; }
      elseif (filter_has_var(INPUT_GET,'loadfile')) { //telecharger
        if ($file = filter_input(INPUT_GET,'loadfile',FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>'#^[a-z]{3,7}_[0-9]+\.[a-z]{3,5}$#')))) {        
          $monFichier = FILEPATH.$file; 
          if (file_exists($monFichier)){
             if ($ok = $this->downLoad($file,$monFichier)) { exit(0); }
             else { $retour = FILE_DOWN_ERR; } 
          }
          else{ throw new MyPhpException("Fichier incorrect"); }
        }
      }
      elseif (filter_has_var(INPUT_GET,'delfile')) { //supprimer
        if (! ($this->droits & ADMIN)){ $retour = MBR_NO_ACTION; }
        elseif ($delfile = filter_input(INPUT_GET,'delfile',FILTER_VALIDATE_INT)) {
          $sql = "DELETE FROM ".T_LOAD." WHERE idFile = :delfile"; 
          $stmt = $this->dbh->prepare($sql);
          if ($ok = $stmt->execute(array(':delfile'=>$delfile))) {
            $loc = "Location: ".BASEURL."index.php?token=".$token."&del=file";
          }
          else {  $loc = "Location: ".BASEURL."index.php?token=".$token."&delko=file";}
        }
        header($loc);
      }    
      else {  //voir 
        $sql = "SELECT DISTINCT * FROM ".T_LOAD;  
        if ($stmt = $this->dbh->query($sql)) { 
          while ($fichier = $stmt->fetch(PDO::FETCH_OBJ)){
            $ext = strrchr($fichier->nomFile,'.');  
            switch ($ext) {
              case ".doc": $image = '<img src="'.URLIMG.'doc.png" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
              case ".pdf": $image = '<img src="'.URLIMG.'pdf.png" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
              case ".gif": $image = '<img src="'.MINIURL.$fichier->nomFile.'" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
              case ".jpg": $image = '<img src="'.MINIURL.$fichier->nomFile.'" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
              case ".jpeg": $image = '<img src="'.MINIURL.$fichier->nomFile.'" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
              case ".png": $image = '<img src="'.MINIURL.$fichier->nomFile.'" title="'.$fichier->nomFile.'" alt="'.$fichier->nomFile.'" />';break;
            }
            $tabVue[$i]['urlImg'] = $image;
            $tabVue[$i]['id'] = $fichier->idFile;
            $tabVue[$i]['name'] = urlencode($fichier->nomFile);
            $tabVue[$i]['desc'] = $fichier->descFile;
            $tabVue[$i]['size'] = $fichier->sizeFile;
            $tabVue[$i]['date'] = $fichier->dateFile;
            $i++;  
          }
        }
        else { $retour =  MBR_NO_RESULT; }
      }      
      $page = $this->vueMng($token,'voir',NULL,$retour,$tabVue);
      $this->output($page);
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }           
  }
  protected function output($data)
  {
    if (is_array($data)) {print_r($data);}
    else {    echo $data; }
  }
}

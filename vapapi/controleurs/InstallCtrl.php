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
* Class InstallCtrl g�re l'installation du programme vapspace  
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

include_once("Controleur.class.php");
include_once("StaticInstall.class.php");
class InstallCtrl extends Controleur
{
  private $pdoStmt;
  private $installDbOblig = array();
  private $installDbFull = array();
  private $nbrIni;
  private $tableSuff=array();
  private $Tshort=array();
  private $dirWrite = array();
  private $urlInstall;

  public function __construct()
  {
    parent::__construct(); 
    $this->nbrIni = 11;
    $this->tableSuff = array('antenne','chat','core','multinews','file','human','mobile','news','meeting','region','trajet','transport');  
    $this->Tshort = array('T_ANT','T_CHAT','T_CORE','T_NEWS_ANT','T_LOAD','T_HUM','T_HUM_SOC','T_NEWS','T_MEET','T_REG','T_ROUTE','T_SOC');
    $this->installDbOblig = array('instPre','instPays','instAnt','instMbr','instContact'); 
    $this->installDbFull =  array('instPre','instPays','instAnt','instHa','instMbr','instContact','instReg','instSoc');
    $this->dirWrite = array(DIRTABLE,DIRCONTEXTE,DIREXTRA,DIRCACHANT,DIRCONTL);
    $this->urlInstall = BASEURL."index.php?ctrl=install";
  }
   /**
  * Des entit�s de la vue du contexte � appeler � chaque fois
  * l'entite index_admin est dans le pied.tpl et reference l'index de ce contexte-ci
  */ 
  private function globalEntities()
  {
    $this->vue->titre_head = 'Vap';
    $this->vue->titre_page = "VAP";
    $this->vue->vapspace = $this->url_vapspace.$this->token; 
    $this->vue->connecte = '';
    $this->vue->index_admin = '';  
  }
  /**
  * gestion de la vue (deux pages de formulaire 
  * @param $mode string, d�termine quoi afficher, 
  * @param $ctl string, liste des champs obligatoires vides
  * @param $tableau, array, les donn�es � afficher dans les formulaires
  */
  private function vueInstall($mode=NULL,$ctl=NULL,$tableau=array())
  {
    $this->globalEntities();    
    if ($mode == 'error') {  
      $this->vue->retour = 'La connexion a �chou�, veuillez recommencer';     
      $this->vue->choix_script = BASEURL."index.php?ctrl=install&amp;action=installCnx";
      $this->vue->setFile("contenu","install_Cnx.tpl");
    }
    elseif($mode == 'zero') { $this->vue->retour = 'installation impossible'; }
    elseif($mode == 'db') {
      $this->vue->retour = 'La connexion � la base est r�ussie.';
      $this->vue->instPre = 'vap_';
      $this->vue->instMbr = 'AAA000';
      $this->vue->instContact = 'info@vap-vap.be';
      $this->vue->checkHa = 'checked=';
      $this->vue->checkHaVal = '"checked"';
      $this->vue->instPays = $this->vue->instAnt = $this->vue->instReg = $this->vue->instSoc = NULL;
      $this->vue->choix_script = BASEURL."index.php?ctrl=install&amp;action=installDb";
      $this->vue->setFile("contenu","install_Db.tpl");
    }
    elseif ($mode == 'dbCtl') {
      $this->vue->retour = 'Des donn�es manquent: '.$ctl;
      $this->vue->choix_script = BASEURL."index.php?ctrl=install&amp;action=installDb";
      foreach ($this->installDbFull as $full) {
        if (($full == 'instHa') &&  (! empty($tableau[$full]))) {
           $this->vue->checkHa = 'checked=';
           $this->vue->checkHaVal = '"checked"';
        }
        elseif (! empty($tableau[$full])) { $this->vue->$full = $tableau[$full]; }
        else { $this->vue->$full = ''; }
      } 
      $this->vue->choix_script = BASEURL."index.php?ctrl=install&amp;action=installDb";
      $this->vue->setFile("contenu","install_Db.tpl");
    }
    elseif($mode == 'okGo') {
      $this->vue->retour = 'Tables cr��es';
    }
    elseif($mode == 'koGo') {
       $this->vue->retour = 'Tables �chec';
    }
    elseif ($mode == 'noWrite') {
       $repertory = implode(',',$tableau);
       $this->vue->retour = "<p>Le syst�me ne sait �crire dans le(s) r�pertoires: ".$repertory." merci de corriger.</p>"
                            ."<p><a href=".$this->urlInstall.">Poursuivre l'installation</a></p>";
    }
    else {
      $this->vue->choix_script = BASEURL."index.php?ctrl=install&amp;action=installCnx";
      $this->vue->setFile("contenu","install_Cnx.tpl");
    }
    $out = $this->vue->render("page");  
    return $out;
  } 

  /**
  * controle v�rifie que les param�tres obligatoires sont bien l�, sinon, construit  $ctl (string) avec le nom des parm manquants
  */
  private function installControl($tableau)
  {
    $ctlOblig = array(); $ctl = '';   
    foreach($this->installDbOblig as $valOblig) {
      if (empty($tableau[$valOblig])) { $ctlOblig[] = $valOblig; }
    }
    if (!empty($ctlOblig)) { $ctl = implode(',',$ctlOblig); }
    return $ctl;
  }
  /**
  * //verification des droits en ecriture sur certains repertoires
  */
  public function testW()
  {
    $rights = array(); 
    foreach ($this->dirWrite as $dir) {
      if (! is_writable($dir)) { $rights[] = $dir; }
    }
    if (!empty($rights)) { $page = $this->vueInstall('noWrite',NULL,$rights); }
    else { $page = ''; }
    return $page;
  }        
  /**
  * installation de la conexion � la database selon les param�tres fournis 
  */
  public function installCnx()
  {   
    if (!file_exists(DIRLIB.'dsn.php')) { 
      //reception des donn�es
      if (filter_has_var(INPUT_POST,'dbhost')) { $dbhost = filter_input(INPUT_POST,'dbhost',FILTER_SANITIZE_SPECIAL_CHARS); }
      if (filter_has_var(INPUT_POST,'dbname')) { $dbname = filter_input(INPUT_POST,'dbname',FILTER_SANITIZE_SPECIAL_CHARS); }
      if (filter_has_var(INPUT_POST,'dbuser')) { $dbuser = filter_input(INPUT_POST,'dbuser',FILTER_SANITIZE_SPECIAL_CHARS); }
      if (filter_has_var(INPUT_POST,'dbpass')) { $dbpass = filter_input(INPUT_POST,'dbpass',FILTER_SANITIZE_SPECIAL_CHARS); }
      $dsn = 'mysql:host='.$dbhost.';dbname='.$dbname;
      $user = strval($dbuser);
      $pass = strval($dbpass);
      $option = array(PDO::ERRMODE_SILENT);
      //test connexion database
      try { $this->pdoStmt = new PDO($dsn,$user,$pass); } 
      catch(Exception $e) { 
        $loc = 'Location: '.BASEURL.'index.php?ctrl=install&action=index&mode=error'; 
        header($loc); exit(0); 
      } 
      //ecriture dsn.php
      $Const = "<?php \n" 
               ."define('DSN','".$dsn."');\n"
               ."define('NOM','".$user."');\n"
               ."define('PASS','".$pass."');\n"
               ."?>";
      $fileCnx = DIRLIB.'dsn.php';
      $fp = fopen($fileCnx,'wb');
      fwrite($fp,$Const);
      fclose($fp);
      $this->pdoStmt = NULL;
      //la redirection va relancer un mod�le avec ouverture de connexion
      $loc = 'Location: '.BASEURL.'index.php?ctrl=install&action=installDb';   
    }
    else { $loc = 'Location: '.BASEURL.'index.php?ctrl=install&action=index'; }
    header($loc); exit(0);
  }
  /**
  * Fait tout le reste de l'installation, inclus StaticInstall.class.php
  * cr�ation des tables selon le pr�fixe fourni
  * ecrit 14 lignes dans vaoplib/connect.php pour faire la relation entre les tables sql et leurs constantes dans le code
  * installation d'une antenne (obligatoire) dans la table T_ANT, installation ou non de l'antenne 'hors-antenne'
  * installation d'une r�gion dans T_REG soit 'neutre' (pas pris en compte) soit li�e � l'antenne cr�e ci-dessus
  * gestion des hors-antennes et hors-regions 
  * ecriture des contextes /vapdata/contextes via l'appel � StaticInstall::contextes(param)
  * Si ok, redirige vers le contexte membre, action installation pour inscrire le premier membre (d'office un admin)
  */
  public function installDb()
  {
    try {
      $page = '';
      if (! filter_has_var(INPUT_POST,"okCnx")) { 
        $page =  $this->vueInstall('db');
        $this->output($page);
      }
      else {
        $ctl = $this->installControl($_POST);
        if (!empty($ctl)) { $page = $this->vueInstall('dbCtl',$ctl,$_POST); }
        else {
          $creaTables = $nomTables = $nomSoc  = $globCtxt = $antenne = $region = $lienreg = $lienant = array();
          $x = 0; $dataCtxt = '';
          
          if (!file_exists(DIRLIB.'dsn.php')) { throw new MyPhpException('Le fichier dsn.php doit exister'); }
          else { include_once('StaticInstall.class.php'); }        
          $dbh = $this->modele->getCnx();
          $prefixe = filter_input(INPUT_POST,'instPre',FILTER_SANITIZE_SPECIAL_CHARS);
          $mail =   filter_input(INPUT_POST,'instContact',FILTER_SANITIZE_EMAIL);
          $newMbr = filter_input(INPUT_POST,'instMbr',FILTER_SANITIZE_SPECIAL_CHARS); 
                    
          //commandes sql CREATE les tables de la base
          $creaTables = StaticInstall::sqlTables($prefixe); 
          foreach ($creaTables as $nom=>$sql) {
            //echo '<p>'.$nom.' : '.$sql.'</p>'; 
            $dbh->exec($sql); 
          } 
          //fixe le nom reel des tables et du nouveau membre dans  vaplib/Connect.php 
          $fichier = DIRLIB.'Connect.php';
          $fp = fopen("$fichier",'a');
          foreach($this->tableSuff as $idx=>$name) {            
            $short = $this->Tshort[$idx];
            $nomTables[$idx] = $prefixe.$name;
            $dataCtxt .= 'define("'.$short.'","'.$nomTables[$idx].'");'."\n";
          }
          $dataCtxt .= 'define("MAILWM","'.$mail.'");'."\n"
                       .'define("NEWMBR","'.$newMbr.'");'."\n";         
          if (!defined('MAILWM')) { fwrite($fp,$dataCtxt); }
          fclose($fp);
          $dataCtxt = '';
          
         //verification
          $sql = "SHOW TABLES";
          $stmt = $dbh->query($sql);
          while ($rslt = $stmt->fetch(PDO::FETCH_NUM)) { $tableStock[] = $rslt[0];}
          foreach ($this->tableSuff as $suffixe) { $good[] = $prefixe.$suffixe; }
          foreach ($tableStock as $tesTable) { 
            foreach ($good as $goodTable) {
              if ($tesTable == $goodTable){ $x++; }
            }
          }             
          if ($x == count($this->tableSuff)) {  
            //installation de l'antenne et d'une region (neutre), des soci�t�s eventuelles
            $antenne[0] = filter_input(INPUT_POST,'instAnt',FILTER_SANITIZE_SPECIAL_CHARS);            
            if (!empty($_POST['instReg'])) { 
              $region[0] = filter_input(INPUT_POST,'instReg',FILTER_SANITIZE_SPECIAL_CHARS);              
              $tmpMbrCtrl = DIRCONTL."MembreDeuxCtrl.php";
            }
            else { 
              $region[0] = 'Neutre'; 
              $tmpMbrCtrl = DIRCONTL."MembreUnCtrl.php";
            }
            //Le fichier du controleur Membre diff�re  selon liste Antenne simple ou emboit�e dans liste R�gion
            $newMbrCtrl = DIRCONTL."MembreCtrl.php";
            rename($tmpMbrCtrl,$newMbrCtrl);
        
            //si gestion HA avec R�gions, l'antenne HA doit appartenir � une R�gion sp�cifique 
            if (!empty($_POST['instHa'])) {                
              $antenne[1] = 'Hors-Antenne';
              if ($region[0] != 'Neutre'){ $region[1] = 'Autre'; }
            }

            $sqlReg = "INSERT INTO ".$nomTables[9]." (nomregion,province) VALUES (:reg,:pro) ";
            $stmt = $dbh->prepare($sqlReg);
            foreach ($region as $nomreg) {
              $stmt->bindParam(':reg',$nomreg,PDO::PARAM_STR);
              $stmt->bindParam(':pro',$nomreg,PDO::PARAM_STR);
              $stmt->execute();
              $lienreg[] = $dbh->lastInsertId();
            }
             
            $sqlAnt = "INSERT INTO ".$nomTables[0]." (lienreg,nomantenne) VALUES (:lnreg,:ant) ";
            $stmt = $dbh->prepare($sqlAnt);
            foreach ($antenne as $id=>$nom) { 
              //repecher ici l'id de Autre region ($altreg) necessaire pour la creation des contextes
              if (($id == 1) && empty($lienreg[1])) { $idreg = $altreg = $lienreg[0]; }
              elseif (($id == 1) && (!empty($lienreg[1]))) { $idreg = $altreg = $lienreg[1]; }
              else { 
                $idreg = $lienreg[$id]; 
                $altreg = NULL;              
              }              
              $stmt->bindParam(':lnreg',$idreg,PDO::PARAM_INT);
              $stmt->bindParam(':ant',$nom,PDO::PARAM_STR);
              $stmt->execute();
              $lienant[] = $dbh->lastInsertId();
            }
            if (!empty($_POST['instSoc'])) { 
              $societes = filter_input(INPUT_POST,'instSoc',FILTER_SANITIZE_SPECIAL_CHARS); 
              $flagSoc = true;
              $nomSoc = explode(',',$societes);
              $sqlSoc = "INSERT INTO ".$nomTables[11]." (societes) VALUES (:soc) ";
              $stmt = $dbh->prepare($sqlSoc);
              foreach($nomSoc as $soc) {
                $stmt->bindParam(':soc',$soc,PDO::PARAM_STR);
                $stmt->execute();
              }
            }              
            else { $flagSoc = false; }            
            $txt = '';
            //repecher l'id de horsAntenne ($altant) necessaire pour la creation des contextes
            if (!empty($lienant[1])) {$altant = $lienant[1]; }
            else { $altant = NULL; }
  
            //Construction des bons contextes, $altreg et $altant sont des cles sql (integer)
            foreach ($this->modele->getArrayContextes() as $nom) {
              if ($ctxt = StaticInstall::contextes($nom,$nomTables,$mail,$flagSoc,$altreg,$altant)) { 
                $globCtxt[] = $ctxt;
               }
            } 
            $compt = 0;
            //ecriture des contextes          
            foreach ($globCtxt as $idx=>$contexte) {  
              $fichier = DIRCONTEXTE.$contexte[0]['nom'].".ini";
              $fp = fopen("$fichier","wb");
              foreach ($contexte as $num=>$chapitre) {
                if (!empty($chapitre)) { 
                  foreach ($chapitre as $param=>$valeur) {
                    if ($param == 'titre'){ $dataCtxt .= $valeur."\n"; }
                    else { $dataCtxt .= $param.'='.$valeur."\n"; }
                  }
                }
              }
              if (fwrite($fp,$dataCtxt)) { $compt++ ; }
              fclose($fp);
              $dataCtxt = '';
              $x = $idx;            
            }
            if($compt == $this->nbrIni){ //Installation OK on peut charger le contexte membre
              $loc = "Location: ".BASEURL."index.php?ctrl=membre&action=inscrire&install=oui";
              header($loc); exit(0);                  
            }
          }
          else { $page = $this->vueInstall('koGo'); } 
        }
        $this->output($page);
      }
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }               
  }
  
  public function index()
  {    
    $page = $this->testW(); 
    if (empty($page)) {   
      if (!file_exists(DIRLIB.'dsn.php')) { 
        if (filter_has_var(INPUT_GET,'mode') && ($_GET['mode'] == 'error')){
          $page =  $this->vueInstall('error');
        }
        else { $page = $this->vueInstall(); } 
      }
      else { $page = $this->vueInstall('zero'); }
    } 
    $this->output($page);
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
  
  
  


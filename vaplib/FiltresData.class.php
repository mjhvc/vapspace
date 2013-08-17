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
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package MODELE
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

 
require_once('IniData.class.php');

/**
* La classe des filtres h�rite de IniData()
* Aujourd'hui (fevrier 2011) j'utilise la base standart en ligne � ce jour
* les requ�tes sont adapt�es � cette base
* Doivent se trouver ici, tous les filtres utilisables par la classe controlData.class.php
*/

class FiltresData extends IniData
{
  /**
  * Les attributs
  */
  private $mail = '' ;
  private $cle;
  protected $attributsAttendus = array();
  private $dataFiltre = array();
  private $erreurMasque = array();
  private $erreurTaille = array();
  private $erreurOblig ='' ;
  private $formatMail = array();
  /**
  * Fonction __construct(), fait appel au parent
  */
  function __construct($contexte=NULL,$statut=NULL)
  {
    //initialisation des attributs generaux de la classe
    $this->mail = ''; $this->cle = ''; $this->erreurOblig ='' ;
    $this->attributsAttendus = array();
    $this->dataFiltre = array(); $this->erreurMasque = array();
    $this->erreurTaille = array();
    $this->formatMail = array('mail','chatmail','expediteur','commanditaire');
    //appel le constructeur parent en surchargeant les parametres
    if ($contexte != NULL && $statut != NULL) {
      parent::__construct($contexte,$statut); 
    } 
    else {  parent::__construct(); }  
  }

/*  
  * Fonction qui affiche les erreurs de captures de preg_match_all
  * $nb est le nombre d'erreurs,$match est le tableau fourni par preg_match_all,
  * retourne une chaine $retour
  */
  public function affichErreurs($nb,$match)
  {
    $retour = '';	  
    for ($i=0;$i<$nb;$i++)
	  { $retour .= $match[0][$i];}
	  return $retour;
  }
  
/*   
  * Methode generique. Application de la suppression de tout echappement produit par magic_quotes
  * si necessaire pour tout tableau contenant des donn�es http.
  * Sources: http://www.lamsade.dauphine.fr/rigaux/mysqlphp/?page=code 
  */
  public function filtreMagicQuote()
  {
	  //Si echappement automatique, rectifier:
	  if (get_magic_quotes_gpc()) {
	  	$_POST = $this->magicNormHTTP($_POST);
	  	$_GET = $this->magicNormHTTP($_GET);
	  	$_REQUEST = $this->magicNormHTTP($_REQUEST);
	  	$_COOKIE = $this->magicNormHTTP($_COOKIE);
	  }
  } 

  /* 
  * Fonction generique qui enleve tout espace invisible en d�but et fin des valeurs d'un tableau
  * C'est un filtre a lui tout seul, doit etre repertorie dans le fichier de contexte
  * Filtre a appliquer avant masques (pour l'inscription en tout cas)  
  */
  public function filtreCarMasq(&$tableau)
  {		
	  foreach($tableau  as $val) {	
      if (is_array($val)){ 
        $val = filtreCarMasq($val); 
      }  
      else {  $val = trim($val); }
    }
    return $tableau;
  }
  /**
  * Filtre tout doublon selon 
  * @param $champ(string): nom du champ � verifier
  * @param $valeur(string): valeur du champ
  * @param $table : nom de la table
  * janvier 2012, rajout de $alt une cle facultative pour transformer la methode en compteur
  */
  public function doublon($champ,$valeur,$table,$alt=NULL)
  {
    if (!empty($alt)) {
      $marq = ':'.$alt;
      $cle = $alt;
    }
    else {
      $marq = ':'.$champ;
      $cle = $champ;
    }    
    $sql = "SELECT COUNT($champ) AS nbre FROM $table WHERE $cle = $marq ";
    $stmt = $this->bd->prepare($sql);
    $stmt->execute(array($marq=>$valeur));     
    $reponse = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($reponse['nbre'] > 0) { $sortie = 1; } 
    else { $sortie = false;}
    return $sortie;
  }  
  /*
  * selectionne un vieux $champ dans $table selon $nomcle qui vaut $valcle 
  * se filtre permet des controles de donnees hors-contexte  
  */
  public function getOld($champ,$nomcle,$valcle,$table,$flag=NULL)
  {
    $doublon = false;	$ancien = array();  
    //s�lection de l'ancien champ
	  $sql=  "SELECT $champ FROM $table WHERE $nomcle = :valcle ";
	  $stmt = $this->bd->prepare($sql);
    $stmt->execute(array(":valcle"=>$valcle));
	  $ancien = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($flag) && (!empty($ancien[$champ]))) { $retour = $ancien[$champ]; }
    elseif ((!empty($flag)) && (!empty($ancien[$champ]))) { $retour = true; }
    else { $retour = false;}
    return $retour;
  }

/**
  * Filtre dns : verifie si le dns d'un mail est correct
  * @param $mail, string, le mail a verifier
  * retourne true: ok, false: ko verifier en aval
  */
  public function filtreDns($mail)	 
  {
    $this->mail = $mail;  
    $pos = strpos($mail,'@');		//retourne la position de @
  	$dns = substr($mail,$pos+1);	// retourne la sous-chaine qui suit @ ($pos+1)		
  	if ($ok = checkdnsrr($dns)) { return TRUE;}
  	else {return FALSE;}
  }

/**
  * Le filtre anti-spam du contexte Membre
  */
  public function antiSpam($valeur,$lang)
  {
    $valeur = trim($valeur);
    $vrai = array(7,'sept','SEPT','zeven','ZEVEN');
    if (in_array($valeur,$vrai)) { $filtre = true; }
    else { $filtre = false;}
    return $filtre;
  }
 /**
  * @param: $num (int) est la valeur de la ppk d'un contexte
  * ppk est la PK de la premiere table dynamique du contexte
  * @param $altcont : string facultatif si une ppk est � v�rifier dans un autre contexte 
  */
  public function filtreClePPK($num)
  {
    $tables = $this->getTables();     
    $laTable = $tables[0]; //ici, choix arbitraire, filtre uniquement une cle primaire de contexte
    $ppk = $this->getPPK();
    $sql = "SELECT $ppk FROM $laTable WHERE  $ppk = :laCle ";
    $stmt = $this->bd->prepare($sql);
    $stmt->bindValue(':laCle',$num,PDO::PARAM_INT);
    $stmt->execute();
	  if ($tab = $stmt->fetch(PDO::FETCH_ASSOC)){
	    return 	true;
    }
	  else {	return false; }
  } 
  
  //une fonction pour 'matcher' un courriel
  public function filtreNonMail($mail)
  {
	  //la regex qui suit reconnait un courriel, sa fonction est non filtrante ...
			//la regex vient de :http://www.expreg.com/expreg_article.php?art=verifmail
			//alternative: ($valmail = preg_match_all('#[^a-zA-Z0-9@_.-]#',$tableau['mail'],$match))  
	  $match = array();
	  if (! preg_match('#^[[:alnum:]](?:[-_.]?[[:alnum:]])+_?@[[:alnum:]](?:[-.]?[[:alnum:]])+\.[a-z]{2,6}$#',$mail,$match))
	  { return TRUE;}
	  else
	  { return FALSE;}	
  }

/**  
* filtre le format des dates pour MYSQL (aaaa-mm-dd)
*/
  public function filtreDate($date)
  {
    $masque_date = '#^\d{4}-\d{2}-\d{2}$#';     
    $tabdate = explode('-',$date);
    $errDate='';        
    if (! preg_match($masque_date,$date)){  $errDate = 1; }
    elseif (! checkdate($tabdate[1],$tabdate[2],$tabdate[0])){ $errDate = 2;}
    return $errDate;
  }
   /**
  * Filtre simple de date naissance
  */
  public function naissance($an,$min=1912,$max=2000)
  {
    $date = intval($an);
    $sortie = false;
    if (($date < $min) || ($date > $max)) {
      $sortie = true;
    }
    return $sortie;
  } 
/**
  * Filtre Masque, la fonction qui :
  * matche tous les masques fournis en parametre ($tableau)
  * retourne un tableau this->erreurMasque[attribut] = string:les caracteres interdits de masques d�tect�s.  
  * ce tableau est vide si pas d'erreurs.  
  */
  public function filtreMasques($tableau)
  {    
    // appelle filtreCarMasq() pour supprimer tout caract�re invisible 
    $this->filtreCarMasq($tableau);
    //print_r($tableau);
    $drapstat = 0; $masque = array();
    //3 champs  font exeption au filtre (table: _vap_chat, _vap_news) 
    $valeursExepte = array('message','contenu','altcontenu');  
    //Boucle sur toutes les tables dynamiques du contexte($this->tablesdyn vient de $this->chargerContexte)  
    foreach ($this->dynTables as $nom)
    {
      // appel chargerTable (IniData) qui charge les proprietes de la table selon le contexte fourni      
      $this->tableData = $this->chargerTable($nom); 
      
      //appel attributsTable (IniData) qui charge un tableau simple avec le nom des attributs attendus dynamiques-oblig et facul-  
      $this->attributsAttendus  = $this->attributsTable($nom);
 
      foreach ($this->attributsAttendus as $x=>$valeur){ //Boucle sur les attributs oblig et facul
        if (!empty($tableau[$valeur])) {  //certains attributs sont facul 
          if ($masque = strval($this->tableData[$valeur]['masque'])){   //echo $valeur.' : '.$masque.'<br />' ;
            $attribut = strval($tableau[$valeur]); 
            if (! in_array($valeur,$valeursExepte)){  //pas de filtre pour ces valeurs
              if (in_array($valeur,$this->formatMail)){
                if ( $attribut != MBR_NOMAIL) { //eluder ce filtre pour les sans mails  
                  if (! $email = filter_var($attribut,FILTER_VALIDATE_EMAIL)) { //if ($ko = $this->filtreNonMail($attribut))
                    $this->erreurMasque[$valeur] = 'nomail';
                  }
                }
              }              
              elseif (preg_match_all($masque, $attribut, $match)){
                $nb= count($match[0]);
                $this->erreurMasque[$valeur] = $this->affichErreurs($nb,$match);      
              }                
            }
          }
        }
      }
    }    
    foreach ($this->dataContexte as $cont=>$opt) { //Boucle 2   : sur toutes les donnees  du contexte
      //Le contexte peut-il contenir des donnees statiques ?      
      if (!empty($opt['data'])) { $drapstat++ ; }
    }
    if ($drapstat > 0){
      $this->cleSchemaDataStat = array_keys($this->schemaDataStatique); 
      foreach ($tableau as $stat=>$valeur){
        if (in_array($stat,$this->cleSchemaDataStat)){     //une valeur statique detect�e dans le tableau client      
          $masque =  $this->dataContexte[$stat]['masque'];         
          $attribut = $valeur;              
          if (! preg_match_all($masque,$attribut,$match)){
            $nb= count($match[0]);
            $this->erreurMasque[$stat] = $this->affichErreurs($nb,$match);    
          }
        }
      }
    }
    return $this->erreurMasque ;
  }

/**
  * Un filtre de taille de donnees
  * retourne un tableau vide ou remplis par attribut
  */
  public function filtreTaille($tableau)
  {
    $drapstat = 0;    
    //Boucle sur toutes les tables dynamiques du contexte($this->tablesdyn vient de $this->chargerContexte)  
    foreach ($this->dynTables as $nom)
    {
      // appel chargerTable (IniData) qui charge les proprietes de la table selon le contexte fourni      
      $this->tableData = $this->chargerTable($nom);//cette fonction gere aussi 'PasDeMail'...A VERIFIER
      
      //appel attributsTable (IniData) qui 
      //charge un tableau simple avec le nom des attributs attendus dynamiques-oblig et facul-  
      $this->attributsAttendus  = $this->attributsTable($nom);
      foreach ($this->attributsAttendus as $valeur){    //Boucle sur les attributs oblig et facul
        if (!empty($tableau[$valeur])) {    //certains attributs sont facul
          $taille = $this->tableData[$valeur]['longueur']; //$this->dataContexte[$valeur]['longueur'];
          if (strlen($tableau[$valeur]) > $taille) {
            $this->erreurTaille[$valeur] = $taille ;
          }
        }      
      }
    }
    foreach ($this->dataContexte as $cont=>$opt) { //Boucle 2   : sur toutes les donnees  du contexte
      if (!empty($opt['data'])) { $drapstat++ ; }  //Le contexte peut-il contenir des donnees statiques ?    
    }
    if (!empty($drapstat)){
      $this->cleSchemaDataStat = array_keys($this->schemaDataStatique); 
      foreach ($tableau as $stat=>$valeur){
        if (in_array($stat,$this->cleSchemaDataStat)){ //une valeur statique detect�e dans le tableau client 
          $taille = $this->dataContexte[$stat]['longueur']; 
          if (strlen($valeur) > $taille) {
            $this->erreurTaille[$stat] = $taille ;
          }
        }
      }
    }  
    return $this->erreurTaille;
  }

/**
  * Filtre la presence des elements requis(obligatoires) d'un contexte.
  * @param $tableau : array, les donn�es � filtrer
  * retourne une chaine vide ou remplie des parametres manquants
  */
  public function filtreOblig($tableau,$flag=NULL,$table=NULL)
  {
    $corrige = $dataOblig = array(); 
    $back = 1; 
    $stroblig =  $this->erreurOblig = '';
    if (!empty($table)){ $dataOblig = $this->attributsOblig($table); }
    else { $dataOblig = $this->dataOblig; }    
    sort($dataOblig); //$this->attributsOblig() retourne un tableau 'd�tri�'  
    if ($flag == 'nopass'){
      $idxpass = array_search('passe',$dataOblig);
      array_splice($dataOblig,$idxpass,1);
    }
    foreach ($dataOblig as $val){ 
      if (($val == 'idpere') && ($tableau[$val] == 0)) { $back = 1; } //if (isset($tableau[$val]) && ($tableau[$val] === 0 || $tableau[$val] === '0')) { $back = 1; }     
      elseif(empty($tableau[$val]) || trim($tableau[$val]) == '') //if ((!array_key_exists($val,$tableau)) || is_null($tableau[$val]))
      {
        $back = 0; 
        $corrige[] = $val;
      }        
    }
    if ($back == 0){
      $stroblig = implode(',',$corrige);
      $this->erreurOblig = $stroblig;
    }
    else { $this->erreurOblig = '';}
    return $this->erreurOblig;
  }
  /**
  * Filtre ObligAlterna: verifie que un nombre precis de champs parmis une liste est present
  * @param $tabUn: array(), le tableau de toutes les donnees en entr�e
  * @param $faculA: array(), la liste des champs en alternace
  * @param $max: int, le nombre de champs en alternance possible
  */
  public function ObligAlterne($tabUn,$faculA,$max)  
  {
    $compt = 0;  $this->erreurOblig = '';  
    foreach($faculA as $valA) {
      if (array_key_exists($valA,$tabUn) && (!empty($tabUn[$valA]))) { $compt++; }
    }    
    if ($compt < $max) { $this->erreurOblig = 'Choisir au moins un lieu de passage '; }
    elseif ($compt > $max) { $this->erreurOblig = 'Seulement un seul choix parmis les noeuds de proximit�, merci'; }
    return $this->erreurOblig;
  }
  /**
  * Une methode qui teste l'absence totale de donnees attendues
  */
  public function filtreVacuum($tableau)
  {
    $ensembleDonnees = array_merge($this->dataOblig,$this->dataFacul);
    $tableau = $this->filtreCarMasq($tableau);
    $x = 0;
    foreach ($ensembleDonnees as $key=>$val)
    {
      if (!empty($tableau[$val])){ $x++;} 
    }
    if ($x == 0) {  return true;}
    else  { return false;}  
  }
  /**
  * Une fonction de controle qui verifie que le nom des donn�es est bien attendu
  */
  public function postInattendu($tableau,$hidden=array())
  {
    $retour = false;    $inattendu = '';
    $this->listeName = $this->getListeAttr();  
    $this->listePost = array_merge($this->listeName,$hidden);
    if ($this->contexte == 'Membre'){ array_push($this->listePost,'confirmation'); }
    foreach ($tableau as $key=>$val) {
      if (! in_array($key,$this->listePost)){ $retour = $key; }
    }
    return $retour;
  }
/**
*   selectionne la plus grande PK d'une table
*/
  public function calculKeyMax($cle,$table)
  {
    $sql = "SELECT MAX($cle) AS maxi FROM $table ";
    $stmt = $this->bd->query($sql);
    $rsps = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt == NULL;
    return $rsps['maxi'];
  }
/**
*   selectionne une PK de T_CORE si cette PK est bien li�e � T_CORE.'lienant'
*   @return : idhum ou false
*/  
  public function mbrInAnt($cle,$cleant)
  {
    $sql = "SELECT idvap FROM ".T_CORE." WHERE idvap = :cle AND lienant = :lienant";
    $stmt = $this->bd->prepare($sql); 
    $stmt->bindValue(':cle',$cle,PDO::PARAM_INT);
    $stmt->bindValue(':lienant',$cleant,PDO::PARAM_INT);
    $stmt->execute();
    $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($rslt['idvap'])) { $rsps = false; }
    else { $rsps = $cle; }
    return $rsps;
  }  
/**
*   selection d'une region en fonction d'une cle d'antenne
*   cette methode et la pr�c�dente pourrait migrer dans GererData.class.php sous forme plus automtis�e....
*/  
  public function selectreg($lienant)
  {
    $sql = "SELECT lienreg FROM ".T_ANT." WHERE idant=:reg";
    $stmt = $this->bd->prepare($sql); 
    $stmt->bindValue(':reg',$lienant,PDO::PARAM_INT);
    $stmt->execute();
    $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($rslt['lienreg'])) { $rsps = false; }
    else { $rsps = $rslt['lienreg']; }
    return $rsps;
  } 
  /**
  * methode verifmail($mail,$statut)
  * V�rifie si le mail appartient bien � quelqu'un en base
  * @param $mail: string, le mail � v�rifier
  * @param $statut: string, le statut du mail � v�rifier
  */
  public function verifmail($mail)
  {
	  $sql = "SELECT COUNT(idhum) AS nbres,statut FROM ".T_HUM." WHERE mail = :mail GROUP BY idhum";
	  $stmt = $this->bd->prepare($sql);
    $stmt->bindValue(':mail',$mail,PDO::PARAM_STR);
    $stmt->execute();
    $rslt = $stmt->fetch(PDO::FETCH_ASSOC);
	  if ($rslt['nbres'] >= 1){ $end =  true; }
	  else { $end = false; }
    return $end;
  }
   /**
  * Compte les news actives et renvoi true-false
  * devrait peut-etre migrer dans FiltresData.class.php
  */ 
  public function lockMoulin()
  {
    $sql = "SELECT commanditaire,COUNT(idnews) AS id FROM ".T_NEWS." WHERE active = 'oui' GROUP BY commanditaire";
    $stmt = $this->bd->query($sql);
    $rslt = $stmt->fetch(PDO::FETCH_ASSOC);  
    if ($rslt['id'] >= 1) {  $retour = $rslt['commanditaire']; }   
    else { $retour = false ; }
    return $retour;
  }
   /**
  * Une fonction qui matche vite un mail (utiliser par inscrire)
  */
  public function matchMail($mail)
  { 
    if (! $test = $this->filtreDns($mail)) { return false; }
    elseif (! ($test =  filter_var($mail,FILTER_VALIDATE_EMAIL))) { return false; }
    else { return true; }
  }       
    
  
}

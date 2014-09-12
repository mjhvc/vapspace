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
* Class GererData :  traite les relations entre les donn�es et la database
* se base sur la notion de contexte trait� par sa classe parente: IniData
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package MODELE
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be  
*/
include_once('IniData.class.php');

class GererData extends IniData
{
  /**
  * Declaration attributs 
  */
  protected $attributsAttendus = array();  
  protected $champUtile = '';
  protected $champInutile = '';  
  protected $classement = array();  
  protected $cleStat;
  protected $cleSchemaTableStat = array();
  protected $dataClientStat = array();  
  protected $lastId ;   
  protected $nomClePK;
  protected $nomCleFK;
  protected $nomContexte;  
  protected $ofk;  
  protected $tableStat;  
  protected $valChampUtile = '';
  protected $valChampinutile = '';  
  protected $valofk;
  protected $valPPK;
 
  /**
  * Fonction __construct(), quelques initiations puis appellle methode parent  
  */
  public function __construct($contexte=NULL,$statut=NULL)
  {
    //Initiation
    $this->tableStat = $this->nomClePK = $this->nomCleFK = $this->ofk = $this->valofk = '';
    $this->champUtile = $this->valChampUtile = $this->champInutile = $this->valChampInutile = NULL;   
    $this->attributsAttendus = $this->classement = $this->dataClientStat = array();  

    //appel le constructeur parent en surchargeant les parametres
    if ($contexte != NULL && $statut != NULL) {
      parent::__construct($contexte,$statut);    
    } 
    else { parent::__construct(); }  
  }
  
  //-------------ENCAPSULATION de IniData pour les controleurs-------------------//
  /**
  * Un simple chargeur de contexte, 
  * @param contexte:string est obligatoire
  * @param $statut:string est obligatoire
  */
  public function getContexte($contexte,$statut)
  {
    return $this->chargerContexte($contexte,$statut);
  }
  /**
  * getDataContexte: 
  * @return $this->dataContexte calcul� par parent::chargerContexte($contexte,$statut)  
  */  
  public function getDataContexte()
  {
    return $this->dataContexte;  
  }
  /**
  * rechargerContexte: Appelle parent::rechargerContexte($contexte,$statut) 
  */ 
  public function resetContexte($contexte,$statut){
    try {
      $tabNewContexte = array();  
      $tabNewContexte = parent::rechargerContexte($contexte,$statut);
      return $tabNewContexte;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }
  }
  /**
  * methode getLiensCles()
  * @return $this->liensCles : array() calcul� par parent::chargerContexte($contexte,$statut)
  */
  public function getLiensCles()
  {
    return $this->liensCles;
  }
  /**
  * getArrayContextes()
  * @return un array() de tous les contextes
  */  
  public function getArrayContextes()
  {
    return $this->arrayContextes;
  }
  /**
  * getTables()
  * @return parent::getTables()
  */
  public function getTables($dyn=NULL)  
  {
    return parent::getTables($dyn);
  }
  /**
  * getDynTables()
  * @return parent::getTables(1)
  */
  public function getDynTables()
  {
    $dynamique = 1;    
    return parent::getTables($dynamique);
  }
  /**
  * getPassiveTable()
  * @return string parent::getPassiveTable()
  */
  public function getPassiveTable()
  {
    $passive = parent::getPassiveTable();
    return $passive;
  }
  /**
  * getLiaisonTable()  
  * @return string $this->liaisonTable calcul� par parent::chargerContexte($contexte,$statut)
  */
  public function getLiaisonTable()
  {
    return $this->liaisonTable;
  }
   /**
  * getExtraTable() 
  * @return array() avec le nom des tables extra
  */
  public function getExtraTable()
  {
    $extraTable = parent::getExtraTable();
    return $extraTable;
  }
  /**
  * getPPK()
  * @return le nom de la Primarey Principal Key (PPK) 
  * la premiere PK de la premiere table d'un contexte
  */
  public function getPPK()  
  {
     return $this->PPK;
  }
  /**
  * getExtraFK 
  * @return array() un tableau des FK class�es dans [extra] uniquement 
  */
  public function getExtraFK()
  {
    return $this->extraFK;
  }
  /**
  * getCnx()
  * @return l'objet de la connexion en bd
  */
  public function getCnx()   
  {
    return $this->bd;
  }
  /**
  * getListeAttr()
  * @return array(), la liste des attributs obligatoires et facultatifs li�s � un contexte
  */
  public function getListeAttr()
  {
    $liste = parent::getListeAttr();
    return $liste;
  }
  /**
  * getOblig()  
  * @return array(), la liste des attributs obligatoires d'un contexte
  */
  public function getOblig()
  {
    $liste = parent::getOblig();
    return $liste;
  }
  /**
  * getFaculFull()  
  * @return array(), la liste de tous les attributs Facul possibles (les statiques compris)
  */
  public function getFaculFull()
  {
    $liste = parent::getFaculFull();
    return $liste;
  }
  /**
  * getFaculDyn() 
  * @return array(), la liste dynamique des attributs facultatifs
  */
  public function getFaculDyn()
  {
    $liste = $this->dataFacul;
    return $liste;
  }
  /**
  * getStatiqueValeurs()
  * @return array() $this->statiqueValeurs, calcul� par parent::chargerContexte($contexte,$statut)
  */
  public function getStatiqueValeurs()
  {
    return $this->statiqueValeurs;
  }
  /**
  * getSchemaDataStatique()  
  * @return array() $this->schemaDataStatique, calcul� par parent::chargerContexte($contexte,$statut)
  */
  public function getSchemaDataStatique()
  {
    return $this->schemaDataStatique;
  }
  /**
  * getSchemaDataPassive()  
  * @return array() $this->schemaDataPassive, calcul� par parent::chargerContexte($contexte,$statut)
  */
  public function getSchemaDataPassive()
  {
    return $this->schemaDataPassive;
  }
  /**
  * getVue()  
  * @return array(),la liste des champs pour organiser la vue d'un contexte
  * todo: inclure un parametre statut pour  selectionner une vue selon un statut, 
  * modifier alors $this->dataVue en $this->dataVue['membre'] p.ex 
  */
  public function getVue()
  {
     return $this->dataVue;
  }
  /**
  * getMaGpc() stripslashes les donn�es extraites de la database
  */
  public function getmaGpc($tableau)
  {
    $newTab = $this->magicNormHTTP($tableau);
    return $newTab;
  }
  /**
  * getDataTable($table,$flag=NULL) appel parent::chargerTable()
  * @param : $table:string le nom d'une table sql
  * @param : $flag, bool
  * @return array()
  */
  public function getDataTable($table,$flag=NULL)
  {
    $data = array();    
    if (!empty($flag)){
      $data = parent::chargerTable($table,'1');
    }
    else { $data = parent::chargerTable($table);}
    return $data;
  }
  /**
  * R�cup�rer les variables fixes Hors-Antenne,Hors-Region et $mail
  */
  public function getGeneral($type)
  { 
    $dataCont = NULL ;    
    if (!empty($this->dataContexte['general'][$type])) 
    { $dataCont = $this->dataContexte['general'][$type]; }
    return $dataCont;
  }
  //--------------protected---------------------//
  /**
  * dataEntity() connerti les elements html en entites html
  * @param $chaine : string en entr�e
  * @return string, une chaine convertie
  */
  protected function dataEntity($chaine)
  {
    if ($this->contexte == 'News'){ $propre = $chaine;}
    else { 
      $propre = htmlspecialchars($chaine,ENT_QUOTES,"ISO-8859-1");
      $propre = trim($propre);
    }
    return $propre;
  }
  /**
  * preparation()  cr�e et retourne un tableau par Table avec 
  * - comme cles : les noms des champs oblig et facul de la table du contexte en parametre 
  * - comme valeur: leurs valeurs PRISES DANS LA DATABASE
  * @param $valcle, integer  est une FK (propre au contexte) ou un PK mais la FK du contexte sera selectionn�e en premier
  * @param $table: string, nom de la table o� preparer les donnees
  */
  protected function preparation($valcle,$table) 
  {    
    $x = 0;  $tableBaseCont = array(); $idx = 0; $wagonSql = ' ';        
    $this->table = strval($table); 
    $this->attributsAttendus = array(); $testkeys = NULL;     
    $this->schema = $this->schemaTable($this->table);
    //Recherche du nom des cles FK ou PK qui repr�sentent la ligne � preparer 
    //$valcle represente-t-il la valeur d'une cle etrang�re d'une table?     
    foreach ($this->schema as $champ=>$option){
      if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $this->PPK)){
        $this->nomClePK = $champ;
        $testkeys = 1;
      }
    }
    if (empty($testkeys)){
      foreach ($this->schema as $champ=>$option){ 
        if ($option['clePrimaire']) { 
          $this->nomClePK = $champ; 
          $testkeys = 1;
        }
      }
    }
    if (empty($testkeys)){ throw new MyPhpException("La table: ".$this->table." n'a pas de cl� primaire?");}
    //construction et execution de la requete sql de selection selon la cl� (PK ou FK)  
    $ptmq = " = ? ";  
    $whereSql = $this->nomClePK.$ptmq; 
    $fromSql = $this->table;  
    //Recherche des attributs attendus dans la base de donn�e, l'appel de parent::attributsTable() est justifi�
    $this->attributsAttendus  = $this->attributsTable($this->table);    
    $nbr = count($this->attributsAttendus);      
    foreach ($this->attributsAttendus as $nomVal){    //Boucle sur les attributs oblig et facul
      ($idx < ($nbr-1))?  $Sep = ", ": $Sep = " "; 
      $wagonSql .= $nomVal.$Sep;
      $idx++; 
    }
    $sql = "SELECT $wagonSql FROM $fromSql WHERE $whereSql ";
    $stmt = $this->bd->prepare($sql);
    $stmt->execute(array($valcle));
    
    //le classement des donnees doit etre diff�rent selon que il s'agit d'une table de 'liaison' ou pas 
    if ($this->table == $this->liaisonTable){
      while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)){
        $tableBaseCont[$x] = $ligne;
        $x++;
      }
    }
    else {
      while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)){
        $tableBaseCont = $ligne;
      }
    }
    $stmt = NULL;
    return $tableBaseCont;   
  }
  /**
  * chargeDataStat()
  * recoit en parametres : nom(string) et valeur($string) 
  * 'nom' est le nom d'une valeur statique selectionnee precedemment par dataClasse()
  * valeur est la valeur 'statique' de 'nom'
  * retourne un array() 
  * avec comme cl�s : un nom des colonnes parmis FK et 'champs sp�cifiques' de la table de liaison 
  * avec comme valeurs : la valeur de ces colonnes pour la table de liaison (sauf pour la cle FK vers PPK)
  * Cette methode fait donc la relation entre des valeurs statiques et 
  * les valeurs ad hoc diff�rentes mais li�es sur la table de liaison
  * exemple : 
  * entree parametre statique : 'STIB' 'ouiavec',  
  * sortie array('lienhum'=>'idhum,'lientrans'=>12,'utilisation'=>'oui','abonne'=>'oui') 
  * C'est Controleur.class.php::constructEntity() qui prend la main
  */
  protected function chargeDataStat($nom=NULL,$valeur=NULL)
  {
    $tableaustat = array(); $laclefk = array();
    if (!$nom || !$valeur) { //aucuns choix statique mais le contexte statique existe bien            
      //Parcourir le tableau des cles etrang�res du contexte      
      foreach ($this->liensCles as $name=>$val){  
        //D�tecter les cles FK de $this->liensCles presentent dans $this->schemaLiaison        
        if (isset($this->schemaLiaison[$name])){
          //D�tection du nom de la FK qui pointe vers le PPK du contexte 
          if ($this->schemaLiaison[$name]["cleSecondaireRelie"] == $this->PPK){ 
            $tableaustat[$name] = $val;  
          }
          else {  //Une valeur par default du champ ad hoc doit exister pour la cle qui pointe pas sur PPK       
            $tableaustat[$name] = $this->schemaLiaison[$name]['default']; 
          }
        }      
      }
      if (! empty($this->liaisonChamps)) { //Si des champs sp�cifiques � la table de liaison existent 
        foreach ($this->liaisonChamps as $champ){ 
          $tableaustat[$champ] = $this->schemaLiaison[$champ]['default'];  
        }
      }
    }
    else {          
      //detecter la table de liaison, le nom et la valeur de la FK de la table de liaison 
      //qui pointent vers les valeurs 'Statiques'  
      $this->tableStat = $this->schemaDataStatique[$nom]['tableLiaison'];            
      $this->cleStat = $this->schemaDataStatique[$nom]['cleLiaison'];          
      $this->valCleStat = $this->schemaDataStatique[$nom]['valCleLiaison'];         
      //detecter le nom de la FK de liaison qui pointe sur la PPK         
      foreach ($this->liaisonFK as $num=>$cle){
        if ($cle != $this->cleStat){ 
          $this->ofk = $cle;
          $this->valofk = $this->liaisonFKP[$num];  //ici, rien que le 'nom'de la cle 
        }
      }
      $tableaustat[$this->cleStat] = $this->valCleStat; //la cle FK statique et sa valeur reelle
      $tableaustat[$this->ofk] = $this->valofk;         //la cle PPK et le nom de la PPK en valeur
      
      //relie 'valeur'(statique) fournie en param�tre aux champs de 'liaison' sur la table de liaison :
      //Une 'valeur statique' �tabli une relation avec une valeur champ 'specifique' sur la table de liaison  
      if (!empty($this->statiqueValeurs)) {         
        foreach ($this->statiqueValeurs as $idx=>$mot){   
          if ($mot == $valeur){ 
            //$this->champUtile <- le nom ad hoc du champ de liaison
            //$this->valChampUtile <- la valeur de 'liaison' ad hoc (unique pour le contexte membre)
            $this->champUtile = $this->liaisonChamps[$idx];  
            $this->valChampUtile = $this->liaisonValeurs;
            $idxchamp = $idx;
          } 
        }
        //detection du champ specifique de table de liaison  non choisi en parametre et de sa valeur  
        switch($idxchamp){ 
          case 0:        
            $this->champInutile = $this->liaisonChamps[1];
            $this->valChampInutile = $this->schemaLiaison[$this->champInutile]['default'];
            break;
          case 1: 
            $this->champInutile = $this->liaisonChamps[0];
            $this->valChampInutile = $this->liaisonValeurs; //ici: utilisation = 'oui' et abonne = 'oui'
            break; 
        }
        $tableaustat[$this->champUtile] = $this->valChampUtile;
        $tableaustat[$this->champInutile] = $this->valChampInutile;      
      }
    }
    return $tableaustat;
  }
  /**
  * Une methode qui classe les DONNEES CLIENTES (prealablement filtrees par une action du controleur) par TABLE
  * methodes attributsTable()  -> IniData
  * boucle foreach 1 : Boucle sur toutes les tables dynamiques du contexte
  * sous_boucle foreach 1-1: Boucle sur les attributs oblig et facul
  * sous_boucle foreach 1-2: Recherche d'une cle FK si semblable � $this->PPK  et non list�e dans attributsAttendus
  * boucle foreach 2 : Boucle  sur toutes les donnees  du contexte � la recherche de donnees 'Statiques'      
  * Si le mod�le inclus une structure statique:
  * sous_boucle 2-1 sur le tableau fourni; detection donn�es statique dans tableau client et
  * appel de chargeDataStat() avec les donnees ou pas de donn�es 
  * certains attributsAttendus sont facultatifs...tres important ici...pour de la souplesse
  * c'est la couche 'controle" qui verifiera la presence obligatoire de certaines donnees      
  * Retourne un array : 
  * data 'dynamiques': $wagon[$this->table] = array("nom"=>"valeurcliente")
  * data 'liaison' : $wagon[$this->tableLiaison][$int] = array('lienhum'=>'idhum,'lientrans'=>12,'utilisation'=>'oui','abonne'=>'oui') 
  * dataClasse travaille avec un tableau fourni par le client
  * si une $cleligne est fournie, on est en mode 'update'
  */
  protected function dataClasse($tableau,$FKey=NULL)
  {
    $drapstat = 0;  $clestatiques = array(); $wagon = array();
    if (!empty($FKey)) { $adhoCle = $FKey ; }
    else { $adhoCle = $this->PPK ; }     
    foreach ($this->dynTables as $nom){         
      $this->table = $nom; 
      $this->schema = $this->schemaTable($this->table);    
      $this->attributsAttendus  = $this->attributsTable($this->table);    
      foreach ($this->attributsAttendus as $nomVal){    
        if (isset($tableau[$nomVal]) && $tableau[$nomVal] === 0) {
          $wagon[$this->table][$nomVal] = $tableau[$nomVal];
        }        
        elseif (!empty($tableau[$nomVal])) {        
          $valeurPropre =  $this->dataEntity($tableau[$nomVal]);          
          $wagon[$this->table][$nomVal] = $valeurPropre;
        }
      }
      foreach ($this->schema as $champ=>$option){
        if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $adhoCle)){
          $wagon[$this->table][$champ] = $option["cleSecondaireRelie"];   //valeur symbolique(son nom) maintenant
        }
      }
    }
    foreach ($this->dataContexte as $cont=>$opt) { 
      if (!empty($opt['data'])) { $drapstat++ ; }
    }
    if (!empty($drapstat)){
      $this->table = $this->liaisonTable;
      $this->cleSchemaDataStat = array_keys($this->schemaDataStatique);
      $cpt = 0;
      foreach ($tableau as $stat=>$valeur){  
        if (in_array($stat,$this->cleSchemaDataStat)){    
          $dataStatique[$cpt] = $this->chargeDataStat($stat,$valeur);
          $cpt++;
        }
      }        
      if (empty($cpt)) { $dataStatique[0] = $this->chargeDataStat();} 
      foreach ($dataStatique as $id=>$ligne){    
        $wagon[$this->table][$id] = $ligne;
      } 
    }
    return $wagon;
  }
  /**
  * Injecter: Possibilit� d'injecter ici plusieurs donn�es non class�e(ni oblig ni facul) 
  * @arg $dataIn, les donnee�s � injecter et la table d'injection type: array($table,array($nomChamp),array($valChamp))
  * @arg $trier, type array: le tableau de valeurs initiales.
  */    
  protected function Injecter($trier,$dataIn)
  {
    $injTable = $dataIn[0];
    $injChamp = $dataIn[1];
    $injVal = $dataIn[2];
    if (! is_array($injChamp)){ $alertInj = 1;}      
    elseif (! is_array($injVal)){ $alertInj = 1;}
    if (!empty($alertInj)) { throw new MyPhpException('inscrire: array injection invalide'); } 
    $cptChamp = count($injChamp);      
    $cptVal = count($injVal); 
    if ($cptChamp != $cptVal) { throw new MyPhpException(' inscrire: array injection: les sous-tableaux[1] et [2] sont inegaux '); }    
    foreach ($injChamp as $cle=>$val) {
      $valAccroc = $injVal[$cle];
      $cleanValAccroc = $this->dataEntity($valAccroc);
      $trier[$injTable][$val] = $cleanValAccroc;
    }
    return $trier ;
  }
  /**
  *   methode insertion() insere une ligne dans une table (selon contexte)
  *   retourne $this->lastId valeur de la PK de la ligne inseree  
  */
  protected function insertion($nomTable,$train=array(),$lastPK=NULL)
  { 
    $tablesOrig = array_keys($this->classement);
    if (! in_array($nomTable,$tablesOrig)){ 
      $error = $nomTable.' inconnue dans ce contexte';      
      throw new MyPhpException($error);
    }
    $this->schema = $this->schemaTable($nomTable);
    $nbr = count($train);     
    $idx = 0; $marqueur = ''; $listeNomAttrib = ''; $listeMarqueurs = ''; 
    //Attribution de la valeur reelle ($lastPK) aux cles secondaires qui avaient une valeur symbolique et pointent sur $this->PPK 
    //La 'premiere' table du contexte (celle de la premiere insertion) ne doit pas avoir de FK qui point vers PPK   
    foreach ($this->schema as $champ=>$opt){
      if (($opt['cleSecondaire'])&& ($opt["cleSecondaireRelie"] == $this->PPK)){
        if (is_null($lastPK)){ 
          $msg = $champ.' : pas de valeur pour cette cle en insertion ??';          
          throw new MyPhpException($msg);
        }
        else { $train[$champ] = $lastPK; }
      }
    }
    //creation des chaines $listeNomAttr et $listeValAttrib avec le $sep(arateur)     
    foreach ($train as $cle=>$val){
      ($idx < ($nbr-1))?  $Sep = ", ": $Sep = NULL; 
      $listeNomAttrib .= $cle.$Sep;
      $listeMarqueurs .= ' :'.$cle.$Sep; //et pas $listeNomAttrib !!
      $idx++;
    }
    //finalisation des listes d'attributs, de marqueurs et creation de la requete sql
    $listeNomAttrib = ' ('.$listeNomAttrib.') ';
    $listeMarqueurs = ' ('.$listeMarqueurs.') ';
    $tableChamps = $nomTable.$listeNomAttrib;
    $sql = strval("INSERT INTO $tableChamps VALUES $listeMarqueurs ");   
    $stmt = $this->bd->prepare($sql); 
    //liaison marqueurs-valeurs   
    foreach ($train as $cle=>$val){
      switch ($this->schema[$cle]["type"]){
        case ('char'):case('varchar'): case('date'):case('text'): $data_type = PDO::PARAM_STR ; break;
        case('bigint'):case ('int'):case('tinyint'):case('timestamp'): $data_type = PDO::PARAM_INT ; break;
      }                  
      $marqueur = ":".$cle;         
      $stmt->bindValue($marqueur,$val,$data_type);  
    } 
    //Ex�cution de la requete prepar�e.    
    if ($ok = $stmt->execute()) { 
      $lastId = intval($this->bd->lastInsertId());
      $stmt = NULL;
      return $lastId;
    }
    else { throw new MyPhpException('Impossible d\'inserer la donnee');}
  }
  /**
  * La requete de mise � jour d'une ligne d'une table
  * @param $nomtable : la table sur laquelle faire un update
  * @param $train : array() le train des valeurs � mettre � jour
  * @param $id :integer, la valeur de la PK de la ligne � mettre � jour
  * @param $flag:(NULL) un drapeau pour evaluer si l'update doit se baser sur une FK
  * note : $train peut etre minimaliste...
  * donc, p.ex la mise � jour du champ numbr de spip_vap_core peut s'effectuer par appel de cette methode 
  * depuis le controleur MembreCtrl.php 
  */ 
  protected function update($nomTable,$train,$id,$condExtra=NULL)
  {
      $this->table = $nomTable;
      $this->schema = $this->schemaTable($this->table);
      $nbr = count($train);    
      $idx = 0; $marqueur = ''; $chaine = '';$testkey = 0;
      foreach ($this->schema as $nom => $option) {
        if (empty($flag) && ($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $this->PPK)){
          $nomCle = $nom; 
          $train[$nom] = $id;
          $testkeys = 1;
        }
      }
      if (empty($testkeys)){
        foreach ($this->schema as $nom => $option){        
          if ($option['clePrimaire']){ $nomCle = $nom; $testkeys = 1; }
        }   
      }
      if (empty($testkeys)){ throw new MyPhpException ("La table: ".$this->table." n'a pas de cl� primaire en update()??");}
      //creation des chaines $listeNomAttr et $listeValAttrib avec le $sep(arateur)
      //creation de la requete sql     
      foreach ($train as $cle=>$val){
        ($idx < ($nbr-1))?  $Sep = ", ": $Sep = NULL ; 
        $marqueur = ':'.$cle; 
        $fullMarque =  $cle.' = '.$marqueur.$Sep;      
        $chaine .= $fullMarque;
        $idx++;
      }
      $marqueurCle = ":".$nomCle ;
      $chaineCle = strval($nomCle ." = ". $marqueurCle) ;
      $sql = "UPDATE $this->table SET $chaine WHERE $chaineCle "  ;
      
      //liaison marqueurs valeurs, preparation de requete 
      $stmt = $this->bd->prepare($sql);
      foreach ($train as $cle=>$val)
      {
        switch ($this->schema[$cle]["type"])
        {
          case ('char') : case('varchar') : case('date') : case('text') : $data_type = PDO::PARAM_STR ; break;
          case('bigint') : case ('int') : case('tinyint') : case('timestamp') : $data_type = PDO::PARAM_INT ; break;
        }    
        $marqueur = ":".$cle;       
        $stmt->bindValue($marqueur,$val,$data_type); 
      }
      $stmt->bindValue($marqueurCle,$id,PDO::PARAM_INT);
      if ($stmt->execute()){ $stmt = NULL; $train = array(); return true;}
      else { throw new MyPhpException('Erreur d\'execution de requete preparee par update()');}
    }
  
  //--------------public---------------------//
  /**
  * Cette fonction inscrit des donnees clients en base
  * Appelle 2 methodes :
  * $this->dataClasse pour le tri selon les tables
  * $this->insertion pour le traitement sql
  * @param $tableau: array() le tableau de donnee � ins�rer
  * @param $injection: array() eventuellement un array suppl�mentaire pour injection
  */
  public function inscrire($tableau,$injection=array())
  {
    $x = 0;  $id=NULL; $alertInj = NULL;  $premTri = array(); 
    //Pour toutes les donnees recues, je les range comme suit:
    $premTri = $this->dataClasse($tableau);
    if (! empty($injection)){
      $this->classement = $this->Injecter($premTri,$injection);
    }
    else { $this->classement = $premTri; }
    $tableIns = array_keys($this->classement); 
    foreach ($tableIns as $table){               
      // Bien que trait�e en premier ici, $id doit exister donc une donn�e statique vient apr�s une standart      
      if ($table == $this->liaisonTable){ 
        foreach ($this->classement[$table] as $nbr=>$tabliaison){
          $idliaison = $this->insertion($table,$tabliaison,$id);
        }
      }
      else { //premier enregistrement? on recupere lastid et l'attribue � id et � valPPK
        $this->lastId = $this->insertion($table,$this->classement[$table],$id);
        if ($x == 0) { 
          $this->valPPK= $this->lastId; 
          $id = $this->lastId;          
        }
        $x++;
      }   
    }
    return $this->valPPK;
  }
  /**
  * Met a jour un contexte multi table par appel � update(unetable)
  * @param $tableau: array() tableau de donn�es  
  * @param $clelligne: integer la valeur d'une ligne 
  */
  public function mettreajour($tableau,$cleligne,$injection=array())
  {
    $x = $sortie = 0;  $id = NULL; 
    $inLiaison = $premTri = array();     
    //Pour toutes les donnees recues, je les range [par table] et  [par nom=valeur]
    $premTri = $this->dataClasse($tableau);
    if (! empty($injection)){
      $this->classement = $this->Injecter($premTri,$injection);
    }
    else { $this->classement = $premTri; }
   /* 
    echo '<pre>'; print_r($this->classement); echo '<pre>';
    exit();
   */
    //  Parcourir les tables concernees:
    $tableUp = array_keys($this->classement); 
    foreach ($tableUp as $table){   
      //1. Je m'occupe de la table de liaison qui gere le multi2multi                           
      if ($table == $this->liaisonTable){                                                    
        if (!$ok = $this->supprimePlusLignes($cleligne,$this->liaisonTable)){                 
          $msg = 'Impossible de nettoyer'.$this->liaisonTable;          
          throw new MyPhpException($msg);
        }
        foreach ($this->classement[$table] as $nbr=>$ligne){                                  
          if ($ok = $this->insertion($table,$ligne,$cleligne)){ $sortie++ ; }
        }  
      }
      else {
        if ($ok = $this->update($table,$this->classement[$table],$cleligne)){ $sortie++ ; }
        else { throw new MyPhpException("erreur mettreajour() dans table: $table");}
      }
    }
    return $sortie;
  }
   /**
  * Fonction multi table qui selectionne en DATABASE  et retourne l'ensemble des donnees li�es � la PPK d'un contexte.
  * Pour toute table 'dynamique', les donnees sont : $tableau[$nomTable][$attribut]=$valeur
  * Pour les tables de liaisons, les donnees sont : $tableau[$idx][$attribut]=$valeur
  * $idx est de type INT, $nomTable est de type ASSOC ...c'est permis
  */    
  public function selection($clePK)
  {
    $retour = array(); $tableauliaison = array(); $x = 0;
    if (empty($clePK)) {
      throw new MyPhpException("Cl� de selection invalide");
    } 
    $this->valPPK = intval($clePK); //valPPK est la valeur de la PK de la premi�re table du contexte
    foreach ($this->dynTables as $nomtable){     
     $retour[$nomtable] = $this->preparation($this->valPPK,$nomtable);
    }
    if (!empty($this->liaisonTable)){ //traitement des donnes de liaison lignes par lignes     
      $tableauliaison = $this->preparation($this->valPPK,$this->liaisonTable);   
      foreach ($tableauliaison as $ligne){
        $retour[$x] = $ligne;$x++;
      }
    }
    //petits ajouts pratiques...
    $retour['inliaison'] = $x;
    $retour['valppk'] = $this->valPPK;
    return $retour;  	       
  }
  /**
  * Retourne une ligne de table car on selectionne sur une PK
  * $valcle: string, la valeur de la PK de $table
  * $table: string, la table sur laquelle travailler 
  * $colonnes: array(), facultatif mais permet de moduler les colonnes selectionn�es 
  */
  public function selectUneLigne($valcle,$table,$colonnes=array())  
  {
    $result = array();  
    $this->table = $table;
    $valcle = intval($valcle);
    $this->schema = $this->schemaTable($this->table);
    //Recherche du nom des cles FK ou PK qui repr�sentent la ligne � preparer      
    foreach ($this->schema as $champ=>$option){
      if ($option['clePrimaire']) { $this->nomClePK = $champ;}    
    }
    if (empty($this->nomClePK)){ trigger_error("La table: ".$this->table." n'a pas de cl� primaire?",E_USER_ERROR);}
    if (empty($colonnes)) {    
      $sql = "SELECT * FROM $this->table WHERE $this->nomClePK = :pk";
    }
    else {
      $idx = 0;
      $nbr = count($colonnes);
      $wagonSql = ' '; 
      foreach ($colonnes as $name) {     
        ($idx < ($nbr-1))?  $Sep = ", ": $Sep = " "; 
        $wagonSql .= $name.$Sep;
        $idx++;
      }
      $sql = "SELECT $wagonSql FROM $this->table WHERE $this->nomClePK = :pk";      
    }  
    $stmt = $this->bd->prepare($sql);
    $stmt->execute(array(':pk'=>$valcle));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = NULL;  
    //$result = $this->magicNormHTTP($result);  
    return  $result;    
  }
  /**
  * Methode de selection de ligne par valeurs, 
  * employ�e par (Chat,Help,Membre,News)Ctrl.php pour selectionner une seule ligne
  * employ�e par Iter.php pour selection multiple ligne 
  * @param $colonnes, array() : un tableau des colonnes � selectionner
  * @param $tables, str: la table de selection
  * @param $nomval, str, nom de la colonne qui conditionne la s�lection
  * @param $varval, la valeur de la colonne de condition type variable
  * @param $all, bool, drapeau pour indiquer la methode de fetch
  */
  public function ligneParValeurs($colonnes,$table,$nomval,$varval,$all=NULL,$condi=NULL)
  {
    $idx = 0;    
    $nbr = count($colonnes);
    $wagonSql = ' ';    
    foreach ($colonnes as $name) {
      ($idx < ($nbr-1))?  $Sep = ", ": $Sep = " "; 
      $wagonSql .= $name.$Sep;
      $idx++;
    }    
    if (empty($condi)) { $sql = "SELECT $wagonSql FROM $table WHERE $nomval = :varval "; }
    else { $sql = "SELECT $wagonSql FROM $table WHERE $nomval = :varval $condi"; }
    $stmt = $this->bd->prepare($sql);
    $stmt->execute(array(":varval"=>$varval));
    if (empty($all)) { $result = $stmt->fetch(PDO::FETCH_ASSOC); }
    else { $result = $stmt->fetchAll(PDO::FETCH_ASSOC); }
    $stmt = NULL; 
    //$result = $this->magicNormHTTP($result);     
    return  $result;    
  }

   /**
  * Retourne une ou plus ligne(s) de table car on selectionne sur une FK mais dans le cadre du contexte
  * $valcle est ici une FK
  */
  public function selectPlusLignes($valcle,$table,$cle=NULL)
  {
    $result = array(); $sortie = array();$x = 0;
    $this->table = $table;
    $valcle = intval($valcle);
    $this->schema = $this->schemaTable($this->table);
    //Recherche du nom des cles FK ou PK qui repr�sentent la ligne � preparer      
    // 1 si une 'vrai' cl� �trang�re est fournie    
    if (!empty($cle)){
      foreach ($this->schema as $champ=>$option){
        if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $cle)){
          $this->nomCleFK = $champ; 
        } 
      }
    }
    else {  // 2 sinon on se rabat sur une cle etrang�re qui pointe vers la PPK
      foreach ($this->schema as $champ=>$option){
        if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $this->PPK)){
          $this->nomCleFK = $champ; 
        }
      }
    }
    if (empty($this->nomCleFK)){  throw new MyPhpException ("La table: ".$this->table." n'a pas de cl� secondaire ");}
    $sql = "SELECT * FROM $this->table WHERE $this->nomCleFK = :fk " ; 
    $stmt = $this->bd->prepare($sql);
    $stmt->execute(array(':fk'=>$valcle));
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){    
      $sortie[$x] = $result;
      $x++;      
    }
    $stmt = NULL;
    return $sortie;
  }
  /**
  * Supprime les donnees li�s � un identifiant de contexte
  * Suppose que $this->dataContexte est defini (par appel � $this->chargerContexte($contexte,$statut))
  */
  public function supprime($valcle)
  {
    $testdel = 1;      
    foreach ($this->dynTables as $nom){
      if (! $ok = $this->supprimeUneLigne($valcle,$nom)){ 
        $testdel = 0;          
        throw new MyPhpException('Erreur de suppression de table, methode supprime()');
      }
    }
    if (!empty($this->liaisonTable)){
      if (! $ok = $this->supprimePlusLignes($valcle,$this->liaisonTable)){
        $testdel = 0;  
        throw new MyPhpException('Erreur de suppression de table de liaison, methode supprime()');
      }
    }
    return $testdel;
  }       
   /**
  * Supprime une ligne de table car on supprime sur une PK($valcle)
  */
  public function supprimeUneLigne($valcle,$table)
  {
    $result = array();  
    $this->table = $table;
    $valcle = intval($valcle);
    $this->schema = $this->schemaTable($this->table);
    //Recherche du nom des cles FK ou PK qui repr�sentent la ligne � preparer
    foreach ($this->schema as $champ=>$option){
      if ($option['clePrimaire']) { $this->nomClePK = $champ;$testPK = 1;}       
    }
    if (empty($testPK)){ throw new MyPhpException ("La table: ".$this->table." n'a pas de cl� primaire?");}
    $sql = "DELETE FROM $this->table WHERE $this->nomClePK = :pk";
    $stmt = $this->bd->prepare($sql);
    if ($stmt->execute(array(':pk'=>$valcle))){    
      $stmt = NULL; 
      return true;
    }
  }
  /**
  * $valcle est ici une FK 
  */
  public function supprimePlusLignes($valcle,$table,$cle=NULL)
  {
    $result = array();  
    $this->table = $table;
    $valcle = intval($valcle);
    $this->schema = $this->schemaTable($this->table);
    //Recherche du nom des cles FK ou PK qui repr�sentent la ligne � preparer      
    if (!empty($cle)){
      foreach ($this->schema as $champ=>$option){
        if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $cle)){
        $this->nomCleFK = $champ; 
        }
      }
    }    
    else {    
      foreach ($this->schema as $champ=>$option){
        if (($option["cleSecondaire"]) && ($option["cleSecondaireRelie"] == $this->PPK)){
          $this->nomCleFK = $champ; 
        }
      }
    }    
    if (empty($this->nomCleFK)){ throw new MyPhpException ("La table: ".$this->table." n'a pas de cl� secondaire?");}
    $sql = "DELETE FROM $this->table WHERE $this->nomCleFK = :fk";
    $stmt = $this->bd->prepare($sql);    
    if ($stmt->execute(array(':fk'=>$valcle))){
      $stmt = NULL; 
      $ok = true;
    }
    else {$ok = false;}
    return $ok;
  }
        
}

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
*  Class IniData,  
*  1. charge un fichier contexte.ini pre-existant decrivant 
*  - avec quelles tables MySQL le contexte interragit
*  - quelles sont les donnees obligatoires et facultatives de ce contexte par statut 
*  2. fournit un modele des donnees de ce contexte depuis les proprietes des attributs des tables liés au contexte
*  3. crée un cache des tables statiquesToliaison et des tables passives demandées  
*  4 recoupe les donnes traitees pour un classement par table et/ou par contexte  
*  @package MODELE
*  @copyright Marc Van Craesbeeck : marcvancraesbeck@scarlet.be
*  @licence GPL
*  @version 1.0.0
*/

include_once('UniConnect.class.php');

class IniData extends UniConnect
{
  /**
  * Les attributs de la classe:   
  */
  protected $arrayStatuts = array();      // tableau des statuts
  protected $arrayContextes = array();    // tableau des contextes
  protected $bd;                          // ressource Mysql
  protected $clesDataContexte = array();  // tableau des clés SQL (FK, PK) du contexte
  protected $contexte;                    // string le nom d'un contexte précis
  protected $dataContexte = array();      // tableau des contextes 
  protected $dataOblig = array();         // tableau des colonnes obligtoires
  protected $dataFacul = array();         // tableau des colonnes facultatives                 
  protected $dataVue = array();           // tableau des colonnes pour une VUE           
  protected $dynTables = array();         // tableau des tables sql dynamiques selon contexte.ini
  protected $dynPK = array();             // tableau des clés Primaires (PK)
  protected $dynFK = '';                  // nom d'une clé étrangère (FK)
  protected $dynFKP='';                   // nom d'une colonne référence pour FK
  protected $fileTable = '';              // nom d'une table sql de contexte.ini 
  protected $extraTable = array();        // tableau des tables 'extra' dans contexte.ini
  protected $extraFK = array();           // tableau des clés FK 'extra'
  protected $extraFKP = array();          // tableau des colonnes de références liés à extraFK 
  protected $fileStatiqueTable = '';      // nom du fichier cache table.ini lié à une table sql 'statique'
  protected $fileStatiqueData = '';       // nom du fichier cache 'table_stat.ini' avec les données 'statiques' de table
  protected $filePassiveTable = '';       // nom du fichier cache table.ini lié à une table sql 'passive'
  protected $filePassiveData = '';        // nom du fichier cache 'table_pass.ini' avec les données 'passives'  de table
  protected $fileContexte = '';           // nom du fichier de configuration generale contexte.ini 
  protected $fileDataContexte = '';       // nom du fichier cache table_data.ini avec les données calculées liées à un contexte 
  protected $fileLiaisonTable = '';       // nom du fichier cache  'table.ini'  lié à une table sql 'de liaison' 
  protected $haCont = '';                 // nom de l'antenne 'hors_antenne' 
  protected $haReg = '';                  // nom de la region 'hors-region' 
  protected $liaisonTable = '';           // nom d'une table sql de liaison
  protected $liaisonFK = array();         // tableau des clés FK (clés de liaison) 
  protected $liaisonFKP = array();        // tableau des colonnes vers lesquelles pointent les clés liaisonFK 
  protected $liaisonChamps = array();     // tableau des colonnes supplémentaires sur table de liaison
  protected $liaisonValeurs = '';         // valeur des colonnes supplémentaires sur table de liaison 
  protected $liensCles = array();         // tableau qui contiend toutes les Foreign Key(FK)
  protected $masques = array();           // tableau de gestion des masques regex
  protected $mailCont = '';               // mail general de contact                   
  protected $PPK = '';                    // nom de la cle Primaire Principale du contexte  
  protected $passiveTable='';             // nom d'une table 'passive'
  protected $passiveChamps = array();     // tableau de colonne liées à une table 'passive'
  protected $passivePK='';                // nom de la cle PK de table 'passive'
  protected $schema = array();            // tableau rassemblant le schema sql d'une table 
  protected $schemaLiaison= array();      // tableau rassemblant le schema sql d'une table 'de liaison'
  protected $schemaPassive=array();       // tableau rassemblant le schema sql d'une table 'passive'
  protected $schemaStatique=array();      // tableau rassemblant le schema sql d'une table 'statique' 
  protected $schemaDataStatique = array();// tableau de sortie de methode calculDataStatique($table,$tableau,$fichier)   
  protected $schemaDataPassive=array();   // tableau de sortie de methode calculDataPassive($table,$fichier) 
  protected $statut;                      // statut de fonctionnement du système 
  protected $statiqueTable = '';          // nom d'une table déclaré 'statique'       
  protected $statiqueChamps = '';         // nom d'une colonne sql 'statique' 
  protected $statiquePK = '';             // string le nom de la cle PK d'une table sql statique                   
  protected $statiqueValeurs = array();   // tableau de valeurs que peuvent prendre un champ sql 'statique' 
  protected $table = '' ;                 // nom d'une table sql
   
  /**
  *  Constructeur de la classe:
  *  initialise une instance PDO () meme sans contexte ni statut fourni.
  *  @param : $contexte, string, le nom du contexte
  *  @param : $statut, string nom du statut
  *  Ces deux parametres sont facultatifs maintenant et pourront etre surcharges dans les classes filles.
  */  
  function  __construct($contexte=NULL,$statut=NULL)
  {	  
    $this->contexte = $contexte;
    $this->statut = $statut; 
      
    //Les 4 statuts du système
    $this->arrayStatuts = array('anonym','membre','responsable','admin');
    
    //Le nom de tous les contextes 
    $this->arrayContextes = array('Membre','Antenne','Region','Chat','Sponsor','News','File','Auth','Index','Help','Test','Passage','Trajet','Install');     
     
    //Appel statique de la class UniConnect qui est un singleton 
    if (!file_exists(DIRLIB.'dsn.php')) { $this->bd = NULL; }
    else { $this->bd = UniConnect::getInstance(); }
     
    if ($contexte !== NULL && $statut !== NULL) {
      $this->dataContexte = $this->chargerContexte($this->contexte,$this->statut); 
    }
  }
   /********************************************************************
  * Methodes private : reservées à cette class,appelées par cette class
  *
  * function chargermasques()
  *  Chargement d'un tableau de masques regex pour chaque table MySQL
  *  Les cles des array sont les attributs (colonnes) de la table MySQL
  *  @param: $table, string, le nom de la table MySQL a charger
  */
  private function chargerMasques($table)
  {    
    require('libRgx.php');
    $colo = $regex = $masques = $tabSql = array();
    $fileSqlIni = DIRCONTEXTE."Sql.ini";
    $tabSql = parse_ini_file($fileSqlIni,TRUE);
    switch($table) {
      case T_HUM: $sqlIni='sqlHum'; break;
      case T_CORE:; $sqlIni='sqlCore'; break;
      case T_SOC : $sqlIni='sqlTranspu'; break;
      case T_HUM_SOC: $sqlIni='sqlMobi'; break;
      case T_ANT: $sqlIni='sqlAnt'; break;
      case T_CHAT:$sqlIni='sqlChat';break;
      case T_NEWS:$sqlIni='sqlNews';break;
      case T_REG:$sqlIni='sqlRegions';break;
      case T_LOAD:$sqlIni='sqlFile';break;
      case T_MEET:$sqlIni='sqlPassage';break;
      case T_ROUTE:$sqlIni='sqlTrajet';break;
      default: throw new MyPhpException('chargermasques : table : '.$table.' inexistante');
    } 
    $colo = explode(",",$tabSql[$sqlIni]['colonnes']);
    $regex = explode(",",$tabSql[$sqlIni]['rgx']);
    foreach($colo as $cpt=>$nomCol) {
      $masques[$nomCol] = ${$regex[$cpt]};
    }
    return $masques; 
  }   
  /**
  * showColumns() 
  * @param : $nom_table, string, le nom d'une table a charger
  * retourne un fichier .ini par table basé sur la requete:"SHOW COLUMNS FROM $nom_table"
  * detection des FK grace à $this->liensCles 
  * chaque attribut(colonne) de la table est un array avec (son type, sa clePrimaire(PK), sa longueur, sa valeur default)
  * rajoute en sus : masque=1, oblig=0 et possible=0 pour attribution ultérieures
  */  
  private function showColumns($nom_table)
  {  
    $ini = $tab = $inischema = array();   
    $x = $y = $off = 0;
    $on = -1;
    $this->table = $nom_table;  
    
    //calcul des données (mono-tables) 
    $sqlmeta = "SHOW COLUMNS FROM ".$this->table;
    $stmt = $this->bd->query($sqlmeta); 
    while ($tab = $stmt->fetch())
    {
        $drapFK = $drapExtra = 0;
        //attribution de FIELD (nom colonne)            
        $ini[$x] = "[".$tab[0]."]"; $x++;

        //attribution de la longueur colonne, extraite de Type (pour integer, char et varchar)
        if(preg_match('#(?<=[rt]\()([0-9]+)(?=\))#',$tab[1],$matches)) { $long = $matches[0];}
        elseif($tab[1] == 'date') { $long = 12; }
        elseif($tab[1] == 'timestamp') { $long = 20; }
        elseif($tab[1] == 'text') { $long = 65000; } 
        else { $long = 'non'; }
        $ini[$x] = "longueur=".$long; $x++; 
            
        //attribution du type (sans sa longueur)
        $type = preg_replace('#(?<=[rt])\([0-9]+\)#',' ',$tab[1]);
        $ini[$x] = "type=".$type; $x++;
            
        //attribution de clePrimaire et reconnaissance de la Principal Primary Key (PPK) 
        if ($tab[3] == 'PRI') { $PK  = 1; }
        else { $PK  = 0; }
        $ini[$x]='clePrimaire='.$PK; $x++;

        //detection et structuration si column est une cleSecondaires (FK),  
        if (!empty($this->liensCles)){
          foreach ($this->liensCles as $nomcle=>$ptcle){
            if ($nomcle == $tab[0]){
              $fkdyn = $ptcle;
              $drapFK = 1;
            }
          }  
        }
        $ini[$x]='cleSecondaire='.$drapFK; $x++;                 
        if ($drapFK) { $ini[$x] = 'cleSecondaireRelie='.$fkdyn; $x++; } 
      
        //attribution de Default: 
        if (($tab[4] === '') || ($tab[4] === NULL)) {   $def = ' ';}
        else {  $def = $tab[4]; }
        $ini[$x]='default='.$def;$x++;
         
        //preparation de oblig
        $ini[$x]='oblig=0';$x++;
        $ini[$x]='possible=0';$x++;
  
        //preparer le champ masque : une regex lié à la colonne:
        $ini[$x]='masque=1'; $x++;
    }
    //ecriture, finalisation, nettoyage  
    $iniSchema =  $this->ecriture($this->fileTable,$ini);
    $stmt = NULL;$tab = array();$ini = array();
    return $iniSchema;
  }
  /**
  * calculDataStatique : methode qui cree et charge en memoire un fichier ini 
  * @param : $table: string: nom d'une table
  * @param : $tableau : array() le resulat de schemaTable() pour la table 'statique' 
  * @param : $fichier : string l'adresse ($path) du fichier a créer
  * se base sur une requete en table statique qui selectionne les valeurs des champs statiques
  * pour chaque ligne de champ statique, fourni les valeurs en rapport pour les donnees de liaison
  */
  private function calculDataStatique($table,$tableau,$fichier)
  {  
    $ini = $inischema = $sortie = array(); 
    $x = 0; 
    $colonnes = $this->statiqueChamps.','.$this->statiquePK;
    $titre = $this->statiqueChamps; 
    $cleLigne = $this->statiquePK;
    
    //relation(facultative) entre des valeurs de 'liaison' et des valeurs statiques   
    if (!empty($this->liaisonValeurs)){ $name = implode(",",$this->statiqueValeurs);}
    else { $name = NULL;}  
    if(!empty($this->liaisonChamps)){ $name2tab = implode(",",$this->liaisonChamps);} 
    else {  $name2tab = NULL;}      
    
    // requete sql et calculs 
    // boucle while : toutes les lignes de la table statique 
    $sql = "SELECT  $colonnes  FROM  $table ";
    $stmt = $this->bd->query($sql);
    while ($rslt = $stmt->fetch(PDO::FETCH_OBJ)){ 
      //la valeur du titre est la valeur du nom de la colonne statique
      $valTitre = $rslt->$titre;      

      //detection apostrophe
      if ($posApos = strpos($valTitre,"&")){
          $valTitre = htmlspecialchars_decode($valTitre,ENT_QUOTES);          
          $valTitre = str_replace("'",'"\'"',$valTitre);
      }  
   
      //creation du fichier ini: les donnees qui relient la valeur statique aux valeurs de liaison  
      //ligne 269: le chiffre magique 1 defini la cle FK qui pointe vers la PK statique 
      $ini[$x] = "[".$valTitre."]" ; $x++;       
      $ini[$x] = "data=statique";$x++;
      $ini[$x] = "tableLiaison=".$this->liaisonTable;$x++;
      $ini[$x] = "cleLiaison=".$this->liaisonFK[1];$x++;    
      $ini[$x] = "valCleLiaison=".$rslt->$cleLigne;$x++;
      if (!empty($name)){
        $ini[$x] = "valeurs=".$name;$x++;
        $ini[$x] = "champsLienValeur=".$name2tab;$x++;
      }         
      $ini[$x] = "longueur=".$tableau[$titre]["longueur"]; $x++;
      $ini[$x] = "type=".$tableau[$titre]["type"]; $x++;
      $ini[$x] = 'clePrimaire=0'; $x++;   
      $ini[$x] = 'default='.$tableau[$titre]["default"];$x++;
      $ini[$x] = 'oblig=0';$x++;
      $ini[$x] = 'possible=1';$x++; //les champs extras sont facultatifs 
      $ini[$x] = 'masque=statique'; $x++;
    }
    $sortie = $this->ecriture($fichier,$ini);
    $stmt = NULL ; $ini = array();    
    return $sortie;
  } 
  /**
  * methode privée calculDataPassive: 
  * creation du fichier ini pour les 'champs Passifs'  selon un nom de table et un nom de fichier
  * @param $table: string, nom d'une table 'passive'
  * @param $fichier: string, nom complet du fichier ini 
  * @return array() le fichier ini crée et parsé en memoire
  */
  private function calculDataPassive($table,$fichier)    //($table,$tableau,$fichier)
  {  
    $ligne = $column = $ini = $inischema = $sortie = array();
    $x = $y = 0; 
    $passiveline = '';
    $colonnes = implode(',',$this->passiveChamps); 
    $compt = count($this->passiveChamps);

    // requete sql et calculs:
    // boucle while : selection ligne par ligne de la table 
    // boucle for: repère le nom et la valeur des champs passifs pour chaques lignes de la table
    $sql = "SELECT DISTINCT $colonnes FROM $table ";
    $stmt = $this->bd->query($sql);
    while ($rslt = $stmt->fetch(PDO::FETCH_OBJ)){   
      for ($y = 0; $y < $compt;$y++){               
        $nomColumn = $this->passiveChamps[$y];
        $column[$y]=$rslt->$nomColumn;
        if ($posApos = strpos($column[$y],"&")){    //repère les apostrophes entitisées
          $column[$y] = htmlspecialchars_decode($column[$y],ENT_QUOTES);          
          $column[$y] = str_replace("'",'"\'"',$column[$y]);
        } 
      }
      $ligne[]=$column;
    }
    
    //cree l'ensemble des lignes [Passive] du futur fichier ini
    $ini[$x] = "[Passive]" ; $x++ ; 
    foreach ($ligne as $num=>$valigne){
      $x = $x + $num;           
      for($y = 0; $y < $compt;$y++){
        if ($y == $compt-1){  $passiveline .= $valigne[$y];}
        else { $passiveline .= $valigne[$y].',';}
      }
      $ini[$x] = $num."=".$passiveline;
      $passiveline = '';
    } 

    $sortie = $this->ecriture($fichier,$ini);
    $stmt = NULL ; $ini = array();
    return $sortie;
  }   
 
   /**
  * methode qui concentre les opérations d'ecritures dans $fichier
  * @param: $fichier: string; le nom absolu d'un fichier ini
  * @param: $tableau: array(), le tableau des lignes à ecrires dans $fichier
  * @return: un array() rempli par parse_ini_file("$fichier",true)
  */ 
  private function ecriture($fichier,$tableau)
  {
    $iniSchema = array();
    if (! is_array($tableau)) { throw new MyPhpException('Inidata->ecriture : tableau fourni invalide'); }  
    $lockFile = dirname($fichier).DIRECTORY_SEPARATOR."lock.txt";
    if (! file_exists($lockFile)) { touch($lockFile); }
    $lockCtrl = fopen($lockFile,'r');  
    flock($lockCtrl,LOCK_EX);
    $fp = fopen("$fichier","a");      
    foreach ($tableau as $cle=>$val){ 
      fwrite($fp,$val);
      $car = "\n";
      fwrite($fp,$car);
    }
    fclose($fp);
    flock($lockCtrl,LOCK_UN);
    fclose($lockCtrl);
    //conclusions
    $iniSchema =  parse_ini_file("$fichier",TRUE);
    return $iniSchema;
  } 

  /********************************************************************
  * Methodes protected sont appelables par les methodes filles
  */
  /**
  *  Chargement ou calcul du fichier $this->fileContexte
  *  Crée un fichier .ini pour tout le contexte (multi table)
  *  @param : $contexte, string, nom du contexte
  *  @param : $statut, string, nom du statut
  *  Ces deux parametres sont ici obligatoires
  * La constante DIRCONTEXTE est le path du repertoire des fichiers de  contexte
  */
  protected function chargerContexte($contexte,$statut)
  {
    //variables internes à la fonction    
    $x = 0;
    $fpc = $regex = $regstat = '';
    $iniCont = $tables = $tabContexte = $iniMasques = $cles = array(); 
    
    //Le statut et le contexte ne s'improvise pas
    if (! in_array($statut, $this->arrayStatuts)) {  throw new MyPhpException ("Statut invalide !");}
    elseif (! in_array($contexte,$this->arrayContextes)) {  throw new MyPhpException ("Contexte invalide !");}
    
    //Initialisations de variables de la classe, parser le Contexte.ini
    $this->statut = $statut;    
    $this->contexte = $contexte;
    $this->fileContexte = DIRCONTEXTE.$this->contexte.'.ini'; 
    if (! $tabContexte = parse_ini_file("$this->fileContexte",TRUE))
    { throw new MyPhpException ("Le fichier de contexte est absent");} 
    $this->fileDataContexte = DIRDATA.$this->contexte."_data.ini"; //à ce stade, ce n'est qu'une chaine
    $this->mailCont = $tabContexte["general"]["mail"];
    if (!empty($tabContexte["general"]["horsAntenne"])) { $this->haCont = $tabContexte["general"]["horsAntenne"]; }
    if (!empty($tabContexte["general"]["horsRegion"])) { $this->haReg = $tabContexte["general"]["horsRegion"];  }
    
    //initialisation du contexte dynamique, le contexte des tables principales en ecriture:    
    $this->dynTables = explode(",",$tabContexte["dynamique"]["tables"]);
    $this->dynPK = explode(",",$tabContexte["dynamique"]["PK"]); 
    $this->PPK = $this->dynPK[0]; //LA CLE PRIMAIRE PRINCIPALE DU CONTEXTE
    if (isset($tabContexte["dynamique"]["FK"])){   
      $this->dynFK = $tabContexte["dynamique"]["FK"];
      $this->dynFKP = $tabContexte["dynamique"]["FKP"];
      $this->liensCles[$this->dynFK] = $this->dynFKP;
    }
    //$tabContexte['extra'] decrit les cles FK d'une table :  
    //on remplit $this->liensCles, array("nameCle"=>"ValueCle")   
    if (!empty($tabContexte["extra"])){
      $this->extraTable = explode(",",$tabContexte["extra"]["tables"]);
      $this->extraFK = explode(",",$tabContexte["extra"]["FK"]);
      $this->extraFKP = explode(",",$tabContexte["extra"]["FKP"]);
      foreach ($this->extraFK as $num=>$cle){
        $this->liensCles[$cle] = $this->extraFKP[$num];
      }
    }
    // initialisation du contexte 'liaison' si une liaison sql multi2multi est necessaire, lien au contexte statique
    // creation de $this->schemaLiaison
    if (!empty($tabContexte["liaison"])){
      $this->liaisonTable = $tabContexte["liaison"]["table"];
      $this->liaisonFK = explode(",",$tabContexte["liaison"]["FK"]);
      $this->liaisonFKP = explode(",",$tabContexte["liaison"]["FKP"]);
      if (!empty($tabContexte["liaison"]["champs"])){
        $this->liaisonChamps = explode(",",$tabContexte["liaison"]["champs"]);
        $this->liaisonValeurs = $tabContexte["liaison"]["valeurs"];
      }
      $this->fileLiaisonTable = DIRTABLE.$this->liaisonTable.'.ini';
      foreach ($this->liaisonFK as $num=>$cle){  
        $this->liensCles[$cle] = $this->liaisonFKP[$num];
      }
      if (file_exists($this->fileLiaisonTable)){ $this->schemaLiaison= parse_ini_file("$this->fileLiaisonTable",TRUE); }
      else { $this->schemaLiaison= $this->schemaTable($this->liaisonTable); }
    }    
    /**
    * initialisation des valeurs  'statiques', une valeur 'statique' 
    * est extraite d'une autre table que la table dynamique
    * table dynamique et table statique sont reliées par une table de liaison 
    * relation sql : multi(dynamique) to multi(statique) via table 'liaison' 
    * caculer $this->schemaStatique  : par appel à schemaTable($this->statInTable)  
    * calculer $this->schemaDataStatique  : appel à calculDataStatique($this->statInTable,$this->statInChamps)
    */    
    if (!empty($tabContexte["statique"])){
      $this->statiqueTable = $tabContexte["statique"]["tables"];
      $this->statiquePK = $tabContexte["statique"]["PK"];
      if (!empty($tabContexte["statique"]["champs"])){  $this->statiqueChamps = $tabContexte["statique"]["champs"];}
      if (!empty($tabContexte["statique"]["valeurs"])){ $this->statiqueValeurs = explode(",",$tabContexte["statique"]["valeurs"]);}
      $this->fileStatiqueTable = DIRTABLE.$this->statiqueTable.'.ini';
      $this->fileStatiqueData = DIREXTRA.$this->statiqueTable.'_stat.ini'; 
      if (file_exists($this->fileStatiqueTable)){ $this->schemaStatique = parse_ini_file("$this->fileStatiqueTable",TRUE);  }
      else { $this->schemaStatique = $this->schemaTable($this->statiqueTable);}
      if (file_exists($this->fileStatiqueData)){ $this->schemaDataStatique = parse_ini_file("$this->fileStatiqueData",TRUE); }
      else {  $this->schemaDataStatique = $this->calculDataStatique($this->statiqueTable,$this->schemaStatique,$this->fileStatiqueData); } 
    }  
    
    /** Prendre en compte des donnees passives (la liste passive sera un tableau mis en cache)
    * relation sql: multi(dynamique) to one(passive) 
    * calcul de $this->schemaPassive : par appel de schemaTable($this->passiveTable)
    * calcul de $this->schemaDataPassive : par appel de calculDataPassive($this->passiveTable,$this->schemaPassive,$this->filePassiveData)
    */
    if (!empty($tabContexte["passive"])){
      $this->passiveTable = $tabContexte["passive"]["tables"];
      $this->passiveChamps = explode(",",$tabContexte["passive"]["champs"]); //doit inclure la PK de la table
      $this->passivePK = $tabContexte["passive"]["PK"];
      $this->filePassiveTable = DIRTABLE.$this->passiveTable.'.ini';
      $this->filePassiveData = DIREXTRA.$this->passiveTable.'_pass.ini';
      if (file_exists($this->filePassiveTable)){ $this->schemaPassive = parse_ini_file("$this->filePassiveTable",TRUE); }
      else { $this->schemaPassive = $this->schemaTable($this->passiveTable); }
      if (file_exists($this->filePassiveData)){ $this->schemaDataPassive = parse_ini_file("$this->filePassiveData",TRUE); }
      else { $this->schemaDataPassive = $this->calculDataPassive($this->passiveTable,$this->filePassiveData); }     
    }  
    //creation du tableau des formulaires Obligatoires et Facultatifs selon les statuts et les tables dynamiques
    if ($this->statut == 'anonym'){    
      if (!empty($tabContexte["anonym"]["facul"])){ $this->dataFacul = explode(",",$tabContexte["anonym"]["facul"]); }
      if (!empty($tabContexte["anonym"]["oblig"])){ $this->dataOblig = explode(",",$tabContexte["anonym"]["oblig"]); }       
    }    
    elseif ($this->statut == 'membre'){
      if (!empty($tabContexte["membre"]["facul"])){ $this->dataFacul = explode(",",$tabContexte["membre"]["facul"]); }      
      if (!empty($tabContexte["membre"]["oblig"])){ $this->dataOblig = explode(",",$tabContexte["membre"]["oblig"]); }
      if (!empty($tabContexte["membre"]["vue"])) { $this->dataVue = explode(",",$tabContexte["membre"]["vue"]); }
    }
    elseif ($this->statut == 'responsable'){
      if (!empty($tabContexte["responsable"]["facul"])){ $this->dataFacul = explode(",",$tabContexte["responsable"]["facul"]); }
      if (!empty($tabContexte["responsable"]["oblig"])){ $this->dataOblig = explode(",",$tabContexte["responsable"]["oblig"]); }
      if (!empty($tabContexte["responsable"]["vue"])) { $this->dataVue = explode(",",$tabContexte["responsable"]["vue"]); }
    }  
    elseif ($this->statut == 'admin'){
      if (!empty($tabContexte["admin"]["facul"])){ $this->dataFacul = explode(",",$tabContexte["admin"]["facul"]); }
      if (!empty($tabContexte["admin"]["oblig"])){ $this->dataOblig = explode(",",$tabContexte["admin"]["oblig"]); }
      if (!empty($tabContexte["admin"]["vue"])) { $this->dataVue = explode(",",$tabContexte["admin"]["vue"]); }
    } 
    
    //charge le fichier _data.ini ad hoc  ou calcul du schema de contexte pour chargement à la fin
    if ( file_exists($this->fileDataContexte))      
    {  $this->dataContexte = parse_ini_file("$this->fileDataContexte",TRUE);}
    else { 
      //appel de methode schemaTable pour chaque table dynamique        
      foreach ($this->dynTables as $idx=>$nomTable){
        $this->table = $nomTable;          
        $this->schema = $this->schemaTable($this->table);
        foreach ($this->schema as $nom => $column){
          $iniCont[$x] = "[".$nom."]";$x++;        
          foreach ($column as $cle => $val){  
            $iniCont[$x] = $cle."=".$val;$x++;
          }
        }
      }
      //Ecriture du schema de contexte (multi tables) et chargement du contexte dans $this->dataContexte
      $this->dataContexte = $this->ecriture($this->fileDataContexte,$iniCont);
      //nettoyages
      unset($iniCont);
    }
  
    //Ajout des donnees statiques à $this->dataContexte et completion du tableau $this->dataFacul
    if (!empty($this->schemaDataStatique)){
      foreach ($this->schemaDataStatique as $nomextra=>$valextra){
        $this->dataContexte[$nomextra] = $valextra;  
      }                     
    }
  
    //detection de oblig et facul répartie à toutes les donnees  
    $this->clesDataContexte = array_keys($this->dataContexte);
    foreach($this->clesDataContexte as $x=>$nom){
      foreach($this->dataOblig as $key=>$val){
        if ($val == $nom){  $this->dataContexte[$nom]["oblig"] = 1;}
      }
      foreach ($this->dataFacul as $key=>$val){  
        if ($val == $nom){  $this->dataContexte[$nom]['possible'] = 1;}
      }
    }  
      
    //rajout des masques au schema de contexte (toujours après ecriture du .ini) 
    foreach ($this->dynTables as $nomTable){ 
      //parcour des tables requises et chargement des masques pour chaque table
      //Le nom du masque doit correspondre au nom d'une colonne de la table !!      
      $this->table = $nomTable;
      if (! $this->masques = $this->chargerMasques($this->table))
      { throw new MyPhpException("Impossible de charger le tableau des masques");}
      
      //creation du tableau de tous les masques (multi table)     
      foreach ($this->masques as $nomasque=>$masq)
      {  $iniMasques[$nomasque] = $masq;}
    }
    // parcours du tableau produit par la lecture de $this->fileDataContexte
    // association de l'attribut et de son masque regex
    //traitement du masque pour donnees statiques (selon les mots du contextes statique)
    //le calcul du masque statique se base sur la valeur statique attendue
    foreach ($this->dataContexte as $nom => $options){    
      if ($options["masque"] == 1) { $regex = "'".$iniMasques[$nom]."'"; }     
      elseif ($options['masque'] == 'statique' && !empty($this->statiqueValeurs)){
        $z = (count($this->statiqueValeurs)-1);
        foreach($this->statiqueValeurs as $id=>$mots){  
          if ($id < $z){ $regstat .= $mots."|";}
          else {$regstat .= $mots;}
        }
        $regstat = "(".$regstat.")";
        $regex = "'".$regstat."'";
      }  
      elseif ($options['masque'] == 'statique' && empty($this->statiqueValeurs)){
        if ($this->contexte == 'News') { $mask =  "[^-A-Za-zâàäéêèôöûùîïç\s\'_,.]"; }
        elseif ($this->contexte == 'Trajet') { $mask = '[^-a-zA-Z0-9àâäéèêûôùïîç\s:;,_.\\\']'; }       
        $regex = "'".$mask."'";  
      } 
      else { $regex = 0;}   
      $this->dataContexte[$nom]['masque']= $regex; 
      $regstat = '';
    }
    unset($iniMasques);  

    //rajout des donnees passives si présentes
    if (!empty($this->schemaDataPassive)){
      foreach ($this->schemaDataPassive as $nompassif=>$valpassif){
        $this->dataContexte[$nompassif]=$valpassif;
      }
    }
    $this->dataContexte['general']['mail'] = $this->mailCont;
    $this->dataContexte['general']['horsAntenne'] = $this->haCont;
    $this->dataContexte['general']['horsRegion'] = $this->haReg;
    return $this->dataContexte;
  }
  /**
  * Comment recharger un contexte?
  * On efface les données produites par chargerContexte() et on rappelle chargerContexte()
  */ 
  protected function rechargerContexte($contexte,$statut)
  {
    $tabpath = array(DIRDATA,DIRTABLE,DIREXTRA);
    foreach ($tabpath as $rep){ 
      if (is_dir($rep)){	
			  chdir($rep);
        if ($rep == DIRDATA){
			    $fichiers = glob('./*_data.ini');
        }
        else  { $fichiers = glob('./*.ini');}
			  foreach ($fichiers as $nom){	
          unlink($nom);
        }
			  chdir(BASEDIR);				
		  }
    }
    $this->dataContexte = $this->chargerContexte($contexte,$statut);
    return $this->dataContexte;
  }  
  /**
  *  Méthode schemaTable()
  *  @param : $nom_table; string le nom de la table a charger
  *  la constante DIRTABLE doit exister et indiquer le path des fichiers $nom_table.ini
  *  execute la méthode privee showColumns ou lit le fichier .ini
  *  retourne toujours le tableau des metaData de : $this->table
  */
  protected function schemaTable($nom_table)
  {      
    $regex = '';$tabIniT = array();
    $this->table = $nom_table;
    $this->fileTable =  DIRTABLE.$this->table.'.ini';       
    if (!file_exists($this->fileTable)) {  $tabIniT = $this->showColumns($this->table); }
    else { $tabIniT = parse_ini_file("$this->fileTable",TRUE); } 
    return $tabIniT;
  } 
   
  /********************************************************************
  * Methodes public sont preparent l'encapsulation pour la classe GererData.class.php
  *
  * Les donnees oblig pour une table orientation formulaire client
  * Cette fonction supprime tout echappement automatique des donnees http
  * dans un tableau de dimmension quelconque. Agit ou fait un appel récursif.
  * Sources: http://www.lamsade.dauphine.fr/rigaux/mysqlphp/?page=code
  */
  protected function magicNormHTTP($tableau)
  {
    if (! is_array($tableau)) {  $tableau = stripslashes($tableau); }
    else {	  
      foreach($tableau as $key => $valeur) { 
	  	  if (! is_array($valeur))  { $tableau[$key] = stripslashes($valeur); }
		    else 	{ $tableau[$key] = $this->magicNormHttp($valeur); }
	    }
    }
    return $tableau;
  }

  public function attributsOblig($nom_table)
  {
    $clesTable = $schema = $dataTableOblig = array();    
    $schema = $this->schemaTable($nom_table);
    $clesTable = array_keys($schema);
    if (!empty($this->dataOblig)){
      $dataTableOblig = array_intersect($clesTable,$this->dataOblig);
    }
    return $dataTableOblig;  
  }
  
  /**
  * Les donnes facultatives pour une table selon contexte dynamique, orientation formulaire (client)
  * Si il s'agit d'une table de liaison, mettre les clés du tableau du schema  statique   
  */
   public function attributsFacul($nom_table)
   {
    $clesTable = $schema = $dataTableFacul = array();     
    if ($nom_table == $this->liaisonTable){  
      if (!empty($this->schemaStatique)){
        $dataTableFacul = array_keys($this->schemaDataStatique);
      }    
    }      
    else {    
      $schema = $this->schemaTable($nom_table);
      $clesTable = array_keys($schema);
      $dataTableFacul = array_intersect($clesTable,$this->dataFacul);  
    }    
    return $dataTableFacul;
  }
  /**
  *   l'ensemble des donnees requises et facul pour une table
  *   orientation database, les attributs viennent tous de la base  
  */
  public function attributsTable($nom_table)
  {  
    $attrTableF = $attrTableO = $dataTableContexte = array();  
     
    //Si c'est une table 'de liaison':  inclure les cles FK de liaison dans le tableau oblig
    if ($nom_table == $this->liaisonTable) {  
      $attrTableO = array_merge($this->liaisonFK,$attrTableO);
      if (!empty($this->liaisonChamps)){
        $attrTableF = array_merge($this->liaisonChamps,$attrTableF);
      } 
    }
    else {  
      $attrTableO = $this->attributsOblig($nom_table);
      $attrTableF = $this->attributsFacul($nom_table); 
    } 
    $dataTableContexte = array_merge($attrTableO,$attrTableF);
    return $dataTableContexte;
  } 
  /**
  * charge  les données facultatives et obligatoires avec leurs proprietes completes pour une table d'un contexte
  * pour les données 'statiques', charge le schema des données statiques
  * @param $nom_table, string, la table a charger
  * @param $pk, bool, indique si la cle primaire doit etre rajoutee ou pas
  * retourne un tableau
  */
  public function chargerTable($nom_table,$pk = NULL)
  {
    $schema = $dataTableOblig = $dataTableCharge = $dataTable = $dataTableContexte = array();    
    if ($nom_table == $this->liaisonTable){  $dataTableCharge = $this->schemaDataStatique; }         
    else {          
      $schema = $this->schemaTable($nom_table);      
      $dataTableContexte = $this->attributsTable($nom_table);
      foreach ($dataTableContexte as $attr){
        $dataTableCharge[$attr] = $this->dataContexte[$attr];
      }
      if (!empty($pk)){
        foreach ($schema as $nom=>$option){
          if ($option['clePrimaire']){ 
            $dataTableCharge[$nom] = $option;}
        }
      }
      $dataTableContexte = array();
    }    
    return $dataTableCharge;
  }
  /**
  * retourne en array() l'ensemble du nom des tables d'un contexte ou une valeur nulle
  */
  public function getTables($dynonly=NULL)
  {
    $wagon = array();
    foreach ($this->dynTables as $tabula){
      $wagon[] = $tabula;
    }
    if ((!empty($this->liaisonTable)) && (empty($dynonly))) {
        array_push($wagon,$this->liaisonTable);
    }
    return $wagon;
  }
  /**
  * retourne le nom de la table passive du contexte ou NULL
  */
  public function getPassiveTable()
  {
    $pass = '';    
    if (!empty($this->passiveTable)){ $pass =  $this->passiveTable; }
    return $pass; 
  }
  /**
  * @return array() avec le nom des tables extra
  */
  public function getExtraTable()
  {
    return $this->extraTable();    
  }
  /*
  * retourne le nom de l'attribut PPK (Principal Primary Key) du contexte
  * utilisé par classe filtreData
  */
  public function getPPK()
  {
    return $this->PPK;
  }
/**
* Retourne un array() de tous les attributs attendus d'un contexte:dynamiques et statiques
* Fait la meme chose que attributsTable() mais pour tout un contexte
*/
  public function getListeAttr()
  {
    $liste = array();   
    $liste = array_merge($this->dataOblig,$this->dataFacul);
    if (!empty($this->schemaDataStatique)){
      $champStatiques = array_keys($this->schemaDataStatique);
      foreach ($champStatiques as $champ){
        array_push($liste,$champ);
      }
    }
    return $liste;
  }
  /**
  * Tous les attributs facultatifs du contexte
  */
  public function getFaculFull()
  {
    $liste = $this->dataFacul;
    if (! empty($this->schemaDataStatique)){
      $champStatiques = array_keys($this->schemaDataStatique);
      foreach ($champStatiques as $champ){
        array_push($liste,$champ);
      }
    }
    return $liste;
  }
  /**
  * Tous les champs obligatoires du contexte
  */
  public function getOblig()
  {
    return $this->dataOblig;
  }
  protected function output($data)
  {
    if (is_array($data)) {echo '<pre>';print_r($data);echo '</pre>';}
    else {    echo $data; }
  }
}  
  

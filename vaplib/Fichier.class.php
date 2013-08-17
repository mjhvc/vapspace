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

include_once('UniConnect.class.php');
 
/**
* class Fichier : traitement d'un fichier telecharge 
* necessite une connexion bd
* Methode d'appel : sauvegarder($nom_champ,$description=NULL)
* Logique des methodes: sauvegarder() - verifier() - deplacer() - ecrire() - nommer() - miniaturiser()
* Pas de gestion globalisée d'erreur, chaque methode retourne NULL ou une erreur savament reportée
* Mise en ligne: 9 fevrier 2011
* @copyright Marc Van Craesbeeck marcvancraesbeeck@scarlet.be 
* @licence GPL
* @license GPL
* @package MODELE
* @version 1.0.0
*/
class Fichier extends UniConnect
{
  private   $mime_aut;
  private   $mime_type;  
  private   $mime_aut_app;
  private   $mime_aut_img;
  private   $ext_aut ; 
  private   $ext_aut_app;
  private   $ext_aut_img;
  private   $fichier = array();
  private   $description;
  private   $id;
  public    $erreur;
  private   $width;
  private   $height;
  private   $chemin;  
  private   $miniatures;
  private   $extension;
  private   $imagetype;
  private   $prefixe ;
  private   $taille_max;
  private   $taille;
  private   $novname;
  private   $fbd; 
   
/**  
/*  constructeur de la classe
/*  initialise des attributs basiques
/*  initialise connexion base de donnée
*/   
  public function __construct()
  {
    $this->description = $this->novname = $this->chemin = $this->erreur = ''; 
    $this->mime_aut = array("application/msword","application/pdf","image/gif","image/jpeg","image/png");
    $this->ext_aut = array("doc","pdf","gif","jpeg","jpg","png");
    $this->mime_aut_app = array_slice($this->mime_aut,0,2);
    $this->mime_aut_img = array_slice($this->mime_aut,2);
    $this->ext_aut_app = array_slice($this->ext_aut,0,2);
    $this->ext_aut_img = array_slice($this->ext_aut,2);
    $this->fbd = UniConnect::getInstance();        
    $this->taille_max = intval(2000000);
  }

  public function __destruct()
  {
    unset($this->fbd); 
    unset($this->mime_aut); unset($this->mime_aut_app); unset($this->mime_aut_img);
    unset($this->ext_aut);unset($this->mime_aut_app);unset($this->ext_aut_img);
    unset($this);  
  }

  // Préparation des chaines pour ecritures en database (special hors-modele) 
  private function prepareChaine($chaine)
  { 
	  //Suppression des balises html et php
	  $chaine_protect = strip_tags($chaine);
	  $chaine_protect_deux = htmlspecialchars($chaine_protect,ENT_QUOTES);
    return $chaine_protect_deux;
  }
/**
* getErreurMsg() private retourne un message d'erreur en fonction du code d'erreur.
* @param $code_erreur int code d'erreur du tableau $_FILES
* @return $erreur string texte d'erreur
*/
  private function getErreurMsg($code_erreur)
  {
    switch($code_erreur)
    {
      case UPLOAD_ERR_OK :  $this->erreur = ''; break;
      case UPLOAD_ERR_INI_SIZE : case UPLOAD_ERR_FORM_SIZE : $this->erreur = "La taille du fichier est trop grande<br />\n";break;
      case UPLOAD_ERR_PARTIAL : $this->erreur = "Le serveur n\'a pas reçu la totalité du fichier<br />\n";  break;
      case UPLOAD_ERR_NO_FILE : $this->erreur = "Aucun fichier téléchargé<br />\n"; break;
      case UPLOAD_ERR_NO_TMP_DIR : case UPLOAD_ERR_CANT_WRITE :$this->erreur = "Impossible de stoquer le fichier<br />\n"; break;
      case UPLOAD_ERR_EXTENSION : $this->erreur = "Telechargement interrompu<br />\n";  break;  default : $this->erreur = "Erreur inconnue de telechargement<br />\n"; break;
    }
    return $this->erreur;
  }

/**  
* getExtension() private Vérifie que l'extension du fichier est autorisée et la retourne
* @param $nom_fichier string nom du fichier
* @param $tab_ext array tableau d'extension autorisées
* @return string extension trouvée ou false si extension non autorisée
*/
  private function getExtension($nom_fichier,$tab_ext)
  {   
    //recupération de l'extension du fichier sans le '.'   
    $ext_fichier = strtolower(substr(strrchr($nom_fichier,'.'),1));     
    if (! in_array($ext_fichier,$tab_ext))
    { $ext_fichier = false;}
    return $ext_fichier;
  }

/**
* La dernière fonction appelée si le fichier est une image
* Crée une vignette de l'image avec appel de fonctions de la lib GD
* @param: $mini,l'image miniaturisée  
* @param: $w_mini,$h_mini: int largeur et hauteur de $mini
* @param: $path_mini: le path de la miniature
* @parm: $orig : l'image originale
* @param: $copy: bool 
*/
  private function miniaturiser()
  { 
    //redimensionnement de la future image
    if ($this->width < 40 || $this->height < 60 ) 
    {
      $w_mini = intval($this->width * 1/2); 
      $h_mini = intval($this->height * 1/2);
    }
    elseif ($this->width > 300 || $this->height > 400)
    {
      $w_mini = intval($this->width * 5/100);
      $h_mini = intval($this->height * 5/100);
    }
    else 
    { 
      $w_mini = intval($this->width * 20/100);
      $h_mini = intval($this->height * 20/100);
    }

    //  creation d'une image mini, 
    //  copie de image d'origine dans mini avec échelle
    //  sauvegarde de mini dans répertoires vignettes
    $mini = imagecreatetruecolor($w_mini,$h_mini);
    $path_mini = MINIPATH.$this->novname; //$this->novname vient de nommer()
    if ($this->extension == 'jpg' || $this->extension == 'jpeg')
    {
      $orig = imagecreatefromjpeg($this->chemin);       
      if ($copy = imagecopyresampled($mini,$orig,0,0,0,0,$w_mini,$h_mini,$this->width,$this->height))
      { imagejpeg($mini,$path_mini);}
    }
    elseif($this->extension == 'png')
    {
      $orig = imagecreatefrompng($this->chemin);        
      if ($copy = imagecopyresampled($mini,$orig,0,0,0,0,$w_mini,$h_mini,$this->width,$this->height))
      { imagepng($mini,$path_mini);}
    }
    elseif($this->extension == 'gif') 
    {
      $orig = imagecreatefromgif($this->chemin);        
      if ($copy = imagecopyresampled($mini,$orig,0,0,0,0,$w_mini,$h_mini,$this->width,$this->height))
      { imagegif($mini,$path_mini);}
    }
    imagedestroy($mini);    
  }

/**  
* methode nommer() private 
* Génère un nom unique de fichier, son chemin, enregistre ces paramètres en base 
* @return string chemin d'acces au fichier
*/
  private function nommer()
  {
    try {    
      //creer un nom de fichier unique : prefixe +id+ ext
      $this->novname = $this->prefixe . '_' .$this->id. '.' . $this->extension;
      $this->chemin = FILEPATH. $this->novname;
      //preparation des donnees pour ecriture en bd
      $pk = intval($this->id);    
      $clean_nom = $this->prepareChaine($this->novname);
      $clean_dest = $this->prepareChaine($this->chemin);
      //ajout de la taille du fichier en base
      $clean_taille = intval($this->taille);
      //ecriture en bd
      $sql = "UPDATE ".T_LOAD." SET nomFile = :nom, pathFile = :dest,sizeFile = :taille WHERE idFile = :pk ";
      $stmt = $this->fbd->prepare($sql);
      $stmt->execute(array(':nom'=>$clean_nom,':dest'=>$clean_dest,':taille'=>$clean_taille,':pk'=>$pk));
      return $this->chemin;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }         
  }

/**
* methode ecrire() private 
* insertion  de la description du fichier dans la table de la base,
* @param: $id la clé primaire(PK) récupérée suite à l'insertion 
* return $id int la PK 
*/
  private function ecrire()
  {
    try {    
      if (empty($this->description)) 
      { $clean_desc = "Aucune description";}
      else 
      { $clean_desc = $this->prepareChaine($this->description);}
      $sql = "INSERT INTO ".T_LOAD."(descFile) VALUES (:desc)";
      $stmt = $this->fbd->prepare($sql);
      $stmt->bindValue(":desc",$clean_desc,PDO::PARAM_STR);
      $stmt->execute();
      $id = $this->fbd->lastInsertId();
      return $id;
    }
    catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
    }         
  } 
  
/**
* Lance les méthodes d'écritures en base ecrire() et nommer()
* Deplace le fichier temporaire vers sa destination finale
* Lance la miniaturisation :methode miniaturiser()
* Retourne toute erreur rencontrée
* @param $this->id: la PK de retour de ecrire()
* @param $rep_tmp: path du repertoire temporaire
* @param $place: path de destination finale 
* @return string erreur rencontree
*/
  private function deplacer()
  {
    //verifier qu'il est possible d'ecrire dans le repertoire de destination des fichiers
    if (!is_writable(FILEPATH))
    { $this->erreur .= "Repertoire de sauvegarde inacessible en ecriture<br />\n";}
    else
    { 
      //la phase d'ecriture en base doit se lancer ici    
      $this->id = $this->ecrire();
      
        $rep_tmp = $this->fichier['tmp_name'];        
        $place = $this->nommer();
        if (file_exists($place))
        { $this->erreur .= "Le fichier existe deja<br />\n";}
        else
        { 
          if (!@move_uploaded_file($rep_tmp,$place))
          { $this->erreur .= "Erreur de stockage du fichier sur le disque<br />\n";}
          elseif (in_array($this->extension,$this->ext_aut_img))  
          { $this->miniaturiser();}
        }
    }
    return $this->erreur; 
  }
  
/**
* verifier() private, 
* effectue les vérifications,
* initialise des attributs importants: taille, mime_type, extension
* S'il s'agit d'une image, attributs: width, height
* initialise le prefixe du fichier 
* @param $nom_champ array nom du formulaire
* @return string erreur rencontree ou null
*/  
  private function verifier(array $nom_champ)
  { 
    //quelques calculs: et initialisations importantes.
    $this->fichier = $nom_champ; 
    $this->taille = filesize($this->fichier['tmp_name']); 
    $this->mime_type = $this->fichier['type'];
    $this->extension = $this->getExtension($this->fichier['name'],$this->ext_aut);  
    $this->erreur = $this->getErreurMsg($this->fichier['error']);  
    //Controles:
    if (!empty($this->erreur)) 
    { $this->erreur .=  "Upload de : ".$this->fichier['name']." : Erreur de controle telechargement<br />\n"; }     
    elseif($this->taille > $this->taille_max)     //Controle taille
    {  $this->erreur .= "Taille du fichier trop grande<br />\n";}
    elseif (! in_array($this->mime_type,$this->mime_aut))   //verification de MIME 
    { $this->erreur .= "Champ ".$this->fichier['name']." : type Mime non pris en charge<br />\n";}
    elseif (empty($this->extension))    //verif extension
    { $this->erreur .= "Champ ".$this->fichier['name']." : extension non prise en charge<br />\n";}
    elseif(!@is_uploaded_file($this->fichier['tmp_name']))
    { $this->erreur .= "telechargement incorrect<br />\n";}
    elseif (in_array($this->extension,$this->ext_aut_img)) //c'est sans doute une image, on remplit $img des propriétés width, height    
    {   
        // $type_img est la constante IMAGETYPE_XXX, indiquant le type de l'image
        if (list($this->width,$this->height,$this->imagetype) = @getimagesize($this->fichier['tmp_name']))
        {
          $img_ext = image_type_to_extension($this->imagetype,false);  //false car pas de '.' dans extension
          if (! in_array($img_ext,$this->ext_aut_img))
          { $this->erreur .= "Champ ". $this->fichier['name']." : l'extension ".$img_ext." n'est pas prise en charge<br />\n"; }
          else                                   
          {  //attribution du prefixe 'img' et de l'extension
            $this->prefixe = 'img';
            $this->extension =  strval($img_ext);           
          }
        }
        else                                      
        { $this->erreur .= "Champ ".$this->fichier['name']." : image invalide<br />\n";}
    }
    elseif (in_array($this->extension,$this->ext_aut_app)) //c'est un type/application permis   
    { 
        $this->prefixe = 'fichier';  //attribution du prefixe fichier, l'extension est déjà calculée
        $this->width = 0 ; 
        $this->height = 0 ;
    }   
    return $this->erreur;
  }

/**
*  Methode sauvegarder() public seule methode d'appel   
*  initialise erreur par appel à methode verifier()
*  assigne a $this->erreur le retour de methode deplacer(), ce qui lance tout le mecanisme.
*  @param $cles array() les clés obligatoires du parametre $cnom_champ 
*  @param $nom_champ : array: le nom principal du tableau $_FILES 
*  @param string description du fichier 
*  return $erreur string si il y a erreur
*/
  public function sauvegarder($nom_champ,$description=NULL)
  {
    $cles = array('name','type','size','tmp_name','error'); 
    //lance methode d 'initialisation et de controle,    
    $this->erreur = $this->verifier($nom_champ);
  
    if (empty($nom_champ) || (!is_array($nom_champ)))
    { $this->erreur = "Donnees vides, erreur generale ! <br />\n";}
    elseif (array_diff(array_keys($nom_champ),array_values($cles)))
    { $this->erreur = "tableau fourni invalide<br />\n";}
    elseif (empty($this->erreur))   
    {   // initialise $description
        $this->description = $description;    
        //lance tout le processus de classement-ecriture du fichier
        $this->erreur = $this->deplacer();
         
    }
    return $this->erreur;
  }
   
}
//Fin objet  

?>

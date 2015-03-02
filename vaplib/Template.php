<?php
/**
  @class  Template
  @author  Philippe Rigaux
  @copyright [GNU Public License](@ref licence.dox) 
  @brief Une classe implantant un moteur de templates semblable � celui de la PHPLIB.
*/

class Template
{
  private $classname = "Template";
  private $path = "";           /**< le chemin g�n�ral du dossier des template */
  private $varkeys = array();   /**< le tableau des entit�s qui serviront de pattern regex � substituer */
  private $varvals = array();   /**< le tableau des valeurs des entit�s  */
  private $file = array();      /**< le tableau des chemins de fichiers */
   
  /** Constructeur, charge un repertoire de fichiers template par (@ref setScriptPath($path))
    @param $tmplPath string, chemin d'u repertoire de templates 
  */
  public function __construct($tmplPath = NULL)
  {
    if ($tmplPath) { 
      $this->setScriptPath($tmplPath); 
    }
  }

  /** Attribue � $this->path la valeur du parametre fourni et v�rification d'usage 
    @param  $path string The directory to set as the path.
    @return void
  */
  public function setScriptPath($path)
  {
    $this->path = $path; 
    
    //$this->path doit exister
    if (!is_readable($this->path)) {
      throw new Exception("Invalid path '$this->path' provided");
    }
    return true;
  }

  /** R�cup�re le chemin du r�pertoire de template comme un array
    @return array  
  */
  public function getScriptPaths()
  {
    return array($this->path);
  }

  /** @brief Attribue un FQName au fichier de template $filename 
             Classe le contenu du fichier dans l'entite $handle : (@ref loadfile($handle,$final))
    @param $handle string ( ou array)  : est l'entite d'un fichier template repr�sent� par $filename,
    @param $filename string : nom de fichier du template
    @param $final bool flag pour debug pas utilis� 
    @return void
  */
  public function setFile($handle, $filename = "",$final=null) 
  {
    if (!is_array($handle)) {

      // le fichier .tpl doit exister
      if ($filename == "") {
        throw new Exception ("set_file: For handle $handle filename is empty.");
      }

      //stocker le FQName du template $filename dans $this->file['entite']
      $this->file[$handle] = $this->filename($filename);

      // Charge le fichier comme une entite  
      if (!empty($final)){$this->loadFile($handle,$final);}
      else { $this->loadFile($handle);}
    } 
    else {
      reset($handle);
      while(list($h, $f) = each($handle)) {
        $this->file[$h] = $this->filename($f);
      }
    }
  }

  /** Traitement et classement automatique (en boucle) d'un block html au sein de son entit� parente dans une entit� renouvell�e et � chaque fois substitu�e
    @param $parent string l'entit� parente doit pre exister
    @param $handle string l'entit� de base � traiter 
    @param $name string facultatif, dans ce cas on prend $handle comme entit�  
    @return void  
  */
  public function setBlock($parent, $handle, $name = "") 
  {
    //le chargement de $parent doir reussir    
    if (!$this->loadfile($parent)) {
      throw new Exception ("subst: unable to load $parent.");
      return false;
    }
    if (empty($name)) {
       $name = $handle; 
    }
    else { //cree un nouvelle entit� $name (si $name est vide, $name= $handle) '� vide'
      $this->setVar($name, "");
    } 

    //retrouver la valeur du noeud (entit�) $parent
    $str = $this->getVar($parent); 

    //traitement du block d�fini par <!--BEGIN $handle --> contenu du block \n <!-- END $handle -->
    $reg = "/<!--\s+BEGIN $handle\s+-->(.*)\n\s*<!--\s+END $handle\s+-->/sm";
    
    // le contenu du block (.*) est captur� dans $m[1][0]
    preg_match_all($reg, $str, $m); 

    //Cr�er une nouvelle entit�    
    $newHandle = "{" . $name . "}";

    //reassignation de $str : $reg y est remplac� par $newHandle dans $str  
    $str = preg_replace($reg,$newHandle,$str); 
    
    if(!isset($m[1][0])) { 
      throw new Exception ("Block $handle does not exist");
    }
    
    //classement: cle<-$handle et valeur<-le bloc match� par $reg  
    $this->setVar($handle, $m[1][0]); 

    //classement: cle<-$parent et valeur<-$str 
    // ici $str est class� � sa nouvelle valeur
    $this->setVar($parent, $str); 
  } 

  /** @ brief Surcharge de la m�thode __set(): 
    - met $val dans un tableau index� par $key 
    - place $key formatt� en pattern regex dans un autre tableau, index� par $key
   @param $key string Nom de l'entit�
   @param $val mixed Valeur de l'entit�
   @return void
  */
  public function __set($key, $val) 
  {
    $this->setVar($key, $val);
  }

  /** Obtenir une entit� assign�e par surcharge de metthode __get()
   @param $key string nom de l'entit�
   @return mixed la valeur de cette entit�
  */
  public function __get($key)
  {
    return $this->getVar($key);
  }

  /** Tester l'existence d'une entit�
   @param $key string 
   @return boolean
  */
  public function __isset($key)
  {
    return (NULL !== $this->varvals[$key]);
  }

  /** Effacer une entit� et sa valeur
   @param $key string
   @return void
   */
  public function __unset($key)
  {
    $this->clearVar($key); 
  }

  /** 
    @brief methode de classement centrale des entit�s et de leur valeurs 
    - Attribue � $this->varkeys[$varname] l'entit� $varname transform�e en pattern de regex par (@ref varname($varname))
    - Attribue � $this->varvals[$varname] la valeur de cette entit� : $value
    - Important : $value peut �tre vide
    @param $varname string ou array : nom ou tableau de nom d'entit�
    @param $value string facultatif : valeur d'une entit�
    @return true
  */
  public function setVar ($varname,$value = "")
  {
    if (!is_array($varname)) {
      if (!empty($varname)){
        //l'entit� $varname, prot�g�e par (@ref varname($varname)), est assign�e � une cl� de $this->varkeys[$varname]
        $this->varkeys[$varname] = "/". $this->varname($varname) ."/"; // la valeur du masque 
        
        //la valeur de l'entit� $varname (peut �tre nulle)  est attribu�e � $this->varvals[$varname]
        $this->varvals[$varname] = $value;
      }
      return true;
    } 
    else {
      reset($varname);
      foreach ($varname as $k => $v) {
        if (!empty($k)){
          $this->varkeys[$k] = "/".$this->varname($k)."/";
          $this->varvals[$k] = $v;
        }
      }
      return true;    
    }
    
  }

  /** Obtenir la valeur d'une entit�
   @param $varname: nom de l'entit� � fournir
   @return:   la valeur de l'entit�
  */
  public function getVar ($varname)
  {
    return $this->varvals[$varname];
  }

  /** Effacer toute trace d'une seule entit� dans  $this->varkeys et $this->varvals
   @param $varname nom de l'entit� � effacer
   @return   void
  */
  public function clearVar ($varname)
  {
    $this->varvals[$varname] = null;
    $this->varkeys[$varname] = null;
  }
   
  /** Supprime toutes les entit�s assign�es � $this->varkeys et $this->varvals par reset array()
   @return void
  */
  public function clearVars()
  {
    $this->varvals = array();
    $this->varkeys = array();
  }

  /** Appel public de la methode priv�e (@ref parse($target,$handle,$append))   
    @param $target string une entit�
    @param $handle string entit� � assigner
    @return bool 
  */
  public function assign ($target, $handle=null) 
  {
    if ($handle) { 
      $this->parse ($target, $handle); 
      return true;
    }
    else { return false; }
  }
   
  /**  Appel public de la methode priv�e (@ref parse($target,$handle,$append)) 
   @param $target string ou array, une entit� � cr�er si inexistante 
   @param $handle string l'entit� � traiter et � placer dans $target
   @return void   
  */
  public function append ($target, $handle) 
  {
    //si $target n'a pas d'entit� on cr�e l'entit� $target � vide 
    if (!isSet($this->varvals[$target])) { $this->setVar($target, "");  } 

    // (� mon avis) le true ici ne rajoute rien de pr�cis  
    $this->parse ($target, $handle, true);
  }
  
  /**m�thode finale par traitement complet d'un template, de ses entit�s et retourne le processus achev�
    @param $name string l'entit� de template � traiter
    @return string The output.
  */
  public function render($name)
  {
    $this->parse("my_view", $name);
    $str = $this->getVar("my_view");
    $this->clearVar ("my_view");
    return $str;
  }

  /************** Private part of the class  *****************/

  /** @brief methode de traitement centrale : charge une entit�, effectue la/les substitutions et reclasse le resultat dans une nouvelle entit� 
    appel des methodes (@ref subst($nandle)) et (@ref setVar($target,$handle)
    @param $target string nouvelle entit� � g�n�rer
    @param $handle string entit� � substituer
    @param $append string facultatif � rajouter � $handle
    @return void   
  */
  private function parse($target, $handle, $append = NULL ) 
  {
    //lance les substitutions
    $str = $this->subst($handle); 
  
    if ($append) { //Rajouter le nouveau r�sultat � l'ancien
      $newstr = $this->getVar($target) . $str;
      $this->setVar($target, $newstr);
    } 
    else { // Reclassement de l'entit� avec sa valeurs substitu�e
      $this->setVar($target, $str);
    }
  }

  /** effectue la substitution par appel de preg_replace   
    @param $handle  entite � substituer handle of template where variables are to be substituted.
    @return string avec la substitution des entit�s effectu�s
  */
  private function subst($handle) 
  {    
    // le fichier de template doit �tre charg� 'non substitu�' dans son entit�
    if (!$this->loadfile($handle)) {
      throw new Exception("subst: unable to load $handle.");
    }  
   	// R�cup�rer le contenu de l'entit�  class�e  
    $str = $this->getVar($handle);
    
    // remplace $this->varkeys (recherch�es dans $str) par $this->varvals,  et retourner le resultat
    $str = preg_replace($this->varkeys, $this->varvals, $str);
    return $str;
  }

  /** protection pr�alable d'entit� avant substitution automatique de regex  
    @param $varname string  nom d'une entit� de remplacement � prot�ger
    @return string    
  */
  private function varname($varname) 
  {
    return preg_quote("{".$varname."}");
  }

  /** 
   @brief charge le nom du fichier complet (FQName)
   @param $filename string name to be completed.
   @return string FQName of the file
   */
  private function filename($filename) 
  {
    if (substr($filename, 0, 1) != "/") {
       $filename = $this->path . DIRECTORY_SEPARATOR . $filename;
    }
    if (!file_exists($filename))
    { throw new Exception ("filename: file $filename does not exist.");}
    return $filename;
  }

  /** Classe le fichier .tpl de l'entit� $handle par appel de (@ref setVar($handle,$value)) 
    @param $handle  load file defined by handle, if it is not loaded yet.
    @param $final bool flag pour debug (non utilis�)
    @return boolean
  */
  private function loadfile($handle,$final=null) 
  {    
    //Si l'entit� handle existe, finir ici
    if (isset($this->varkeys[$handle]) and !empty($this->varvals[$handle]))
    { return true;}
    
    //L'entit� doit exister
    if (!isset($this->file[$handle])) {
      throw new Exception ("loadfile: $handle is not a valid handle.");
      return false;
    }

    // retrouver le nom complet du chemin de l'entit� d�j� assign� par (@ref setFile($handle,$filename,$final))
    $filename = $this->file[$handle];

    //lit le contenu de l'entit�  dans une chaine...
    $str = file_get_contents($filename);

    //$str doit exister
    if (empty($str)) {
      throw new Exception ("loadfile: While loading $handle, $filename does not exist or is empty.");
      return false;
    }
    //utile pour d�boguage 
    if (!empty($final)){
      $ksearch = array_keys($this->varkeys);
      preg_match_all('#(?<={)([^\{\}])+(?=})#',$str,$match); //detecte bien les entites  
      echo '<pre>';    
      print_r($this->varvals);  
      echo '</pre>';  
    }
    //lance le classement de l'entit� et de sa valeur
    //$str est une chaine dont la valeur est le contenu du fichier.tpl
    //cr�e un noeud avec cle $handle et valeur $str
    $this->setVar($handle, $str);
                                 
    return true;
  }

  /** M�thode statique pour cr�er un champ select � partir d'une liste PHP
	  @param $nom string nom du formulaire
	  @param $liste array($val=>$libelle)
	  @param $defaut string name d'un formulaire option "selected"
	  @param $required bool si la ligne est requise  
  */
  static function  champSelect ($nom, $liste, $defaut=NULL, $required=NULL)
  {
    $options = "";$id = $nom;
    foreach ($liste as $val => $libelle) {
      if ($val != $defaut) {
        $options .=  "<option value=\"$val\">$libelle</option>\n";
      }
      else {
         $options .= "<option value=\"$val\" selected=\"selected\">$libelle</option>\n";
      }
    }
    return "<select name=\"$nom\" id=\"$id\" class=\"form-control\" $required >\n" . $options . "</select>\n";
  }
}


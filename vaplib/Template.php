<?php
/**
 @class    Template
 @autor  Philippe Rigaux
 @copyright [GNU Public License](@ref licence.dox) 
 @brief Une classe implantant un moteur de templates semblable à celui de la PHPLIB.
 */

class Template
{
  private $classname = "Template";
  private $debug     = false;   /**< if set, echo assignments */
  private $root   = "";         /**< relative filenames are relative to this pathname */
  private $path = "";           /**< Get the files from the following path */
  private $varkeys = array();   /**< $varkeys[key] = "key"; */
  private $varvals = array();   /**<$varvals[key] = "value"; */
  private $file = array();
  private $unknowns = "remove"; /**< "remove"  => remove undefined variables; "comment" => replace undefined variables with comments; "keep"=> keep undefined variables */
  private $halt_on_error  = "yes";/**< "yes" => halt, "report" => report error, continue, "no" => ignore error quietly */
  private $last_error     = ""; /**< last error message is retained here */

  /** Constructeur, charge un repertoire de fichiers template par (@ref setScriptPath($path))
  @param $tmplPath string, chemin d'u repertoire de templates
  @param $extraParams array 
  */
  public function __construct($tmplPath = null, $extraParams = array())
  {
    if (null !== $tmplPath) {
      $this->setScriptPath($tmplPath);
    }
    foreach ($extraParams as $key => $value) {
      $this->set_var($key, $value);
    }
  }

  /** Attribue $this->path au parametre fourni et vérifie qu'il existe bien 
   
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

  /** Récupère le chemin du répertoire de template comme un array
   
  @return array  
  */
  public function getScriptPaths()
  {
    return array($this->path);
  }


  /** Attribue un FQName au fichier de template $filename et charge ce fichier dans son entite : (@ref loadfile($handle,$final))

   @param $handle string ( ou array)  : est l'entite d'un fichier template représenté par $filename,
   @param $filename string : nom de fichier du template
   */
  public function setFile($handle, $filename = "",$final=null) {
    if (!is_array($handle)) {

      // le fichier .tpl doit exister
      if ($filename == "") {
        throw new Exception ("set_file: For handle $handle filename is empty.");
        return false;
      }

      //stocker le FQName du template $filename dont $this->file['entite']
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

  /* public: set_block(string $parent, string $handle, string $name = "")
   * extract the template $handle from $parent,
   * place variable {$name} instead.
   */
  public function setBlock($parent, $handle, $name = "") {
    if (!$this->loadfile($parent)) {
      throw new Exception ("subst: unable to load $parent.");
      return false;
    }
    if ($name == "")
    { $name = $handle;}
    else
    { $this->setVar($name, "");} //cree un nouveau noeud vide

    $str = $this->getVar($parent); //retrouve la valeur du noeud parent
    $reg = "/<!--\s+BEGIN $handle\s+-->(.*)\n\s*<!--\s+END $handle\s+-->/sm";
    preg_match_all($reg, $str, $m); //capture donc (.*) dans $m[1][0]
    $str = preg_replace($reg, "{" . "$name}", $str);  //analyse $reg pour la remplacer par "{" . "$name}" dans $str
    if(!isset($m[1][0]))
    {
      throw new Exception ("Block $handle does not exist");
    }
    //cree un noeud avec comme cle:handle et comme valeur: la capture du bloc matché par $reg  
    $this->setVar($handle, $m[1][0]); 
    $this->setVar($parent, $str); //cree un noeud avec cle:$parent et valeur $str
  }// echo "Replace $parent with <pre>" . htmlentities($str) . "</pre>";

  /**
   * Overloading of the __set magic method: put $val in
   * the array, indexed by $key
   * Assign a variable to the template
   * Surcharge de la méthode magique __set: met $val dans un tableau indexé par $key et assigne une variable à tout le contenu d'une entité

   *
   * @param string $key The variable name.
   * @param mixed $val The variable value.
   * @return void
   */
  public function __set($key, $val) 
  {
    $this->setVar($key, $val);
  }

  /**
   * Retrieve an assigned variable (overload the magic __get method)
   *
   * @param string $key The variable name.
   * @return mixed The variable value.
   */
  public function __get($key)
  {
    return $this->getVar($key);
  }

  /**
   * Allows testing with empty() and isset() to work
   *
   * @param string $key
   * @return boolean
   */
  public function __isset($key)
  {
    return (null !== $this->varvals[$key]);
  }

  /**
   * Allows unset() on object properties to work
   *
   * @param string $key
   * @return void
   */
  public function __unset($key)
  {
    $this->clearVars();
  }

  /**
    
   * Attribue à $this->varkeys[$varname] l'entité $varname transformée en pattern de regex par (@ref varname($varname))
   * attribue à $this->varvals[$varname] la valeur de cette entité : $value
   * important : $value peut être vide
   */
 
  public function setVar ($varname, $value = "")
  {
    if (!is_array($varname)) {
      if (!empty($varname)){
        //attribue l'entité $varname protégée par (@ref varname($varname))  à une clé  nommée $this->varkeys[$varname]
        $this->varkeys[$varname] = "/". $this->varname($varname) ."/"; // la valeur du masque 
        
        //attribue $value (la valeur de l'entité $varname) à $this->varvals[$varname]
        $this->varvals[$varname] = $value;
      }
     
    } else {
      reset($varname);
      foreach ($varname as $k => $v) {
        if (!empty($k)){
          $this->varkeys[$k] = "/".$this->varname($k)."/";
          $this->varvals[$k] = $v;
        }
      }
    }
  }

  /* public: getVar(string $varname, string $value)
   * @varname: name of a variable that is to be retrieved
   * @return:   value of that variable
   */
  public function getVar ($varname)
  {
    return $this->varvals[$varname];
  }

  /* public: clearVar(string $varname, string $value)
   * @varname: name of a variable that is to be undefined
   * @return:   value of that variable
   */
  public function clearVar ($varname)
  {
    $this->varvals[$varname] = null;
    $this->varkeys[$varname] = null;
  }
   
  /**
   * Clear all assigned variables
   *
   * Clears all variables assigned to Zend_View either via {@link assign()} or
   * property overloading ({@link __get()}/{@link __set()}).
   *
   * @return void
   */
  public function clearVars()
  {
    $this->varvals = array();
    $this->varkeys = array();
  }

 
  /** Instantiate a template and put it in an entity
   * whose content is replaced
   *
   */
  public function assign ($target, $handle=null) {
    if ($handle != null) {
      $this->parse ($target, $handle);
    }
    else
    { $this->target = "";}
  }
   
  /** Instantiate a template and put it in an entity
   * whose content is cumulated
   *
   */
  public function append ($target, $handle) {
    if (!isSet($this->varvals[$target])) { //si $target n'a pas de noeud on le crée
      $this->setVar($target, "");

    }
     
    $this->parse ($target, $handle, true);
  }
   
   
  /**
   * Processes a template and returns the output.
   *
   * @param $name string l'entité de template à traiter
   * @return string The output.
   */
  public function render($name)
  {
    $this->parse("my_view", $name);
    $str = $this->getVar("my_view");
    $this->clearVar ("my_view");
    return $str;
  }

  /************** Private part of the class  *****************/

   /* Instantiate a template, and put the result in an entity
   *
   * @target: handle of variable to generate
   * @handle: handle of template to substitute
   * @append: append to target handle
   */

  private function parse($target, $handle, $append = false) {
    $str = $this->subst($handle); //lance les substitutions
  // echo "parse: new str = <pre>" . htmlspecialchars($str) . "</pre>";
    if ($append) {
      // The new value is appended to the old one
      $this->setVar($target, $this->getVar($target) . $str);
    } else {
      // The new value replaces the old one
      $this->setVar($target, $str);
    }
    //return $str;
  }


  /** effectue la substitution par appel de preg_replace  
   @param $handle  entite à substituer handle of template where variables are to be substituted.
   */
  private function subst($handle) {
    // Load the file. If it is already done, the loadfile returns at once
    if (!$this->loadfile($handle)) {
      throw new Exception("subst: unable to load $handle.");
    }
  
   	// Make the substitution
     
    $str = $this->getVar($handle);
    $str = preg_replace($this->varkeys, $this->varvals, $str);
    return $str;
  }

  /** protection préalable d'entité avant substitution automatique de regex  
 
    @param $varname string  nom d'une entité de remplacement à protéger
    @return string    
  */
  private function varname($varname) {
    return preg_quote("{".$varname."}");
  }

  /** 
   @brief charge le nom du fichier complet (FQName)
   @param $filename string name to be completed.
   @return string FQName of the file
   */
  private function filename($filename) {
    if (substr($filename, 0, 1) != "/") {
      $filename = $this->path . DIRECTORY_SEPARATOR . $filename;
    }

    if (!file_exists($filename))
    { throw new Exception ("filename: file $filename does not exist.");}

    return $filename;
  }

  /* private: loadfile(string $handle)
   * @handle:  load file defined by handle, if it is not loaded yet.
   */

 /**
  * avant de lancer setVar ci-dessous, il faudrait vérifier que toutes les entités ont une valeur ($this->varkeys[$varname])
  * sinon les entites non attribuées recoivent '' comme valeur
  * un preg_match_all('#{testavant n'importe quoi testapres}#') devrait recuperer toutes les entites d'un template
  * une boucle sur $this->varkeys testerait les entites non attribuees
  * celles-là recoivent alors '' comme valeur
  */
  private function loadfile($handle,$final=null) {
    //Si l'entité handle existe, on renvoie true
    if (isset($this->varkeys[$handle]) and !empty($this->varvals[$handle]))
    { return true;}

    if (!isset($this->file[$handle])) {
      throw new Exception ("loadfile: $handle is not a valid handle.");
      return false;
    }

    // retrouver le nom complet du chemin de l'entité assigné par (@ref setFile($handle,$filename,$final))
    $filename = $this->file[$handle];

    //lit le contenu de l'entité  dans une chaine...
    $str = file_get_contents($filename);

    //si le contenu est vide: exception
    if (empty($str)) {
      throw new Exception ("loadfile: While loading $handle, $filename does not exist or is empty.");
      return false;
    }
    //utile pour déboguage 
    if (!empty($final)){
      $ksearch = array_keys($this->varkeys);
      preg_match_all('#(?<={)([^\{\}])+(?=})#',$str,$match); //detecte bien les entites  
      echo '<pre>';    
      print_r($this->varvals);  
      echo '</pre>';  
    }

    
    $this->setVar($handle, $str);//$str est une chaine dont la valeur est le contenu du fichier.tpl
                                 //crée un noeud avec cle $handle et valeur $str
    return true;
  }

  /** Méthode statique pour créer un champ <select> à partir d'une liste PHP
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


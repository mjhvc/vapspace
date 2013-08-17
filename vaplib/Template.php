<?php
/**
 * @category webscope
 * @package    Template
 * @copyright Philippe Rigaux
 * @license GPL
 * Sources: http://www.lamsade.dauphine.fr/rigaux/mysqlphp/?page=code 
 */


/**
 * Une classe implantant un moteur de templates semblable
 * à celui de la PHPLIB.
 *
 */

class Template
{
  private $classname = "Template";
  /* if set, echo assignments */
  private $debug     = false;

  /* relative filenames are relative to this pathname */
  private $root   = "";

  // Get the files from the following path
  private $path = "";

  /* $varkeys[key] = "key"; $varvals[key] = "value"; */
  private $varkeys = array();
  private $varvals = array();
  private $file = array();

  /* "remove"  => remove undefined variables
   * "comment" => replace undefined variables with comments
   * "keep"    => keep undefined variables
   */
  private $unknowns = "remove";

  /* "yes" => halt, "report" => report error, continue, "no" => ignore error quietly */
  private $halt_on_error  = "yes";

  /* last error message is retained here */
  private $last_error     = "";

  /**
   * Constructor
   *
   * @param string $tmplPath
   * @param array $extraParams
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

  /**
   * Set the path to the templates
   *
   * @param string $path The directory to set as the path.
   * @return void
   */
  public function setScriptPath($path)
  {
    $this->path = $path;
    if (!is_readable($this->path)) {
      throw new Exception("Invalid path '$this->path' provided");
    }
  }

  /**
   * Retrieve the current template directory as an array
   *
   * @return array(string)
   */
  public function getScriptPaths()
  {
    return array($this->path);
  }


  /* public: set_file(array $filelist)
   * @filelist: array of handle, filename pairs.
   *
   * public: set_file(string $handle, string $filename)
   * @handle: handle for a filename,
   * @filename: name of template file
   */
  public function setFile($handle, $filename = "",$final=null) {
    if (!is_array($handle)) {
      if ($filename == "") {
        throw new Exception ("set_file: For handle $handle filename is empty.");
        return false;
      }
      $this->file[$handle] = $this->filename($filename);
      // Load the file now: this allows to load the file content
      // as en entity. Downside: maybe be not efficient at all
      // is setFile is called for files that are no used at the end ..
      if (!empty($final)){$this->loadFile($handle,$final);}
      else { $this->loadFile($handle);}
    } else {
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
   * Overloading of the __set magic method: put the value in
   * the array, indexed by the key
   * Assign a variable to the template
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
   * Assign variables to the template
   *
   * Allows setting a specific key to the specified value, OR passing an array
   * of key => value pairs to set en masse.
   *
   * @see __set()
   * @param string|array $spec The assignment strategy to use (key or array of key
   * => value pairs)
   * @param mixed $value (Optional) If assigning a named variable, use this
   * as the value.
   * @return void
   * setVar cree un masque regex avec: 
   * $varname comme nom de masque  et la valeur du masque :$val
   * "/". $this->varname($varname) ."/" comme valeur du masque
   * setVar prepare une cle[$varname] dans tableau $this->varvals pour $value
   */
 
  public function setVar ($varname, $value = "")
  {
    if (!is_array($varname)) {
      if (!empty($varname)){
        $this->varkeys[$varname] = "/". $this->varname($varname) ."/"; // la valeur du masque 
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
   * @param string $name The template to process.
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

  /* private: subst(string $handle)
   * @handle: handle of template where variables are to be substituted.
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

  /* private: varname($varname)
   * @varname: name of a replacement variable to be protected.
   */
  private function varname($varname) {
    return preg_quote("{".$varname."}");
  }

  /***************************************************************************/
  /* private: filename($filename)
   * @filename: name to be completed.
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
    $filename = $this->file[$handle];

    //$str = implode("", @file($filename));//transforme ici le contenu d'un .tpl par une chaine...
    $str = file_get_contents($filename);
    if (empty($str)) {
      throw new Exception ("loadfile: While loading $handle, $filename does not exist or is empty.");
      return false;
    }
    if (!empty($final)){
     // echo "parse: new str = <pre>" . htmlspecialchars($str) . "</pre>";
      $ksearch = array_keys($this->varkeys);
      preg_match_all('#(?<={)([^\{\}])+(?=})#',$str,$match); //detecte bien les entites
      //preg_match_all('#(\{[^\{\}]+\})#',$str,$match);   
      echo '<pre>';    
      print_r($this->varvals);  
      echo '</pre>';         
      //foreach ($match[0] as $entity){
      // echo "<strong>$entity</strong><br />";
      //  $clear = '{'.$entity.'}'; 
      //  if(! in_array($entity,$ksearch)){
      //    $str = str_replace($clear,' ',$str);
      //  }
      //}
    }
    $this->setVar($handle, $str);//$str est une chaine dont la valeur est le contenu du fichier.tpl
                                 //crée un noeud avec cle $handle et valeur $str
    return true;
  }

  /** Méthode statique pour créer un champ <select> à partir
   * d'une liste PHP
   */

  static function  champSelect ($nom, $liste, $defaut=NULL)
  {
    $options = "";$id = $nom;
    foreach ($liste as $val => $libelle) {
      // Attention aux problèmes d'affichage
      //$val = htmlSpecialChars($val);
      //$defaut = htmlSpecialChars($defaut);

      if ($val != $defaut) {
        $options .=  "<option value=\"$val\">$libelle</option>\n";
      }
      else {
         $options .= "<option value=\"$val\" selected=\"selected\">$libelle</option>\n";
      }
    }
    return "<select name=\"$nom\" id=\"$id\">\n" . $options . "</select>\n";
  }

}


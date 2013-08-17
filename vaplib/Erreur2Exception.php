<?php
require_once("Connect.php");

/** 
* 1. GESTION DES EXECPTIONS
* Basé sur la formation: http://guillaume-affringue.developpez.com/exceptions-et-PHP5/?page=4#LIV-A
*
* @desc Notre classe d'exception pour les erreurs PHP
*/
class MyPhpException extends Exception
{ 
  private $offMsg;
  private $offBack;
  function __construct($msg,$code=0,$file=NULL,$line=NULL,$context=NULL)
  {
        if (! empty($_SESSION['lang']) && ($_SESSION['lang'] == 'fr')) { 
          $this->offMsg = 'Ceci est une exception interne, désolé !'; 
          $this->offBack = 'Retour au Site';
        }
        elseif (! empty($_SESSION['lang']) && ($_SESSION['lang'] == 'nl')) { 
          $this->offMsg = 'Dit is een innerlijke uitzondering, sorry !'; 
          $this->offBack = 'Terug naar website';
        }
        else { 
          $this->offMsg = 'This is an internal exception, sorry! '; 
          $this->offBack = 'Back To Vap Site';
        }                 
        $this->log = BASELOG;             
        $this->code = $code;
        $this->line = $line;
        $this->file = $file;
        if (!empty($context)){$this->context = $context;}
        else {$this->context=NULL;}        
        parent::__construct($msg,$code);
  }
    /**
    * @desc Affichage de l'erreur
    */
    public function alerte()
    {   
        $format = 'd-m-Y, H:i:s';
        $date = date($format); 
        $off = "<!DOCTYPE html>"
                  .'<html><meta charset="ISO-8859-1">'
                  ."<head><title>Internal Exception</title></head>\n"
                  ."<body>"
                  ."<h3>Oups ! </h3><p>".$this->offMsg."</p>\n"
                  . '<p><a href="http://'.$_SERVER['SERVER_NAME'].'">'.$this->offBack.'</a></p>'
                  .'</body></html>';        
        $on = '[ '.$date.' ]: '.$this->code.' : '.$this->getMessage()." Line : ".$this->line.' File : '.$this->file."\n" ;
       
       if (DISPLAYERRORS == 'off') { $out = $off; }
       else { $out = $on; }
       error_log($on,3,$this->log); 
       //error_log($status,1,MAILWM);  ??
       unset($_SESSION);
       if (session_id()) { @session_destroy(); }
       echo $out;
       exit(255);  
    }
}
/*
*   2. GESTION DES ERREURS PAR DEFAUT
*   Selon la gravité, elles sont transformées en exceptions ou pas
*/
function GestionErreursPerso($niveau, $msg, $file, $line)
{
  try {    
    //Determine si cette erreur est une des erreurs etablies par la configuration
    $error_is_enabled = (bool)($niveau & ini_get('error_reporting') );
    $status = true;
    //Je met le niveau WARNING dans fatal
    $fatal =  array(E_USER_ERROR, E_RECOVERABLE_ERROR, E_WARNING, E_USER_WARNING);

    //Erreur fatale, on renvoi une exception    
    if ( in_array($niveau,$fatal) && $error_is_enabled ) {  throw new MyPhpException($msg,$niveau,$file,$line);}
    elseif( $error_is_enabled ) { // -- NON-FATAL NOTICE  agit selon le contexte d'affichage des erreurs ou ignore l'erreur 
      switch ($niveau) {       
        case E_NOTICE: $typeErreur = "NOTICE PHP"; break;
        case E_STRICT: $typeErreur = "STRICT PHP5"; break;
        case E_USER_NOTICE: $typeErreur = "NOTICE USER"; break;
        case E_DEPRECATED:case E_USER_DEPRECATED:$typErreur = 'DEPRECATED PHP5.3';break;
        default: $typeErreur = "Erreur inconnue";
      } // Politique de l'affichage:
      if (DISPLAYERRORS == 'on') { 
        $status = '<div style="color:red">'.$typeErreur." : " . $msg." Script: ".$file." Ligne : ".$line." </div>\n";  
        echo $status;
      } 
      else { $status = $typeErreur." : " . $msg." Script: ".$file." Ligne : ".$line."\n"; }
      if (LOGERRORS == 'on') { error_log($status,3,BASELOG); }
    }
    else 
    { return false;} 
  }
  catch(MyPhpException $e) {
      $msg = $e->getMessage();
      $e->alerte($msg);  
  }       
}
/**
* 3 Le ramasseur d'exception par defaut
* Recupere les exceptions non recuperees et affiche ou log selon contexte
*/ 
function RamassException($exception)
{
    if (DISPLAYERRORS == 'off'){ 
      if (! empty($_SESSION['lang']) && ($_SESSION['lang'] == 'fr')) { 
          $offMsg = 'Ceci est une exception interne, désolé !'; 
          $offBack = 'Retour au Site';
        }
        elseif (! empty($_SESSION['lang']) && ($_SESSION['lang'] == 'nl')) { 
          $offMsg = 'Dit is een innerlijke uitzondering, sorry !'; 
          $offBack = 'Terug naar website';
        }
        else { 
          $offMsg = 'This is an internal exception, sorry! '; 
          $offBack = 'Back To Vap Site';
        }
        $msg =   "<!DOCTYPE html>"
                  .'<html lang="en">'
                  ."<head><title>Internal Exception</title></head>\n"
                  ."<body>"
                  ."<h3>Oups</h3><p>".$offMsg."</p>\n"
                  . '<p><a href="http://'.$_SERVER['SERVER_NAME'].'">'.$offBack.'</a></p>'
                  .'</body></html>';            
    }                
    else {  $msg = $on = $exception->getMessage(); }
    if (ini_get('log_errors')){ error_log($on,3,BASELOG);}
    unset($_SESSION);
    if ($getid = session_id()) { @session_destroy(); }
    echo $msg;
} 

 
 

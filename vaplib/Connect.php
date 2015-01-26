<?php
  
/**
* @file Connect.php 
* @brief D�finir les constantes pour tout le programme vapspace
* 
* @author marcvancraesbeeck@scarlet.be
* @copyright [GNU Public License](@ref licence.dox)
*/

/** G�rer l'affichage et la journalisation des erreurs ou non. */
  define ('DISPLAYERRORS','off');
  define ('LOGERRORS','on');
   
/** 4 DROITS � d�finir. */
  define ('ANONYM',0x01);
  define ('MEMBRE',0x02);
  define ('RESPONSABLE',0x04);
  define ('ADMIN',0x08);
  
  /** BASEDIR, BASEPATH,BASEURL 3 constantes de base � personnaliser si installation locale. 

    BASEURL : constante qui fixe l'url du repertoire accessible au navigateur internet
    BASEDIR:  constante qui fixe l'adresse physique du r�pertoire accessible au navigateur internet
    BASEPATH : constante qui fixe l'adresse physique du r�pertoire racine, un parent de BASEDIR
  */
  if ($_SERVER["SERVER_NAME"] == 'localhost'){
         $baseurl = 'http://'.$_SERVER['SERVER_NAME'].'/vaptest/vapspace/';
    $basedir = $_SERVER['DOCUMENT_ROOT'].'/'.'vaptest'.'/'.'vapspace'.'/';
    $path = $_SERVER['DOCUMENT_ROOT'].'/'.'vaptest'.'/';
  }
  else {
   $baseurl = 'http://'.$_SERVER["SERVER_NAME"].'/vapspace/';
   $basedir = $_SERVER['DOCUMENT_ROOT'].'/'.'vapspace'.'/';
   $path = dirname($_SERVER['DOCUMENT_ROOT']).'/';
  }
  define ('BASEURL',"$baseurl");
  define('BASEDIR',"$basedir");
  define('BASEPATH',"$path");

/** Les constantes bas�es sur BASEDIR, BASEPATH,BASEURL 
  
    - bas�es sur les r�pertoires: 
      + vapdata: un repertoire general li� aux caches du Mod�le et � sa structure de contextes.
      + vaplib: forme un reperoitre contenant les scripts du MODELE
      + vapapi: repertoire contenant les scripts li�es aux controleurs, � la vue, aux fonctions statiques
      + vaptmp: repertoire pour donn�es temporaires (log, donn�es de session, cache li�es aux antennes) 
     
*/
  $dirData = BASEPATH.'vapdata'.'/';                      
  define ('DIRDATA',"$dirData");
  $dirtable = BASEPATH.'vapdata'.'/'.'tables'.'/';
  define ('DIRTABLE',"$dirtable");  
  $dircontexte = BASEPATH.'vapdata'.'/'.'contextes'.'/';
  define ('DIRCONTEXTE',"$dircontexte");  
  $dircartes = BASEPATH.'vapdata'.'/'.'xml'.'/';
  define ('DIRCARTES',"$dircartes");  
  $dirextra = BASEPATH.'vapdata'.'/'.'extra'.'/';
  define ('DIREXTRA',"$dirextra");  
  
  $dirLib = BASEPATH.'vaplib'.'/';                        
  define('DIRLIB',"$dirLib");
  $dirlang = BASEPATH.'vaplib'.'/'.'langues'.'/';
  define('DIRLANG',"$dirlang");

  $log = BASEPATH.'vaptmp'.'/'.'vap.log';
  define ('BASELOG',"$log");  
  $session = BASEPATH.'vaptmp'.'/'.'session'.'/';
  define ('SESSION',"$session");  
  $cache = BASEPATH.'vaptmp'.'/'.'cache'.'/';
  define ('DIRCACHE',"$cache");  
  $anten = BASEPATH.'vaptmp'.'/'.'cache'.'/'.'antennes'.'/';
  define ('DIRCACHANT',"$anten");

  $dirappli = BASEPATH.'vapapi'.'/';
  define('DIRAPPLIC',"$dirappli");  
  $dircontrl = BASEPATH.'vapapi'.'/'.'controleurs'.'/';
  define('DIRCONTL',"$dircontrl");  
  $dirfunc = BASEPATH.'vapapi'.'/'.'fonctions'.'/';
  define('DIRFUNC',"$dirfunc");
  
  $dirpub = BASEDIR;
  define('DIRPUBLIC',"$dirpub"); 
  $dirjs = BASEDIR.'js'.'/';
  define('DIRJS',"$dirjs");  
  $dircss = BASEDIR.'css'.'/';
  define('DIRCSS',"$dircss");  
  $dirimg = BASEDIR.'photos'.'/';
  define('DIRIMG',"$dirimg");  
  $path_mini = BASEDIR.'photos'.'/'.'mini'.'/';
  $urlimg = BASEURL.'photos/';
  define('URLIMG',"$urlimg");  
  $url_mini = BASEURL.'photos/mini/';
 
  $path_file = DIRDATA.'upload'.'/';
  
  // Path pour le centre de telechargement
  define('FILEPATH',"$path_file");
  define('MINIPATH',"$path_mini");
  define('MINIURL',"$url_mini");

  //Nombre de lignes � afficher:
  define ('TAILLE',50);
    
  /** TABLES et MAILWM sont �crits durant � l'installation */
   
  
 

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
* Fichier Connect.php : gestion des constantes pour tout le programme vapspace
* @category vapspace
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package vaplib
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/

  /**
  *  Gestion des erreurs
  */
  define ('DISPLAYERRORS','off');
  define ('LOGERRORS','on');
   
  /**
  * DROITS
  */
  define ('ANONYM',0x01);
  define ('MEMBRE',0x02);
  define ('RESPONSABLE',0x04);
  define ('ADMIN',0x08);

  
  /**
  * BASEDIR, BASEPATH,BASEURL selon locale ou hébergeur, 3 constantes de base
  * à personnaliser si installation locale
  */  
  if ($_SERVER["SERVER_NAME"] == 'localhost'){  
	  $baseurl = 'http://'.$_SERVER['SERVER_NAME'].'/vaptest/vapspace/';
    $basedir =  $_SERVER['DOCUMENT_ROOT'].'/'.'vaptest'.'/'.'vapspace'.'/';  
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
  /**
  * Les constantes basées sur BASEDIR, BASEPATH,BASEURL
  */
  $dirData = BASEPATH.'vapdata'.'/'; 
  $dirtable = BASEPATH.'vapdata'.'/'.'tables'.'/';
  $dircontexte = BASEPATH.'vapdata'.'/'.'contextes'.'/';
  $dircartes = BASEPATH.'vapdata'.'/'.'xml'.'/';
  $dirextra = BASEPATH.'vapdata'.'/'.'extra'.'/';
  $dirLib = BASEPATH.'vaplib'.'/';
  $dirlang = BASEPATH.'vaplib'.'/'.'langues'.'/'; 
  
  $log = BASEPATH.'vaptmp'.'/'.'vap.log';
  $session = BASEPATH.'vaptmp'.'/'.'session'.'/';
  $cache = BASEPATH.'vaptmp'.'/'.'cache'.'/';
  $anten = BASEPATH.'vaptmp'.'/'.'cache'.'/'.'antennes'.'/';  
 
  $dirappli = BASEPATH.'vapapi'.'/';
  $dircontrl = BASEPATH.'vapapi'.'/'.'controleurs'.'/';
  $dirfunc = BASEPATH.'vapapi'.'/'.'fonctions'.'/';
  
  
  $dirpub = BASEDIR;
  $dirjs = BASEDIR.'js'.'/';
  $dircss = BASEDIR.'css'.'/';
  $dirimg = BASEDIR.'photos'.'/';
  $path_mini = BASEDIR.'photos'.'/'.'mini'.'/';
  $urlimg = BASEURL.'photos/';
  $url_mini = BASEURL.'photos/mini/'; 
 
  define('DIRLIB',"$dirLib");
  define ('DIRDATA',"$dirData");
  define ('DIRTABLE',"$dirtable");
  define ('DIRCONTEXTE',"$dircontexte");
  define ('DIRCARTES',"$dircartes");
  define ('DIREXTRA',"$dirextra");
  
  define ('BASELOG',"$log");    
  define ('SESSION',"$session");
  define ('DIRCACHE',"$cache");
  define ('DIRCACHANT',"$anten");
   
  define('DIRAPPLIC',"$dirappli");
  define('DIRCONTL',"$dircontrl");  
  define('DIRLANG',"$dirlang");
  define('DIRFUNC',"$dirfunc");  
  
  define('DIRPUBLIC',"$dirpub");
  define('DIRJS',"$dirjs");
  define('DIRCSS',"$dircss");
  define('DIRIMG',"$dirimg");
  define('URLIMG',"$urlimg");
  
  $path_file = DIRDATA.'upload'.'/'; 
  
// Path pour le centre de telechargement 
  define('FILEPATH',"$path_file");
  define('MINIPATH',"$path_mini");
  define('MINIURL',"$url_mini");

  //Nombre de lignes à afficher:
  define ('TAILLE',50);
    
  /**
  * TABLES et MAILWM sont ecrits à l'installation
  */
  define("T_ANT","spip_vap_antenne");
  define("T_CHAT","spip_vap_chat");
  define("T_CORE","spip_vap_core");
  define("T_NEWS_ANT","spip_vap_destination");
  define("T_LOAD","spip_vap_files");
  define("T_HUM","spip_vap_humains");
  define("T_HUM_SOC","spip_vap_mobiles");
  define("T_NEWS","spip_vap_news");
  define("T_MEET","spip_vap_passage");
  define("T_REG","spip_vap_regions");
  define("T_ROUTE","spip_vap_trajet");
  define("T_SOC","spip_vap_transpu");
  define("MAILWM","info@vap-vap.be");
  define("NEWMBR","AAA000");
  
 

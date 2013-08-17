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
  * class: UniConnect() est un singleton pour l'unique connexion � la BD
  * sources : http://www.apprendre-php.com/tutoriels/tutoriel-47-classe-singleton-d-acces-aux-sgbd-integrant-pdo.html
  * @access private
  *  @package MODELE
  *  @copyright Marc Van Craesbeeck : marcvancraesbeck@scarlet.be
  *  @licence GPL
  *  @version 1.0.0
  */ 
class UniConnect 
{
  private static $statement = null;
  private $pdoStatement = null;
  
  /**
  * un __construct prive garanti le singleton : une seule instance de la classe est fournie
  * pour tout le script.
  */ 
  private function __construct()
  { 
    try {    
      if (file_exists(DIRLIB.'dsn.php')) { 
        include_once('dsn.php'); 
        $this->pdoStatement = new PDO(DSN,NOM,PASS);
      }
      else { $this->pdoStatement = new PDO(NULL,NULL,NULL); }
      $this->pdoStatement->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e){
      print "Error from PDO : ".$e->getCode()."--".$e->getMessage()."<br />\n";
    }
  }
  public static function getInstance() 
  {
    if(!isset(self::$statement)) {
      self::$statement = new UniConnect();      
    }
    return self::$statement;
  }
  public function __call($method,$argument)
  {
    $args = implode(",",$argument);    
    return $this->pdoStatement->$method($args);
  }
}
?>

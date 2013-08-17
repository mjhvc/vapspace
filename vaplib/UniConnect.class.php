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
  * class: UniConnect() est un singleton pour l'unique connexion à la BD
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

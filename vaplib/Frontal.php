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
* Frontal : Classe g�rant le requ�tes HTTP en les routant
 * vers le controleur et l'action appropri�e
 * @category webscope
 * @copyright Philippe Rigaux, 2008
 * @license GPL
 * sources : http://www.lamsade.dauphine.fr/rigaux/mysqlphp/ 
*/

class Frontal 
{
  const NOM_CTRL = "ctrl";
  const NOM_ACTION = "action";

  /**
  * Execution d'une requ�te HTTP
  */
  public function execute()
  {
    // D'abord, on r�cup�re les noms du contr�leur et de l'action
    if (isset($_GET[self::NOM_CTRL])) { $controleur = ucfirst($_GET[self::NOM_CTRL]) . "Ctrl"; }
    else { $controleur = "IndexCtrl";}

    if (isset($_GET[self::NOM_ACTION])) { $action = $this->lcfirst($_GET[self::NOM_ACTION]);}
    else { $action = "index";}

    // Si le fichier DIRLIB/dsn.php est absent, on lance l'installation
    $fileConnect = DIRLIB."dsn.php"; 
    if ((!file_exists($fileConnect)) && ($action != 'installCnx')) {
      $controleur = "InstallCtrl";
      $action = "index";
    }

    // Charger la classe
    $chemin = DIRCONTL . $controleur . ".php";
    if (file_exists($chemin)) { require_once($chemin); } 
    else { throw new MyPhpException ("Le contr�leur: $controleur n'existe pas"); }
      
    // On instancie la classe du controleur
    if (! $ctrl = new $controleur()) { throw new PyPhpException("Impossible d'instancier $controleur"); }

    // Il faut v�rifier que l'action existe
    if ( ! method_exists($ctrl, $action)) { throw new MyPhpException ("L'action <b>$action</b> n'existe pas"); }
 
    // Et pour finir il n'y a plus qu'� ex�cuter l'action
    call_user_func(array($ctrl, $action));
  }

  /**
   * Parfois la fonction lcfirst n'existe pas dans une distribution PHP...
   */
  private function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return (string)$str;
  }
}

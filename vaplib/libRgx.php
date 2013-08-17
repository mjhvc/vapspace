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
* Biblioth�que de Regex appel�e par InitData::chargerMasque()
*  @package MODELE
*  @copyright Marc Van Craesbeeck : marcvancraesbeck@scarlet.be
*  @licence GPL
*  @version 1.0.0
*/

    $rgxInt = '[^0-9]';
    $rgxHexa = '[^#a-z0-9]';
    $rgxMin = '[^a-z]';
    $rgxNom = '[^-a-zA-Z���������������\\\'\s]';
    $rgxFile =  '[^a-z0-9_\.]';
    $rgxAdres = '[^-a-zA-Z0-9������������\s,_:\.\\\']';  
    $rgxMail = '^[[:alnum:]](?:[-_.]?[[:alnum:]])+_?@[[:alnum:]](?:[-.]?[[:alnum:]])+\.[a-z]{2,6}$';
    $rgxTel = '[^-a-z0-9/_\.\s]';    
    //$rgxPass = '[^A-Za-z0-9�������������]';
     $rgxPass = '[*]';
    $rgxMbr = '[^0-9a-zA-Z]';
    $rgxAlpha = '[^A-Za-z]';
    $rgxDate = '[^-0-9:]';
    $rgxPath = '[^-a-zA-Z0-9_/]';
    $rgxPlaque = '[^-a-zA-Z0-9_\.\s]';
    $rgxSujet = "[^-0-9A-Za-z������������\s\'\!\?\+\.,\:\)]";
    $rgxGlob = '(.*)';
    $rgxBin = '(oui|non)';
?>

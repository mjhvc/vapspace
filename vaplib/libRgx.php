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
* Bibliothèque de Regex appelée par InitData::chargerMasque()
*  @package MODELE
*  @copyright Marc Van Craesbeeck : marcvancraesbeck@scarlet.be
*  @licence GPL
*  @version 1.0.0
*/

    $rgxInt = '[^0-9]';
    $rgxHexa = '[^#a-z0-9]';
    $rgxMin = '[^a-z]';
    $rgxNom = '[^-a-zA-Zàïîôùûöëäâãéèêç\\\'\s]';
    $rgxFile =  '[^a-z0-9_\.]';
    $rgxAdres = '[^-a-zA-Z0-9àâäéèêûôùïîç\s,_:\.\\\']';  
    $rgxMail = '^[[:alnum:]](?:[-_.]?[[:alnum:]])+_?@[[:alnum:]](?:[-.]?[[:alnum:]])+\.[a-z]{2,6}$';
    $rgxTel = '[^-a-z0-9/_\.\s]';    
    //$rgxPass = '[^A-Za-z0-9àâéêèôöïîùùûç]';
     $rgxPass = '[*]';
    $rgxMbr = '[^0-9a-zA-Z]';
    $rgxAlpha = '[^A-Za-z]';
    $rgxDate = '[^-0-9:]';
    $rgxPath = '[^-a-zA-Z0-9_/]';
    $rgxPlaque = '[^-a-zA-Z0-9_\.\s]';
    $rgxSujet = "[^-0-9A-Za-zéâèçàûîôêïöü\s\'\!\?\+\.,\:\)]";
    $rgxGlob = '(.*)';
    $rgxBin = '(oui|non)';
?>

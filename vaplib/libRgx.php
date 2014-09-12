<?php
 
/**
* Biblioth�que de Regex appel�e par InitData::chargerMasque()
* @author marcvancraesbeck@scarlet.be
* @copyright [GNU Public License](@ref licence.dox)
*/ 
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

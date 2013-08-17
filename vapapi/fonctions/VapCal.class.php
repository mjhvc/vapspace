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
* @category vapsapce
* @copyright Marc Van Craesbeeck, 2011
* @license GPL
* @package controleur
* @version 1.0.0
* @author marcvancraesbeeck@scarlet.be
*/ 

class VapCal
{
  //const LOC = $_SESSION['lang'].'_BE';
 /*
  * Calcule la calende (le jour Un) du mois fournit en timestamp
  * @param $stamp, integer, un timestamp facultatif 
  * retourne une date
  */ 
  static function calende($stamp=NULL)
  {
    if (empty($stamp)) { $stamp = time(); }
    $jour = date('d',$stamp);    
    $stampUn = intval($stamp - (86400 * ($jour - 1)));
    $jourUn = date('Y-m-d',$stampUn);
    return $jourUn;
  }
  /*
  * calcule le jour ultime du mois precedent une calende (fourni en $stamp)
  * @param $stampUn, integer, un timestamp obligatoire d'une calende 
  * retourne une date
  */  
  static function jourUltime($stampUn)  
  {
    $stampUltime = $stampUn - 86400;     
    $ultime = date('Y-m-d',$stampUltime);
    return $ultime;
  }
  /*
  * calcule le mois precedent une calende (fourni en $stamp)
  * @param $stampUn, integer, un timestamp obligatoire d'une calende 
  * retourne un array() avec 3 valeurs:
  * ['date'] est la date mois-Annee formatee francophone (si setlocale fonctionne)
  * ['stamp'] le stamp de la calende de ce mois précédent
  * ['datePre'] la date 'Y-m-d' de cette calende
  */  
  static function moisPre($stampUn)
  {
    //setlocale(LC_TIME,self::LOC);
    $tableau = array();
    if (empty($stampUn)) { $stampUn = time(); }
    $stampUltime = $stampUn - 86400;
    $calende = self::calende($stampUltime);
    $stampCalende = strtotime($calende); //necessaire pour comparer les stamp
    $tableau['stamp'] = $stampCalende;
    $tableau['date'] = strftime('%B-%Y',$stampUltime);
    $tableau['datePre'] = date('Y-m-d',$stampCalende);
    return $tableau ;
  }
  /*
  * calcule la calende qui suit celle fournie en timestamp
  * @param $stampUn, integer, un timestamp obligatoire d'une calende 
  * retourne un array() avec 3 valeurs:
  * ['date'] est la date mois-Annee formatee francophone (si setlocale fonctionne)
  * ['stamp'] le stamp de la calende de ce mois précédent
  * ['dateSuiv'] la date 'Y-m-d' de cette calende
  */
  static function moisSuiv($stampUn)    
  {
    //setlocale(LC_TIME,self::LOC);
    $tableau = array();
    if (empty($stampUn)) { $stampUn = time(); }
    $moisCourant = date('m',$stampUn);
    $anCourant = date('Y',$stampUn);
    $joursMois = cal_days_in_month(CAL_GREGORIAN,$moisCourant,$anCourant);
    $stampMoisSuiv = ($stampUn + (86400 * $joursMois));
    $moisSuiv = strftime('%B-%Y',$stampMoisSuiv);
    $dateSuiv = date('Y-m-d',$stampMoisSuiv);
    $tableau['date'] = $moisSuiv;
    $tableau['dateSuiv'] = $dateSuiv;
    $tableau['stamp'] =   $stampMoisSuiv;
    return $tableau ;
  }
  /**
  * Evalue le stamp et la date de la fin du mois suivant
  * @param: $stampUn, un timestamp de calende obligatoire
  * retourne: un array() avec 2 parametres:
  * ['dateFin'] = date 'Y-m-d' du dernier jour du mois suivant
  * ['stamp'] = le stamp de fin de mois
  */
  static function finMoisSuiv($stampUn)
  {
    //setlocale(LC_TIME,self::LOC);
    $tableau = array();    
    $moisSuiv = date('m',$stampUn);
    $anSuiv = date('Y',$stampUn);
    $joursMois = cal_days_in_month(CAL_GREGORIAN,$moisSuiv,$anSuiv);
    $stampFinMois = ($stampUn + (86400 * ($joursMois - 1)));
    $dateFin = date('Y-m-d',$stampFinMois);
    $tableau['dateFin'] = $dateFin;
    $tableau['stamp'] = $stampFinMois;
    return $tableau;
  }
  //bel effort inutile :-) la pagination ici est rudimentaire
  // on navigue vers le mois qui precede ou qui suit
  // ces mois sont simplement calculés à chaque appel de la methode
  // un timestamp de calende sert de référence et est mis en session  
  static function comptePages($stamp_beg,$stamp_end)
  {
    $pages = 0;  $sauterMois = array();  
    $calende_beg = self::calende($stamp_beg);  
    $stamp_zero = strtotime($calende_beg);
    $sauterMois[0]['date'] = $calende_beg;
    $sauterMois[0]['stamp'] = $stampzero;    
    while ($stamp_zero <= $stamp_end) {
      $pages++;
      $sauterMois[$pages] = self::moisSuiv($stamp_zero); //les pages sont dans la cle [$pages]
      $stamp_zero = $sauterMois[$pages]['stamp'];
    }
    return $sauterMois;
  }
}

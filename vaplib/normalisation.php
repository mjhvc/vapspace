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
* Cette fonction supprime tout echappement automatique des donnees http
* dans un tableau de dimmension quelconque.
* Sources: http://www.lamsade.dauphine.fr/rigaux/mysqlphp/?page=code 
*/


function NormalisationHTTP($tableau)
{
	foreach($tableau as $cle => $valeur)
	{ 
		if (!is_array($valeur)) //c'est un élément, on agit
			{ $tableau[$cle] = stripSlashes($valeur); }
		else 							//c'est un tableau, on agit recursivement
			{ $tableau[$cle] = NormalisationHttp($valeur); }
	}
	return $tableau;
}

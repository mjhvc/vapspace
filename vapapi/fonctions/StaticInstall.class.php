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
class StaticInstall
{
  private static function ctxtGen($nom,$mail,$hreg=NULL,$hant=NULL)
  {
    $general = array();
    $general['titre'] = "[general]";
    $general['nom'] = $nom;
    $general['mail'] = $mail;
    if ($hreg) { //si il y a un hors-region, il y a un hors-antenne
      $general['horsAntenne'] = $hant; 
      $general['horsRegion'] = $hreg;
    }
    elseif($hant) { //sinon, si il y seulement un hors-antenne
      $general['horsAntenne'] = $hant;
    }
    return $general;
  }  
  private static function ctxtDyn($tables,$PK,$FK=NULL,$FKP=NULL)
  {
    $dynamique =  array();
    $dynamique['titre'] = "[dynamique]";
    $dynamique['tables'] = $tables;
    $dynamique['PK'] = $PK;
    if (!empty($FK)) { $dynamique['FK'] = $FK; }
    if (!empty($FKP)) { $dynamique['FKP'] = $FKP; }
    return $dynamique;
  }
  private static function ctxtExt($tables,$FK,$FKP)
  {
    $extra =  array();
    $extra['titre'] = "[extra]";
    $extra['tables'] = $tables;
    $extra['FK'] = $FK;
    $extra['FKP'] = $FKP;
    return $extra;
  }
  private static function ctxtLi($table,$PK,$FK,$FKP,$champs=NULL,$valeurs=NULL)
  {  
    $liaison = array();
    $liaison['titre'] = "[liaison]"; 
    $liaison['table'] = $table;
    $liaison['PK'] = $PK;
    $liaison['FK'] = $FK;
    $liaison['FKP'] = $FKP;
    if (!empty($champs)) { $liaison['champs'] = $champs; }
    if (!empty($valeurs)) { $liaison['valeurs'] = $valeurs; }
    return $liaison;
  }
  private static function  ctxtStat($tables,$PK,$champs=NULL,$valeurs=NULL)
  {
    $statique = array();
    $statique['titre'] = "[statique]";
    $statique['tables'] = $tables;
    $statique['PK'] = $PK;
    if (!empty($champs)) { $statique['champs'] = $champs; }
    if (!empty($valeurs)) { $statique['valeurs'] = $valeurs; }
    return $statique;
  }  
  private static function ctxtPass($tables,$PK,$champs)
  {
    $passive = array();
    $passive['titre'] = "[passive]";
    $passive['tables'] = $tables;
    $passive['PK'] = $PK;
    $passive['champs'] = $champs;
    return $passive;
  }
  private static function ctxtAno($oblig,$facul=NULL,$vue=NULL)
  {
    $anonym = array();
    $anonym['titre'] = "[anonym]";
    $anonym['oblig'] = $oblig;
    if (!empty($facul)) { $anonym['facul'] = $facul; }
    if (!empty($vue)) { $anonym['vue'] = $vue; }
    return $anonym;
  }
  private static function ctxtMbr($oblig,$facul=NULL,$vue=NULL)
  {
    $membre = array();
    $membre['titre'] = "[membre]";
    $membre['oblig'] = $oblig;
    if (!empty($facul)) { $membre['facul'] = $facul; }
    if (!empty($vue)) { $membre['vue'] = $vue; }
    return $membre;
  }
  private static function ctxtResp($oblig,$facul=NULL,$vue=NULL)
  {
    $resp = array();
    $resp['titre'] = "[responsable]";
    $resp['oblig'] = $oblig;
    if (!empty($facul)) { $resp['facul'] = $facul; }
    if (!empty($vue)) { $resp['vue'] = $vue; }
    return $resp;
  }
  private static function ctxtAdm($oblig,$facul=NULL,$vue=NULL)
  {
    $adm = array();
    $adm['titre'] = "[admin]";
    $adm['oblig'] = $oblig;
    if (!empty($facul)) { $adm['facul'] = $facul; }
    if (!empty($vue)) { $adm['vue'] = $vue; }
    return $adm;
  }
  public static function contextes($nom,$tabTables,$mail,$soc,$hreg=NULL,$hant=NULL)
  {
    $contExtra = $contLiai = $contStat = $contPass = $contAno = $contMbr = $contResp =  $contAdm =  array();
    if ($nom == 'Antenne') {
      $tableOne = $tabTables[0];
      $tableTwo = $tabTables[9];
      $champs = 'idreg,nomregion,province';
      $oblig = $vue = 'nomantenne,lienreg';
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($tableOne,'idant');
      $contExt = self::ctxtExt($tableTwo,'lienreg','idreg');
      $contPass = self::ctxtPass($tableTwo,'idreg',$champs);
      $contAno = self::ctxtAno($oblig);
      $contMbr = self::ctxtMbr($oblig);
      $contResp = self::ctxtResp($oblig,NULL,$vue);
      $contAdm =  self::ctxtAdm($oblig,NULL,$vue);
      $contexte = array($contGen,$contDyn,$contExt,$contPass,$contAno,$contMbr,$contResp,$contAdm);
    }
    elseif ($nom == 'Membre') {
      $T_dyn = $tabTables[5].','.$tabTables[2];
      $PK_dyn = 'idhum,idvap'; 
      $T_ext = $tabTables[0].','.$tabTables[9];
      $FK_ext = 'lienant,lienreg';
      $FKP_ext = 'idant,idreg';
      $FK_Li = 'lienhum,lientrans';
      $FKP_Li = 'idhum,idtrans';
      $champs_Li = 'utilisation,abonne';
      $val_Li = 'oui';
      $val_Stat = 'oui,ouiavec';
      $champs_Pass = 'idant,nomantenne,lienreg';
      $oblig_mbr = 'mail,genre,nom,prenom,adresse,code,ville,inscrit,passe,lienant,lienreg,securite'; 
      $oblig_ano = $oblig_mbr.',spam';
      $oblig_resp = $oblig_adm = $oblig_mbr.',membre';
      $facul_ano = $facul_mbr = 'pays,tel,naissance,news,fiche,connu,statut,plaque';
      $facul_resp = $facul_ano.',cachet';
      $facul_adm = $facul_resp.',reserve';
      $vue_mbr = 'nom,prenom,membre,inscrit';
      $vue_resp = 'numbr,mail,nom,prenom,membre,inscrit,code,adresse,ville,tel,cachet';
      $vue_adm = 'numbr,mail,nom,prenom,membre,lienant,code,adresse,ville,connu,cachet,inscrit';
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn,'lienperso','idhum');
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contLi = self::ctxtLi($tabTables[6],'idmob',$FK_Li,$FKP_Li,$champs_Li,$val_Li);
      if ($soc) { $contStat = self::ctxtStat($tabTables[11],'idtrans','societes',$val_Stat); }
      else { $contStat = NULL; }
      $contPass = self::ctxtPass($tabTables[0],'idant',$champs_Pass);
      $contAno = self::ctxtAno($oblig_ano,$facul_ano);
      $contMbr = self::ctxtMbr($oblig_mbr,$facul_mbr,$vue_mbr);
      $contResp = self::ctxtResp($oblig_resp,$facul_resp,$vue_resp);
      $contAdm =  self::ctxtAdm($oblig_adm,$facul_adm,$vue_adm);
      $contexte = array($contGen,$contDyn,$contExt,$contLi,$contStat,$contPass,$contAno,$contMbr,$contResp,$contAdm);  
    }
    elseif($nom == 'Region') {
      $T_dyn = $tabTables[9];
      $PK_dyn = 'idreg';
      $oblig = $vue = 'nomregion,province';
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contAno = self::ctxtAno($oblig);
      $contMbr = self::ctxtMbr($oblig);
      $contResp = self::ctxtResp($oblig,NULL,$vue);
      $contAdm =  self::ctxtAdm($oblig,NULL,$vue);
      $contexte = array($contGen,$contDyn,$contAno,$contMbr,$contResp,$contAdm);   
    }
    elseif($nom == 'Chat') {
      $T_dyn = $tabTables[1];
      $PK_dyn = 'idchat';
      $T_ext = $tabTables[0].','.$tabTables[5];
      $FK_ext = 'lienhum,lienant';
      $FKP_ext = 'idhum,idant';
      $champs_Pass = 'idant,nomantenne,lienreg';
      $oblig = 'lienant,lienhum,pseudo,vapattache,vaputile,message,sujet,idpere,chatmail';
      $facul = 'poste';
      $vue = 'sujet,message,poste';      
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contPass = self::ctxtPass($tabTables[0],'idant',$champs_Pass);
      $contMbr = self::ctxtMbr($oblig,$facul,$vue);
      $contResp = self::ctxtResp($oblig,$facul,$vue);
      $contAdm =  self::ctxtAdm($oblig,$facul,$vue);
      $contexte = array($contGen,$contDyn,$contExt,$contPass,$contMbr,$contResp,$contAdm);  
    }
    elseif ($nom == 'Sponsor') {  
      $T_dyn = $tabTables[11];
      $PK_dyn = 'idtrans';
      $oblig = $vue = 'societes';
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contAno = self::ctxtAno($oblig);
      $contMbr = self::ctxtMbr($oblig);
      $contResp = self::ctxtResp($oblig);
      $contAdm =  self::ctxtAdm($oblig,NULL,$vue);
      $contexte = array($contGen,$contDyn,$contAno,$contMbr,$contResp,$contAdm); 
    }
    elseif ($nom == 'News') {
      $T_dyn = $tabTables[7];
      $PK_dyn = 'idnews';
      $champs_Pass = 'idant,nomantenne,lienreg';
      $oblig = 'sujet,contenu,expediteur';
      $facul = 'altcontenu,couleurfond,couleurpolice,couleurtitre';
      $vue = 'expediteur,sujet,contenu,couleurfond,couleurpolice,couleurtitre';      
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contPass = self::ctxtPass($tabTables[0],'idant',$champs_Pass);
      $contResp = self::ctxtResp($oblig,$facul,$vue);
      $contAdm =  self::ctxtAdm($oblig,$facul,$vue);
      $contexte = array($contGen,$contDyn,$contPass,$contResp,$contAdm);  
    } 
    elseif ($nom == 'File') {
      $T_dyn = $tabTables[4];
      $PK_dyn = 'idFile';
      $champs_Pass = 'idant,nomantenne,lienreg';
      $oblig = 'nomFile';
      $facul = 'descFile,pathFile,sizeFile,dateFile';    
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contPass = self::ctxtPass($tabTables[0],'idant',$champs_Pass);
      $contResp = self::ctxtResp($oblig,$facul);
      $contAdm =  self::ctxtAdm($oblig,$facul);
      $contexte = array($contGen,$contDyn,$contPass,$contResp,$contAdm);  
    }
    elseif ($nom == 'Auth') {
      $T_dyn = $tabTables[5];
      $PK_dyn = 'idhum';
      $T_ext = $tabTables[9];
      $FK_ext = 'lienreg';
      $FKP_ext = 'idreg';
      $oblig = 'mail,passe';
      $vue = 'nom,passe';      
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contAno = self::ctxtAno($oblig,NULL,$vue);
      $contMbr = self::ctxtMbr($oblig,NULL,$vue);
      $contResp = self::ctxtResp($oblig,NULL,$vue);
      $contAdm =  self::ctxtAdm($oblig,NULL,$vue);
      $contexte = array($contGen,$contDyn,$contExt,$contAno,$contMbr,$contResp,$contAdm); 
    }
    elseif ($nom == 'Help') {
      $T_dyn = $tabTables[5];
      $PK_dyn = 'idhum';
      $T_ext = $tabTables[9];
      $FK_ext = 'lienreg';
      $FKP_ext = 'idreg';
      $oblig = 'mail';
      $facul = 'passe';      
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contAno = self::ctxtAno($oblig,$facul);
      $contMbr = self::ctxtMbr($oblig,$facul);
      $contResp = self::ctxtResp($oblig,$facul);
      $contAdm =  self::ctxtAdm($oblig,$facul);
      $contexte = array($contGen,$contDyn,$contExt,$contAno,$contMbr,$contResp,$contAdm); 
    }
    elseif ($nom == 'Passage') {
      $T_dyn = $tabTables[8];
      $PK_dyn = 'idpass';
      $T_ext = $tabTables[0];
      $FK_ext = 'lienant';
      $FKP_ext = 'idant';
      $champs_Pass = 'idant,nomantenne,lienreg';
      $oblig = $vue = 'lienant,lieu';    
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contPass = self::ctxtPass($tabTables[0],'idant',$champs_Pass);
      $contAno = self::ctxtAno($oblig);
      $contMbr = self::ctxtMbr($oblig,NULL,$vue);
      $contResp = self::ctxtResp($oblig,NULL,$vue);
      $contAdm =  self::ctxtAdm($oblig,NULL,$vue);
      $contexte = array($contGen,$contDyn,$contExt,$contPass,$contAno,$contMbr,$contResp,$contAdm);  
    } 
    elseif ($nom == 'Trajet') {
      $T_dyn = $tabTables[10];
      $PK_dyn = 'idtraj';
      $T_ext = $tabTables[5].','.$tabTables[8].','.$tabTables[0];
      $FK_ext = 'lienhum,lienpass,lienant';
      $FKP_ext = 'idhum,idpass,idant';
      $oblig = 'commune,deal';
      $facul = 'lienpass,lienhum,lienant,destination,periode,poste';
      $vue = 'commune,destination,periode';      
      $contGen = self::ctxtGen($nom,$mail,$hreg,$hant);
      $contDyn = self::ctxtDyn($T_dyn,$PK_dyn);
      $contExt = self::ctxtExt($T_ext,$FK_ext,$FKP_ext);
      $contMbr = self::ctxtMbr($oblig,$facul,$vue);
      $contResp = self::ctxtResp($oblig,$facul,$vue);
      $contAdm =  self::ctxtAdm($oblig,$facul,$vue);
      $contexte = array($contGen,$contDyn,$contExt,$contAno,$contMbr,$contResp,$contAdm); 
    }
    else { $contexte = NULL; }
    return $contexte;
  }
  /**
  * $pre = prefixe des tables
  * $stmt = ressource connexion à la base de donnée
  */
  public static function sqlTables($pre)
  {
    $insTables = array(); 
    $pays = filter_input(INPUT_POST,'instPays',FILTER_SANITIZE_SPECIAL_CHARS);
    $newMbr = filter_input(INPUT_POST,'instMbr',FILTER_SANITIZE_SPECIAL_CHARS); 
    $mail =   filter_input(INPUT_POST,'instContact',FILTER_SANITIZE_EMAIL);
    $sqlRegion = " CREATE TABLE IF NOT EXISTS ".$pre."region ( "
                 ." idreg int(9) NOT NULL auto_increment, "
                 ." nomregion varchar(30) NOT NULL default 'Noregion', "
                 ." province varchar(30) NOT NULL default 'Noprovince', "
                 ." PRIMARY KEY  (idreg) "
                 ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1; ";
    $insTables['region'] = $sqlRegion;
    $sqlAnt = " CREATE TABLE IF NOT EXISTS ".$pre."antenne ( "
           ." idant int(11) NOT NULL auto_increment, "
           ." lienreg int(9) NOT NULL default '0', "
           ." nomantenne varchar(30) NOT NULL, "
           ." PRIMARY KEY  (idant), "
           ." UNIQUE KEY nomantenne (nomantenne), "
           ." FOREIGN KEY (lienreg) REFERENCES ".$pre."region (idreg) "
           ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['antenne'] = $sqlAnt;
    $sqlHumain = " CREATE TABLE IF NOT EXISTS ".$pre."human ( "
                 ." idhum int(11) NOT NULL auto_increment, "
                 ." lienreg int(9) NOT NULL default '0', "
                 ." genre char(1) NOT NULL default 'M', "
                 ." nom varchar(30) NOT NULL, "
                 ." prenom varchar(30) NOT NULL, "
                 ." adresse varchar(60) NOT NULL, "
                 ." ville varchar(30) NOT NULL, "
                 ." code int(5) NOT NULL default '0', "
                 ." pays varchar(30) NOT NULL default '".$pays."', "
                 ." mail varchar(40) NOT NULL default 'PasDeMail', "
                 ." tel varchar(20) NOT NULL default 'non', "
                 ." naissance int(4) NOT NULL default '0', "
                 ." passe varchar(40) NOT NULL, "
                 ." statut varchar(20) NOT NULL default 'membre', "
                 ." PRIMARY KEY  (idhum), "
                 ." FOREIGN KEY (lienreg)  REFERENCES ".$pre."region (idreg) "
                 ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['human'] = $sqlHumain; 
    $sqlMeet = " CREATE TABLE IF NOT EXISTS ".$pre."meeting ( "
               ." idpass bigint(11) NOT NULL auto_increment, "
               ." lienant int(4) NOT NULL default '0', "
               ." lieu varchar(40) NOT NULL, "
               ." poste timestamp NOT NULL default CURRENT_TIMESTAMP, "
               ." compta int(11) NOT NULL default '0', "
               ." offre int(11) NOT NULL default '0', "
               ." demande int(11) NOT NULL default '0', "
               ." PRIMARY KEY  (idpass), "
               ." FOREIGN KEY (lienant) REFERENCES ".$pre."antenne (idant) "
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['meeting'] = $sqlMeet;
    $sqlNews = " CREATE TABLE IF NOT EXISTS ".$pre."news ( "
               ." idnews int(11) NOT NULL auto_increment, "
               ." expediteur varchar(50) NOT NULL default '".$mail."', "
               ." commanditaire varchar(60) NOT NULL default 'commanditaire', "
               ." sujet varchar(60) NOT NULL, "
               ." contenu text NOT NULL, "
               ." altcontenu text NOT NULL, "
               ." destinataires int(11) NOT NULL default '0', "
               ." achemines int(11) NOT NULL default '0', "
               ." active varchar(6) NOT NULL default 'non', "
               ." couleurfond varchar(20) NOT NULL default 'white', "
               ." couleurpolice varchar(20) NOT NULL default 'black', "
               ." couleurtitre varchar(20) NOT NULL default 'black', "
               ." envoye datetime NOT NULL default '0000-00-00 00:00:00', "
               ." PRIMARY KEY  (idnews) "
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 " ;
    $insTables['news'] = $sqlNews;
    $sqlTrans =  " CREATE TABLE IF NOT EXISTS ".$pre."transport ( "
                 ." idtrans int(10) NOT NULL auto_increment, "
                 ." societes varchar(15) NOT NULL default 'NoSoc', "
                 ." PRIMARY KEY  (idtrans) "
                 ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['sondage'] = $sqlTrans;   
    $sqlChat = " CREATE TABLE IF NOT EXISTS ".$pre."chat ( "
               ." idchat bigint(20) NOT NULL auto_increment, "
               ." idpere bigint(20) NOT NULL default '0', "
               ." lienhum bigint(20) NOT NULL, "
               ." lienant int(11) NOT NULL, "
               ." vapattache varchar(50) NOT NULL, "
               ." vaputile varchar(30) NOT NULL, "
               ." chatmail varchar(90) NOT NULL default 'non', " 
               ." pseudo varchar(90) NOT NULL, "
               ." sujet varchar(90) NOT NULL default 'sujet :', "
               ." message text NOT NULL, "
               ." poste timestamp NOT NULL default CURRENT_TIMESTAMP, "
               ." PRIMARY KEY  (idchat), "
               ." FOREIGN KEY (lienhum) REFERENCES ".$pre."human (idhum), "
               ." FOREIGN KEY (lienant) REFERENCES ".$pre."antenne (idant) " 
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['chat'] = $sqlChat;
    $sqlFile = " CREATE TABLE IF NOT EXISTS ".$pre."file ( "
               ." idFile int(11) NOT NULL auto_increment, "
               ." nomFile varchar(30) NOT NULL, "
               ." descFile varchar(150) NOT NULL default 'Aucune', "
               ." pathFile varchar(150) NOT NULL, "
               ." sizeFile int(11) NOT NULL default '0', "
               ." dateFile timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP, "
               ." PRIMARY KEY  (idFile) "
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['file'] = $sqlFile;
    $sqlCore = " CREATE TABLE IF NOT EXISTS ".$pre."core ( "
               ." idvap int(11) NOT NULL auto_increment, "
               ." lienperso int(11) NOT NULL default '0', "
               ." lienant int(9) NOT NULL default '0', "
               ." securite varchar(10) NOT NULL, "
               ." membre varchar(8) NOT NULL default '".$newMbr."', "
               ." plaque varchar(20) NOT NULL default 'non', "
               ." numbr int(11) NOT NULL default '0', "
               ." inscrit varchar(10) NOT NULL, "
               ." cachet date NOT NULL default '0000-00-00', "
               ." reserve varchar(40) NOT NULL default 'reserve', "
               ." connu varchar(20) NOT NULL default 'inconnu', "
               ." news varchar(6) NOT NULL default 'non', "
               ." fiche varchar(5) NOT NULL default 'non', "
               ." iter_compta int(11) NOT NULL default '0', "
               ." PRIMARY KEY  (idvap), "
               ." FOREIGN KEY (lienperso) REFERENCES ".$pre."human (idhum), "
               ." FOREIGN KEY (lienant) REFERENCES ".$pre."antenne (idant) " 
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 " ;
    $insTables['core'] = $sqlCore;
    $sqlDest = " CREATE TABLE IF NOT EXISTS ".$pre."multinews ( "
               ." idest int(11) NOT NULL auto_increment, "
               ." lienant int(11) NOT NULL default '0', "
               ." lienews int(11) NOT NULL default '0', "
               ." PRIMARY KEY  (idest), "
               ." FOREIGN KEY (lienant) REFERENCES ".$pre."antenne (idant), " 
               ." FOREIGN KEY (lienews) REFERENCES ".$pre."news (idnews) " 
               ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['destination'] = $sqlDest;    
    $sqlMob = " CREATE TABLE IF NOT EXISTS ".$pre."mobile ( "
              ." idmob int(11) NOT NULL auto_increment, "
              ." lienhum int(11) NOT NULL default '0', "
              ." lientrans int(10) NOT NULL default '0', "
              ." utilisation varchar(10) NOT NULL default 'non', "
              ." abonne varchar(10) NOT NULL default 'non', "
              ." PRIMARY KEY  (idmob), "
              ." FOREIGN KEY (lientrans) REFERENCES ".$pre."transport (idtrans), "
              ." FOREIGN KEY (lienhum) REFERENCES ".$pre."human (idhum) "
              ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['mobile'] = $sqlMob;
    $sqlTrajet = " CREATE TABLE IF NOT EXISTS ".$pre."trajet ( "
                 ." idtraj int(11) NOT NULL auto_increment, "
                 ." lienhum int(11) NOT NULL default '0', "
                 ." lienpass int(11) NOT NULL default '0', "
                 ." lienant int(4) NOT NULL default '0', "
                 ." commune varchar(30) NOT NULL, "
                 ." destination varchar(50) NOT NULL default 'Indefinie', "
                 ." periode varchar(90) NOT NULL default 'Indéfinie', "
                 ." poste timestamp NOT NULL default CURRENT_TIMESTAMP, "
                 ." deal varchar(10) NOT NULL, "
                 ." compta int(11) NOT NULL default '0', "
                 ." PRIMARY KEY  (idtraj), "
                 ." FOREIGN KEY (lienhum) REFERENCES ".$pre."human (idhum), "
                 ." FOREIGN KEY (lienpass) REFERENCES ".$pre."meeting (idpass), "
                 ." FOREIGN KEY (lienant) REFERENCES ".$pre."antenne (idant) "
                 ." ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
    $insTables['trajet'] = $sqlTrajet;
    
    return $insTables;
  } 
}

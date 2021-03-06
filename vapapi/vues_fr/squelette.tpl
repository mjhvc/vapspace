<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
  <title>{titre_head}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <link rel="shortcut icon" href="photos/favicon.ico"/>
  <link rel="stylesheet" media="all" type="text/css" title="Design-VAP" href="css/sortie.css" />
  <script type= "text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script type= "text/javascript" src="js/menus.js"></script>
  {javascript}
 <!--[if lt IE 8]>
<div id="alertIE">Bonjour <br />
  Votre navigateur est ancien et ne sait interpr�ter les standarts CSS 2.1 de cette page.<br />
    <ul>Merci de choisir un navigateur r�cent : 
    <li><a href="http://www.mozilla.org/fr/firefox/new/">Fondation Mozzilla</a></li>
    <li><a href="http://windows.microsoft.com/fr-FR/internet-explorer/downloads/ie">Microsoft</a></li>
    <li><a href="http://www.google.ch/chrome?hl=fr">Google</a></li>
    <li><a href="http://www.apple.com/fr/safari/">Apple</a></li> 
    </ul></div>
<![endif]-->  

</head>
<body> 
  <div id="entete">
    <img src="photos/logo.png" alt="logo site" class="imgleft" />
    <h1>{titre_page}</h1>
    {connecte}
  </div>
  <div id="msgAjax"></div>
  <div id="contenutpl">
    <p class="retour">{retour}</p>    
    {contenu}
  </div>
  <div id="pied">
    <img src="photos/logo.png" alt="logo site" class="imgleft" />
     <div class="menuPied">
    <p class="piedleft"><a href="{contact}">Contact</a></p>
    {pied}
    </div>
    <img src="photos/logo.png" alt="logo site" class="imgright" />
    <hr class="nettoyeur" />
  </div>
</body> 
</html>

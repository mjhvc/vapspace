<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head> 
  <title>VAP-kaarten</title> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" media="screen" type="text/css" title="Design-VAP" href="<?php echo $urlCss ;?>" />   
  <script type="text/javascript" src="js/swfobject.js"></script>
  <script type="text/javascript">
    <!-- Adobe recommends that developers use SWFObject2 for Flash Player detection. -->
    <!-- For more information see the SWFObject page at Google code (http://code.google.com/p/swfobject/). -->
    <!-- Information is also available on the Adobe Developer Connection Under "Detecting Flash Player versions and embedding SWF files with SWFObject 2" -->
    <!-- Set to minimum required Flash Player version or 0 for no version detection -->
    var swfVersionStr = "9.0.124";
    <!-- xiSwfUrlStr can be used to define an express installer SWF. -->
    var xiSwfUrlStr = "";
    var flashvars = {};
    var params = {};
    params.quality = "high";
    params.bgcolor = "#ffffff";
    params.play = "true";
    params.loop = "true";
    params.wmode = "window";
    params.scale = "showall";
    params.menu = "true";
    params.devicefont = "false";
    params.salign = "";
    params.allowscriptaccess = "sameDomain";
    var attributes = {};
    attributes.id = "heb_bds_no_xml";
    attributes.name = "heb_bds_no_xml";
    attributes.align = "middle";
    swfobject.embedSWF(
      "heb_bds_no_xml.swf", "flashContent",
      "510", "370",
      swfVersionStr, xiSwfUrlStr,
      flashvars, params, attributes);
  </script>
</head>
<body>
  <div id="entete">
    <img src="http://www.vap-vap.be/vapspace/photos/logo.png" alt="logo site" class="imgleft" />
    <h1>VAP-kaarten</h1>
    <?php echo $connecte; ?> 
  <hr class="nettoyeur" />  
  </div>
  <!-- SWFObject's dynamic embed method replaces this alternative HTML content for Flash content when enough JavaScript and Flash plug-in support is available. -->
  <div id="corpsPictos"><!--corpsPictos-->
    <h3> VAP bestemming kaarten met klantgericht!  </h3>
    <p class="cartes"> Deze kartonnen liften <strong> identificeert u als lid van VAP < <br />
        Twee afdrukformaten: A4 en 10x15 cm<br />
        Goede reis </p>
    <div id="flashContent">
      <a href="http://www.adobe.com/go/getflash">
        <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
      </a>
      <p>This page requires Flash Player version 9.0.124 or higher.</p>
    </div>
    <br class="nettoyeur"/>
    <p class="signature"><!--signature-->
      Het VAP team,<br />
      Voor een gedeelde mobiliteit.
    </p>
   <br class="nettoyeur"/>
  </div>
  <div id="pied">
    <img src="http://www.vap-vap.be/vapspace/photos/logo.png" alt="logo site" class="imgleft" /> 
     <div class="menuPied">
    <p class="piedleft"> <a href="mailto:info@vap-vap.be">Contact</a></p> 
    <p class="piedleft"><a href="<?php echo $vapspace; ?>">Vapspace</a></p>
  </div>
  <img src="http://www.vap-vap.be/vapspace/photos/logo.png" alt="logo site" class="imgright" /> 
  </div> 
</body> 
</html>

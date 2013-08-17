<p style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;"> 
  expéditeur: {expediteur} 
</p>
<p style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;"> 
  Sujet: {sujet} 
</p>
<div style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin:0 auto;">
  <p class="newsleft"><img src="{url_logo}" alt="logo"/></p><br class="nettoyeur" />
  {htmlcont}
  <p style="font-size:0.88em;">
    <a style="color:black;" href="{url_cms}">Non actif à ce stade: Voir cette news sur notre site</a><br />
    <a style="color:black;" href="{url_desinscrit}">Non actif à ce stade: Pour toute désinscription, un click ici</a>
  </p>
</div>
<p style="width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;">Altcontenu: {altcontenu}</p>

<table style="width:750px;margin:0 auto;">
  <tr><th>Destinataires choisis:</th></tr>
  <!-- BEGIN retourantennes -->
    {debut}<td><input type="checkbox" name="{nomant}" value="{nomant}" {antenne}{antenne_val} id="{nomant}" /><label for="{nomant}">{nomant}</label></td>{fin}
  <!-- END retourantennes -->
   <tr><td><input type="checkbox" name="toresp" value="respAnt" {respAnt}{respAnt_val} id="toresp" /><label for="toresp">Tous les responsables</label></td></tr>  
</table>

<form method="post" action="{script_formu}">
<p>
  <input type = "submit" name="corrigerNews" value="corriger la news" />
  <input type = "submit" name="testerNews" value="Test: envoyer la news à soi-même" /> 
  <input type = "submit" name="submitNews" value="enregistrer la news" />  <br />
<!-- BEGIN retourdata -->
  <input type="hidden" name ="{nomdata}" value ="{valdata}" /><br />
<!-- END retourdata -->
</p>
</form>





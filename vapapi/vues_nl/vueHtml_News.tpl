<p style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;"> 
  afzender: {expediteur} 
</p>
<p style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;"> 
  voorwerp: {sujet} 
</p>
<div style="background-color:{couleurfond};color:{couleurpolice};width:450px;border:1px solid black;margin:0 auto;">
  <p class="newsleft"><img src="{url_logo}" alt="logo"/></p><br class="nettoyeur" />
  {htmlcont}
  <p style="font-size:0.88em;">
    <a style="color:black;" href="{url_cms}">Niet actief in deze fase: dit nieuwsbrief online</a><br />
    <a style="color:black;" href="{url_desinscrit}">Niet actief in deze fase:  klick hier om zich uitschrijven</a>
  </p>
</div>
<p style="width:450px;border:1px solid black;margin-left: auto;margin-right:auto;margin-bottom:10px;"> tekstinhoud {altcontenu}</p>

<table style="width:750px;margin:0 auto;">
  <tr><th>De gekozen bestemmelingen zijn :</th></tr>
  <!-- BEGIN retourantennes -->
    {debut}<td><input type="checkbox" name="{nomant}" value="{nomant}" {antenne}{antenne_val} id="{nomant}" /><label for="{nomant}">{nomant}</label></td>{fin}
  <!-- END retourantennes -->
   <tr><td><input type="checkbox" name="toresp" value="respAnt" {respAnt}{respAnt_val} id="toresp" /><label for="toresp">Alle antenne lieder</label></td></tr>  
</table>

<form method="post" action="{script_formu}">
<p>
  <input type = "submit" name="corrigerNews" value="nieuws verbeteren" />
  <input type = "submit" name="testerNews" value="test nieuws: zichzelf dit nieuws toezenden" /> 
  <input type = "submit" name="submitNews" value="nieuws registreren" />  <br />
<!-- BEGIN retourdata -->
  <input type="hidden" name ="{nomdata}" value ="{valdata}" /><br />
<!-- END retourdata -->
</p>
</form>





<p class="identifier">{it_pre} {it_nom}, welkom op VAP-ITER, Bibliotheek paden voorgesteld door de leden </p>
<p class="identifier"><strong>Door het registreren van uw reis, machtigt u elke geïnteresseerde leden om u met e-mail contact op te nemen</strong><br />
kunt u altijd wijzigen / verwijderen uw route</p>

<form method="post"  action="{script_formu}">
  <fieldset><legend>Ik schrijf een trajekt erin</legend> 
  <p class="perso"><strong> * Deal :verplicht </strong></p>
  {deal}
  <p class="perso">
    <strong>* Gemeente </strong><br />
    <label for="t_com"><strong>bestemming </strong></label><input type="text" name="commune" id="t_com" size="30" maxlength="30" value = "{commune}" />
    <label for="t_dest"><strong>meer precies :</strong></label><input type="text" name="destination" id="t_dest" size="50" maxlength="50" value = "{destination}" />
  </p>
  <p class="perso"> {preSelect} {select} <br />   
    <label for="t_lieu"><strong>{labPass}</strong></label><br/><input type="text" name="passage" id="t_lieu" size="50" maxlength="50" value = "{passage}" />
  </p>  
  <p class="perso">
    <label for="periode"><strong>schema en / of de frequentie van de reizen (fakultatief)</strong></label><br />
    <textarea rows="3" cols="30" name="periode" id="periode">{periode}</textarea>
  </p>
  <p class="perso"><input type="submit" value="bevestigen" /></p>
  {cachez}  
</fieldset>    
</form>

<p class="identifier">{it_pre} {it_nom}, bienvenue  sur VAP-ITER, la bibliothèque des trajets proposés par les membres  </p>
<p class="identifier"><strong>En inscrivant votre trajet, vous autorisez tout membre VAP intéressé à vous contacter par mail</strong><br />
vous pourrez à tout moment modifier/supprimer votre trajet</p>

<form method="post"  action="{script_formu}">
  <fieldset><legend>Je rentre un trajet</legend> 
  <p class="perso"><strong> * Deal :obligatoire </strong></p>
  {deal}
  <p class="perso">
    <strong>* Commune </strong><br />
    <label for="t_com"><strong>de destination:</strong></label><input type="text" name="commune" id="t_com" size="30" maxlength="30" value = "{commune}" />
    <label for="t_dest"><strong>Plus précisément à :</strong></label><input type="text" name="destination" id="t_dest" size="50" maxlength="50" value = "{destination}" />
  </p>
  <p class="perso"> {preSelect} {select} <br />   
    <label for="t_lieu"><strong>{labPass}</strong></label><br/><input type="text" name="passage" id="t_lieu" size="50" maxlength="50" value = "{passage}" />
  </p>  
  <p class="perso">
    <label for="periode"><strong>horaire et/ou fréquence du trajet (facultatif)</strong></label><br />
    <textarea rows="3" cols="30" name="periode" id="periode">{periode}</textarea>
  </p>
  <p class="perso"><input type="submit" value="Valider" /></p>
  {cachez}  
</fieldset>    
</form>

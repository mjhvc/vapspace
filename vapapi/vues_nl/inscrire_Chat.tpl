<p class="identifier"><a href="{chatAntenne}">Vap-Chat per maand in {antenne}</a></p>
<fieldset id="formu">
  <legend>Een chat op {antenne} posten</legend>
  <p class="formuchatintro"> 
    Dag <span> {pseudo} uit {vapattache} </span>, <br />
	  De VAP-team biedt u een gelegenheid  om informatie met mobiliteit te delen. <br />
		Gepubliceerd inhoud is uw eigen verantwoordelijkheid  en houdt zich niet bezig onze vereniging. <br />
		Elk misbruik zal worden verwijderd.
  </p>
  <form method="post" action="{script_formu}">
    <p class="formusujet"><label for="sujet"><strong>{sujet_o} Onderwerp: </strong>(90 tekens)</label><input type="text" name="sujet" id="sujet" size="{sujet_s}" maxlength="{sujet_s}" value="{sujet}" /></p>
    <p class="formumess"><label for="message"><strong>{message_o} Boodschap : </strong>(900 tekens)</label><textarea name="message" id="message" cols="70" rows="10">{message}</textarea> </p>
    <p class="formubouton"><input type="submit" value="Bekijken" name="chatprevue" /><input type="submit" name="chatpost" value="Verzenden" /></p>
    <p class="formucache">
      <input type="hidden" name="pseudo" value="{pseudo}" />
		  <input type="hidden" name="vapattache" value="{vapattache}" />
		  <input type="hidden" name="lienant" value="{lienant}" />
		  <input type="hidden" name="idpere" value="{idpere}" />
		  <input type="hidden" name="lienhum" value="{lienhum}" /> 
		  <input type="hidden" name="vaputile" value="{bis_vaputile}" />
		  <input type="hidden" name="chatmail" value="{chatmail}" /> 
    </p>
  </form>
</fieldset>	
<br class="nettoyeur"/>	


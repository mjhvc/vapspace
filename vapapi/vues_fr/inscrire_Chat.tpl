<p class="identifier"><a href="{chatAntenne}">Le VapChat mensuel à {antenne}</a></p>
<fieldset id="formu">
  <legend>Poster sur {antenne}</legend>
  <p class="formuchatintro"> 
    Bonjour <span> {pseudo} de  {vapattache} </span>, <br />
	  L'équipe des VAP vous propose ce forum entre membres pour échanger vos informations autour du système d'autostop proposé. <br />
    Depuis peu, un outil dédié aux trajets par autostop des membres est disponible, il permet : <br />
    - <a href="{urlOffre}">la consultation de l'offre de trajets à {antenne}</a><br />
    - <a href="{urlDemande}">la consultation de la demande de trajets {antenne} </a><br />
    - <a href="{urlInsItTer}">de proposer un trajet dans votre antenne : {vapattache}</a><br />
    Cet outil permet un contact par mail entre membres sur base des trajets proposés.<br />
		Le contenu publié sur ce forum est de votre responsabilité et n'engage pas notre asbl. <br />
		Tout usage abusif sera supprimé.
  </p>
  <form method="post" action="{script_formu}">
    <p class="formusujet"><label for="sujet"><strong>{sujet_o} Sujet: </strong>(90 caractères)</label><input type="text" name="sujet" id="sujet" size="{sujet_s}" maxlength="{sujet_s}" value="{sujet}" /></p>
    <p class="formumess"><label for="message"><strong>{message_o} Message : </strong>(900 caractères)</label><textarea name="message" id="message" cols="70" rows="10">{message}</textarea> </p>
    <p class="formubouton"><input type="submit" value="Prévisualiser" name="chatprevue" /><input type="submit" name="chatpost" value="Poster" /></p>
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



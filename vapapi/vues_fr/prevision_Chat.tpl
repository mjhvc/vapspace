<ul>
  <li class="cadreforum">
    <div class=forumtete> 
      <span>Le {tampon} &nbsp;&nbsp; {pseudo},&nbsp;&nbsp; antenne: {vapattache} &nbsp;&nbsp;&nbsp; {vaputile} </span>
    </div>
    <p class="sujetforum"><span>Sujet : </span> {sujet} </p>
    <p class="forum"><span>Message : </span> {message} </p>  
    <p class="forumqueue">Répondre</p>
  </li>
</ul>
<form method="post" action="{script_formu}">
<p class="formubouton"><input type="submit" value="Corriger" name="chatcorr" /><input type="submit" name="chatpost" value="Poster" /></p>
<p class="formucache">
  <input type="hidden" name="sujet" value="{sujet}" />
  <input type="hidden" name="message" value="{message}" />
  <input type="hidden" name="pseudo" value="{pseudo}" />
  <input type="hidden" name="vapattache" value="{vapattache}" />
  <input type="hidden" name="lienant" value="{lienant}" />
	<input type="hidden" name="idpere" value="{idpere}" />
	<input type="hidden" name="lienhum" value="{lienhum}" /> 
	<input type="hidden" name="vaputile" value="{bis_vaputile}" />
	<input type="hidden" name="chatmail" value="{chatmail}" /> 
</p>

<div id="vapspace">
  <div id="dataPerso">
    <h3 class="space">Mes données VAP</h3>
      <p><a href="{urlProfil}">Mon profil VAP </a></p>
      <p><a href="{urliterperso}">{iterperso}</a></p>
      <p><a href="{urlchatperso}">{chatperso}</a></p>
    <h3 class="space">Recommander les VAP</h3>   
      <form id="promoVap"><p>
        J'encode un, deux, trois courriels et un message éventuel, VAP leur enverra un mail.<br /> 
        <label for="promoUn">Premier courriel : </label><br /><input type="text" name="promoUn" id="promoUn" size="18" maxlength="60" value="" /><br />
        <label for="promoDeux">Second courriel : </label><br /><input type="text" name="promoDeux" id="promoDeux"  size="18" maxlength="60" value="" /><br />
        <label for="promoTrois">Dernier courriel : </label><br /><input type="text" name="promoTrois" id="promoTrois"  size="18" maxlength="60" value="" />
        <label for="promoMsg">Un message :</label><textarea name="promoMsg" id="promoMsg"></textarea>
        <input id="promoGo" type="button" value="Recommander" />
      </p></form>
      <p id="charge"><img src="photos/chargeur.gif" alt=' ' /></p>
    <h3 class="space">À propos des VAP</h3>
    <p><a href="{urlIter}">Documentation</a></p> 
    <p id="statistiques"><a href="#statistiques" class="statreg">Chiffres par Régions</a></p>    
    <ul class="sreg">
      <!-- BEGIN STATISTIQUES --> 
        <li class="slireg"><a href="{urlRegStats}">{nomreg}</a></li>
       <!-- END STATISTIQUES -->
    </ul>       
    <p><a href="{urlDelMbr}" onclick="return confirm('Je confirme ma désincription du réseau VAP ');">Me désinscrire des VAP</a></p>  
  </div>
  <div id="bibIter">
  {tabIter} 
   <p><a href="{urlTrajet}">Inscrire un nouveau trajet</a></p>  
  </div>
  <div id="nav"> 
    <div id="menuMembre">
      <h3 class="space">Membres VAP</h3>
      <ul class="Membre">
        <li class="princ"><a href="{vue_mbr}">à {nomantenne}</a></li>
        {listeAntennes}
      </ul>
    </div>       
    <div id="menuChat">
      <h3 class="space">Vap-chat:{nomantenne}</h3>
      <ul class="navChat">
        <li><a href="#" class="aChat">Derniers Chats : </a></li>
        <!-- BEGIN vapchats -->
        <li class="liChat"><a href="{urlchat}">{sujetchat}</a></li>
        <!-- END vapchats -->
        <li><a href="{url_chatMens}">{chatMens}</a></li>
        <li><a href="{url_chatGlb}">{chatGlob}</a></li>
        <li><a href="{url_chatPost}">Poster un Chat </a></li>
      </ul>
    </div>
  </div>
</div>
 


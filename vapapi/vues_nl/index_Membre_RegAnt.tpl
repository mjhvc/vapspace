<div id="vapspace">
  <div id="dataPerso"> 
    <h3 class="space">Mijn VAP gegevens</h3>
    <p><a href="{urlProfil}">Mijn VAP-profiel</a></p>
    <p><a href="{urliterperso}">{iterperso}</a></p>
    <p><a href="{urlchatperso}">{chatperso}</a></p>
    <p><a href="{urlCarte}">Een persoonlijke lift-kaart</a></p>
    <h3 class="space"> De VAP bevelen</h3>   
      <form id="promoVap"><p>
        Ik vermeld een, twee, drie e-mails met een mogelijke boodschap, VAP zal ze een e-mail sturen.<br /> 
        <label for="promoUn">Eerste E-Mail : </label><br /><input type="text" name="promoUn" id="promoUn" size="15" maxlength="60" value="" /><br />
        <label for="promoDeux">Tweede E-Mail : </label><br /><input type="text" name="promoDeux" id="promoDeux"  size="15" maxlength="60" value="" /><br />
        <label for="promoTrois">Laatste E-Mail: </label><br /><input type="text" name="promoTrois" id="promoTrois"  size="15" maxlength="60" value="" /><br />
        <label for="promoMsg">Een boodschap:</label><textarea name="promoMsg" id="promoMsg"></textarea>
        <input id="promoGo" type="button" value="aanbevelen" />
      </p></form>
      <p id="charge"><img src="photos/chargeur.gif" alt=' ' /></p>
    <h3 class="space">Over VAP</h3>
    <p><a href="{urlIter}">Documentatie</a></p> 
    <p id="statistiques"><a href="#statistiques" class="statreg">Chijfers per gebied</a></p>    
    <ul class="sreg">
      <!-- BEGIN STATISTIQUES --> 
        <li class="slireg"><a href="{urlRegStats}">{nomreg}</a></li>
       <!-- END STATISTIQUES -->
    </ul>       
    <p><a href="{urlDelMbr}" onclick="return confirm(' Ik bevestig mijn uitschrijven uit het VAP-netwerk ');">Ik wil me uitschrijven uit VAP</a></p> 
  </div>
  <div id="bibIter">
  {tabIter} 
   <p><a href="{urlTrajet}">Een nieuw traject opgeven</a></p>  
  </div>
  <div id="nav"> 
    <div id="menuMembre">
      <h3 class="space">VAP leden</h3>
      <ul class="Membre">
        <li class="princ"><a href="{vue_mbr}">in {nomantenne}</a></li>
        <li class="firstReg"><a href="#" class="aReg">Andere antenne:</a> 
          <ul class="navReg">
          <!-- BEGIN regions -->    
            <li class="liReg"><a href="#">{listeregion}</a>
              <ul class="navAnt">
                {newListeAnt} 
              </ul>   
            </li>    
          <!-- END regions -->
          </ul>
        </li>
      </ul>
    </div>       
    <div id="menuChat">
      <h3 class="space">Vap-chat:{nomantenne}</h3>
      <ul class="navChat">
        <li><a href="#" class="aChat">laatste chats :</a></li>
        <!-- BEGIN vapchats -->
        <li class="liChat"><a href="{urlchat}">{sujetchat}</a></li>
        <!-- END vapchats -->
        <li><a href="{url_chatMens}">{chatMens}</a></li>
        <li><a href="{url_chatGlb}">{chatGlob}</a></li>
        <li><a href="{url_chatPost}">Een chat posten</a></li>
      </ul>
    </div>
  </div>
</div>

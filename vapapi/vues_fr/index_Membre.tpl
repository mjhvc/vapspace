<div id="vapspace">
  <div id="dataPerso">
    <h3 class="space">Cartes VAP</h3>
    <p><a href="{urlCarte}">Une carte perso pour chaque destination</a></p>   
    <h3 class="space">Mes données VAP</h3>
    <p><a href="{urlProfil}">Mon profil VAP </a></p>
    <p><a href="{urliterperso}">{iterperso}</a></p>
    <p><a href="{urlchatperso}">{chatperso}</a></p>
    <p><a href="{urlIter}">Explications</a></p> 
    <p><a href="{urlDelMbr}" onclick="return confirm('Je confirme ma désincription du réseau VAP ');">Me désinscrire des VAP</a></p> 
  </div>
  <div id="bibIter">
  {tabIter} 
   <p><a href="{urlTrajet}">Inscrire un nouveau trajet</a></p>  
  </div>
  <div id="nav"> 
    <div id="menuMembre">
      <h3 class="space">Liste de membres VAP</h3>
      <ul class="Membre">
        <li class="princ"><a href="{vue_mbr}">à {nomantenne}</a></li>
        <li class="firstReg"><a href="#" class="aReg">Autres antennes:</a> 
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
 


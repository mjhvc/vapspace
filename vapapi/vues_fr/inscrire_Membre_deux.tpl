<form method="post"  action="{choix_script}">
<fieldset><legend>Page Deux: les données propres au réseau Vap</legend>  
  <p class="vap"><label for="lienant"><strong>{lienant_o} Mon antenne VAP :</strong></label> {select}</p>
  <p class="vap"><strong>{inscrit_o} Je m'inscris comme:</strong>
      <input type="radio" name="inscrit" value="pieton" id="pieton" {pietonCheck} {pietonCheck_val} /><label for="pieton">Piéton</label>
      <input type="radio" name="inscrit" value="auto" id="automobiliste"  {autoCheck} {autoCheck_val} /><label for="automobiliste">Automobiliste</label>
      <input type="radio" name="inscrit" value="deux" id="deux"  {deuxCheck} {deuxCheck_val} /><label for="deux">Les Deux </label>
  </p>
  <p class="uno"><strong>{securite_o} Sécurité : </strong></p>
  <p class ="duo"><input type="checkbox" name="securite" value="oui" id="securite" {securiteCheck}{securiteCheck_val} />&nbsp;<label for="securite">J'ai lu et j'approuve <a href="http://www.vap-vap.be/spip.php?article77">les règles de sécurité</a></label></p>         
  <p class="vap">{intro_passe}</p>  
  <p class="uno">  <label for="passe"><strong>{passe_o} Mot de Passe :</strong></label><p>
  <p class="duo"><input type="password" name="passe" id="passe" size="15" maxlength="12" value="{passe}" /> 
   &nbsp; <label for="confirmation"><strong>{passe_o} Confirmation :</strong></label><input type="password" name="confirmation" id="confirmation" size="15" maxlength="12"  value="{confirmation}" />
  </p>  
  {spam}
  <p class="uno"><label for="plaque">{plaque_o} <strong>Plaque du véhicule:</strong> </label></p>
  <p class="duo"><input type="text" name="plaque" value="{plaque}" size="{plaque_s}" maxlenght="{plaque_s}" /></p>    
  <p class="perso"><label for="connu"><strong>{connu_o} J'ai connu les VAP:</strong></label><input type="text" name="connu" id="connu" size="{connu_s}" maxlength="{connu_s}"  value="{connu}" /></p>
  {multiRecomm}
  <p class="vap">
    <input type="checkbox" name="fiche" value="oui" id="ficheoui"  {ficheCheck}{ficheCheck_val} /><label for="ficheoui">Fiche: Mes nom, prénom sont affichables dans l'espace membre de mon antenne</label><br />
    <input type="checkbox" name="news" id="newsoui"  value="oui"  {newsCheck}{newsCheck_val} /><label for="newsoui">Je m'inscris à la newsletter</label>    
  </p>  
  {statuts}  
</fieldset>
{multiChoice}
<p class="pagi">
  <input type="submit" value="Valider {type}" /> <br />     
  <input type="hidden" name="nom"  value="{nom}" /><br /> 
  <input type="hidden" name="prenom"  value="{prenom}" /><br />
  <input type="hidden" name="mail"  value="{mail}" /><br />
  <input type="hidden" name="adresse" value="{adresse}" /><br />
  <input type="hidden" name="code"  value="{code}" /><br /> 
  <input type="hidden" name="ville"  value="{ville}" /><br />
  <input type="hidden" name="tel"  value="{tel}" /><br />  
  <input type="hidden" name="naissance"   value="{naissance}" />
  <input type="hidden" name="lienreg"  value="{lienreg}" />
  <input type="hidden" name="lang" value="{lang}" />
  {cachez}  
</p>      
</form>

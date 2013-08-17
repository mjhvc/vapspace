<form method="post"  action="{choix_script}">
  <p class="poste">{intro}</p>
  <p >Les champs qui commencent par * sont obligatoires.</p>
  <fieldset><legend> Données personnelles: </legend>
    <p class="perso">Attention, votre courriel convient pour une et une seule inscription ! </p>
    <p class="perso"><label for="nom"><strong>{nom_o} Nom :</strong></label><input type="text" name="nom" id="nom" size="{nom_s}" maxlength="{nom_s}" value="{nom}" /></p> 
    <p class="perso"><label for="prenom"><strong>{prenom_o} Prénom :</strong></label><input type="text" name="prenom" id="prenom" size="{prenom_s}" maxlength="{prenom_s}" value="{prenom}" /></p>
    <p class="perso"><label for="mail"><strong>{mail_o} Courriel :</strong></label><input type="text" name="mail" id="mail" size="{mail_s}" maxlength="{mail_s}" value="{mail}" /></p>
    <p class="perso">{intro_passe}</p>       
    <p class="uno">  <label for="passe"><strong>{passe_o} Mot de passe :</strong></label><p>
    <p class="duo"><input type="password" name="passe" id="passe" size="15" maxlength="12" value="{passe}" /> 
      &nbsp;<label for="confirmation"><strong>{passe_o} Confirmation :</strong></label><input type="password" name="confirmation" id="confirmation" size="15" maxlength="12" value="{confirmation}" />
    </p>
    
    <p class="perso"><label for="adresse"><strong>{adresse_o} Adresse :</strong></label><input type="text"name="adresse" id="adresse" size="{adresse_s}" maxlength="{adresse_s}" value="{adresse}" /></p>
    <p class="uno"><label for="code"><strong>{code_o} Code postal:</strong></label></p>
    <p class="duo"><input type="text" name="code" id="code" size="{code_s}" maxlength="{code_s}" value="{code}" />&nbsp;<label for="ville"><strong>{ville_o} Localité :</strong></label><input type="text" name="ville" id="ville" size="{ville_s}" maxlength="{ville_s}" value="{ville}" /></p>
    <p class="uno"><label for="tel"><strong>{tel_o} Téléphone : </strong></label></p>
    <p class="duo"><input type="text" name="tel" id="tel" size="{tel_s}" maxlength="{tel_s}" value="{tel}" />&nbsp;<label for="naissance"><strong>{naissance_o} Année de naissance: </strong><small>4 chiffres</small></label><input type="text" name="naissance" id="naissance" size="{naissance_s}" maxlength="{naissance_s}" value="{naissance}" /></p>
  </fieldset>
  <fieldset><legend>Mon inscription dans le réseau VAP : </legend>
    <p class="vap"><label for="lienant"><strong>{lienant_o} Votre antenne VAP :</strong></label> {select}</p>
    <p class="vap"><strong>{inscrit_o} Mon usage VAP:</strong>
      <input type="radio" name="inscrit" value="pieton" id="pieton" {pietonCheck} {pietonCheck_val} /><label for="pieton">Piéton</label>
      <input type="radio" name="inscrit" value="auto" id="automobiliste"  {autoCheck} {autoCheck_val} /><label for="automobiliste">Automobiliste</label>
      <input type="radio" name="inscrit" value="deux" id="deux"  {deuxCheck} {deuxCheck_val} /><label for="deux">Les Deux </label>
    </p>
    <p class="perso"><strong>{securite_o} Sécurité: </strong><input type="checkbox" name="securite" value="oui" id="securite"  {securiteCheck} {securiteCheck_val} /><label for="securite">J'ai lu et j'approuve <a href="http://www.vap-vap.be/spip.php?article77">les règles de sécurité</a></label></p>
    <p class="perso"><label for="connu"><strong>{connu_o} J'ai connu les VAP:</strong></label><input type="text" name="connu" id="connu" size="{connu_s}" maxlength="{connu_s}" value="{connu}" /></p>
    <p class="perso"><strong>{plaque_o} Plaque du véhicule: </strong><input type="text" name="plaque" value="{plaque}" size="{plaque_s}" maxlenght="{plaque_s}"/> </p>
    <p class="vap">
      <input type="checkbox" name="fiche" value="oui" id="ficheoui"  {ficheCheck}{ficheCheck_val} /><label for="ficheoui">Fiche: Mes nom, prénom sont affichables dans l'espace membre de mon antenne</label><br />
      <input type="checkbox" name="news" value="oui" id="newsoui"   {newsCheck}{newsCheck_val} /><label for="newsoui">Je m'inscris à la newsletter</label>    
    </p>
    {spam}
    {statuts}
  </fieldset>
  {multiChoice}
  <p>
    <input type="submit" value="{type}" />  
    <input type="hidden" name="lienreg" value="{lienreg}" /> 
    {cachez}      
  </p>      
</form>

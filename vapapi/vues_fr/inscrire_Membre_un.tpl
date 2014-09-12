<form method="post"  action="{choix_script}">
  <p class="identifier">{intro}</p>
  <p class="identifier">Les champs qui commencent par * sont obligatoires.</p>
  <fieldset><legend>Page Un: les données personnelles </legend>
    <p class="perso"><label for="genre"><strong>{genre_o} Civilité : </strong></label> {civil} </p>
    <p class="perso"><label for="nom"><strong>{nom_o} Nom :</strong></label><input type="text" name="nom" id="nom" size="{nom_s}" maxlength="{nom_s}" value="{nom}" /></p> 
    <p class="perso"><label for="prenom"><strong>{prenom_o} Prénom :</strong></label><input type="text" name="prenom" id="prenom" size="{prenom_s}" maxlength="{prenom_s}" value="{prenom}" /></p>
    <p class="perso"><label for="mail"><strong>{mail_o} Courriel :</strong></label><input type="text" name="mail" id="mail" size="{mail_s}" maxlength="{mail_s}" value="{mail}" /></p>       
    <p class="perso"><label for="adresse"><strong>{adresse_o} Adresse :</strong></label><input type="text" name="adresse" id="adresse" size="{adresse_s}" maxlength="{adresse_s}" value="{adresse}" /></p>
    <p class="uno"><label for="code"><strong>{code_o} Code postal:</strong></label></p>
    <p class="duo">
      <input type="text" name="code" id="code" size="{code_s}" maxlength="{code_s}" value="{code}" /> &nbsp;
      <label for="ville"><strong>{ville_o} Localité :</strong></label><input type="text" name="ville" id="ville" size="{ville_s}" maxlength="{ville_s}" value="{ville}" />
    </p>
    <p class="uno"><label for="tel"><strong>{tel_o} Téléphone : </strong></label></p>
    <p class="duo">
      <input type="text" name="tel" id="tel" size="{tel_s}" maxlength="{tel_s}" value="{tel}" /> &nbsp;
      <label for="naissance"><strong>{naissance_o} Année de naissance: </strong> 4 chiffres</label><input type="text" name="naissance" id="naissance" size="{naissance_s}" maxlength="{naissance_s}" value="{naissance}" />
    </p>   
    <p class="uno">{lang_o} <strong>Langue choisie :</strong></p>
    <p class="duo">  
      <label for="fr"><strong>FR : </strong></label><input type="radio" name="lang" value="fr" id="fr" {frCheck} {frCheck_val} />&nbsp;      
      <label for="nl"><strong>NL : </strong></label><input type="radio" name="lang" value="nl" id="nl" {nlCheck} {nlCheck_val} />      
    </p>
    <p class="poste"> <strong>Choix de la Region/Province VAP</strong></p>
    <p class="poste"><strong>{introReg}</strong></p>
    <p>
      <!-- BEGIN choixReg --> 
        <input type="radio" name="lienreg" value="{clereg}" id="a{clereg}" {regionCheck}{regionCheck_val} /><label for="a{clereg}"><strong>{nomreg}</strong></label><br />         
      <!-- END choixReg -->
    </p>
    </fieldset>
    <p class="pagi">
      <input type="submit" value="{type}" />  
      {cachez}
    </p>      
</form>
 

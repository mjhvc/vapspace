<form method="post"  action="{choix_script}">
  <p class="poste">{intro}</p>
  <p>De vakken voorafgegaan door een * moeten verplicht ingevuld worden.</p>
  <fieldset><legend>persoonsgegevens : </legend>
    <p class="perso">Opgelet: slechts één inschrijving is mogelijk per e-mailadres. </p>
    <p class="perso"><label for="genre"><strong>{genre_o} Geslacht : </strong></label> {civil} </p>
    <p class="perso"><label for="nom"><strong>{nom_o} Naam :</strong></label><input type="text" name="nom" id="nom" size="{nom_s}" maxlength="{nom_s}" value="{nom}" /></p> 
    <p class="perso"><label for="prenom"><strong>{prenom_o} Voornaam :</strong></label><input type="text" name="prenom" id="prenom" size="{prenom_s}" maxlength="{prenom_s}" value="{prenom}" /></p>
    <p class="perso"><label for="mail"><strong>{mail_o} E-mailadres: </strong></label><input type="text" name="mail" id="mail" size="{mail_s}" maxlength="{mail_s}" value="{mail}" /></p>
    <p class="perso">{intro_passe}</p>       
    <p class="uno">  <label for="passe"><strong>{passe_o} Paswoord :</strong></label><p>
    <p class="duo"><input type="password" name="passe" id="passe" size="15" maxlength="12" value="{passe}" /> 
      &nbsp;<label for="confirmation"><strong>{passe_o} Bevestiging paswoord :</strong></label><input type="password" name="confirmation" id="confirmation" size="15" maxlength="12" value="{confirmation}" />
    </p>
    
    <p class="perso"><label for="adresse"><strong>{adresse_o} Adres :</strong></label><input type="text" name="adresse" id="adresse" size="{adresse_s}" maxlength="{adresse_s}" value="{adresse}" /></p>
    <p class="uno"><label for="code"><strong>{code_o} Postcode:</strong></label></p>
    <p class="duo"><input type="text" name="code"id="code" size="{code_s}" maxlength="{code_s}" value="{code}" />&nbsp;<label for="ville"><strong>{ville_o} Gemeente :</strong></label><input type="text" name="ville" id="ville" size="{ville_s}" maxlength="{ville_s}" value="{ville}" /></p>
    <p class="uno"><label for="tel"><strong>{tel_o} Telfoon : </strong></label></p>
    <p class="duo"><input type="text" name="tel" id="tel" size="{tel_s}" maxlength="{tel_s}" value="{tel}" />&nbsp;<label for="naissance"><strong>{naissance_o} Geboorte jaar: </strong><small>4 cijfers</small></label><input type="text" name="naissance" id="naissance" size="{naissance_s}" maxlength="{naissance_s}" value="{naissance}" /></p>
  </fieldset>
  <fieldset><legend>VAP gegeevens : </legend>
    <p class="vap"><label for="lienant"><strong>{lienant_o} Kies uw antenne :</strong></label> {select}</p>
    <p class="vap"><strong>{inscrit_o} Ik sluit aan als :</strong>
      <input type="radio" name="inscrit" value="pieton" id="pieton" {pietonCheck} {pietonCheck_val} /><label for="pieton">Voetganger</label>
      <input type="radio" name="inscrit" value="auto" id="automobiliste"  {autoCheck} {autoCheck_val} /><label for="automobiliste">automobilist</label>
      <input type="radio" name="inscrit" value="deux" id="deux"  {deuxCheck} {deuxCheck_val} /><label for="deux">beide </label>
    </p>
    <p class="perso"><strong>{securite_o} Veiligheid </strong><input type="checkbox" name="securite" value="oui" id="securite"  {securiteCheck} {securiteCheck_val} /><label for="securite">  Ik heb <a href="http://www.vap-vap.be/spip.php?article77">de veiligheidsconsignes gelezen en ga ermee akkoord</a></label></p>
    <p class="perso"><label for="connu"><strong>{connu_o} Ik heb VAP leren kennen br />langs :</strong></label><input type="text" name="connu" id="connu" size="{connu_s}" maxlength="{connu_s}" value="{connu}" /></p>
    <p class="perso"><strong>{plaque_o} Nummerplaat van de wagen : </strong><input type="text" name="plaque" value="{plaque}" size="{plaque_s}" maxlenght="{plaque_s}"/> </p>
    <p class="vap">
      <input type="checkbox" name="fiche" value="oui"  id="ficheoui"  {ficheCheck}{ficheCheck_val} /><label for="ficheoui">Mijn naam en voornaam mogen vermeld worden op de ledenlijst van mijn antenne</label><br />
      <input type="checkbox" name="news"  value="oui"  id="newsoui"   {newsCheck}{newsCheck_val} /><label for="newsoui">Ik schrijf in op de nieuwsbrief</label>    
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

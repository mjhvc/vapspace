<form method="post"  action="{choix_script}"> 
<fieldset><legend>Prefixe des tables sql</legend>
  <p>Avant d'installer les tables sql, merci de renseigner le préfixe souhaité pour ces tables</p>
  <p>
    <label for="installPre">Préfixe des tables</label>
    <input type="text" name="instPre" id="installPre" size="40" maxlength="40" value="{instPre}" />
  </p>
</fieldset> 
<fieldset><legend>* Choix du pays des membres</legend>
<p>
    <label for="installPays">Nom du Pays</label>
    <input type="text" name="instPays" id="installPays" size="40" maxlength="40" value="{instPays}" />
  </p>
</fieldset> 
<fieldset><legend>* Choix d'une antenne</legend>
  <p>Afin de vous inscrire, la table antenne doit contenir une antenne</p>
  <p>
    <label for="installAnt">Nom de l'antenne</label>
    <input type="text" name="instAnt" id="installAnt" size="40" maxlength="40" value="{instAnt}" />
  </p>
</fieldset> 
<fieldset><legend>Choix de la région</legend>
  <p>Cette antenne doit-elle faire partie d'une Région/Province?<br />Si non, laisser vide</p>
  <p>
    <label for="installreg">Nom de la Région/Province</label>
    <input type="text" name="instReg" id="installReg" size="40" maxlength="40" value="{instReg}" />
  </p>
</fieldset> 
<fieldset><legend>Inscription hors-antenne?</legend>
  <p>Décochez ce choix si le système ne doit pas prendre en charge l'inscription de membres hors antenne</p>  
  <p>
    <input type="checkbox" name="instHa" id="instHa" value="oui" {checkHa}{checkHaVal} /><label for="instHA">Oui, créer une antenne pour les 'hors-antenne'</label>
  </p>
</fieldset> 

<fieldset><legend>* Identifier un nouveau membre?</legend>
<p>Ce numero de membre permettra d'identifier un membre récemment inscrit en ligne afin de lui envoyer son kit membre</p> 
<p>
    <label for="installMbr">Nouveau Membre</label>
    <input type="text" name="instMbr" id="installMbr" size="40" maxlength="40" value="{instMbr}" />
  </p>
</fieldset> 
<fieldset><legend>* Choix d'un courriel de contact général</legend>
<p>Le système nécéssite une adresse mail générale de contact </p> 
<p>
    <label for="installcontact">Contact mail</label>
    <input type="text" name="instContact" id="installContact" size="40" maxlength="40" value="{instContact}" />
</p>
</fieldset> 

<fieldset><legend>Choix de sociétés de transports</legend>
  <p>Souhaitez-vous installer un formulaire de sondage sur l'utilisation des transports en communs en fin d'inscription?</p>
  <p>Si oui, merci de fournir ici les noms des sociétés de transport cibles du sondage</p>
  <p>Si non, laisser vide</p>
  <p>Noms de(s) société(s), séparées par une virgule si plusieurs</p>
  <p><input type="text" name="instSoc" id="instSoc" size="40" maxlength="40" value="{instSoc}" /></p>
</fieldset>
<p>
  <input type="submit" name="okCnx"  value="Suivant" />      
  </p>      
</form>


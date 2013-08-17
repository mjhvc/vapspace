<form method="post"  action="{choix_script}">
<p class="perso">Connexion à votre base de données </p>
<p class="perso">
  Consultez les informations fournies par votre hébergeur : <br />
  vous devez y trouver le serveur de base de données qu'il propose et vos identifiants personnels pour vous y connecter.
</p>
<fieldset><legend>Adresse de la base de donnée </legend>
  <p>
    (Souvent cette adresse correspond à celle de votre site, parfois elle correspond à la mention «localhost», parfois elle est laissée totalement vide.)<br />
    <input type="text" name="dbhost" id="dbhost" size="40" maxlength="40" value="localhost" />
  </p>
</fieldset> 
<fieldset><legend>Nom de la base de donnée </legend>
  <p>
    (Une base de donnée doit exister au préalable)<br />
    <input type="text" name="dbname" id="dbname" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<fieldset><legend>Le login de connexion</legend>
  <p>
   (Correspond parfois à votre login d'accès au FTP; parfois laissé vide)<br />
    <input type="text" name="dbuser" id="dbuser" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<fieldset><legend>Le mot de passe de connexion</legend>
  <p>
    (Correspond parfois à votre login d'accès au FTP; parfois laissé vide)<br />
    <input type="password" name="dbpass" id="dbpass" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<p>
  <input type="submit" name="iCnx" value="Suivant" />      
  </p>      
</form>

<form method="post"  action="{choix_script}">
<p class="perso">Connexion � votre base de donn�es </p>
<p class="perso">
  Consultez les informations fournies par votre h�bergeur : <br />
  vous devez y trouver le serveur de base de donn�es qu'il propose et vos identifiants personnels pour vous y connecter.
</p>
<fieldset><legend>Adresse de la base de donn�e </legend>
  <p>
    (Souvent cette adresse correspond � celle de votre site, parfois elle correspond � la mention �localhost�, parfois elle est laiss�e totalement vide.)<br />
    <input type="text" name="dbhost" id="dbhost" size="40" maxlength="40" value="localhost" />
  </p>
</fieldset> 
<fieldset><legend>Nom de la base de donn�e </legend>
  <p>
    (Une base de donn�e doit exister au pr�alable)<br />
    <input type="text" name="dbname" id="dbname" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<fieldset><legend>Le login de connexion</legend>
  <p>
   (Correspond parfois � votre login d'acc�s au FTP; parfois laiss� vide)<br />
    <input type="text" name="dbuser" id="dbuser" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<fieldset><legend>Le mot de passe de connexion</legend>
  <p>
    (Correspond parfois � votre login d'acc�s au FTP; parfois laiss� vide)<br />
    <input type="password" name="dbpass" id="dbpass" size="40" maxlength="40" value="" />
  </p>
</fieldset> 
<p>
  <input type="submit" name="iCnx" value="Suivant" />      
  </p>      
</form>

<fieldset><legend>Ecrire une Newsletter </legend>
<ul>La syntaxe xhtml est obligatoire: 
  <li>Titre: &lt;h1&gt;Titre&lt;/h1&gt; </li>
  <li>Sous-titre: &lt;h3&gt;Sous-titre&lt;/h3&gt;</li>
  <li>Une liste: &lt;ul&gt;Ent&ecirc;te de liste&lt;li&gt;&eacute;l&eacute;ment d'une liste&lt;/li&gt;&lt;/ul&gt;</li>
  <li>Paragraphe: &lt;p&gt;Un paragraphe&lt;/p&gt;</li>
  <li>Liens : &lt;a href=&quot;adresse internet&quot;&gt;texte du lien&lt;/a&gt;</li>
</ul>
<p>
Les liens sont toujours inclus dans un paragraphe <br />
Un retour de ligne forcé dans un paragraphe se fait avec la balise: &lt;br /&gt;
</p>

<form method="post" action="{script_formu}">
  <table>
    <tr><td><label for="expediteur">Expéditeur: </label></td><td><input type="text" name="expediteur" id="expediteur" size="40" maxlenght="40" value="{expediteur}" /></td></tr> 
    <tr><td><label for="sujet">Sujet: </label></td><td><input type="text" name="sujet" id="sujet" size="40" maxlenght="40" value="{sujet}" /></td></tr>
    <tr><td><label for="contenu">Contenu en xhtml: </label></td><td><textarea name="contenu" id="contenu" rows="20" cols="60">  {htmlcont} </textarea></td></tr>
    <tr><td><label for="couleurfond">Couleur de fond: </label></td><td><input type="text" name="couleurfond" id="couleurfond" size="15" maxlenght="15" value="{couleurfond}" /></td></tr>
    <tr><td><label for="couleurtitre">Couleur de titraille: </label></td><td><input type="text" name="couleurtitre" id="couleurtitre" size="15" maxlenght="15" value="{couleurtitre}" /></td></tr>
    <tr><td><label for="couleurpolice">Couleur des caractères: </label></td><td><input type="text" name="couleurpolice" id="couleurpol" size="15" maxlenght="15" value="{couleurpolice}" /></td></tr>
  </table>
  <table>
    <tr><th> * Envoyer la news aux membres Vap de : </th><th>&nbsp;</th><th>&nbsp;</th></tr>
    <!-- BEGIN antennes -->
    {debut}<td><input type="checkbox" name="{nomant}" value="{nomant}" {antenne}{antenne_val} id="{nomant}" /><label for="{nomant}">{nomant}</label></td>{fin}
    <!-- END antennes --> 
    <tr><td><input type="checkbox" name="toresp" value="respAnt" {respAnt}{respAnt_val} id="toresp" /><label for="toresp">Tous les responsables</label></td></tr> 
  </table>
  <p><input type = "submit" value="visionner la news" /> 
	<p><input type = "submit" name="fileNews" value="Télécharger un mailing de membres" /> </p> 
</form>
</fieldset>



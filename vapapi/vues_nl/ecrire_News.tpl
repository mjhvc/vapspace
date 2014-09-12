<fieldset><legend>Een nieuwsbrief schrijven </legend>
<p>XHTML syntax is verplicht, bvbd:</p>
<ul>
  <li>Een titel: &lt;h1&gt;Min titel&lt;/h1&gt; </li>
  <li>Een ondertitel: &lt;h3&gt;Mijn ondertitel:&lt;/h3&gt;</li>
  <li>een lijst: &lt;ul&gt;&lt;li&gt;Item in de lijst: &lt;/li&gt;&lt;/ul&gt;</li>
  <li>Paragraphe: &lt;p&gt;paragraaf&lt;/p&gt;</li>
  <li>Hyperlinks : &lt;a href=&quot;url&quot;&gt;tekst van de link&lt;/a&gt;</li>
</ul>
<p>
 Hyperlink moet binnen een paragraaf geschreven worden <br />
 Andere lijn in een paragraaf wordt: &lt;br /&gt;
</p>

<form method="post" action="{script_formu}">
  <table>
    <tr><td><label for="expediteur">Afzender: </label></td><td><input type="text" name="expediteur" id="expediteur" size="40" maxlenght="40" value="{expediteur}" /></td></tr> 
    <tr><td><label for="sujet">Onderwerp: </label></td><td><input type="text" name="sujet" id="sujet" size="40" maxlenght="40" value="{sujet}" /></td></tr>
    <tr><td><label for="contenu">Boodschap en xhtml: </label></td><td><textarea name="contenu" id="contenu" rows="20" cols="60">  {htmlcont} </textarea></td></tr>
    <tr><td><label for="couleurfond">Achtergrond kleur: </label></td><td><input type="text" name="couleurfond" id="couleurfond" size="15" maxlenght="15" value="{couleurfond}" /></td></tr>
    <tr><td><label for="couleurtitre">Kleur van de titels: </label></td><td><input type="text" name="couleurtitre" id="couleurtitre" size="15" maxlenght="15" value="{couleurtitre}" /></td></tr>
    <tr><td><label for="couleurpolice">Kleur van de tekens: </label></td><td><input type="text" name="couleurpolice" id="couleurpol" size="15" maxlenght="15" value="{couleurpolice}" /></td></tr>
  </table>
  <table>
    <tr><th> * De nieuwsbrief uitzenden naar leden van: </th><th>&nbsp;</th><th>&nbsp;</th></tr>
    <!-- BEGIN antennes -->
    {debut}<td><input type="checkbox" name="{nomant}" value="{nomant}" {antenne}{antenne_val} id="{nomant}" /><label for="{nomant}">{nomant}</label></td>{fin}
    <!-- END antennes --> 
    <tr><td><input type="checkbox" name="toresp" value="respAnt" {respAnt}{respAnt_val} id="toresp" /><label for="toresp">Alle Antenne verantwoordelijken</label></td></tr> 
  </table>
  <p><input type = "submit" value="News controleren" />
	<p><input type = "submit" name="fileNews" value="Mailing van Members download" /> </p>  
</form>
</fieldset>



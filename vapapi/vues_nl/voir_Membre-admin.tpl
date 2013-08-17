{pagination}
<table class="vapilote">
<tr><th>Mail</th><th>Naam</th><th>Voornaam</th><th>Lidnummer</th><th>{lienant_t}</th><th>Adres</th><th>{code_t}</th><th>Gemeente</th><th>{cachet_t}</th><th>{inscrit_t}</th><th>Aansluiting langs : </th>{manage}</tr>
<!-- BEGIN ligne -->
  <tr class="{cssline}"><td><a href="mailto:{mail}">{mail}</a></td><td>{nom}</td><td>{prenom}</td><td>{membre}</td><td>{Antenne}</td><td>{adresse}</td><td>{code}</td><td>{ville}</td><td>{cachet}</td><td>{inscrit}</td><td>{connu}</td><td><a href="{editer}">aanpassen</a></td><td><a href="{supprimer}" onclick="return confirm('Kunt u de verwijdering bevestigen ?');">Verwijderen</a></td></tr>
<!-- END ligne -->
</table>
{pagination}

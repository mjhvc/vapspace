{pagination}
<table class="vapilote">
<tr><th>{mail_t}</th><th>Naam</th><th>Voornaam</th><th>Lidnummer</th><th>{cachet_t}</th><th>Adres</th><th>Postcode</th><th>Gemeente</th><th>{inscrit_t}</th>{manage}</tr>
<!-- BEGIN ligne -->
  <tr class="{cssline}"><td><a href="mailto:{mail}">{mail}</a></td><td>{nom}</td><td>{prenom}</td><td>{membre}</td><td>{cachet}</td><td>{adresse}</td><td>{code}</td><td>{ville}</td><td>{inscrit}</td><td><a href="{editer}">edit</a></td><td><a href="{supprimer}" onclick="return confirm('unt u de verwijdering te bevestigen ? ?');">verwijderen</a></td></tr>
<!-- END ligne -->
</table>
{pagination}

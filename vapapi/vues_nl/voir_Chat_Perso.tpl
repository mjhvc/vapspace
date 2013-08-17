{pagination}
<table class="vapilote">
  <tr><th>Datum</th><th>Onderwerp</th><th>Boodschap</th><th>Aanpassen</th><th>Verwijderen</th></tr>
  <!-- BEGIN meschats -->
    <tr class="{cssline}"><td>{poste}</td><td>{sujet}</td><td>{message}</td><td><a href="{urlchatedit}">Edit</a></td><td><a href="{urldelchat}" onclick="return confirm('Verwijdering bevestigen ?');">Verwijderen</a></td></tr>
  <!-- END meschats -->
</table>
{pagination}

{pagination}
<table class="vapilote">
  <tr><th>Provincie</th><th>Gewest</th><th>Aanpassen</th><th>Verwijderen</th>
  <!-- BEGIN ligne -->
    <tr class="{cssline}"><td>{nom_province}</td><td>{nom_region}</td><td><a href="{editer}">aanpassen</a></td><td><a href="{supprimer}" onclick="return confirm('dit region verwijderen?');">Verwijdering</a></td></tr>
  <!-- END ligne -->
</table>
{pagination}

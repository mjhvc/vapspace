{pagination}
<table class="vapilote">
  <tr><th>Province</th><th>Region</th><th>Editer</th><th>Supprimer</th>
  <!-- BEGIN ligne -->
    <tr class="{cssline}"><td>{nom_province}</td><td>{nom_region}</td><td><a href="{editer}">edition</a></td><td><a href="{supprimer}" onclick="return confirm('Supprimer la région ?');">Suppression</a></td></tr>
  <!-- END ligne -->
</table>
{pagination}

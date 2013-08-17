{pagination}
<table class="vapilote">
  <tr><th>Société de Transports</th><th>Editer</th><th>Supprimer</th>
  <!-- BEGIN ligne -->
    <tr class="{cssline}"><td>{societes}</td><td><a href="{editer}">edition</a></td><td><a href="{supprimer}" onclick="return confirm('Supprimer la société ?');">Suppression</a></td></tr>
  <!-- END ligne -->
</table>
{pagination}

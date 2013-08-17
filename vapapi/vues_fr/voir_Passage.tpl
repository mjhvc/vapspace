{pagination}
<table class="vapilote"><caption>Points de rencontre à : {anten}</caption>
  <tr><th>Lieux : </th><th>Editer</th><th>Supprimer</th></tr>
  <!-- BEGIN ligne -->
    <tr class="{cssline}"><td>{lieu}</td><td><a href="{editer}">edition</a></td><td><a href="{supprimer}" onclick="return confirm('Supprimer ce point');">Suppression</a></td></tr>
  <!-- END ligne -->
</table>
{pagination}

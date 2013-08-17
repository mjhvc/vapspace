{pagination}
<table class="vapilote">
  <tr><th>Date</th><th>Sujet</th><th>Message</th><th>Editer</th><th>Supprimer</th></tr>
  <!-- BEGIN meschats -->
    <tr class="{cssline}"><td>{poste}</td><td>{sujet}</td><td>{message}</td><td><a href="{urlchatedit}">Editer</a></td><td><a href="{urldelchat}" onclick="return confirm('Supprimer ce chat ?');">Supprimer</a></td></tr>
  <!-- END meschats -->
</table>
{pagination}

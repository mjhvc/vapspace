<p> 
{fetchIntro}
Nieuwsbrieven reeds gepubliceerd zijn online beschikbaar: <br />
<a href="{urlPageNews}"> op de publieke site </a>
</p>

{pagination}
<table class="vapilote">
  <tr><th>Onderwerp</th><th>Begin</th><th>Edit</th><th>Uitzenden</th><th>Stoppen</th><th>Verwijderen</th></tr>
  <!-- BEGIN lettre -->
    <tr class="{cssline}">
      <td>{sujet}</td><td>{debut}...</td><td><a href="{editer}">EDIT</a></td><td><a href="{active}" onclick="return confirm('Dit nieuws brief uitzenden ?')">Uitzending</a></td><td><a href="{inactive}" onclick="return confirm('De verzending onderbreken ?')">ARRET</a></td>
      <td><a href="{suppress}" onclick="return confirm('Dit nieuws verwijderen ?')">Verwijderen</a></td>
    </tr>
  <!-- END lettre -->
</table>
{pagination}


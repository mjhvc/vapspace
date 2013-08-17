<p> 
{fetchIntro}
Les newsletters déjà diffusées sont visibles en ligne : <br />
<a href="{urlPageNews}"> sur le site public </a>
</p>

{pagination}
<table class="vapilote">
  <tr><th>Sujet</th><th>Début</th><th>Editer</th><th>Diffuser</th><th>Stopper</th><th>Supprimer</th></tr>
  <!-- BEGIN lettre -->
    <tr class="{cssline}">
      <td>{sujet}</td><td>{debut}...</td><td><a href="{editer}">EDITER</a></td><td><a href="{active}" onclick="return confirm('Diffuser cette news ?')">DIFFUSER</a></td><td><a href="{inactive}" onclick="return confirm('Stopper la diffusion de cette news ?')">ARRET</a></td>
      <td><a href="{suppress}" onclick="return confirm('Supprimer cette news ?')">Supprimer</a></td>
    </tr>
  <!-- END lettre -->
</table>
{pagination}


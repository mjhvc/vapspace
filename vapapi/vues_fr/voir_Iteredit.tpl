<p class="identifier">Des membres proposent leurs trajets. <br />
En cliquant sur <strong>'me contacter'</strong>, un mail vous est envoyé. <br />
Ce mail contient le courriel du membre initiant le deal.</p>

<p class="identifier">Le <strong>point de rencontre  </strong> est le point d'embarquement pour l'automobiliste, le point de stop pour le piéton.</p>
 
{pagination}
<table class="vapilote">
  <tr><th>Au point de rencontre, </th><th>Mme/M</th><th>{deal}</th><th>un trajet vers: </th><th>plus précisément à: </th><th>selon l'horaire: </th><th>Me contacter</th><th>Editer</th><th>Supprimer</th>  </tr>
  <!-- BEGIN vapiters -->
    <tr class="{cssline}"><td>{lieu}, </td><td>{nom}</td><td>{deal}</td><td>{commune}: </td><td>{destination} </td><td>{periode}. </td><td><a href="{urlitercnx}"><img src="photos/test_aro.gif" alt="autostop.png"/></a></td><td><a href="{editer}">edition</a></td><td><a href="{supprimer}" onclick="return confirm('Supprimer ce trajet ?');">Suppression</a></td></tr>
  <!-- END vapiters -->
</table>
{pagination}

<p class="identifier">Des membres proposent leurs trajets. <br />
En cliquant sur <strong>'me contacter'</strong>, un mail vous est envoyé. <br />
Ce mail contient le courriel du membre initiant le deal.</p>
<!-- <strong>'Mme/Mr'</strong>,<strong> offre/demande</strong> un trajet à destination de la <strong>'COMMUNE'</strong>; dans cette commune à <strong>'ENDROIT'</strong> -->
<!-- selon un <strong>'HORAIRE'</strong> et en passant par <strong>'Point de rencontre'</strong>.</p> -->
<p class="identifier">Le <strong>point de rencontre  </strong> est le point d'embarquement pour l'automobiliste, le point de stop pour le piéton.</p>

{pagination}
<table class="vapilote">
  <tr><th>Au point de rencontre, </th><th>Mme/M</th><th>{deal}</th><th>un trajet vers: </th><th>plus précisément à: </th><th>selon l'horaire: </th><th>Me contacter</th> </tr>
  <!-- BEGIN vapiters -->
    <tr class="{cssline}"><td>{lieu}, </td><td>{nom}</td><td>{deal}</td><td>{commune}: </td><td>{destination} </td><td>{periode}. </td><td><a href="{urlitercnx}"><img src="photos/test_aro.gif" alt="autostop.png"/></a></tr>
  <!-- END vapiters -->
</table>
{pagination}

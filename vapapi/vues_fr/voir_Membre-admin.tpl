{pagination}
<table class="vapilote">
<tr><th>{mail_t}</th><th>{nom_t}</th><th>{prenom_t}</th><th>{membre_t}</th><th>{lienant_t}</th><th>{adresse_t}</th><th>{code_t}</th><th>{ville_t}</th><th>{cachet_t}</th><th>{inscrit_t}</th><th>{connu_t}</th>{manage}</tr>
<!-- BEGIN ligne -->
  <tr class="{cssline}"><td><a href="mailto:{mail}">{mail}</a></td><td>{nom}</td><td>{prenom}</td><td>{membre}</td><td>{Antenne}</td><td>{adresse}</td><td>{code}</td><td>{ville}</td><td>{cachet}</td><td>{inscrit}</td><td>{connu}</td><td><a href="{editer}">edition</a></td><td><a href="{supprimer}" onclick="return confirm('Supprimer ce membre ?');">Suppression</a></td></tr>
<!-- END ligne -->
</table>
{pagination}

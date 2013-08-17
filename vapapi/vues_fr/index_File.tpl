<div id="tele">
<h3>Administrer</h3>
<table class="vapilote"><caption>À propos des trajets</caption>
<tr>
  <th>Trajets inscrits :</th><td>{nbr_traj}</td>
</tr>
<tr>
  <th>Contactés par mails via trajets :</th><td>{traj_exch}</td>
</tr>
</table>
<form  method="post" action="{url_file}"  enctype="multipart/form-data" >
<fieldset><legend>Téléverser un fichier</legend>
<p>
  <strong>Formats autorisés :</strong> <br />.doc, .pdf, .jpg, .gif, .png <br />
  <strong>Choisir un fichier: </strong><br/>
  <input type="file" name="nomFile" /><br />
  <label for="descFile" ><strong>Description:</strong> </label><br />
  <input type="text" id="descFile" value="Entrez sa description ici" name="descFile" size="20" /><br />
  <input type="submit" value="Verser" />
</p>
</fieldset>
</form>
<h3>Webmaster</h3>
<ul> 
  <li><a href="{url_ctxt}">{ctxt}</a></li>
  <li><a href="{url_cachAnt}">{cachant}</a></li>
</ul>       
</div>


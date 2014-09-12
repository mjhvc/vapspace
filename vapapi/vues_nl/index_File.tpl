<div id="tele">
<h3>Beheren</h3>
<table class="vapilote"><caption>Over reizen</caption>
<tr>
  <th>Ingeschreven reizen :</th><td>{nbr_traj}</td>
</tr>
<tr>
  <th>uitwisselingen van mails dankzij trajecten</th><td>{traj_exch}</td>
</tr>
</table>
<form  method="post" action="{url_file}"  enctype="multipart/form-data" >
<fieldset><legend>Een bestand invoegen</legend>
<p>
  <strong> toegestaande files :</strong> <br />.doc, .pdf, .jpg, .gif, .png <br />
  <strong>voeg een file </strong><br/>
  <input type="file" name="nomFile" /><br />
  <label for="descFile" ><strong>Description:</strong> </label><br />
  <input type="text" id="descFile" value="Voer een beschrijving hier" name="descFile" size="20" /><br />
  <input type="submit" value="Upload" />
</p>
</fieldset>
</form>
<h3>Webmaster</h3>
<ul> 
  <li><a href="{url_ctxt}">{ctxt}</a></li>
  <li><a href="{url_cachAnt}">{cachant}</a></li>
</ul>       
</div>


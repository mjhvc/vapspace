<div id="tele">
<h3>Een bestand invoegen</h3>
<p> .doc, .pdf, .jpg, .gif, .png </p>
<form  method="post" action="{url_file}"  enctype="multipart/form-data" >
<p>
  <input type="file" name="nomFile" /><br />
  <label for="descFile" >titel </label><input type="text" id="descFile" value="Voer een beschrijving hier" name="descFile" size="20" />
  <input type="submit" value="Upload" />
</p>
</form>
</div>


<h3>Interroger la base des membres</h3>
<form method="post" action="{url_voir}class=ant">
  <p>
    <label for="lienant">Afficher les membres de l'antenne:  </label>
    {selectAntenne}
    <input type="submit" value="Afficher" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=numbr">
  <p>
    <label for="lienant">Choisissez l'antenne, j'affiche ses membres par leur numero :  </label>
    {selectAntenne}
    <input type="submit" value="Afficher" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=numbrinv">
  <p>
    <label for="lienant">Choisissez l'antenne, j'affiche les derniers membres enregistr�s:  </label>
    {selectAntenne}
    <input type="submit" value="Afficher" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=new">
  <p>
    <label for="MbrNew">Lister les nouveaux membres:   </label> 
    <input type="submit" value="Lister" name="submit" id="MbrNew" />
  </p>
</form>

<form method="post" action="{url_voir}class=nom">
  <p>
    <label for="MbrNom">Chercher un membre d'apr�s son nom:  </label>
    <input type="text" name="MbrNom" id="MbrNom" />
    <input type="submit" value="Afficher" name="submit" /><br />
    le caract�re '%' remplace n'importe quel ensemble de caract�res
  </p>
</form>
<form method="post" action="{url_voir}class=numero">
  <p>
    <label for="MbrParNum">Identifier un membre d'apr�s son num�ro:   </label>
    <input type="text" name="MbrParNum" id="MbrParNum" />
    <input type="submit" value="Identifier" name="submit" /><br />
  </p>
</form>
<form method="post" action="{url_voir}class=courrier">
  <p>
    <label for="mailMbr">Identifier un membre par son mail: </label>
    <input type="text" name="mailMbr" id="mailMbr" />
    <input type="submit" value="Identifier" value="submit" /><br />
  </p>
</form>
<form method="post" action="{url_voir}class=date">
  <p>
    <label for="MbrParDate">Chercher les membre inscrits depuis la date:  </label>
    <input type="text" name="MbrParDate" id="MbrParDate" />
    <input type="submit" value="Afficher" name="submit" /><br />
    Rentrez une date au format aaaa-mm-jj
  </p>
</form> 
{HA}
<h3>Export des membres </h3>
<form method="post" action="{url_extract}">
<p>
  <label for="lienant">Cr�er un fichier csv des membres de : </label>  {selectAntenne}
  <input type="submit" value="Cr�er" name="submit" />
</p>
</form>
<p class="identifier">
  Cet outil calcule et exporte dans un fichier csv les membres d'une antenne.<br />
  Ce fichier est ensuite t�l�chargeable et utilisable dans un tableur (Microsoft Excell p.ex)<br />
  <strong>N�anmoins, toutes les modifications dans la base des membres devront se r�aliser avec les outils ci-dessus</strong>  
</p>

   
  
  



<h3>De databank raadplegen</h3>
<form method="post" action="{url_voir}class=ant">
  <p>
    <label for="lienant">Ledenlijst van een antenne:  </label>
    {selectAntenne}
    <input type="submit" value="Tonen" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=numbr">
  <p>
    <label for="lienant">Ledenlijst van een antenne tonen, geklasseerd volgens lidnummer :  </label>
    {selectAntenne}
    <input type="submit" value="Tonen" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=numbrinv">
  <p>
    <label for="lienant">De laatst aangesloten leden van een antenne: </label>
    {selectAntenne}
    <input type="submit" value="Tonen" name="submit" />
  </p>
</form>
<form method="post" action="{url_voir}class=new">
  <p>
    <label for="MbrNew">De laatst aangesloten leden van de vzw:   </label> 
    <input type="submit" value="Tonen" name="submit" id="MbrNew" />
  </p>
</form>

<form method="post" action="{url_voir}class=nom">
  <p>
    <label for="MbrNom">Een lid opzoeken volgens naam: </label>
    <input type="text" name="MbrNom" id="MbrNom" />
    <input type="submit" value="Tonen" name="submit" /><br />
     het teken '%' mag een reeks van opeenvolgende letters vervangen 
  </p>
</form>
<form method="post" action="{url_voir}class=numero">
  <p>
    <label for="MbrParNum">Een lid opzoeken volgens zijn nummer:  </label>
    <input type="text" name="MbrParNum" id="MbrParNum" />
    <input type="submit" value="opzoeken" name="submit" /><br />
  </p>
</form>
<form method="post" action="{url_voir}class=courrier">
  <p>
    <label for="mailMbr">Een lid opzoeken volgens zijn e-mailadres:</label>
    <input type="text" name="mailMbr" id="mailMbr" />
    <input type="submit" value="opzoeken" value="submit" /><br />
  </p>
</form>
<form method="post" action="{url_voir}class=date">
  <p>
    <label for="MbrParDate">De leden tonen die aansloten na de volgende datum:</label>
    <input type="text" name="MbrParDate" id="MbrParDate" />
    <input type="submit" value="Tonen" name="submit" /><br />
    De datum invullen als jjjj-mm-dd 
  </p>
</form> 
{HA}
<h3>Ledenlijsten exporteren </h3>
<form method="post" action="{url_extract}">
<p>
  <label for="lienant">Een csv-bestand aanleggen van de leden van :  </label>  {selectAntenne}
  <input type="submit" value="bestaan" name="submit" />
</p>
</form>
<p class="identifier">
  Dit csv-bestand kan vervolgens gedownload worden en als Microsoft Excel bestand gebruikt.<br />
  Let wel : dit csv-bestand laat niet toe wijzigingen aan te brengen aan de databank.<br />
  Deze moeten steeds gebeuren met de tools van de rubriek "Beheren - opvragen". 
</p>



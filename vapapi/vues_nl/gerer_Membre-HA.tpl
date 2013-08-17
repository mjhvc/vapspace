<form method="post" action="{url_ha}"/>
  <p>
    Lijst van de leden zonder antenne, per gemeente <br />
    <label for="MbrHorsAnt">De eerste letters van lidnummer geven: </label>
    <input type="text" name="MbrHorsAnt" id="cherchehors" />
    <input type="submit" value="Tonen" name="submit" />
  </p>
</form> 
<form method="post" action="{url_resp}"/>
  <p>
    <label for="MbrResp">Lijst van de antenne-verantwoordelijken :   </label> 
    <input type="submit" value="Tonen" name="submit" id="MbrResp" />
  </p>
</form>
<form method="post" action="{url_mv}" />
  <p>
   De leden van : {antenneUn} naar : {antenneDeux}
   <input type="submit" value="Verplaatsen"/>
  </p>
</form>

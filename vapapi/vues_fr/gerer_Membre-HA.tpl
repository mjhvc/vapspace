<form method="post" action="{url_ha}"/>
  <p>
    Classer les membres <strong>hors-antenne </strong> par commune   <br />
    <label for="MbrHorsAnt">saisir les lettres du numero du membre:  </label>
    <input type="text" name="MbrHorsAnt" id="cherchehors" />
    <input type="submit" value="Afficher" name="submit" />
  </p>
</form> 
<form method="post" action="{url_resp}"/>
  <p>
    <label for="MbrResp">Liste des responsables d'antenne:   </label> 
    <input type="submit" value="Responsables" name="submit" id="MbrResp" />
  </p>
</form>
<form method="post" action="{url_mv}" />
  <p>
    D�placer les membres de l'antenne: {antenneUn} �: {antenneDeux}
    <input type="submit" value="D�placer"/>
  </p>
</form>

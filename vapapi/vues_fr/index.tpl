<div id="inscrire">
  <h3> Inscrire</h3>
  <ul>
  <!-- BEGIN menuInscrire -->
    <li> <a href="{url_ins}">{nom_ins}</a></li>
  <!-- END menuInscrire -->
  </ul>
</div> 
<div id="gerer">
  <h3> Voir - Gérer</h3>
  <ul>  
  <!-- BEGIN menuGerer -->
   <li> <a href="{url_ger}">{nom_ger}</a></li>
  <!-- END menuGerer -->
  <li>
    <form method="post" action="{gerer_stat}">
    <fieldset><legend>{stat_region}</legend>
       <p>
        {selectRegions} <br />
        <input type="submit" value="{iter_meet}" name="submit" />
      </p>
    </fieldset>
    </form>
  </li>
  <li>
    <form method="post" action="{gerer_lieu}">
    <fieldset><legend>{iter_meet_ant}</legend>
       <p>
        {selectAntenne} <br />
        <input type="submit" value="{iter_meet}" name="submit" />
      </p>
    </fieldset>
    </form>
  </li>
  </ul>
</div>
{teleFile}

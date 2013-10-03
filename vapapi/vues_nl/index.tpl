<div id="inscrire">
  <h3> Inschrijven</h3>
  <ul>
  <!-- BEGIN menuInscrire -->
    <li> <a href="{url_ins}">{nom_ins}</a></li>
  <!-- END menuInscrire -->
  </ul>
</div> 
<div id="gerer">
  <h3> Beheren-Opvragen</h3>
  <ul>  
  <!-- BEGIN menuGerer -->
   <li> <a href="{url_ger}">{nom_ger}</a></li>
  <!-- END menuGerer -->
  <li>
    <form method="post" action="{gerer_lieu}">
       <p>
        <label for="antenne">{iter_meet_ant}</label>
        {selectAntenne}
        <input type="submit" value="{iter_meet}" name="submit" />
      </p>
    </form>
  </li>
  </ul>
</div>
{teleFile}
 

 <form method="post"  action="{script_formu}">
<fieldset>  
    <p class="perso">
      <label for="nomantenne"><strong>{nomantenne_o} Nom de l'antenne </strong></label><input type="text" name="nomantenne" id="nomantenne" size="{nomantenne_s}" maxlength="{nomantenne_s}" value = "{nomantenne}" />
      <label for="lienreg"><strong>{lienreg_o} Choix de la région: </strong></label> 
        {select}
    </p>
    <p>
      <input type="submit" value="Inscription" />
      {cachez}
    </p>
</fieldset>        
  </form>

<form method="post"  action="{script_formu}">
<fieldset>
    <p class="perso">
      <label for="nomantenne"><strong>{nomantenne_o} Naam van de antenne </strong></label><input type="text" name="nomantenne" id="nomantenne" size="{nomantenne_s}" maxlength="{nomantenne_s}" value = "{nomantenne}" />
      <label for="lienreg"><strong>{lienreg_o} Kies een gewest </strong></label> 
        {select}
    </p>
    <p>
      <input type="submit" value="Inschrijving" />
      {cachez}
    </p> 
</fieldset>     
  </form>

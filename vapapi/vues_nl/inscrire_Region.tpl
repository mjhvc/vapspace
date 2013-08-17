 <form method="post"  action="{script_formu}" >
  <fieldset>  
    <p class="perso"><label for="nomregion"><strong>{nomregion_o}Naam van het gewest </strong></label><input type="text"name="nomregion"id="nomregion" size="{nomregion_s}" maxlength="{nomregion_s}" value = "{nomregion}" /></p>
    <p class="perso"><label for="province"><strong>{province_o} Naam van de provincie</strong></label><input type="text"name="province"id="province" size="{province_s}" maxlength="{province_s}" value="{province}" /></p>
    <p>
      <input type="submit" value="Inschrijving" />
      {cachez}  
    </p>
</fieldset>        
  </form>

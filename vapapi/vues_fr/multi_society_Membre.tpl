<fieldset><legend>Je circule en train, tram, bus...</legend>
    <table> 
      <tr class="mobi">
        <th>OUI <small>sans abonnement</small></th><th>&nbsp;</th>
        <th>Oui <small>avec abonnement</small></th><th>&nbsp;</th>
      </tr>
      <!-- BEGIN sponsors -->      
      <tr class="mobi">      
          <td><input type="radio" name="{societes}" value="{valUno}" id="{concatUno}"  {moboui}{moboui_val}  /> <label for="{concatUno}">{societes}</label></td><td><input type="hidden" id="{soc_temp}" value=""  />  </td>
          <td><input type="radio" name="{societes}" value="{valDuo}" {mobouiavec}{mobouiavec_val}  id="{concatDuo}"  /> <label for="{concatDuo}">{societes}</label></td><td><input type="hidden" id="{soc_temp}" value=""  />  </td>
      </tr>
       <!-- END sponsors -->
    </table>
    <p class="mobi">Vous ne circulez pas en transport public? ne cochez rien...</p>
</fieldset>

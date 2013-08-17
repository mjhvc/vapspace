<form method="post"  action="{script_formu}">
  <p class="perso">
    <label for="lieu"><strong>* Trefpunt </strong></label><input type="text" name="lieu" id="lieu" size="50" maxlength="50" value = "{lieu}" />
    <label for="lienant"><strong>* In antenne: </strong></label> {select}
  </p>
  <p>
    <input type="submit" value="{submit}" />
    {cachez}
  </p>      
</form>

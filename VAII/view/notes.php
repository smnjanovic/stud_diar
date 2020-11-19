<!-- Úlohy -->
<div id="notes-container">
  <div id="subjects">
    <ul>
      <li id="sub-1">
        <span>Predmet</span>
        <span>0</span>
      </li>
      <li id="sub-2" class="active">
        <span>Predmet</span>
        <span>90</span>
      </li>
    </ul>
  </div>
  <div id="notes">
    <ul>
      <li>
        <div class="note">
          <div class="readonly">
            <div class="readable">
              <div class="datetime">18.11.2020</div>
              <div class="info">text</div>
            </div>
            <div class="actions">
              <button type="button" class="fa fa-edit" title="Upraviť"></button>
              <button type="button" class="fa fa-remove" title="Vymazať"></button>
            </div>
          </div>
          <div class="writable">
            <form action="#" name="note-n" method="post">
              <input type="hidden" name="note-id" value="0">
              <input type="hidden" name="sub-id" value="0">
              <div class="editable">
                <input type="date" name="date" value="">
                <textarea name="content" placeholder="popis"></textarea>
              </div>
              <div class="decision">
                <button type="submit" name="submit" class="fa fa-save" title="Uložiť"></button>
                <button type="reset" name="cancel" class="fa fa-remove" title="Vymazať"></button>
              </div>
            </form>
          </div>
        </div>
      </li>
      <li>
        <div class="note">
          <div id="new-note">
            <form action="#" name="new-note" method="post">
              <input type="hidden" name="note-id" value="-1">
              <input type="hidden" name="sub-id" value="0">
              <div class="editable">
                <input type="date" name="date" value="">
                <textarea name="content" placeholder="popis"></textarea>
              </div>
              <div class="decision">
                <button type="submit" name="submit" class="fa fa-save" title="Uložiť"></button>
                <button type="reset" name="cancel" class="fa fa-remove" title="Vymazať"></button>
              </div>
            </form>
          </div>
        </div>
      </li>
    </ul>

    <script>
      (function() {

      })()
    </script>

  </div>
</div>

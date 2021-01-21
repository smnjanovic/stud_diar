<?php
namespace Main;
use Model\SessionManager;
$in = SessionManager::hasLoggedInUser();
?>
<!-- Úlohy -->
<div>
  <div>
    <div id="categories">
      <div>
        <select name="category">
          <option value="all-notes">Všetko</option>
          <option value="late-notes">Neskoro</option>
          <option value="recent">Tento týždeň</option>
          <option value="long-term">Bez termínu</option>
          <option value="subject-related">Predmet</option>
        </select>
      </div>
      <div id="queries"><input type="search" name="subject-search" value="" autocomplete="off"><div><ul></ul></div></div>
      <div id="category-description"><h3></h3></div>
    </div>
    <div id="notes-container">
      <div id="new-note" title="Pridať úlohu"><span><span class="fa fa-plus"></span>Pridať úlohu</span></div>
      <div id="notes"></div>
    </div>
  </div>
  <script src="js/url-manager.js"></script>
  <script src="js/dom-builder.js"></script>
  <script src="js/dialog-builder.js"></script>
  <?php if (!$in) { ?><script src="js/storage.js"></script><?php } ?>
  <script src="js/paginator.js"></script>
  <script src="js/subject-browser.js"></script>
  <script src="js/note-manager-template.js"></script><?php if ($in) { ?>
  <script src="js/note-manager-logged-in.js"></script><?php } else { ?>
  <script src="js/note-manager-logged-out.js"></script><?php } ?>
  <script src="js/notes.js"></script>
</div>

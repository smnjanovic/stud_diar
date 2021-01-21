<?php
namespace Main;
use Model\SessionManager;
$in = SessionManager::hasLoggedInUser();
?>
<!-- Rozvrh s dizajnom  -->
<div id="design-container">
  <div id="edit-tools">
    <div id="toolbox-header">
      <div id="tool-options">
        <ul>
          <li class="color-settings fa fa-tint"></li>
          <li class="customization fa fa-paint-brush"></li>
          <li class="schedule-settings fa fa-table"></li>
          <li class="close fa fa-close"></li>
        </ul>
      </div>
    </div>
    <div id="color-settings" class="edit-tool-group">
      <h3>Farby</h3>
      <form id="color-settings-form" action="#" method="post">
        <select id="des-categories" name="des-categories">
          <option value="background">Pozadie</option>
          <option value="heading">Hlavička</option>
          <option value="lectures">Prednášky</option>
          <option value="courses">Cvičenia</option>
          <option value="free">Voľno</option>
        </select>
        <span>Odtieň</span>
        <input id="des-set-hue" type="range" min="0" max="359">
        <span></span>
        <span>Sýtosť</span>
        <input id="des-set-saturation" type="range" min="0" max="100">
        <span></span>
        <span>Svetlosť</span>
        <input id="des-set-luminance" type="range" min="0" max="100">
        <span></span>
        <span>Alfa</span>
        <input id="des-set-alpha" type="range" min="0" max="100">
        <span></span>
        <!-- javascript bude reagovat na zmeny formulára a posielat na server cez xml http request -->
      </form>
    </div>
    <div id="customization" class="edit-tool-group">
      <form id="customization-form" action="#" method="post">
        <h3>Prispôsobenie</h3>
        <h4>Rozmery</h4>
        <div><select name="resolution"></select></div>
        <div id="own-resolution" class="hidden">
          <input type="number" name="resolution-width" min="1">
          <span>×</span>
          <input type="number" name="resolution-height" min="1">
        </div>
        <h4>Posun</h4>
        <div id="object-translation">
          <span>Tabuľka</span><input type="range" name="table-position" value="50" min="0" max="100"><span>50%</span>
          <span>Obrázok</span><input type="range" name="image-position" value="50" min="0" max="100"><span>50%</span>
        </div>
        <h4>Prispôsobenie obrázku</h4>
        <div id="image-fit">
          <label><input type="radio" name="imageFit" value="cover"><span>Orezať</span></label>
          <label><input type="radio" name="imageFit" value="contain"><span>Zmestiť</span></label>
          <label><input type="radio" name="imageFit" value="fill"><span>Roztiahnúť</span></label>
        </div>
      </form>
    </div>

    <div id="schedule-settings" class="edit-tool-group">
      <form id="schedule-settings-form" action="index.html" method="post">
        <h3>Rozvrh</h3>
        <div id="schedule-setter">
          <h4></h4>
          <div id="sched-time-sliders">
            <span>Deň</span><input type="range" name="day" min="1" max="5" value="1"><span>17</span>
            <span>Začiatok</span><input type="range" name="start" min="1" max="17" value="1"><span>71</span>
            <span>Trvanie</span><input type="range" name="dur" min="1" max="17" value="1"><span>17</span>
          </div>
          <div id="sched-clr"><button type="button" name="clr">Uvoľniť</button></div>
          <div>
            <select name="lesson-type">
              <option value="lecture">Prednáška</option>
              <option value="course">Cvičenie</option>
            </select>
          </div>
          <div>Predmet</div>
          <div id="subject-browser">
            <div><input id="search" type="search" name="subject"></div>
            <div><ul id="filter"></ul></div>
          </div>
          <div>Miestnosť</div>
          <div><input type="search" name="room"></div>
          <div id="sched-add"><button type="button" name="add">Pridať</button></div>
        </div>
      </form>
    </div>
  </div>
  <div id="design-frame">
    <canvas id="drawable" width="720" height="1560"></canvas>
    <div id="frame-actions">
      <ul>
        <li id="design-show-tools" class="fa fa-pencil"></li>
        <li id="image" class="fa fa-image"></li>
        <li id="design-save" class="fa fa-save"></li>
      </ul>
    </div>
  </div>
</div>
<script src="js/dom-builder.js"></script>
<script src="js/dialog-builder.js"></script>
<?php if (!$in) { ?><script src="js/storage.js"></script><?php } ?>
<script src="js/subject-browser.js"></script>
<script src="js/design-manager-template.js"></script>
<script src="js/schedule-manager-template.js"></script><?php if ($in) { ?>
<script src="js/schedule-manager-logged-in.js"></script>
<script src="js/design-manager-logged-in.js"></script><?php } else { ?>
<script src="js/schedule-manager-logged-out.js"></script>
<script src="js/design-manager-logged-out.js"></script><?php } ?>
<script src="js/schedule-design.js"></script>

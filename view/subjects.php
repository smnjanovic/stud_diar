<?php
namespace MAIN;
use Model\SessionManager;
?>
<h2>Predmety</h2>
<div id="new-subject"><p><span class="fa fa-plus">&nbsp;</span>Pridať predmet</p></div>
<div id="subjects"></div>
<h3>Žiadne predmety</h3>
<script src="js/dom-builder.js"></script>
<script src="js/dialog-builder.js"></script>
<script src="js/url-manager.js"></script>
<script src="js/paginator.js"></script><?php if (SessionManager::hasLoggedInUser()) { ?>
<script src="js/subject-manager-logged-in.js"></script><?php } else { ?>
<script src="js/storage.js"></script>
<script src="js/subject-manager-logged-out.js"></script><?php } ?>
<script src="js/subjects.js"></script>

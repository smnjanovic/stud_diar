<?php

$targetUser;
$me = User::getLoggedInUser();
if (isset($_GET['user'])) $targetUser = User::getUserByNameOrEmail($_GET['user']);
else $targetUser = $me;

if (!$targetUser || !$me) {
  $accessDenied = PageLoader::denyAccess();
  ?>
  <script id="getMeOutOfHere">
    document.title = "<?= $accessDenied['title'] ?>";
  </script>
  <?php
  include "view/" . $accessDenied['content'] . '.php';
}

else {

?>

<!--Účet-->
<div id="user-account">
  <div id="user-profile-picture-box">
    <div id="user-picture-zone">
      <svg viewBox="0 0 40 40" width="200" height="200">
          <path stroke-width="0" d="M 39.999999,39.999999 H 4.9993032e-7 v -4.34749 L 13.645542,26.912239 v -3.63104 c -1.872406,0 -3.446551,-2.17911 -3.446551,-3.51579 V 8.2394695 c 0,-2.64654 4.448832,-8.23947 9.97991,-8.23947 5.531075,0 9.712545,5.61375 9.712545,8.18141 V 19.765409 c 0,1.33668 -1.52757,3.51579 -3.461839,3.51579 v 3.62129 l 13.532626,7.72389 z"/>
      </svg>
      <!--<img src="https://media0.webgarden.com/images/media0:4e2b6b54034a9.jpg/wall_ANIMALSINLOVE_1280x1024_07.jpg" alt="Profilová fotka" width="200" height="200">-->
    </div>

    <?php if ($me->compareTo($targetUser)) { ?>
    <div id="user-picture-options">
      <div><span class="fa fa-edit" title="Zmeniť fotku profilu"></span><span>Zmeniť fotku profilu</span></div>
      <div><span class="fa fa-remove" title="Odstrániť fotku profilu"></span><span>Odstrániť fotku profilu</span></div>
    </div>
    <?php } ?>
  </div>
  <div id="user-sensitive-data">
    <!--<div>
      <span>Celé meno</span>
      <span>Meno Priezvisko</span>
    </div>-->
    <div>
      <span<?php if ($me->compareTo($targetUser)) { ?> id="nick-change" class="fa fa-edit"<?php } ?>></span>
      <span>Nick</span>
      <span><?=$targetUser->getNick()?></span>
    </div>
    <?php if ($me->compareTo($targetUser)) { ?>
    <div>
      <span id="mail-change" class="fa fa-edit"></span>
      <span>E-mail</span>
      <span><a href="mailto:<?=$targetUser->getEmail()?>"><?=$targetUser->getEmail()?></a></span>
    </div>
    <?php } ?>

    <?php if ($me->compareTo($targetUser)) { ?>
    <script>
      (function(){
        const NICK = document.getElementById("nick-change");
        const MAIL = document.getElementById("mail-change");

        NICK.addEventListener("click", function(){
          const LABEL = NICK.nextElementSibling.nextElementSibling
          const xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200 && this.responseText) {
              LABEL.innerHTML = this.responseText;
            }
          };
          let change = prompt("nick: ", LABEL.innerHTML);
          if (change) {
            xhttp.open("POST", "form/user-edit.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("id=<?=$targetUser->getId()?>&nick=" + change);
          }
        });

        MAIL.addEventListener("click", function(){
          const LABEL = MAIL.nextElementSibling.nextElementSibling.firstElementChild;
          const xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200 && this.responseText) {
              LABEL.innerHTML = this.responseText;
              LABEL.setAttribute("href", "mailto:" + this.responseText);
            }
          };
          let change = prompt("mail: ", LABEL.innerText);
          if (change && change != LABEL.innerText) {
            xhttp.open("POST", "form/user-edit.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("id=<?=$targetUser->getId()?>&mail=" + change);
          }
        });
      })();
    </script>

    <div id="delete-account">
      <a href="form/remove-account.php">Zrušiť účet</a>
    </div>
    <?php } ?>
  </div>

  <?php if ($me->compareTo($targetUser)) { ?>
  <div id="user-stats">
    <table>
      <tr>
        <td>Počet predmetov</td>
        <td>0</td>
      </tr>
      <tr>
        <td>Počet fotiek v galérii</td>
        <td>0</td>
      </tr>
      <tr>
        <td>Nesplnené úlohy</td>
        <td>0</td>
      </tr>
      <tr>
        <td>Úlohy blížiacich sa termínov</td>
        <td>0</td>
      </tr>
      <tr>
        <td>Celkový počet úloh</td>
        <td>0</td>
      </tr>
    </table>
  </div>
  <?php } ?>
</div>

<?php }

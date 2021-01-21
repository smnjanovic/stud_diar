<?php
namespace MAIN;

use Model\PageLoader;
use Model\UserManager;
use Model\User;
use Model\FormKeys;
use Model\SessionManager;
use Model\SubjectManager;
use Model\NoteManager;

// Tento obsah sa načítal za predpokladu, že užívateľ existuje a návštevník smie zobraziť tento obsah
$targetUser = UserManager::getUserByNameOrEmail($_GET['user']);
$authorized = SessionManager::isUserModifiable($targetUser);
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

    <?php if ($authorized) { ?>
      <div id="user-picture-options">
        <div><span class="fa fa-edit" title="Zmeniť fotku profilu"></span><span>Zmeniť fotku profilu</span></div>
        <div><span class="fa fa-remove" title="Odstrániť fotku profilu"></span><span>Odstrániť fotku profilu</span></div>
      </div>
    <?php } ?>
  </div>
  <div id="user-sensitive-data">
    <div>
      <span<?php if ($authorized) { ?> id="nick-change" class="fa fa-edit"<?php } ?>></span>
      <span>Nick</span>
      <span><?=$targetUser->getNick()?></span>
    </div>
    <?php if ($authorized) { //E-mail je súkromný. Smie ho vidieť len vlastník! ?>
    <div>
      <span id="mail-change" class="fa fa-edit"></span>
      <span>E-mail</span>
      <span><a href="mailto:<?=$targetUser->getEmail()?>"><?=$targetUser->getEmail()?></a></span>
    </div>
    <?php } ?>

    <?php if ($authorized) { ?>
    <script src="js/dom-builder.js"></script>
    <script src="js/dialog-builder.js"></script>
    <div id="change-password"><a href="#">Zmeniť heslo</a></div>
    <div id="delete-account"><a href="#">Odstrániť účet</a></div>
    <?php } ?>
  </div>

  <?php if ($authorized) { // len vlastník konta má prehľad úloh, predmetov a pod. ?>
    <div id="user-stats">
      <table>
        <tr><td>Počet predmetov</td><td><?=SubjectManager::getSubjectCount()?></td></tr>
        <tr><td>Počet fotiek v galérii</td><td>0</td></tr>
        <tr><td>Nesplnené úlohy</td><td><?=NoteManager::countLateNotes()?></td></tr>
        <tr><td>Úlohy blížiacich sa termínov</td><td><?=NoteManager::countCurrentNotes()?></td></tr>
        <tr><td>Celkový počet úloh</td><td><?=NoteManager::countNotes()?></td></tr>
      </table>
    </div>
  <?php } ?>
</div>
<?php if ($authorized) { ?>
<script>(() => {
  const NICK = DOMManager.getEl("#nick-change");
  const MAIL = DOMManager.getEl("#mail-change");
  const NICK_LABEL = DOMManager.getEl("#nick-change+span+span").innerText;
  const MAIL_LABEL = DOMManager.getEl("#mail-change+span+span>a").innerText;

  NICK.addEventListener("click", () => new PromptDialog({
    title: "Prezývka",
    message: "Zadajte prezývku",
    confirm: {
      text: "Zmeniť",
      fn: input => {
        fd = new FormData();
        fd.append("<?=FormKeys::MODIFY_ACCOUNT?>", "");
        fd.append("<?=FormKeys::USER_ID?>", "<?=$targetUser->getId()?>");
        fd.append("<?=FormKeys::NICK?>", input[0].value);

        fetch("<?=PageLoader::DOMAIN?>/ajax-forms.php", { method: 'POST', body: fd })
        .then((res) => res.json())
        .then((data) => {
          const len = data.length || Object.keys(data).length || 0;
          if (len === 0) window.location = `<?=PageLoader::makeLink(
            PageLoader::ACCOUNT, ["user", ""])?>${input[0].value}`;
          else {
            const UL = dom.newEl("ul");
            data.forEach((item, i) => { dom.newEl(`li{${item}}`, UL) });
            dialog.alert("Chyba!", UL.outerHTML);
          }
        })
        .catch((err) => { console.log(err) });
      }
    },
    inputs: [{
      type: "text",
      default: NICK_LABEL,
      description: "Nick",
      attributes: { patern: "[a-zA-Z0-9-_]{15}", required: "" },
      validate: text => {
        if (text === NICK_LABEL) return "Žiadna zmena!"
        else if (text.length === 0) return "Nutné vyplniť!";
        else if (text.length > 15) return "Príliš dlhá prezývka!";
        else if (text.match(/[^a-zA-Z0-9-_]/)) return "Povolené sú len písmená, čislice a znaky '-', '_'!";
        else return "";
      }
    }]
  }));
  MAIL.addEventListener("click", () => new PromptDialog({
    title: "E-mail",
    message: "Zadajte nový e-mail!",
    confirm: {
      text: "Zmeniť",
      fn: input => {
        fd = new FormData();
        fd.append("<?=FormKeys::MODIFY_ACCOUNT?>", "");
        fd.append("<?=FormKeys::USER_ID?>", "<?= $targetUser->getId() ?>");
        fd.append("<?=FormKeys::EMAIL?>", input[0].value);

        fetch("<?=PageLoader::DOMAIN?>/ajax-forms.php", { method: 'POST', body: fd })
        .then((res) => res.json())
        .then((data) => {
          const len = data.length || Object.keys(data).length || 0;
          if (len === 0) MAIL_LABEL = input[0].value;
          else {
            const UL = dom.newEl("ul");
            data.forEach((item, i) => { dom.newEl(`li{${item}}`, UL) });
            dialog.alert("Chyba!", UL.outerHTML);
          }
        })
        .catch((err) => { console.log(err) });
      }
    },
    inputs: [{
      type: "email",
      default: MAIL_LABEL,
      attributes: { required: "" },
      validate: text => {
        if (text === MAIL_LABEL) return "Žiadna zmena!"
        else if (text.length === 0) return "Nutné vyplniť!";
        else if (!text.match(/^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\[((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si))
          return "Neplatný formát e-mailu!";
        else return "";
      }
    }]
  }));

  let pass1 = "";

  DOMManager.getEl("#change-password>a").addEventListener("click", (e) => {
    e.preventDefault();
    new PromptDialog({
      title: "Zmena hesla",
      message: "Vyplňte údaje!",
      confirm: {
        text: "Zmeniť",
        fn: inputs => {
          let fd = new FormData();
          fd.append("<?=FormKeys::CHANGE_PASSWORD?>", "");
          fd.append("<?=FormKeys::USER_ID?>", "<?=$targetUser->getId()?>");
          fd.append("<?=FormKeys::OLD_PASS?>", inputs[0].value);
          fd.append("<?=FormKeys::NEW_PASS1?>", inputs[1].value);
          fd.append("<?=FormKeys::NEW_PASS2?>", inputs[2].value);

          fetch("<?=PageLoader::DOMAIN?>/ajax-forms.php", { method: 'POST', body: fd })
          .then((res) => res.json())
          .then((data) => {
            const len = data.length || Object.keys(data).length || 0;
            let title = len === 0 ? "Heslo zmenené" : "Chyba";
            let content;
            if (len === 0) content = "Heslo úspešne zmenené";
            else {
              let ul = dom.newEl("ul");
              data.forEach(err => dom.newEl(`li{${err}}`, ul));
              content = ul.outerHTML;
            }
            dialog.alert(title, content);
          })
          .catch((err) => { console.log(err) });
        }
      },
      inputs: [
        {
          type: "password",
          description: "Staré heslo",
          attributes: { minlength: 6, required: "" },
          validate: text => !text.length ? "Vyplňte súčasné heslo!" : ""
        }, {
          type: "password",
          description: "Nové heslo",
          attributes: { minlength: 6, required: "" },
          validate: text => {
            console.log("asign", pass1, text);
            pass1 = text;
            return text.length < 6 ? "Nové heslo je príliš krátke!" : "";
          }
        }, {
          type: "password",
          description: "Nové heslo 2",
          attributes: { minlength: 6, required: "" },
          validate: text => {
            console.log("cmp", pass1, text);
            return pass1 != text ? "Heslá sa nezhodujú!" : !text ? "Znovu vyplňte nové heslo!" : ""
          }
        }
      ]
    })
  });
  DOMManager.getEl("#delete-account>a").addEventListener("click", function(e) {
    e.preventDefault();
    new ConfirmDialog({
      title: "Odstránenie účtu",
      message: "Naozaj chcete natrvalo odstrániť svoj účet?",
      confirm: {
        text: "Áno",
        fn: ()=>{
          let fd = new FormData();
          fd.append("<?=FormKeys::ACCOUNT_REMOVAL?>", "");
          fd.append("<?=FormKeys::USER_ID?>", "<?=$targetUser->getId()?>");

          fetch("ajax-forms.php", {method: 'POST', body: fd})
          .then(res => res.json())
          .then(data => {
            const len = data.length || Object.keys(data).length || 0;
            if (len === 0) window.location = "<?=PageLoader::makeLink(
              PageLoader::LOG_IN, ['action'=>FormKeys::SIGN_IN_FORM])?>";
          })
          .catch(err => console.log(err));
        }
      },
      cancel: { text: "Nie" }
    })
  });
})()</script>
<?php } ?>

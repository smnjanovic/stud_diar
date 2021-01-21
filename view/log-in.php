<?php
namespace MAIN;

use Model\PageLoader;
use Model\FormKeys;

$logIn = @$_GET["action"] === FormKeys::SIGN_IN_FORM;
$create = @$_GET["action"] === FormKeys::SIGN_UP_FORM;
$forgot = @$_GET["action"] === FormKeys::FORGOT_PASSWORD;
?>

<!--Prihlasenie.html-->
<div id="log-in">
    <form method="post" action="ajax-forms.php">
      <fieldset>
        <?php if ($create)  { ?>
          <legend>Registrácia</legend>
					<label>
						<span>Prihlasovacie meno</span>
						<i id="nickhint" class="hint"></i>
						<input
              type="text"
              name="<?=FormKeys::NICK?>"
              placeholder="Prihlasovacie meno"
              pattern="^[a-zA-Z0-9-_]{1,15}$"
              required="">
					</label>
					<label>
						<span>E-mail</span>
            <i id="emailhint" class="hint"></i>
						<input type="email" name="<?=FormKeys::EMAIL?>" placeholder="e-mail" required="">
					</label>
					<label>
						<span>Heslo</span>
            <i id="passhint" class="hint"></i>
						<input type="password" name="<?=FormKeys::PASS?>" placeholder="Heslo" minlength="6" required="" autocomplete="off">
					</label>
					<label>
						<span>Heslo (znovu)</span>
						<input type="password" name="<?=FormKeys::PASS2?>" placeholder="Heslo" minlength="6" required="" autocomplete="off">
					</label>
        <?php } elseif ($forgot)  { ?>
					<legend>Zabudnuté heslo</legend>
					<label>
						<span>Zadajte vaše prihlasovacie meno</span>
            <i id="nickhint" class="hint"></i>
            <input
              type="text"
              name="<?=FormKeys::NICK?>"
              placeholder="Prihlasovacie meno"
              pattern="^[a-zA-Z0-9-_]{1,15}$">
					</label>
					<label>
						<span>Ak ste zabudli aj Prihlasovacie meno zadajte e-mail.</span>
            <i id="emailhint" class="hint"></i>
						<input
              type="email"
              name="<?=FormKeys::EMAIL?>"
              placeholder="e-mail">
					</label>
					<span class="info">Vaše nové prihlasovacie údaje boli odoslané na váš e-mail!</span>
        <?php } elseif($logIn) { ?>
          <legend>Prihlásenie</legend>
					<label>
						<span>Prihlasovacie meno alebo e-mail</span>
            <i id="nickemailhint" class="hint"></i>
						<input type="text" name="<?=FormKeys::NICK?>" placeholder="Prihlasovacie meno alebo e-mail" required="">
					</label>
					<label>
						<span>Heslo</span>
            <i id="passhint" class="hint"></i>
						<input type="password" name="<?=FormKeys::PASS?>" placeholder="Heslo" minlength="6" required="" autocomplete="off">
            <a href="<?=PageLoader::makeLink("log-in",["action"=>FormKeys::FORGOT_PASSWORD])?>"
              class="new-password">Nepamätám si heslo</a>
					</label>
        <?php } ?>
        <ul id="error-list"></ul>
        <button type="submit" name="<?php
        if ($logIn) echo FormKeys::SIGN_IN_FORM;
        elseif($create) echo FormKeys::SIGN_UP_FORM;
        elseif($forgot) echo FormKeys::FORGOT_PASSWORD;
        ?>"><?php
        if ($logIn) echo "Prihlásiť sa";
        elseif($create) echo "Zaregistrovať sa";
        elseif($forgot) echo "Poslať";
        ?></button>
      </fieldset>
    </form>

    <!--kontrola vstupov-->
    <script>
      (function(){

        const mailPattern = /^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\[((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si;
        const nickPattern = /^[a-zA-Z0-9-_]+$/;

        const EMPTY_NICK = "Vyplňte prosím prihlasovacie meno!";
        const EMPTY_EMAIL = "Vyplňte prosím e-mail!";
        const EMPTY_PASSWORD = "Zadajte prosím heslo!";

        const INVALID_NICKEMAIL = "Vyplňte prosím prihlasovacie meno alebo email!";
        const INVALID_NICK = "Povolené sú len písmená bez diakritiky a číslice v max. počte: 15 znakov";
        const INVALID_EMAIL = "E-mail je v zlom formáte!";
        const SHORT_PASSWORD = "Heslo je príliš krátke";
        const CONFLICTING_PASSWORDS = "Heslá sa nezhodujú!";
        const LONG_NICK = "Prihlasovacie meno je príliš dlhé!";

        const form = document.querySelector("form");
        if (form == null) return;

        const nick = form.querySelector("input[name=nick]")
        const email = form.querySelector("input[name=email]")
        const pass = form.querySelector("input[name=pass]")
        const pass2 = form.querySelector("input[name=pass2]")

        const nickHint = form.querySelector("#nickhint");
        const emailHint = form.querySelector("#emailhint");
        const nickemailHint = form.querySelector("#nickemailhint");
        const passwordHint = form.querySelector("#passhint");

        const errorList = form.querySelector("#error-list");
        const submitter = form.querySelector("button[type=submit]");

        function isSubmissionPossible() {
          return nickHint && nickHint.innerHTML
            || emailHint && emailHint.innerHTML
            || passwordHint && passwordHint.innerHTML;
        }

        if (nickHint != null && nick != null) {
          nick.addEventListener("input", function() {
            const reg = /^[a-zA-Z0-9-_]{1,15}$/;
            if (nick.value == "") nickHint.innerHTML = EMPTY_NICK;
            else if(nick.value.length > 15) nickHint.innerHTML = LONG_NICK;
            else if (!nick.value.match(nickPattern))
              nickHint.innerHTML = INVALID_NICK;
            else nickHint.innerHTML = "";
            submitter.disabled = isSubmissionPossible();
          });
        }

        if (emailHint != null && email != null) {
          email.addEventListener("input", function() {
            const pattern = /^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\[((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si;

            <?php //formulár zabudnuté heslo nemá email ako povinný údaj ?>
            if (email.value == "") <?php if (!$forgot) { ?> emailHint.innerHTML = EMPTY_EMAIL;
              <?php } else { ?> emailHint.innerHTML = ""; <?php } ?>
            else if (!email.value.match(pattern)) emailHint.innerHTML = INVALID_EMAIL;
            else emailHint.innerHTML = email.validationMessage;
            submitter.disabled = isSubmissionPossible();
          });
        }

        if (nickemailHint != null && nick != null) {
          nick.addEventListener("input", function() {
            if (nick.value != "" && nick.value.match(nickPattern)
              && nick.value.length <= 15 || nick.value.match(mailPattern))
              nickemailHint.innerHTML = nick.validationMessage
            else nickemailHint.innerHTML = INVALID_NICKEMAIL;
            submitter.disabled = isSubmissionPossible();
          });
        }

        if (passwordHint != null && pass != null) {
          pass.addEventListener("input", function() {
            const pattern = /^((\"[^\"]*?\")|([^\(\)\<\>\@\,\;\:\\\"\[\]\s\*\/]+))@(\[((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}|((([a-zA-Z0-9\-]+)\.)+))([a-zA-Z]{2,}|(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\])$/si;
            if (pass.value == "") passwordHint.innerHTML = EMPTY_PASSWORD;
            else if (pass.value.length < 6) passwordHint.innerHTML = SHORT_PASSWORD;
            else if (pass2 != null && pass2.value.length > 0 && pass.value != pass2.value)
              passwordHint.innerHTML = CONFLICTING_PASSWORDS;
            else passwordHint.innerHTML = pass.validationMessage;
            submitter.disabled = isSubmissionPossible();
          });

          if (pass2 != null) {
            pass2.addEventListener("input", function() {
              if (pass.value != pass2.value) passwordHint.innerHTML = CONFLICTING_PASSWORDS;
              else if (pass.value.length < 6) passwordHint.innerHTML = SHORT_PASSWORD;
              else passwordHint.innerHTML = pass2.validationMessage;
            });
          }
        }

        form.addEventListener("submit", function(e) {
          e.preventDefault();

          let data = new FormData(form);
          data.append(e.submitter.getAttribute("name"), "");
          fetch(form.action, {method: 'POST', body: data})
            .then((res) => res.json())
            .then((data) => {
              console.log("data: ", data);
              const len = data.length || Object.keys(data).length || 0;
              errorList.innerHTML = ""
              errorList.className = (len === 0) ? "hidden" : "";
              data.forEach((item, i) => { errorList.innerHTML += `<li>${item}</li>` });
              if (len == 0) window.location = "<?=PageLoader::makeLink(PageLoader::ACCOUNT)?>";
            })
            .catch((error) => console.log(error));
        });
      })();
    </script>
</div>

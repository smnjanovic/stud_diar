<!--Prihlasenie.html-->
<div id="log-in" class="<?= @$_GET["action"] ? $_GET["action"] : "access-account" ?>">
    <ul>
      <li class="access-account"><a href="?page=<?= @$pageLoader->getPageData()["id"] ?>&action=access-account">Prihlásenie</a></li>
      <li class="create-account"><a href="?page=<?= @$pageLoader->getPageData()["id"] ?>&action=create-account">Registrácia</a></li>
    </ul>
    <form method="post" name="access-account" action="form/access-account.php">
      <fieldset>
        <legend>Prihlásenie</legend>
        <label>
          <span>Prihlasovacie meno</span>
          <i class="hint hidden">Povolených je max. 15 znakov (písmená bez diakritiky a číslice - nie na začiatku)</i>
          <input type="text" name="nick" value="" placeholder="Prihlasovacie meno" pattern="[A-Za-z][A-Za-z0-9]*" maxlength="15" required>
        </label>
        <label>
          <span>Heslo</span>
          <input type="password" name="pass" value="" placeholder="Heslo" minlength="6" required>
          <span class="new-password"><a href="?page=<?= @$pageLoader->getPageData()["id"] ?>&action=new-password">Nepamätám si heslo</a></span>
        </label>
        <span>
          <button type="submit">Prihlásiť</button>
        </span>
      </fieldset>
    </form>
    <form method="post" name="new-password" action="#">
      <fieldset>
        <legend>Zabudnuté heslo</legend>
        <label>
          <span>Zadajte vaše prihlasovacie meno</span>
          <input type="text" name="nick" value="" placeholder="Prihlasovacie meno" pattern="[A-Za-z][A-Za-z0-9]*" maxlength="15">
        </label>
        <label>
          <span>Ak ste zabudli aj Prihlasovacie meno zadajte e-mail.</span>
          <input type="email" name="email" value="" placeholder="e-mail">
        </label>
        <span class="info">Vaše nové prihlasovacie údaje boli odoslané na váš e-mail!</span>
        <span class="info">Konto s takýmto prihlasovacím menom alebo e-mailom neexistuje!</span>
        <span>
          <button class="access-account" type="reset">Späť</button>
          <button type="submit">Odoslať</button>
        </span>
      </fieldset>
    </form>
    <form method="post" name="create-account" action="form/create-account.php">
      <input type="hidden">
      <fieldset>
        <legend>Registrácia</legend>

        <label>
          <span>Prihlasovacie meno</span>
          <i class="hint hidden">Povolených je max. 15 znakov (písmená bez diakritiky a číslice - nie na začiatku)</i>
          <input type="text" name="nick" value="" placeholder="Prihlasovacie meno" pattern="[A-Za-z][A-Za-z0-9]*" maxlength="15" required>
        </label>

        <label>
          <span>E-mail</span>
          <input type="email" name="email" value="" placeholder="e-mail" required>
        </label>

        <label>
          <span>Heslo</span>
          <input type="password" name="pass" value="" placeholder="Heslo" minlength="6" required>
        </label>

        <label>
          <span>Heslo (znovu)</span>
          <input type="password" name="pass2" value="" placeholder="Heslo" minlength="6" required>
        </label>

        <button type="submit">Zaregistrovať sa</button>
      </fieldset>
    </form>

    <!--kontrola vstupov-->
    <script>
      (function(){
        const checking = document.querySelectorAll("i.hint+input[pattern]")

        function check(e) {
          const el = e.target
          const prev = el.previousElementSibling
          if (el == null || prev == null || !el.hasAttribute("pattern")) return
          const reg = new RegExp(`^${el.getAttribute("pattern")}$`)
          if (el.value == "" || el.value.match(reg)) prev.classList.add("hidden")
          else prev.classList.remove("hidden")
        }
        if (checking != null) checking.forEach((item, i) => { item.oninput = check });
      })()
    </script>
</div>

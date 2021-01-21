(() => {
  //konstanty
  const CONTAINER = DOMManager.getEl("#design-container");
  const MENU_OPENER = DOMManager.getEl("#design-show-tools", CONTAINER);
  const IMAGE_CONTROL = DOMManager.getEl("#image", CONTAINER);
  const SETTING_MENU = Object.assign([], DOMManager.getEls("#tool-options li", CONTAINER));

  const COLOR_TARGET = DOMManager.getEl("#color-settings select", CONTAINER);
  const COLOR_SETTERS = Object.assign([], DOMManager.getEls("#color-settings input", CONTAINER));

  const RESOLUTION_CHOICE = DOMManager.getEl("#customization select", CONTAINER);
  const OWN_RESOLUTION = DOMManager.getEl("#own-resolution", CONTAINER);
  const OWN_WIDTH = DOMManager.getEl("input[name=resolution-width]", OWN_RESOLUTION);
  const OWN_HEIGHT = DOMManager.getEl("input[name=resolution-height]", OWN_RESOLUTION);

  const TABLE_POS = DOMManager.getEl("input[name=table-position]", CONTAINER);
  const IMAGE_POS = DOMManager.getEl("input[name=image-position]", CONTAINER);

  const SCHED_TIME_LABEL = DOMManager.getEl("#schedule-setter h4", CONTAINER);
  const SCHED_TIME_SLIDERS = DOMManager.getEls("#sched-time-sliders input[type=range]", CONTAINER);

  const CLEAR_BTN = DOMManager.getEl("#sched-clr button", CONTAINER);
  const ADD_LESSON_BTN = DOMManager.getEl("#sched-add button", CONTAINER);

  const LESSON_TYPE = DOMManager.getEl("#schedule-setter select[name=lesson-type]", CONTAINER);
  const LESSON_SUB = DOMManager.getEl("#search", CONTAINER);
  const LESSON_ROOM = DOMManager.getEl("#schedule-setter input[name=room]", CONTAINER);

  for (res of [{w: 768, h:1024}, {w: 720, h:1280}, {w: 480, h:800},
    {w: 360, h:640}, {w: 320, h:568}, {w: 320, h:480}, {w: 1920, h:1200},
    {w: 1920, h:1080}, {w: 1680, h:1050}, {w: 1600, h:900}, {w: 1440, h:900},
    {w: 1366, h:768}, {w: 1360, h:768}, {w: 1280, h:1024}, {w: 1280, h:800},
    {w: 1280, h:720}, {w: 1024, h:768}]) {
    DOMManager.newEl(`option[value=${res.w}x${res.h}]{${res.w}×${res.h}}`, RESOLUTION_CHOICE);
  }
  DOMManager.newEl(`option[value=-1]{Vlastné}`, RESOLUTION_CHOICE);

  // spravca rozvrhu
  const SCHEDULE_MANAGER = new ScheduleManager(
    DOMManager.getEl("#search"),
    DOMManager.getEl("#filter"),
    schedule => DESIGN_MANAGER.setScheduleData(schedule)
  );

  // spravca dizajnu
  const DESIGN_MANAGER = new DesignManager(DOMManager.getEl("#drawable"), data => {
    // aktualizacia farieb
    COLOR_SETTERS.forEach((item, i) => {
      item.value = data.colors[COLOR_TARGET.value][i];
      item.nextElementSibling.innerText = item.value;
    });
    // aktualizacia pomeru stran
    OWN_WIDTH.value = data.aspectRatio[0];
    OWN_HEIGHT.value = data.aspectRatio[1];
    RESOLUTION_CHOICE.value = `${OWN_WIDTH.value}x${OWN_HEIGHT.value}`;
    if (!RESOLUTION_CHOICE.value) RESOLUTION_CHOICE.value = "-1";
    OWN_RESOLUTION.classList[RESOLUTION_CHOICE.value === "-1" ? "remove" : "add"]("hidden");
    //aktualizacia posunu obrazku a tabulky
    TABLE_POS.value = data.tablePos;
    TABLE_POS.nextElementSibling.innerText = `${data.tablePos}%`;
    IMAGE_POS.value = data.imagePos;
    IMAGE_POS.nextElementSibling.innerText = `${data.imagePos}%`;
    //aktualizacia rozlozenia obrazku na ploche
    let opt = DOMManager.getEl(`input[name=imageFit][value=${data.imageFit}]`);
    if (opt) opt.checked = true;
    else console.error("Nepodarilo sa načítať rozloženie obrázku!");
    // po nacitani konfiguracie nacitat rozvrh
    SCHEDULE_MANAGER.getLessons();
  });

  var imageButtonBusy = false;

  //udalosti
  function viewSettings(cls = null) {
    if (cls) CONTAINER.className = cls;
    else CONTAINER.removeAttribute("class");
  }

  function updateResolutionChoice() {
    if (OWN_WIDTH.value && OWN_HEIGHT.value) {
      DESIGN_MANAGER.setAspectRatio(`${OWN_WIDTH.value}x${OWN_HEIGHT.value}`);
    }
  }

  function scheduleTimeChange(e) {
    let day = "Pon Uto Str Štv Pia".split(" ")[SCHED_TIME_SLIDERS[0].value - 1] || "";
    let st = parseInt(SCHED_TIME_SLIDERS[1].value);
    let du = parseInt(SCHED_TIME_SLIDERS[2].value);
    let lessonCount = parseInt(SCHED_TIME_SLIDERS[1].max) + parseInt(SCHED_TIME_SLIDERS[2].min);
    SCHED_TIME_SLIDERS[2].max = lessonCount - st;
    if (st + du > lessonCount) {
      SCHED_TIME_SLIDERS[2].value = du = lessonCount - st;
      (SCHED_TIME_SLIDERS[2].nextElementSibling || {}).innerText = du;
    }
    SCHED_TIME_LABEL.innerText = `${day} ${st}${du > 1 ? `-${st + du - 1}.` : "."} hod.`;
    (e && e.target.nextElementSibling || {}).innerText = e && e.target.value;
  }

  SETTING_MENU[0].addEventListener("click", e => viewSettings(e.target.classList[0]));
  SETTING_MENU[1].addEventListener("click", e => viewSettings(e.target.classList[0]));
  SETTING_MENU[2].addEventListener("click", e => viewSettings(e.target.classList[0]));
  SETTING_MENU[3].addEventListener("click", e => viewSettings(null));

  MENU_OPENER.addEventListener("click", () => viewSettings(CONTAINER
    .hasAttribute("class") ? null : SETTING_MENU[0].classList[0]));

  for (cs of COLOR_SETTERS) {
    cs.addEventListener("input", e => {
      e.target.nextElementSibling.innerText = e.target.value;
      DESIGN_MANAGER.setColor(COLOR_TARGET.value, COLOR_SETTERS.map(x => parseInt(x.value)));
    });
    cs.addEventListener("change", () => DESIGN_MANAGER.storeColor(COLOR_TARGET.value));
  }
  COLOR_TARGET.addEventListener("change", e => {
    let col = DESIGN_MANAGER.getColor(e.target.value);
    COLOR_SETTERS.map((x, i) => {
      x.value = col[i];
      (x.nextElementSibling || {}).innerText = x.value;
    });
  });
  RESOLUTION_CHOICE.addEventListener("change", e => {
    let m = e.target.value.match(/^([0-9]+)x([0-9]+)$/);
    OWN_RESOLUTION.classList[m ? "add" : "remove"]("hidden");
    if (m) {
      DESIGN_MANAGER.setAspectRatio(e.target.value);
      OWN_WIDTH.value = m[1];
      OWN_HEIGHT.value = m[2];
    }
  });
  OWN_WIDTH.addEventListener("input", updateResolutionChoice);
  OWN_HEIGHT.addEventListener("input", updateResolutionChoice);
  IMAGE_POS.addEventListener("input", e => {
    DESIGN_MANAGER.setImagePos(e.target.value);
    e.target.nextElementSibling.innerText = `${e.target.value}%`;
  });
  TABLE_POS.addEventListener("input", e => {
    DESIGN_MANAGER.setTablePos(e.target.value);
    e.target.nextElementSibling.innerText = `${e.target.value}%`;
  });
  IMAGE_POS.addEventListener("change", () => DESIGN_MANAGER.storeImagePos());
  TABLE_POS.addEventListener("change", () => DESIGN_MANAGER.storeTablePos());
  DOMManager.getEls("input[name=imageFit]").forEach(item => item
    .addEventListener("click", radio => DESIGN_MANAGER.setImageFit(radio.target.value)));

  SCHED_TIME_SLIDERS.forEach(item => {
    item.addEventListener("input", scheduleTimeChange);
    item.nextElementSibling.innerText = item.value;
  });
  scheduleTimeChange();

  //uvolnit hodinu
  CLEAR_BTN.addEventListener("click", () => SCHEDULE_MANAGER.clearSchedule(
    SCHED_TIME_SLIDERS[0].value,
    SCHED_TIME_SLIDERS[1].value,
    SCHED_TIME_SLIDERS[2].value
  ));

  ADD_LESSON_BTN.addEventListener("click", () => SCHEDULE_MANAGER.addLesson(
    SCHED_TIME_SLIDERS[0].value,
    SCHED_TIME_SLIDERS[1].value,
    SCHED_TIME_SLIDERS[2].value,
    LESSON_TYPE.value === "course" ? 0 : 1,
    LESSON_SUB.value,
    LESSON_ROOM.value
  ));

  //pridat obrazok
  IMAGE_CONTROL.addEventListener("click", () => {
    if (imageButtonBusy) return;
    imageButtonBusy = true;

    var fd = new FormData();
    fd.append('get-user', '');

    fetch("ajax-forms.php", { method: "post", body: fd })
    .then(response => response.text())
    .then(result => {
      /*let uploadDialog = result > -1 ? {
        icon: "fa-photo",
        label: "Vložiť vlastný obrázok",
        action: () => new UploadDialog({
          title: "Vložiť obrázok",
          upload: (file) => {
            let msg = !file.type.match(/image\/(jpe?g)|(gif)|(png)|(bmp)/g)
            ? "Podporované typy súborov sú: jpg, jpeg, gif, png, bmp!"
            : file.size > 15 * 1024 * 1024 ? "Obrázok musí byť menší ako 15MB!" : null;
            if (msg) new AlertDialog({ title: "Neplatný vstup", message: msg });
            else {
              let fd = new FormData();
              fd.append('add-photo', '');
              fd.append('file', file)
              fetch("ajax-forms.php", { method: "post", body: fd })
              .then(res => res.json())
              .then(data => {
                if (data.msg) new AlertDialog({ title: "Neplatný vstup", message: data.msg });
                else {
                  data.id
                }
              })
              .catch(err => console.log(err));
            }
          },
          cancel: { text: "Zrušiť", fn: () => {} }
        })
      } : null;*/
      let hasImageBg = !!DESIGN_MANAGER.getImageUrl();

      let urlDialog = {
        title: "Obrázok",
        message: "Zadajte URL obrázku, ktorý chcete vložiť do pozadia.",
        confirm: {
          text: hasImageBg ? "Nahradiť" : "Vložiť",
          fn: inputs => DESIGN_MANAGER.setImageUrl(inputs[0].value)
        },
        inputs: [{ type: "url", attributes: { required: '' } }]
      }

      if (/*!!uploadDialog + */hasImageBg) {
        let opt = {};
        opt.title = "Obrázok na pozadí";
        opt.message = "Vyberte jednu z možností, ako zmeniť obrázok na pozadí.";
        opt.options = [];
        //if (uploadDialog) opt.options.push(uploadDialog);
        opt.options.push({
          icon: "fa-link",
          label: "Vložiť URL adresu obrázku",
          action: () => new PromptDialog(urlDialog)
        });
        if (hasImageBg) opt.options.push({
          icon: "fa-ban",
          label: "Odstrániť obrázok z pozadia",
          action: () => DESIGN_MANAGER.setImageUrl("")
        });
        new OptionDialog(opt);
      }
      else new PromptDialog(urlDialog);
      imageButtonBusy = false;
    });
  })
})();

(() => {
  const OPTIONS = DOMManager.getEl("#categories select");
  const QUERIES = DOMManager.getEl("#queries");
  const SEARCH = DOMManager.getEl("input[type=search]", QUERIES);
  const FILTER = DOMManager.getEl("ul", QUERIES);
  const SCROLLABLE = DOMManager.getEl("#notes-container");
  const LABEL = DOMManager.getEl("#category-description h3");
  const NOTES = DOMManager.getEl("#notes", SCROLLABLE);
  const NEW_NOTE = DOMManager.getEl("#new-note");

  const PROMPT_DIALOG = new PromptDialog();
  const REMOVAL_DIALOG = new ConfirmDialog();
  const PROMPT_INPUTS = [
    { type: "date", description: "Dátum platnosti" },
    { type: "time", description: "Čas platnosti" },
    { type: "search", description: "Predmet", attributes: { required: '' } },
    { type: "textarea", description: "Popis", attributes: { maxlength: 255, required: '' } }
  ];
  PROMPT_INPUTS[2].extraAction = (error, search, filter) => {
    NOTE_MANAGER.setSubjectFilter(search, filter, 5, (chosenItem, count) => {
      error.innerText = count > 1 ? "Vyberte jeden z výsledkov!" : count === 1
      ? "" : "Žiadny predmet nevyhovuje zadanému výrazu!";
    });
  }
  const REMOVAL_DIALOG_DATA = {
    title: "Odstrániť úlohu",
    msg: "Naozaj chcete zrušiť túto úlohu?",
    confirm: { text: "Áno" },
    cancel: { text: "Nie" }
  }

  const UNFOLD_DATE = date => {
    let dt = date instanceof Date ? date : typeof date === "number" ? new Date(parseInt(date)) : null;
    if (dt === null || isNaN(dt.getTime())) return [];
    return [dt.getFullYear(), dt.getMonth() + 1, dt.getDate(), dt.getHours(), dt.getMinutes()];
  }
  const NOTE_EDIT = note => {
    let dt = UNFOLD_DATE(note && note.date);
    let couple = n => `${parseInt(n/10)}${n%10}`;
    PROMPT_INPUTS[0].default = dt.length ? `${dt[0]}-${couple(dt[1])}-${couple(dt[2])}` : "";
    PROMPT_INPUTS[1].default = dt.length ? `${couple(dt[3])}:${couple(dt[4])}` : "";
    PROMPT_INPUTS[2].default = note ? note.subject && note.subject.abb || ""
    : OPTIONS.value === "subject-related" && FILTER.childElementCount === 0
    && SEARCH.value || "";
    PROMPT_INPUTS[3].default = note && note.info || "";
    PROMPT_DIALOG.destroy();
    PROMPT_DIALOG.build({
      title: `${note ? "Upravíť" : "Vytvoriť"} úlohu`, msg: "",
      inputs: PROMPT_INPUTS, confirm: {
        text: note ? "Upravíť" : "Vytvoriť",
        fn : inputs => {
          let now = new Date();
          let date = inputs[0].value && inputs[1].value
          ? `${inputs[0].value} ${inputs[1].value}` : inputs[0].value
          ? `${inputs[0].value} 22:00` : inputs[1].value
          ? `${[now.getFullYear(), now.getMonth() + 1, now.getDate()]
            .join('-')} ${inputs[1].value}` : '';

          if (date && (new Date(date)).getTime() < now.getTime() + 1000)
            new AlertDialog({ title: "Chyba", message: "Čas uplynul!" });
          else if (!note) NOTE_MANAGER.insertNote(date, inputs[2].value, inputs[3].value);
          else NOTE_MANAGER.updateNote(note.id, date, inputs[2].value, inputs[3].value);
        }
      }
    });
    PROMPT_DIALOG.popup();
  }
  const NOTE_REMOVE = note => {
    REMOVAL_DIALOG_DATA.confirm.fn = () => NOTE_MANAGER.deleteNote(note.id);
    REMOVAL_DIALOG.destroy();
    REMOVAL_DIALOG.build(REMOVAL_DIALOG_DATA);
    REMOVAL_DIALOG.popup();
  }
  const ITEM_DRAW = note => {
    const NOTE = DOMManager.newEl("div.note");
    const SUBJECT = DOMManager.newEl(`div.subject{${note.subject.abb}}`, NOTE);
    let dt = UNFOLD_DATE(note.date);
    let date = dt.length ? `${dt[2]}.${dt[1]}.${dt[0]} ${dt[3]}:${parseInt(dt[4]/10)}${dt[4]%10}` : '';
    const DATE = DOMManager.newEl(`div.date{${date}}`, NOTE);
    const INFO = DOMManager.newEl(`div.info{${note.info}}`, NOTE);
    const EDIT = DOMManager.newEl("div.edit.fa.fa-pencil", NOTE);
    const REMOVE = DOMManager.newEl("div.remove.fa.fa-remove", NOTE);

    let edit = () => NOTE_EDIT(note);
    SUBJECT.addEventListener("click", edit);
    DATE.addEventListener("click", edit);
    INFO.addEventListener("click", edit);
    EDIT.addEventListener("click", edit);
    REMOVE.addEventListener("click", evt => NOTE_REMOVE(note));
    return NOTE;
  }
  const NOTE_MANAGER = new NoteManager(50, 2000, SCROLLABLE, NOTES, ITEM_DRAW);

  const SET_CATEGORY = (category, offset = 0) => {
    Paginator.setPage(offset);
    let opt = Object.assign([], OPTIONS.children).find(x => x.value === category);
    let item = !opt || opt && opt.value !== "subject-related" ? category : SEARCH.value;
    URLManager.setItem("category", item);
  }

  const GET_CATEGORY = () => {
    let category = URLManager.getItem("category");
    if (category === undefined) category = "all-notes";
    else if (category.match(/[^\w-+]/)) category = "";
    return category;
  }

  const VISUALIZE_CATEGORY = () => {
    let page = Paginator.getPage();
    let category = GET_CATEGORY();
    let sub = "subject-related";
    let opt = (cat) => DOMManager.getEl(`option[value="${cat}"]`);
    OPTIONS.value = (opt(category) || opt(sub)).value;
    let isSub = OPTIONS.value === sub;
    QUERIES.classList[isSub ? "remove" : "add"]("hidden");
    SEARCH.value = isSub && category !== sub ? category : "";
  }

  const APPLY_CATEGORY = () => {
    let category = GET_CATEGORY();
    let opt = DOMManager.getEl(`option[value="${category}"]`, OPTIONS);
    LABEL.innerText = opt && opt.innerText || category;
    NOTE_MANAGER.loadNotes(category, Paginator.getPage());
  }

  const SELECT = () => {
    SET_CATEGORY(OPTIONS.value === "subject-related" ? "" : OPTIONS.value, 0);
    VISUALIZE_CATEGORY();
    APPLY_CATEGORY();
  }
  NOTE_MANAGER.setSubjectFilter(SEARCH, FILTER, 15, (data, count) => {
    if (count === 1) {
      SET_CATEGORY(data.abb, 0);
      APPLY_CATEGORY();
    }
  });
  OPTIONS.addEventListener("change", SELECT);
  NEW_NOTE.addEventListener("click", () => NOTE_EDIT());

  let href = window.location.href;
  let cat = (href.match(/category=[^\&\#]+/)||["category=all-notes"])[0].replace("category=", "");
  let page = Paginator.getPage();
  let noSub = DOMManager.getEl(`option[value=${cat}]:not([value=subject-related])`, OPTIONS);
  if (noSub) {
    QUERIES.classList.add("hidden");
    OPTIONS.value = noSub.value;
    LABEL.innerText = noSub.innerText;
  }
  else {
    QUERIES.classList.remove("hidden");
    OPTIONS.value = 'subject-related';
    SEARCH.value = cat;
    LABEL.innerText = cat;
  }
  VISUALIZE_CATEGORY();
  APPLY_CATEGORY();
})()

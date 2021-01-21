(() => {
  var noteURL = null;

  let getNotesId = () => {
    let fd = new FormData();
    fd.append("get-page", "notes");
    fetch("ajax-forms.php", { method: "post", body: fd })
    .then(res => res.text()).then(page => noteURL = parseInt(page))
    .catch(err => console.error(err));
    window.removeEventListener("load", getNotesId);
  }

  window.addEventListener("load", getNotesId);

  var subjectManager = null;
  let inputs = (abb="", name="") => [
    {
      type: "text", description: "Skratka", default: abb,
      attributes: {
        pattern: "^[a-zA-ZÀ-ž0-9-_]{1,5}$",
        minlength: 1, maxlength: 5, required: ""
      },
      validate: text => text.length < 1 ? "Vyplňte skratku predmetu!" :
        text.length > 5 ? "Skratka je príliš dlhá!" :
        text.match(/[^a-zA-ZÀ-ž0-9-_]/) ? "Nepovolené znaky v skratke predmetu!" : ""
    },
    {
      type: "text", description: "Celý názov", default: name,
      attributes: {
        pattern: "^[a-zA-ZÀ-ž0-9-_ ]{1,48}$",
        minlength: 1, maxlength: 48, required: ""
      },
      validate: text => text.length < 1 ? "Vyplňte názov predmetu!" :
        text.length > 48 ? "Názov predmetu je príliš dlhý!" :
        text.match(/[^a-zA-ZÀ-ž0-9-_ ]/) ? "Nepovolené znaky v názve predmetu!" : ""
    }
  ];
  let enableInteraction = (node, subject) => {
    node.addEventListener("click", click => new OptionDialog({
      title: "Možnosti",
      message: `Vyberte, čo sa má stať s predmetom ${subject.name}!`,
      options: [
        {
          icon: "fa-link",
          label: "Zobraziť úlohy",
          action: () => {
            if (typeof noteURL === "number" && !isNaN(noteURL))
              URLManager.setUrlData({ 'page': noteURL, 'category': subject.abb }, true);
          }
        },
        {
          icon: "fa-pencil",
          label: "Upraviť",
          action: () => new PromptDialog({
            title: "Upraviť predmet",
            message: "Zmeňte skratku alebo názov predmetu!",
            confirm: {
              text: "Upraviť",
              fn: inputs => subjectManager.updateSubject(subject.id, inputs[0].value,inputs[1].value)
            },
            inputs: inputs(subject.abb, subject.name)
          })
        },
        {
          icon: "fa-remove",
          label: "Odstrániť",
          action: () => {
            new ConfirmDialog({
              title: "Odstrániť predmet",
              message: "Odstránením tohoto predmetu odstránite aj všetky "
              + "úlohy a hodiny súvisiace s predmetom. Chcete pokračovať?",
              confirm: { text: "Áno", fn: () => subjectManager.removeSubject(subject.id) },
              cancel: { text: "Nie" }
            })
          }
        },
      ]
    }));
  }
  let itemDraw = subject => {
    const NS_SVG = "http://www.w3.org/2000/svg";
    const SUBJECT = DOMManager.newEl("div.subject");
    const SVG = DOMManager.newEl(`svg[viewBox="0 0 25 25"]`, SUBJECT, null, NS_SVG);
    const SVG_TEXT = DOMManager.newEl(`text[x=12.5][y=${
      subject.abb.length <= 1 ? 18 :
      subject.abb.length == 2 ? 17 :
      subject.abb.length == 3 ? 16 :
      subject.abb.length == 4 ? 15 : 14
    }][text-anchor=middle][font-weight=bold][font-size=${
      subject.abb.length <= 1 ? 14 :
      subject.abb.length == 2 ? 12 :
      subject.abb.length == 3 ? 9.25 :
      subject.abb.length == 4 ? 7 : 5
    }]{${subject.abb}}`, SVG, null, NS_SVG);
    const SUB_TITLE = DOMManager.newEl(`span.sub-name{${subject.name}}`, SUBJECT);
    enableInteraction(SUBJECT, subject);
    return SUBJECT;
  }
  let scrollable = DOMManager.getEl("html");
  let parentNode = DOMManager.getEl("#subjects");

  subjectManager = new SubjectManager(50, 2000, scrollable, parentNode, itemDraw);

  DOMManager.getEl("#new-subject").addEventListener("click", click => subjectManager
    && new PromptDialog({
    title: "Vytvoriť predmet",
    message: "Zadajte skratku a názov predmetu!",
    confirm: {
      text: "Pridať",
      fn: inputs => subjectManager.insertSubject(inputs[0].value, inputs[1].value)
    },
    inputs: inputs()
  }));
})()

// musi existovat trieda DOMManager

class Dialog {
  constructor(data) {
    if (data instanceof Object) {
      this.build(data);
      this.popup();
    }
  }

  onEnterPressed() { this.dismiss() }
  onEscapePressed() { this.dismiss() }
  onArrowUpPressed() {}
  onArrowDownPressed() {}

  /**
    vytvorenie DOM dialógu
  */
  build(data={}) {
    if (!(data instanceof Object)) throw Error("The 1st argument must be Object!");
    this.dialog = DOMManager.newEl(`div.popup`);
    const BODY = DOMManager.newEl("div.popup-body", this.dialog);
    DOMManager.newEl("div.popup-action", this.dialog);
    DOMManager.newEl(`h3{"${typeof data.title === "string" ? data.title : ""}"}`, BODY);
    DOMManager.newEl(`p{"${typeof data.message === "string" ? data.message : ""}"}`, BODY);
    this.dialog.dialogObject = this;
  }

  popup() {
    if (DOMManager.isEl(this.dialog)) {
      let dialogZone = DOMManager.getEl("#popup-zone");
      if (dialogZone === null) throw ("The '#popup-zone' node is missing!");
      if (!this.dialog.parentNode || this.dialog.parentNode !== dialogZone) dialogZone.appendChild(this.dialog);
    }
    else throw ("Dialog needs to be built first!");
  }

  dismiss() {
    if (this.dialog && this.dialog instanceof Node && this.dialog.nodeType == 1) {
      this.dialog.parentNode && this.dialog.parentNode.removeChild(this.dialog);
    }
  }

  destroy() {
    this.dismiss();
    this.dialog = null;
  }

  getActionBox() {
    return DOMManager.getEl("div.popup-action", this.dialog);
  }

  getContentBox() {
    return DOMManager.getEl("div.popup-body", this.dialog);
  }

  setTitle(title) {
    let el = DOMManager.getEl("h3", this.getContentBox());
    if (el) el.innerText = typeof title === "string" ? title : "Správa";
  }

  setMessage(message) {
    let el = DOMManager.getEl("p", this.getContentBox());
    if (el) el.innerText = typeof message === "string" ? message : "Detail správy";
  }
}

document.addEventListener("keydown", down => {
  let dialog = DOMManager.getEl("#popup-zone>.popup:last-child");
  if (dialog !== null && dialog.dialogObject instanceof Dialog && window.keypressed !== true) {
    window.keypressed = true;
    if (down.which === 13) dialog.dialogObject.onEnterPressed();
    else if (down.which === 27) dialog.dialogObject.onEscapePressed();
    else if (down.which === 38) dialog.dialogObject.onArrowUpPressed();
    else if (down.which === 40) dialog.dialogObject.onArrowDownPressed();
  }
});
document.addEventListener("keyup", down => { delete window.keypressed });

/**
* Zobrazí sa oznamovací dialóg
*/
class AlertDialog extends Dialog {
  build(data={}) {
    super.build(data);
    this.confirmEvent = () => {
      data.confirm && data.confirm.fn instanceof Function && data.confirm.fn();
      this.dismiss();
    }
    this.dialog.classList.add("alert");
    const LABEL = data.confirm && typeof data.confirm.text === "string" ? data.confirm.text : "OK";
    DOMManager.newEl(`button.confirm{"${LABEL}"}`, this.getActionBox()).addEventListener("click", ()=>this.confirmEvent());
  }

  onEnterPressed () {
    if (this.dialog) {
      let confirm = DOMManager.getEl("button.confirm", this.dialog);
      confirm && confirm.click();
    }
    else super.onEnterPressed();
  }

  onEscapePressed () {
    this.onEnterPressed();
  }
}

class ConfirmDialog extends AlertDialog {
  build(data={}) {
    super.build(data);
    this.cancelEvent = () => {
      data.cancel && data.cancel.fn instanceof Function && data.cancel.fn();
      this.dismiss();
    }
    this.dialog.classList.replace("alert", "confirm");
    const LABEL = data.cancel && typeof data.cancel.text === "string" ? data.cancel.text : "Zrušiť";
    DOMManager.newEl(`button.cancel{"${LABEL}"}`, this.getActionBox()).addEventListener("click", () => this.cancelEvent());
  }

  onEscapePressed () {
    if (this.dialog) {
      let cancel = DOMManager.getEl("button.cancel", this.dialog);
      cancel && cancel.click();
    }
    else super.onEscapePressed();
  }
}

class PromptDialog extends ConfirmDialog {
  build(data={}) {
    super.build(data);
    this.dialog.classList.replace("confirm","prompt");
    this.confirmEvent = () => {
      let toConfirm = true;
      let inputs = DOMManager.getEls(".field input, .field textarea", this.dialog);

      for (let input of inputs) {
        input.modified = true;
        toConfirm = (input.validate instanceof Function ? input.validate() : false) && toConfirm;
      }

      if (toConfirm) {
        data.confirm && data.confirm.fn instanceof Function && data.confirm.fn(inputs);
        this.dismiss();
      }
    }

    const INPUT_ZONE = DOMManager.newEl("form.input-zone", this.getContentBox());
    INPUT_ZONE.addEventListener("submit", e => e.preventDefault());
    // vkladat vstupne polia a nastavit im vlastnosti
    if (Array.isArray(data.inputs)) {
      const TYPES = {
        text: (placeholder) => `input[type=text][placeholder="${placeholder}"][autocomplete=off]`,
        textarea: (placeholder) => `textarea[placeholder="${placeholder}"]`,
        search: (placeholder) => `input[type=search][placeholder="${placeholder}"][autocomplete=off]`,
        number: (placeholder) => `input[type=number][placeholder="${placeholder}"][autocomplete=off]`,
        email: (placeholder) => `input[type=email][placeholder="${placeholder}"][autocomplete=off]`,
        tel: (placeholder) => `input[type=tel][placeholder="${placeholder}"][autocomplete=off]`,
        url: (placeholder) => `input[type=url][placeholder="${placeholder}"][autocomplete=off]`,
        password: (placeholder) => `input[type=password][placeholder="${placeholder}"][autocomplete=off]`,
        range: () => `input[type=range][autocomplete=off]`,
        color: () => `input[type=color][autocomplete=off]`,
        date: () => `input[type=date][autocomplete=off]`,
        time: () => `input[type=time][autocomplete=off]`,
        'datetime-local': () => `input[type=datetime-local][autocomplete=off]`,
        month: () => `input[type=month][autocomplete=off]`,
        week: () => `input[type=week][autocomplete=off]`
      };
      for (let input of data.inputs) {
        if (input instanceof Object) {
          const DESCRIPTION = typeof input.description === "string" ? input.description : "";
          const FIELD = DOMManager.newEl("div.field", INPUT_ZONE);
          const LABEL = DOMManager.newEl(`span.description{"${DESCRIPTION}"}`, FIELD);
          const ERROR = DOMManager.newEl("span.error", FIELD);
          const INPUT = DOMManager.newEl((TYPES[input.type] || TYPES.text)(DESCRIPTION), FIELD);
          INPUT.name = "input" + Object.assign([], FIELD.parentNode.children).indexOf(FIELD);
          INPUT.value = typeof input.default === "string" ? input.default : ""
          INPUT.modified = false;
          INPUT.validate = () => {
            ERROR.innerText = INPUT.modified ? input.validate instanceof Function
              && input.validate(INPUT.value) || INPUT.validationMessage : '';
            INPUT.modified = !!INPUT.modified;
            return !ERROR.innerText;
          };

          // pokial sa jedna o filtrovane vyhladavanie
          const FILTER = INPUT.type === "search" ? DOMManager.newEl("ul",
            DOMManager.newEl("div.filter", FIELD)) : null;

          //atributy
          if (input.attributes && input.attributes instanceof Object)
            for (let entry of Object.entries(input.attributes))
              INPUT.setAttribute(entry[0], entry[1]);

          //udalosti a doplnkove akcie
          INPUT.addEventListener("input", () => {
            INPUT.modified = true;
            INPUT.validate();
          });

          if (input.extraAction instanceof Function)
            input.extraAction(ERROR, INPUT, FILTER);
        }
      }
    }
  }

  // pokial nevyplnam textarea alebo search input, enterom potvrdim vstup
  onEnterPressed () {
    if (this.dialog && (!document.activeElement
      || "TEXTAREA INPUT".indexOf(document.activeElement.tagName) === -1
      || DOMManager.getEls("input[type=search], textarea", this.dialog)
      .indexOf(document.activeElement) === -1)) super.onEnterPressed();
  }

  popup() {
    super.popup();
    let first = DOMManager.getEl("textarea, input");
    first && first.focus();
  }
}

class OptionDialog extends Dialog {
  build(data={}) {
    let thisDialog = this;
    super.build(data);
    this.dialog.classList.add("options");
    const OPTIONS = DOMManager.newEl("table", this.getContentBox());
    if (!Array.isArray(data.options)) throw Error("Dialóg možností je bez možností!");
    let addItem = option => {
      const ROW = DOMManager.newEl("tr", OPTIONS);
      const ICON = DOMManager.newEl(`td.fa${option.icon && option.icon
        .match(/^fa-[a-z0-9-]+$/) ? `.${option.icon}` : ""}`, ROW);
      const LABEL = DOMManager.newEl(`td{${option.label}}`, ROW);
      ROW.addEventListener("click", evt => {
        option.action instanceof Function && option.action();
        thisDialog.dismiss();
      });
    }
    data.options.forEach(addItem);
    addItem({icon:"fa-arrow-left", label: "Zrušiť"});
  }

  onEnterPressed() {
    let row = DOMManager.getEl("tr.active", this.dialog);
    row && row.click() || super.onEnterPressed();
  }

  onEscapePressed() {
    let row = DOMManager.getEl("tr.active", this.dialog);
    row ? row.classList.remove("active") : super.onEscapePressed();
  }

  setActive(index) {
    let rows = DOMManager.getEls("tr", this.dialog);
    if (rows.length && typeof index === "number") {
      let active = DOMManager.getEl("tr.active", this.dialog);
      active && active.classList.remove("active");
      let i = parseInt(index % rows.length);
      if (i < 0) i += rows.length;
      rows[i].classList.add("active");
      this.active = i;
    }
    else {
      delete this.active;
    }
  }

  onArrowUpPressed() {
    this.setActive(this.active !== undefined ? this.active - 1 : -1);
  }

  onArrowDownPressed() {
    this.setActive(this.active !== undefined ? this.active + 1 : 0);
  }
}

class UploadDialog extends AlertDialog {
  build(data) {
    data.message = "";
    data.confirm = data.cancel;
    delete data.cancel;
    super.build(data);
    this.dialog.classList.replace("alert", "upload");

    const FORM = DOMManager.newEl("form", this.getContentBox());
    FORM.addEventListener("submit", e => e.preventDefault());
    const LABEL = DOMManager.newEl("label.drop-zone", FORM);
    const TEXT = DOMManager.newEl('span{"Sem vložte súbor!"}', LABEL);
    const FILE = DOMManager.newEl(`input[type=file][name="upload"]`, LABEL);
    if (!(data.upload instanceof Function))
      throw Error("Objekt musí obsahovať funkciu s názvom 'upload'!");
    FILE.addEventListener("change", e => data.upload(FILE.files[0]));
    LABEL.addEventListener("dragover", e => e.preventDefault());

    LABEL.addEventListener("drop", ev => {
      ev.preventDefault();
      if (ev.dataTransfer.items) {
        for (var i = 0; i < ev.dataTransfer.items.length; i++) {
          // If dropped items aren't files, reject them
          if (ev.dataTransfer.items[i].kind === 'file') {
            this.dismiss();
            data.upload(ev.dataTransfer.items[i].getAsFile());
            return false;
          }
        }
      } else {
        // Use DataTransfer interface to access the file(s)
        for (var i = 0; i < ev.dataTransfer.files.length; i++) {
          data.onUpload(ev.dataTransfer.items[i].getAsFile());
          this.dismiss();
          return false;
        }
      }
      return false;
    });
  }

}

class DOMManager {
  static isNode(node) {
    return node instanceof Object && !!node.nodeType;
  }

  static isEl(node) {
    return this.isNode(node) && node.nodeType === 1;
  }

  static isDoc(node) {
    return this.isNode(node) && node.nodeType === 9;
  }

  static scanCreator(creator) {
    const FORMAT = /^\w+(#[\w-]+)?(\.[\w-]+)*(\[[\w-]+=((\"((\\\")|([^\"]))+\")|([^\]]+))\])*(\{(\s|.)*\})?$/;
    if (typeof selector !== "string" && !creator.match(FORMAT)) throw Error(
      `Nebol dodržaný formát prvého parametra! Platné formáty:
      "div",
      "div#id",
      "div.class1",
      "div#id.class1.class2",
      "div[attr1=val1][attr2=val2]",
      "div#id.class1.class2[attr1=val1][attr2=val2]",
      "div{innerHTML}",
      "div#id.class1.class2{innerHTML}",
      "div#id.class1.class2[attr1=val1][attr2=val2]{innerHTML}".`
    );

    const TAG_ID_CLASS = creator.match(/^[\w-.#]*/)[0];
    const ATTRIBUTES = creator.replace(TAG_ID_CLASS, "")
      .match(/^(\[[\w-]+=((\"((\\\")|([^\"]))+\")|([^\]]+))\])*/)[0];

    const RET = {};
    RET.tag = TAG_ID_CLASS.match(/^\w+/)[0];
    RET.id = TAG_ID_CLASS.match(/#/) && TAG_ID_CLASS.replace(/.*#([\w-]+).*/, "$1") || "";
    RET.class = TAG_ID_CLASS.match(/\./) && (TAG_ID_CLASS.match(/\.[\w-]+/g)||[])
      .filter((x, i, a) => a.findIndex(cls => cls === x) === i).join(" ").replace(/\./g, "");
    RET.attributes = [];
    let content = creator.replace(ATTRIBUTES, "").replace(TAG_ID_CLASS, "").match(/^(\{(\s|.)*\})?$/)[0];
    RET.htmlEncode = content.substr(0,2) === '{"' && content.substr(content.length - 2) === '"}';
    RET.content = content.replace(/(^\{\"?)|(\"?\}$)/g, "");

    (ATTRIBUTES.match(/(\[[\w-]+=((\"[^\"]*\")|([^\]]+))\])/g) || []).forEach(atr => {
      let atval = atr.replace(/(^\[)|(\]$)/g, "").split("=");
      if (name !== "id" && name !== "class")
        RET.attributes.push({ name: atval[0], value: atval[1].replace(/(^\")|(\"$)/g, "") });
    });

    return RET;
  }

  static insert(child, parent, before = null) {
    let err = !this.isEl(child) ? "Parameter 1 musí byť element (potomok)!" :
    !this.isEl(parent) ? "Parameter 2 musí byť element (rodič)!" :
    before !== null && !this.isEl(before) && typeof before !== "number" ?
      "Parameter môže byť null, inak to musí byť element (pred ktorý bude "
        + "vložený potomok) alebo poradie elementu" : null;
    if (err) throw Error(err);
    parent.insertBefore(child, this.isEl(before) ? before : parent.children[before] || null);
    return child;
  }

  static newEl (creator, parent=null, before=null, ns=false) {
    if (typeof creator !== "string") throw Error("Prvý parameter musí byť reťazec!");
    if (parent !== null && (!(parent instanceof Node) || parent.nodeType !== 1))
      throw Error("Druhý parameter musí byť HTML element alebo null!");
    if (before !== null && (!(before instanceof Node) || before.nodeType !== 1))
      throw Error("Tretí parameter musí byť HTML element alebo null!");
    if (before !== null && before.parentNode !== parent)
      throw Error("Tretí parameter (element) musí byť potomkom druhého parametra (elementu) alebo null!");
    if (ns && ns !== true && typeof ns !== "string")
      throw Error("Štvrtý parameter (namespace) musí byť typu string alebo boolean!");

    let material = DOMManager.scanCreator(creator);
    let element = ns
      ? document.createElementNS(typeof ns === "string" ? ns : null, material.tag)
      : document.createElement(material.tag);
    material.id
      ? element.id = material.id : material.class
      ? element.className = material.class : undefined;
    if (material.htmlEncode) element.innerText = material.content;
    else element.innerHTML = material.content;
    material.attributes.forEach(atr => {
      ns ? element.setAttributeNS(null, atr.name, atr.value) :
        element.setAttribute(atr.name, atr.value);
    });
    return parent ? this.insert(element, parent, before) : element;
  }

  static getEl (selector, parent = document) {
    return (this.isEl(parent) || this.isDoc(parent)) && parent.querySelector(selector) || null;
  }
  static getEls (selector, parent = document) {
    return this.isEl(parent) || this.isDoc(parent) ? Object.assign([], parent.querySelectorAll(selector)) : [];
  }
  static popEl (element) {
    return this.isEl(element) && element.parentNode ? element.parentNode.removeChild(element) : null;
  }
}

window.dom = {
  isNode: function(node){
    return node instanceof Object && !!node.nodeType;
  },
  isEl: function (node){
    return node instanceof Object && !!node.nodeType;
  },
  isDoc: function (node){
    return node instanceof Object && !!node.nodeType;
  },
  newEl: function(creator, parent=null, before=null) {
    const format = /^\w+(#[\w-]+)?(\.[\w-]+)*(\[[\w-]+=(([^\]]+)|(\"[^\"]+\"))\])*(\{(\s|.)*\})?$/;
    if (typeof selector !== "string" && !creator.match(format))
    throw Error(`Nebol dodržaný formát prvého parametra! Platný formát môže byť napr.
      "div",
      "div#id",
      "div.class",
      "div[attribute=value],
      "div{contentHTML}",
      "div#id.class1.class2[attr1=val1][attr2=val2]{innerHTML}".`);
    if (parent !== null && (!(parent instanceof Object) || parent.nodeType !== 1))
      throw Error("Druhý parameter musí byť nejaký HTML element alebo null!");

    const TAG_ID_CLASS = (creator.match(/^[^\[\]\{\}]+/g)||[""])[0];
    const element = document.createElement(TAG_ID_CLASS.match(/^\w+/)[0]);
    if (TAG_ID_CLASS.match(/#/)) element.id = TAG_ID_CLASS.replace(/.*#([\w-]+).*/, "$1");
    if (TAG_ID_CLASS.match(/\./)) element.className = (TAG_ID_CLASS.match(/\.[\w-]+/g)||[]).join(" ").replace(/\./g, "");

    (creator.match(/(\[[\w-]+=(([^\]]+)|(\"[^\"]*\"))\])/g) || []).forEach(atr => {
      const atval = atr.replace(/(^\[)|(\]$)/g, "").split("=");
      const value = atval[1].replace(/(^\")|(\"$)/g, "");
      atval[0] && atval[0].match(/[A-Z]/) ? element.setAttributeNS(null, atval[0], value) : element.setAttribute(atval[0], value);
    });
    element.innerHTML = (creator.match(/\{(\s|.)*\}/g) || ["{}"])[0].replace(/(^\{)|(\}$)/g, "");
    if (parent)
      if (before) parent.insertBefore(element, before);
      else parent.appendChild(element);
    return element;
  },
  getEl: function(selector, parent = document) {
    return (this.isEl(parent) || this.isDoc(parent)) && parent.querySelector(selector);
  },
  getEls: function(selector, parent = document) {
    return (this.isEl(parent) || this.isDoc(parent)) && parent.querySelectorAll(selector);
  },
  popEl: function(element) {
    if (this.isEl(element) && element.parentNode)
    element.parentNode.removeChild(element);
  }
};

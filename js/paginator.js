class Paginator {

  static setPage(num) {
    let page = parseInt(num);
    if (isNaN(page) || page === Paginator.getPage()) return;
    let href = window.location.href;
    let hash = "";
    let hashIndex = href.indexOf("#");
    if (hashIndex > -1) {
      hash = href.substr(hashIndex);
      href = href.substr(0, hashIndex);
    }
    let url = (href.match(/offset\=[0-9]*/) ? href.replace(/offset\=[^\&]*/, `offset=${page}`) :
    `${href}${!href.match(/\?/) ? "?" : href.match(/\&$/) ? "" : "&"}offset=${page}`) + hash;
    history.pushState({}, document.title, url);
  }

  static getPage() {
    let offset = window.location.href.match(/offset=[0-9]*/);
    return parseInt(offset && offset[0].replace(/[^0-9]+/g, "")) || 0;
  }

  constructor(
    list,
    offset,
    itemsPerPage,
    maxItemCount,
    scrollable,
    parentNode,
    itemDraw,
    findItem,
    determineIndex
  ) {
    let err = e => { throw Error(e) };
    if (!Array.isArray(list))
      err("Parameter 1 musí byť pole!");
    else if (typeof offset !== "number" || offset < 0)
      err("Parameter 2 musí byť celé číslo vyššie ako -1!");
    else if (typeof itemsPerPage !== "number" || itemsPerPage < 1)
      err("Parameter 3 musí byť celé číslo vyššie ako 0!");
    else if (typeof maxItemCount !== "number" || maxItemCount < itemsPerPage)
      err("Parameter 4 musí byť celé číslo, nesmie byť menšie ako parameter 3!");
    else if (!DOMManager.isEl(scrollable))
      err("Parameter 5 musí byť element! Pre správne fungovanie triedy by to mal byť ten element, ktorý má scrollbar!");
    else if (!DOMManager.isEl(parentNode))
      err("Parameter 6 musí byť element! Budú doň vkladané jednotlivé položky zoznamu. Môže sa zhodovať s parametrom 5 alebo byť jeho potomkom.");
    else if (!(itemDraw instanceof Function))
      err("Parameter 7 musí byť funkcia! Mala by vykresľovať html pre jednotlivé položky zoznamu.");
    else if (!(determineIndex instanceof Function))
      err("Parameter 8 musí byť funkcia! Mala by nájsť určiť index prvku v zozname.");
    else if (!(determineIndex instanceof Function))
      err("Parameter 9 musí byť funkcia! Mala by nájsť určiť index prvku v zozname.");

    this.data = list;
    this.offset = offset;
    this.itemsPerPage = itemsPerPage;
    this.maxItemCount = maxItemCount;
    this.scrollable = scrollable;
    this.parentNode = parentNode;
    this.itemDraw = itemDraw;
    this.findItem = findItem;
    this.determineIndex = determineIndex;
    this.pages = [];

    document.addEventListener("scroll", () => this.scrollEvent());
    this.scrollable.addEventListener("scroll", () => this.scrollEvent());
    window.addEventListener("resize", () => this.scrollEvent());

    this.loadMore()
  }

  scrollEvent() {
    let s = this.scrollable;
    let ch = this.parentNode.children;
    if (s.scrollTop + 2 * s.clientHeight >= s.scrollHeight) this.loadMore();
    let page = 0;
    let len = parseInt(ch.length / this.itemsPerPage);
    while (page < len) {
      let start = page * this.itemsPerPage;
      let end = start + this.itemsPerPage - 1;
      let t = ch[start].offsetTop - ch[0].offsetTop;
      if (s.scrollTop < t) break;
      let b = ch[end].offsetTop - ch[0].offsetTop;
      if (t <= s.scrollTop && s.scrollTop < b || s.scrollTop < t) break;
      page++;
    }
    Paginator.setPage(page + this.offset / this.itemsPerPage);
  }

  loadMore() {
    let st = this.parentNode.childElementCount;
    let more = this.data.slice(st, st + this.itemsPerPage);
    if (more.length) {
      more.forEach(item => DOMManager.insert(this.itemDraw(item), this.parentNode));
      if (2*this.scrollable.clientHeight >= this.scrollable.scrollHeight) this.loadMore();
    }
  }

  setItem(item) {
    let oldItem = this.data[this.findItem(item, this.data)];
    //vymazat stare
    if (oldItem) {
      let index = this.data.indexOf(oldItem);
      this.data.splice(index, 1);
      if (index < this.parentNode.childElementCount)
        DOMManager.popEl(this.parentNode.children[index]);
    }
    //vlozit nove
    let index = this.determineIndex(item, this.data);
    this.data.splice(index, 0, item);
    if (index <= this.parentNode.childElementCount)
      DOMManager.insert(this.itemDraw(item), this.parentNode, index);
  }

  removeItem(item) {
    let index = this.findItem(item, this.data);
    if (index > -1) {
      this.data.splice(index, 1);
      if (index < this.parentNode.childElementCount)
        DOMManager.popEl(this.parentNode.children[index]);
    }
  }

  setData(list, offset) {
    if (!Array.isArray(list))
      throw Error("Parameter 1 musí byť pole!");
    if (typeof offset !== "number" || offset % 1 > 0 || offset < 0)
      throw Error("Parameter 2 musí byť prirodzené číslo!");
    this.scrollable.scrollTop = 0;
    this.parentNode.innerHTML = "";
    this.data = list;
    this.offset = offset;
    this.loadMore();
  }
}

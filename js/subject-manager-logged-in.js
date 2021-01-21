class SubjectManager {
  constructor(itemsPerPage, maxItemCount, scrollable, parentNode, itemDraw) {
    if (
      typeof itemsPerPage === "number" &&
      typeof maxItemCount === "number" &&
      itemsPerPage > 0 &&
      maxItemCount >= itemsPerPage &&
      DOMManager.isEl(scrollable) &&
      DOMManager.isEl(parentNode) &&
      itemDraw instanceof Function
    ) {
      let startIndex = Paginator.getPage() * itemsPerPage;
      this.jsonAction({
        'load-subjects': '',
        'start-index': startIndex,
        'item-count': maxItemCount
      },
      list => {
        let findItem = (item, list) => list.findIndex(x => x.id == item.id);
        let determineIndex = (item, list) => list.filter(x => x.abb < item.abb).length;
        this.paginator = new Paginator(
          list,
          startIndex,
          itemsPerPage,
          maxItemCount,
          scrollable,
          parentNode,
          itemDraw,
          findItem,
          determineIndex
        );
      })
    }
    else throw Error("Nesprávne parametre!");
  }

  getCols(id, abb, name) {
    let result = {};
    if (typeof id === "number") result['id'] = id;
    if (typeof abb === "string") result['abb'] = abb;
    if (typeof name === "string") result['name'] = name;
    return result;
  }

  checkPaginator() {
    if (!(this.paginator instanceof Paginator))
      throw Error("Stránkovač ešte nebol načítaný!");
  }

  jsonAction(input, output) {
    if (input instanceof Object && output instanceof Function) {
      let fd = new FormData();
      Object.keys(input).forEach(key => fd.append(key, input[key]));
      fetch("ajax-forms.php", {method: "post", body: fd})
      .then(response => response.json())
      .then(result => output(result))
      .catch(err => console.log(err));
    }
  }

  insertSubject(abb, name) {
    this.checkPaginator();
    let input = this.getCols(null, abb, name);
    input['insert-subject'] = '';
    console.log("input", input);
    this.jsonAction(input, result => {
      console.log("output", result);
      if (result.msg !== undefined) new AlertDialog({ title: "Chyba", message: result.msg });
      else this.paginator.setItem(result.subject);
    });
  }

  updateSubject(id, abb, name) {
    this.checkPaginator();
    let input = this.getCols(id, abb, name);
    input['update-subject'] = '';
    this.jsonAction(input, result => {
      if (result.msg !== undefined) new AlertDialog({ title: "Chyba", message: result.msg });
      else this.paginator.setItem(result.subject);
    })
  }

  removeSubject(id) {
    this.checkPaginator();
    let input = this.getCols(id, null, null);
    input['delete-subject'] = '';
    this.jsonAction(input, result => {
      if (result.msg) new AlertDialog({ title: "Chyba!", message: result.msg });
      else this.paginator.removeItem(result.subject);
    });
  }
}

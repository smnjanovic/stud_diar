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
      this.worker = new Worker("js/worker.js");
      let g = () => this.getSubjects();
      let startIndex = Paginator.getPage() * itemsPerPage;
      this.worker.postMessage( { loadSubjects: { storage:g(), start: startIndex, count: maxItemCount } } );
      this.worker.onmessage = msg => {
        let findItem = (item, list) => list.findIndex(x => x.id == item.id);
        let determineIndex = (item, list) => list.filter(x => x.abb < item.abb).length;
        this.paginator = new Paginator(
          msg.data,
          startIndex,
          itemsPerPage,
          maxItemCount,
          scrollable,
          parentNode,
          itemDraw,
          findItem,
          determineIndex
        );
      };
    }
    else throw Error("Nesprávne parametre!");
  }

  checkPaginator() {
    if (!(this.paginator instanceof Paginator))
      throw Error("Stránkovač ešte nebol načítaný!");
  }

  getSubjects() {
    return STORAGE.subjects;
  }

  setData(data) {
    STORAGE.subjects = data;
  }

  insertSubject(abb, name) {
    this.checkPaginator();
    let fn = () => this.getSubjects();
    this.worker.postMessage({addSubject:{storage:fn(),abb:abb,name:name}});
    this.worker.onmessage = e => {
      if (e.data.msg !== undefined) new AlertDialog({ title: "Chyba", message: e.data.msg })
      else {
        this.paginator.setItem(e.data.subject);
        this.setData(e.data.storage);
      }
    }
  }

  updateSubject(id, abb, name) {
    this.checkPaginator();
    let fn = () => this.getSubjects();
    this.worker.postMessage({editSubject:{storage:fn(),id:id,abb:abb,name:name}});
    this.worker.onmessage = e => {
      if (e.data.msg !== undefined) new AlertDialog({title:"Chyba",message:e.data.msg})
      else {
        this.paginator.setItem(e.data.subject);
        this.setData(e.data.storage);
      }
    }
  }

  removeSubject(id) {
    this.checkPaginator();
    let fn = () => this.getSubjects();
    this.worker.postMessage({removeSubject:{id:id,subjects:fn()}});
    this.worker.onmessage = e => {
      if (e.data.msg) new AlertDialog({ title: "Chyba!", message: e.data.msg });
      else {
        this.paginator.removeItem(e.data.subject);
        this.setData(e.data.storage);
      }
    }
  }
}

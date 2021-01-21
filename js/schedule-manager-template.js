class ScheduleManagerTemplate {
  constructor(onDataSetChanged) {
    if (!(onDataSetChanged instanceof Function)) throw Error("Parameter 'onDataSetChanged' musí byť funkcia!");
    this.onDataSetChanged = onDataSetChanged;
  }

  addLesson(day, start, dur, type, subject, room) {}

  clearSchedule(day, start, dur) {}

  getLessons() {}

  onScheduleReceived(data) {
    if (data.msg !== undefined) new AlertDialog({title: "Chyba", message: data.msg})
    else this.onDataSetChanged(data.lessons);
  }
}

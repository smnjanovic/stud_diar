class ScheduleManager extends ScheduleManagerTemplate {
  constructor(search, filter, onDataSetChanged) {
    super(onDataSetChanged);
    new SubjectBrowser(search, filter, 5, ()=>{});
  }

  post (label, day=null, start=null, dur=null, type=null, subject=null, room=null) {
    let fd = new FormData();
    fd.append(label, '');
    if (day !== null) fd.append('day', day);
    if (start !== null) fd.append('start', start);
    if (dur !== null) fd.append('dur', dur);
    if (type !== null) fd.append('type', type);
    if (subject !== null) fd.append('subject', subject);
    if (room !== null) fd.append('room', room);
    fetch("ajax-forms.php", { method: "post", body: fd })
    .then(res => res.json())
    .then(data => this.onScheduleReceived(data))
    .catch(err => console.log(err));
  }

  addLesson(day, start, dur, type, subject, room) {
    this.post('add_lesson', day, start, dur, type, subject, room);
  }

  clearSchedule(day, start, dur) {
    this.post('clear_schedule', day, start, dur);
  }

  getLessons() {
    this.post('get_lessons');
  }
}

var myCalendar = {
  month: document.querySelectorAll('[data-calendar-area="month"]')[0],
  next: document.querySelectorAll('[data-calendar-toggle="next"]')[0],
  previous: document.querySelectorAll('[data-calendar-toggle="previous"]')[0],
  label: document.querySelectorAll('[data-calendar-label="month"]')[0],
  activeDates: null,
  date: new Date(),
  todaysDate: new Date(),

  init: function (options) { // инициализация
    this.options = options
    this.date.setDate(1) // 
    this.createMonth() // отрисовка дней месяца
    this.createListeners() // переключение месяцев
  },

  createListeners: function () { // переключалка месяцев
    var _this = this
    this.next.addEventListener('click', function () { // при клике показать следующий месяц
      _this.clearCalendar() // очищаем календарь - все дни
      var nextMonth = _this.date.getMonth() + 1 // следущий_месяц = текущий + 1
      _this.date.setMonth(nextMonth) // устанавливаем в календарь следующий месяц
      _this.createMonth() // перерисовать дни в календаре
    })
    // Clears the calendar and shows the previous month
    this.previous.addEventListener('click', function () { // при клике показать предыдущий месяц
      _this.clearCalendar() // очистить календарь (дни)
      var prevMonth = _this.date.getMonth() - 1 // пред_месяц = текущий - 1
      _this.date.setMonth(prevMonth) // устанавливаем в календарь предыдущи месяц
      _this.createMonth() // перерисовываем дни в календаре
    })
  },

  createDay: function (num, day, year) { // создание дней
    var newDay = document.createElement('div') // ячейка div для span. div имеет атрибут data-calendar-date = дате
    var dateEl = document.createElement('span') // span в котором отображается сам день - число месяца
    var msec = 1000 // миллисекунд в секунде
    var daysec = 86400 // секунд в сутках -> 60сек * 60мин * 24часа
    var dayperiod = 31 // период через сколько дней блок для резерва начиная с текущего дня
    dateEl.innerHTML = num // заполняем span переменной переданной в функцию
    newDay.className = 'mycal-date'  // класс div = mycal-date
    newDay.setAttribute('data-calendar-date', ('0' + this.date.getDate()).slice(-2)
    +'-'+ ('0' + (this.date.getMonth() + 1)).slice(-2) +'-'+ this.date.getFullYear()) // назначаем div'у атрибут data-calendar-date = дате !!!!! ТУТ ФОРМАТ ДАТЫ!!!

    // if it's the first day of the month
    if (num === 1) {
      if (day === 0) {
        newDay.style.marginLeft = (6 * 14.28) + '%'
      } else {
        newDay.style.marginLeft = ((day - 1) * 14.28) + '%'
      }
    }

    if (this.options.disableDays && this.date.getTime() <= this.todaysDate.getTime() - 1) {
      newDay.classList.add('mycal-date--disabled')
    }
    else
    if (this.options.disableDays && this.date.getTime() >= this.todaysDate.getTime() + (msec*daysec*dayperiod)) {
      newDay.classList.add('mycal-date--disabled')
    }
    else {
      newDay.classList.add('mycal-date--active')
      newDay.setAttribute('data-calendar-status', 'active')
    }

    if (this.date.toString() === this.todaysDate.toString()) {
      newDay.classList.add('mycal-date--today')
    }

    newDay.appendChild(dateEl)
    this.month.appendChild(newDay)
  },

  dateClicked: function () {
    var _this = this
    this.activeDates = document.querySelectorAll(
      '[data-calendar-status="active"]'
    )
    for (var i = 0; i < this.activeDates.length; i++) {
      this.activeDates[i].addEventListener('click', function (event) {
        //var picked = document.querySelectorAll('[data-calendar-label="picked"]')[0]
        //picked.innerHTML = this.dataset.calendarDate
        var divdate= document.getElementById('divdate'); // берем див календаря
	    divdate.style.display = 'none'; // и прячем
        document.getElementById('inpdate').value = this.dataset.calendarDate // пишем в input значение из div'а по которому прошел клик мышкой
        document.dateFinder.submit(); //сабмитим форму
        _this.removeActiveClass()
        this.classList.add('mycal-date--selected')
      })
    }
  },

  createMonth: function () {
    var currentMonth = this.date.getMonth()
    while (this.date.getMonth() === currentMonth) {
      this.createDay(
        this.date.getDate(),
        this.date.getDay(),
        this.date.getFullYear()
      )
      this.date.setDate(this.date.getDate() + 1)
    }
    // while loop trips over and day is at 30/31, bring it back
    this.date.setDate(1)
    this.date.setMonth(this.date.getMonth() - 1)

    this.label.innerHTML =
      this.monthsAsString(this.date.getMonth()) + ' ' + this.date.getFullYear()
    this.dateClicked()
  },

  monthsAsString: function (monthIndex) {
    return [
      'Январь',
      'Февраль',
      'Март',
      'Апрель',
      'Май',
      'Июнь',
      'Июль',
      'Август',
      'Сентябрь',
      'Октябрь',
      'Ноябрь',
      'Декабрь'
    ][monthIndex]
  },

  clearCalendar: function () { // очищаем календарь
    myCalendar.month.innerHTML = ''
  },

  removeActiveClass: function () {
    for (var i = 0; i < this.activeDates.length; i++) {
      this.activeDates[i].classList.remove('mycal-date--selected')
    }
  }
}
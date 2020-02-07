function find(array, value) {

  for (var i = 0; i < array.length; i++) {
    if (array[i][3] == value) return i;
  }

  return -1;
}

var hideCal = function(){
	var divdate= document.getElementById('divdate');
	divdate.style.display = 'none';
}

var showCal = function (){
	var divdate= document.getElementById('divdate');
	divdate.style.display = 'block'; // показываем календарь
}

function res(id){

	element = document.getElementById(id); // присваиваем id элемента по которому произошел клик
	color = window.getComputedStyle(element).backgroundColor; // получаем цвет ячейки по которой произошел клик

	if (color == 'rgb(238, 255, 238)'){ // если цвет выьранной ячейки "свободно для резерва"
		userOrder.push(id); // помещаем в массив заказа выбранный элемент
		document.getElementById(id).style.backgroundColor = '#ffd'; // меняем цвет ячейки на цвет "зарезервировать"
	}
	else
	if (color == 'rgb(255, 255, 221)'){ // если цвет выбранной ячейки "зарезервировать"
		userOrder.splice(find(userOrder,id),1); // удаляем из массива заказа userOrder выбранный в таблице элемент
		document.getElementById(id).style.backgroundColor = '#efe'; // меняем на цвет "свободо для резерва"
	}

	if (userOrder.length==0){ // если массив пользовательского заказа пуст, то есть пользователь ничего не выбрал
		document.getElementById('magicBtn').style.display = 'none'; // прячем кнопку Зарезервировать
	}
	// иначе если массив имеет позиции
	else if (userOrder.length!=0) {
		document.getElementById('magicBtn').style.display = 'block'; // показываем кнопку		
		input = document.getElementById('order'); // добавляем в скрытую форму элементы заказа
		order.value = userOrder; // приравниваем значение из массива заказа
	}

}
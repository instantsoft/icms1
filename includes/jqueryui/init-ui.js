$(function() {

// ========================================================================== //
//перетаскиваемые элементы

$( ".uidrag" ).draggable({});
//умолчания см. http://api.jqueryui.com/draggable/

// ========================================================================== //
//элементы контейнеры для перетаскиваемых элементов

//$( ".uidrop" ).droppable({
//умолчания см. http://api.jqueryui.com/droppable/

// ========================================================================== //
//авторесайз элементы

//$( ".autores" ).resizable({});
//умолчания см. http://api.jqueryui.com/resizable/

// ========================================================================== //
//выборки

//$( ".uiselect" ).selectable({
//умолчания см. http://api.jqueryui.com/selectable/

// ========================================================================== //
//сортируемые элементы

//$(".uisort").sortable();
//умолчания см. http://api.jqueryui.com/sortable/

//$( ".uisort" ).disableSelection();

//ВИДЖЕТЫ:
// ========================================================================== //
//Аккордеон

$( ".uiacc" ).accordion({});
//умолчания см. http://api.jqueryui.com/accordion/

// ========================================================================== //
//Автоподстановка значений

//$( ".autocomp" ).autocomplete({});
//умолчания см. http://api.jqueryui.com/autocomplete/

// ========================================================================== //
//Кнопки, тулбары и т.п.

$( ".uibtn" ).button({});
//умолчания см. http://api.jqueryui.com/button/

//доступны те же опции
$( ".uibtnset" ).buttonset({});

// ========================================================================== //
//Установка дат

$( "#pubdate, #enddate, #answerdate" ).datepicker({
//умолчания см. http://api.jqueryui.com/datepicker/

//altField: "",
//altFormat: "",
//appendText: "",
autoSize: true,
buttonImage: "/images/icons/date.gif",
buttonImageOnly: true,
//buttonText: "...",
calculateWeek: jQuery.datepicker.iso8601Week,
changeMonth: true,
changeYear: true,
//closeText: 'Закрыть',
//prevText: '&#x3C;Пред',
//nextText: 'След&#x3E;',
//currentText: 'Сегодня',
//monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
//monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
//dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
//dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
//dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
//weekHeader: 'Нед',
dateFormat: 'dd.mm.yy',
firstDay: 1,
isRTL: false,
showMonthAfterYear: false,
yearSuffix: '',
constrainInput: false,
//defaultDate: null,
//duration: "normal",
gotoCurrent: false,
//hideIfNoPrevNext: false,
maxDate: "+5y",
minDate: "-5y",
//navigationAsDateFormat: false,
//numberOfMonths: 1,
selectOtherMonths: false,
//shortYearCutoff: "+10",
//showAnim: "show",
showButtonPanel: true,
//showCurrentAtPos: 0,
//showMonthAfterYear: false,
showOn: "both",
//showOptions: {},
showOtherMonths: true,
//showWeek: false,
//stepMonths: 1,
//yearRange: "c-10:c+10",
//beforeShow: null,
//beforeShowDay: null,
//onChangeMonthYear: null,
//onClose: null,
//onSelect: null

//no events

});
//$( "#enddate" ).datepicker("option", "dateFormat", "yy-mm-dd");
// ========================================================================== //
//Диалоги, модалки

$( ".uidialog" ).dialog({});
//умолчания см. http://api.jqueryui.com/dialog/

// ========================================================================== //
//Меню

$( ".uimenu" ).menu({});
//умолчания см. http://api.jqueryui.com/menu/

// ========================================================================== //
//Прогрессбар

 $( ".uipbar" ).progressbar({});
//умолчания см. http://api.jqueryui.com/progressbar/

// ========================================================================== //
//Слайдеры

$( ".uisl" ).slider({});
//умолчания см. http://api.jqueryui.com/slider/

// ========================================================================== //
//Спиннеры

$( '.uispin').spinner();
//умолчания см. http://api.jqueryui.com/spinner/

// ========================================================================== //
//Табы

$( ".uitabs" ).tabs({});
//умолчания см. http://api.jqueryui.com/tabs/

// ========================================================================== //
//Тултипы

$( '.uittip' ).tooltip({});
//умолчания см. http://api.jqueryui.com/tooltip/


//подхватываем от lightbox
//$( '.lightbox-enabled' ).colorbox({ transition: "none", width: "90%", height: "90%"});

});
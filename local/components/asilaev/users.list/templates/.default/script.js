function init() {
    var elem = document.querySelectorAll('.export');
    for (var i = 0; i < elem.length; i++)
        elem[i].addEventListener('click', exportAction);
}

exportAction = function () {
    exportActionAjax(this.dataset.type);
}

exportActionAjax = function(type, data = []) {
    BX.ajax.runComponentAction('asilaev:users.list',
        'startDataExport', {
            mode: 'class',
            data: {post: {type: type, data: data}},
        })
        .then(function (response) {
            console.log(response);
            if (response.status === 'success') {
                if(response.data.done) {
                    document.querySelector('.export-result-' + type).innerHTML =
                        'Файл успешно сгенерирован <br/><a href="'+ response.data.filename + '" >Загрузить</a>';
                }
                else {
                    exportActionAjax(type, response.data);
                    document.querySelector('.export-result-' + type).innerHTML =
                        'Генерируем файл...<br/>Загрузили  '+ response.data.num + ' строк';
                }
            }
            else {

            }
        });
}
BX.ready(function () {
    init();
});

BX.addCustomEvent('onAjaxSuccess', function () {
    init();
});

$().ready(function () {
    if (Cookies.get('uuid') != null) {
        layer.msg('sorry, you had gotten id.', {icon: 2});
        Cookies.remove('uuid');
        return;
    }
    $.ajax({
        url: "?r=site/get-uid",
        type: "GET",
        dataType: "JSON"
    }).done(function (data) {
        console.log(data);
        Cookies.set('uuid', data.uuid, {expires: 1, path: '/'}); // set cookie uuid
        layer.alert(
            'Ok, your uuid is ' + data.uuid,
            {icon: 1, btn: ['进入活动页'], skin: 'layui-layer-lan'},
            function (index) {
                layer.close(index);
                redirect({view: "index",url: true});
            }
        );
    }).fail(function (xhr, status) {
        console.log(xhr.responseText);
        console.error(status);
    });
});

function redirect(data) {
    $.get(
        "?r=site/redirect",
        data,
        function (result) {
            console.log(result.url);
            window.location.href = result.url;
        },
        "JSON"
    );
}
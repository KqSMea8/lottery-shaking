jQuery(document).ready(function($){

    var uuid = Cookies.get("uuid");
    if (typeof(uuid) == "undefined") {
        layer.alert(
            '你尚未注册哦，无法获取合法权限！',
            {icon: 2, title: '注册码获取失败', btn: ['返回注册']},
            function (index) {
                layer.close(index);
                self.location = "?r=site/index";
            }
        )
        return;
    }
    layer.alert(
        '活动权限获取成功',
        {icon: 1, title: '操作成功', skin: 'layui-layer-lan'},
        function (index) {
            layer.close(index);
            shakePhone(uuid);
        }
    );
// refer to index.php

});

/**
 * count shake times and update to database
 * @param uuid
 */
function shakePhone(uuid) {
    var times = -1; // 记录摇动次数
    var last_time = 0;
    var borderSpeed = 800;  // 加速度变化临界值
    var last_update_time = 0;
    var x = y = z = last_x = last_y = last_z = 0;
    if (window.DeviceMotionEvent) {
        window.addEventListener('devicemotion',shake,false);
    }
    else
    {
        layer.alert('您的设备不支持摇一摇哦');
        return;
    }

    var allTime = 0;
    const timeId = setInterval(function () {
        if (allTime > 10e3) {
            clearInterval(timeId);
            layer.alert('activity end!', {icon: 0})
        }
        updateToDb(times, uuid); // TODO: update once every 200ms
        allTime += 200;
    }, 200);

    // 每次手机移动的时候都会执行下面shake函数的代码
    function shake(eventData)
    {
        var acceleration = eventData.accelerationIncludingGravity;
        var curTime = new Date().getTime();
        var diffTime  = curTime - last_time;
        // 每隔100ms进行判断
        if (diffTime > 100) {
            x = acceleration.x;
            y = acceleration.y;
            z = acceleration.z;
            var speed = Math.abs(x + y + z - last_x - last_y - last_z) / diffTime * 10000;
            // 判断手机确实发生了摇动而不是正常的移动
            if (speed > borderSpeed) {
                times ++;
                document.getElementById("shake").innerHTML = times+" times";
                // 用户的微信昵称和头像连接发送一次即可，不需要每次都发送
                if (times==0)
                {
                }
            }
            last_time = curTime;
            last_x = x;
            last_y = y;
            last_z = z;
        }
    }
}

function updateToDb(times, uuid) {
    $.ajax({
        url: "?r=site/update-count",
        type: "POST",
        data: {shake_count: times, uuid: uuid},
        dataType: "JSON"
    }).done(function (data) {
        if (data.code != 0) {
            console.log(data);
        }
        else {
            layer.alert('updated: ' + data.updated);
            console.log(data);
        }
    }).fail(function (xhr, status) {
        console.log(xhr.responseText);
        console.error(status);
    });
}
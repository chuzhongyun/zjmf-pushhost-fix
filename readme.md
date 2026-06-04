# 财务无限同步下游修复 [迷你哆云](https://www.miniduo.cn)

> 免预存招代理 Q:1283187190

## 修复说明
> 放到 public/plugins/addons/ 目录下即可

安装插件即可修复，原理

```php
    \think\Db::name('zjmf_pushhost')
        ->where('status', 0)
        ->where('time', '<', time() - 300 * 10)   // 10次定时任务，自动超期，保留 status 状态用于后续同步
        ->update(['num' => 10]);
```

## bug 源头

```php Cron.php#L263
// id 没有出现在 field 中导致 update 未能成功更新 num 参数，故导致无限调用下游接口，修改下游产品状态
// 因为 post_data 是缓存的，所有如果状态为 待开通 会一直修改产品状态为待开通
$pushhost = \think\Db::name("zjmf_pushhost")->field("host_id,url,post_data")->where("status", 0)->where("num", "<", 5)->select()->toArray();
foreach ($pushhost as $v) {
    $res = commonCurl($v["url"], json_decode($v["post_data"], true), 30);
    if ($res["status"] == 200) {
        $update = ["status" => 1, "time" => time(), "num" => $v["num"] + 1];
    } else {
        $update = ["status" => 0, "time" => time(), "num" => $v["num"] + 1];
    }
    \think\Db::name("zjmf_pushhost")->where("id", $v["id"])->update($update);
}
```

[Cron.php#L263](https://github.com/aazooo/zjmf-manger-decoded/blob/58532ead9cd01515b0794dabe744e186eea6eea2/app/admin/command/Cron.php#L263)

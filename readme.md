# 魔方财务无限同步下游 (pushhostinfo) 修复插件

## 使用方式

将 `zjmf_pushhost/` 目录放到 `public/plugins/addons/` 下，在后台插件管理安装即可。

## 原理

魔方财务 `app/admin/command/Cron.php` 的 `hostInfo()` 方法每 5 分钟执行一次推送任务。该方法存在两个导致无限同步的 bug：

### Bug 1：SELECT 缺少 `id` 字段

```php
// Cron.php 原代码
$pushhost = \think\Db::name("zjmf_pushhost")->field("host_id,url,post_data")...
```

`field()` 中未包含 `id`，但后续 UPDATE 的 WHERE 条件是 `id = $v["id"]`。由于 `$v["id"]` 为 null，`WHERE id = null` 匹配不到任何记录，update 永不执行。推送到下游的记录始终 `status=0`，每 5 分钟被重复推送，形成无限循环。

### Bug 2：SELECT 缺少 `num` 字段

`num` 同样不在 field 列表中，`$v["num"] + 1` 始终为 `null + 1 = 1`，`WHERE num < 5` 的限流保护永不触发。

## 插件说明

安装本插件后，在每次定时任务(`afterCron`)和五分钟任务(`afterFiveMinuteCron`)完成后自动执行：

```php
\think\Db::name('zjmf_pushhost')
    ->where('status', 0)
    ->where('time', '<', time() - 300 * 10)   // 超过 50 分钟未处理成功
    ->update(['num' => 10]);                   // 标记为跳过(num >= 5)
```

将长时间未处理成功（超过 50 分钟）的记录 `num` 设为 10，使其不再满足 `num < 5` 的查询条件，从而停止重复推送。

## 数据库表结构

```sql
CREATE TABLE `shd_zjmf_pushhost` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `host_id`    INT(10) NOT NULL COMMENT '主机ID',
  `status`     CHAR(1) NOT NULL DEFAULT '0' COMMENT '1成功,0失败',
  `url`        TEXT NOT NULL COMMENT '下游URL',
  `post_data`  TEXT NOT NULL COMMENT '推送数据(JSON)',
  `time`       INT(10) NOT NULL COMMENT '上次推送时间',
  `num`        TINYINT(2) NOT NULL COMMENT '已推送次数',
  PRIMARY KEY (`id`),
  KEY `ststus` (`status`),
  KEY `host_id` (`host_id`),
  KEY `num` (`num`)
) ENGINE=INNODB;
```

## 友链

- [初衷云](https://chuzhongyun.vip)

## 参考

- 原补丁作者：[迷你哆云](https://www.miniduo.cn)
- 原仓库：[M1niduo/zjmf-pushhost-fix](https://github.com/M1niduo/zjmf-pushhost-fix)

## 许可证

[MIT](https://github.com/chuzhongyun/zjmf-pushhost-fix/blob/main/LICENSE)

# 企业微信web hook sdk

## 食用方式

### 安装

```shell script
composer require niefufeng/wework-webhook --prefer-dist -o
```

### 使用

```php
$config = [
    'key' => '你的微信机器人的key'
];

$client = new \NieFufeng\WeWorkWebHook\Client($config);

# 发送纯文本消息（at手机号时不需要加at符）
$client->sendText('hello world!', ['@all', '10086']);

# 发送 markdown 消息
$client->sendMarkdown("# hello\n> world!");

# 发送图片消息（图片不得超过2M）
$client->sendImage('/path/image.jpg');

# 发送图片内容
$client->sendImageFromContent(
    file_get_contents('/path/image.jpg')
);

# 发送图文消息
$client->sendNews([
    [
        'title' => 'Hello world',
        'description' => '该消息发送自企业微信webhook sdk',
        'url' => 'https://www.p***hub.com',
        'picurl' => 'https://image.p***hub.com/cover.jpg'
    ]
]);

# 上传文件（会返回media_id，可用于发送文件消息）
$mediaID = $client->uploadFile('图片.jpg', '/path/image.jpg');

# 发送文件消息
$client->sendFile($mediaID);
```

## Licence

自豪的采用 [MIT](http://opensource.org/licenses/MIT)

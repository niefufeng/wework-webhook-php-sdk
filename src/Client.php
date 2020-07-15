<?php

namespace NieFufeng\WeWorkWebHook;

use GuzzleHttp\RequestOptions;

class Client
{
    /**
     * @var array
     */
    protected $config;

    protected $http;

    const MESSAGE_TYPE_TEXT = 'text';
    const MESSAGE_TYPE_MARKDOWN = 'markdown';
    const MESSAGE_TYPE_IMAGE = 'image';
    const MESSAGE_TYPE_NEWS = 'news';
    const MESSAGE_TYPE_FILE = 'file';

    /**
     * Server constructor.
     * @param array $config 包含【key】的配置数组
     */
    public function __construct(array $config)
    {
        if (!($config['key'] ?? null)) {
            throw new \InvalidArgumentException('缺少参数【key】');
        }

        $this->config = $config;

        $this->http = new \GuzzleHttp\Client();
    }

    protected function sendMessage(string $type, array $data)
    {
        $this->http->post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send', [
            RequestOptions::QUERY => [
                'key' => $this->config['key']
            ],
            RequestOptions::JSON => array_merge($data, [
                'msgtype' => $type
            ])
        ]);
    }

    /**
     * 发送文本消息
     * @param string $content
     * @param array $mentioned_mobile_list 手机号码数组，@all 表示at所有人
     * @param array $mentioned_list 用户ID数组，@all 表示at所有人
     */
    public function sendText(string $content, array $mentioned_mobile_list = [], array $mentioned_list = [])
    {
        $this->sendMessage(self::MESSAGE_TYPE_TEXT, [
            'text' => compact('content', 'mentioned_mobile_list', 'mentioned_list')
        ]);
    }

    public function sendMarkdown(string $content)
    {
        $this->sendMessage(self::MESSAGE_TYPE_MARKDOWN, [
            'markdown' => compact('content')
        ]);
    }

    /**
     * @param string $imageContent 图片的内容
     */
    public function sendImageFromContent(string $imageContent)
    {
        $this->sendBase64Image(base64_encode($imageContent), md5($imageContent));
    }

    /**
     * @param string $path 图片的真实路径
     */
    public function sendImage(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('图片不存在或图片不可读');
        }

        $image = file_get_contents($path);

        $this->sendBase64Image(base64_encode($image), md5($image));
    }

    protected function sendBase64Image(string $base64, string $md5)
    {
        $this->sendMessage(self::MESSAGE_TYPE_IMAGE, [
            'image' => compact('base64', 'md5')
        ]);
    }

    /**
     * @param array $articles
     * [{
     *   "title" : "中秋节礼品领取",
     *   "description" : "今年中秋节公司有豪礼相送",
     *   "url" : "www.qq.com",
     *   "picurl" : "http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png"
     * }]
     */
    public function sendNews(array $articles)
    {
        $this->sendMessage(self::MESSAGE_TYPE_NEWS, compact('articles'));
    }

    public function uploadFile(string $fileName, string $fileRealPath)
    {
        if (!file_exists($fileRealPath) || !is_readable($fileRealPath)) {
            throw new \InvalidArgumentException('文件不存在或文件不可读');
        }

        $response = $this->http->post('https://qyapi.weixin.qq.com/cgi-bin/webhook/upload_media', [
            RequestOptions::QUERY => [
                'key' => $this->config['key'],
                'type' => self::MESSAGE_TYPE_FILE
            ],
            RequestOptions::MULTIPART => [
                [
                    'name' => 'media',
                    'contents' => fopen($fileRealPath, 'r'),
                    'filename' => $fileName,
                    'filelength' => filesize($fileRealPath)
                ]
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result['media_id'];
    }

    public function sendFile(string $media_id)
    {
        $this->sendMessage(self::MESSAGE_TYPE_FILE, [
            'file' => compact('media_id')
        ]);
    }
}
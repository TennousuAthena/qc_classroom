<?php
/**
 * 业务逻辑层
 * 接收Transfer发来的请求, 进行业务逻辑处理, 返回给Transfer, 最后Transfer返回给用户。
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/7/9
 * Time: 下午11:35
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

use MeepoPS\Core\MeepoPS;

class Business extends MeepoPS{
    //confluence的IP
    public $confluenceIp;
    //confluence的端口
    public $confluencePort;

    public function __construct()
    {
        $this->callbackStartInstance = array($this, 'callbackBusinessStartInstance');
        parent::__construct();
    }

    /**
     * 进程启动时, 链接到Confluence
     * 作为客户端, 连接到中心机(Confluence层), 获取Transfer列表
     */
    public function callbackBusinessStartInstance(){
        //作为客户端, 连接到中心机(Confluence层), 获取Transfer列表
        $businessAndConfluenceService = new BusinessAndConfluenceService();
        $businessAndConfluenceService->confluenceIp = $this->confluenceIp;
        $businessAndConfluenceService->confluencePort = $this->confluencePort;
        $businessAndConfluenceService->connectConfluence();
    }
}
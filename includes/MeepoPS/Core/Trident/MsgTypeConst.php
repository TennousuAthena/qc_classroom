<?php
/**
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/6/30
 * Time: 下午3:10
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

class MsgTypeConst{
    //PING
    const MSG_TYPE_PING = 'MEEPO_PS_SYS_INNER_PING';
    //PONG
    const MSG_TYPE_PONG = 'MEEPO_PS_SYS_INNER_PONG';
    //Transfer加入到Confluence
    const MSG_TYPE_ADD_TRANSFER_TO_CONFLUENCE = 'MEEPO_PS_SYS_INNER_ADD_TRANSFER_TO_CONFLUENCE';
    //Business加入到Confluence
    const MSG_TYPE_ADD_BUSINESS_TO_CONFLUENCE = 'MEEPO_PS_SYS_INNER_ADD_BUSINESS_TO_CONFLUENCE';
    //Business加入到Transfer
    const MSG_TYPE_ADD_BUSINESS_TO_TRANSFER = 'MEEPO_PS_SYS_INNER_ADD_BUSINESS_TO_TRANSFER';
    //Confluence发送Transfer列表给Business
    const MSG_TYPE_RESET_TRANSFER_LIST = 'MEEPO_PS_SYS_INNER_RESET_TRANSFER_LIST';
    //业务消息 - 此消息表示, 是业务相关消息。是Transfer和Business的数据类型。
    const MSG_TYPE_APP_MSG = 'MEEPO_PS_APP_MESSAGE';

    //-----------业务功能相关------------

    //给所有人发消息
    const MSG_TYPE_SEND_ALL = 'MEEPO_PS_SEND_ALL';
    //给指定人发消息
    const MSG_TYPE_SEND_ONE = 'MEEPO_PS_SEND_ONE';
}
<?php 
/**
 * 日志相关
 */
class oauthLogIndex extends Controller {
    public function __construct() {
		parent::__construct();
		$this->pluginName = 'oauthPlugin';
    }

	// 写入日志
	public function logAdd($action, $data = array()){
        // 用户注册
        if ($action == 'regist') {
            $type = 'user.regist.'.$action;
            $userID = $data['userID'];
        } else { // 绑定相关
            if (!in_array($action, array('bind', 'unbind', 'bindApi'))) return;
            if($action != 'unbind' && $GLOBALS['loginLogSaved'] == 1) return;
            $type = 'user.bind.'.$action;
        }
		if(!$userID){$userID = (defined('USER_ID') && USER_ID) ? USER_ID:Session::get("kodUser.userID");}
		if(!$userID){$userID = 0;}
		
		// 写入日志
		$data['ip'] = get_client_ip();
        $insert = array(
            "sessionID" => Session::sign(),
            "userID"    => $userID,
            'type'      => $type,
            "desc"      => json_encode($data)
        );
        Model('SystemLog')->add($insert);
	}

    /**
     * 日志类型
     * @param [type] $data
     * @return void
     */
    public function logType($data) {
        foreach($data['data'] as &$item) {
            if ($item['id'] != 'user') continue;
            foreach($item['children'] as &$value) {
                if (strpos($value['id'], 'user.setting') === 0) {
                    $value['id'] .= ',user.bind.bind,user.bind.unbind';
                    break;
                }
            }
		}
		return $data;
    }

    /**
     * 日志列表
     * @param [type] $data
     * @return void
     */
    public function logList($data) {
        $action = array(
            'user.bind.bind'    => LNG('admin.log.thirdBind'),
            'user.bind.unbind'  => LNG('admin.log.delBind')
        );
        foreach($data['data'] as $i => &$item) {
            $type = $item['type'];
            if (isset($action[$type])) $item['title'] = $action[$type];
		}
        return $data;
    }

}
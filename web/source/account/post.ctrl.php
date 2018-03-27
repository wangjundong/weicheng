<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('cloud');
load()->model('cache');
load()->classs('weixin.platform');
load()->model('wxapp');
load()->func('communication');

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
if (empty($uniacid) || empty($acid)) {
	itoast('请选择要编辑的公众号', url('account/manager'), 'error');
}
$state = uni_permission($_W['uid'], $uniacid);

$dos = array('base', 'sms', 'modules_tpl');
if ($state == ACCOUNT_MANAGE_NAME_FOUNDER || $state == ACCOUNT_MANAGE_NAME_OWNER) {
	$do = in_array($do, $dos) ? $do : 'base';
} elseif ($state == ACCOUNT_MANAGE_NAME_MANAGER) {
	$do = in_array($do, $dos) ? $do : 'modules_tpl';
} else {
	itoast('您是该公众号的操作员，无权限操作！', url('account/manager'), 'error');
}

$_W['page']['title'] = '管理设置 - 微信' . ACCOUNT_TYPE_NAME . '管理';
$headimgsrc = tomedia('headimg_'.$acid.'.jpg');
$qrcodeimgsrc = tomedia('qrcode_'.$acid.'.jpg');
$account = account_fetch($acid);

if($do == 'base') {
	if ($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
		itoast('无权限操作！', url('account/post/modules_tpl', array('uniacid' => $uniacid, 'acid' => $acid)), 'error');
	}

	if($_W['ispost'] && $_W['isajax']) {
		if(!empty($_GPC['type'])) {
			$type = trim($_GPC['type']);
		}else {
			iajax(40035, '参数错误！', '');
		}
		switch ($type) {
			case 'qrcodeimgsrc':
			case 'headimgsrc':
				if(!empty($_GPC['imgsrc'])) {
					if(parse_path($_GPC['imgsrc'])) {
						$img_parse = parse_url($_GPC['imgsrc']);
						$new_pic_name = IA_ROOT . '%s'.$acid.'.jpg';
						if ($type == 'qrcodeimgsrc') {
							$filename = sprintf($new_pic_name, '/attachment/qrcode_');
						}
						if ($type == 'headimgsrc') {
							$filename = sprintf($new_pic_name, '/attachment/headimg_');
						}
						if ($img_parse['host'] != $_SERVER['HTTP_HOST']) {
							$return_content = ihttp_get($_GPC['imgsrc']);
							if ($return_content['code'] == 200) {
								$result = file_put_contents($filename, $return_content['content']);
							} else {
								$result = false;
							}
						} else {
							$img_absolute_path = IA_ROOT . $img_parse['path'];
							$result = copy($img_absolute_path, $filename);
						}
					}else {
						iajax(40035, '参数错误！', '');
					}
				}
				break;
			case 'name':
				$uni_account = pdo_update('uni_account', array('name' => trim($_GPC['request_data'])), array('uniacid' => $uniacid));
				$account_wechats = pdo_update(uni_account_tablename(ACCOUNT_TYPE), array('name' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				$result = ($uni_account && $account_wechats) ? true : false;
				break;
			case 'account' :
				$data = array('account' => trim($_GPC['request_data']));break;
			case 'original':
				$data = array('original' => trim($_GPC['request_data']));break;
			case 'level':
				$data = array('level' => intval($_GPC['request_data']));break;
			case 'key':
				$data = array('key' => trim($_GPC['request_data']));break;
			case 'secret':
				$data = array('secret' => trim($_GPC['request_data']));break;
			case 'token':
				$oauth = (array)uni_setting($uniacid, array('oauth'));
				if($oauth['oauth'] == $acid && $account['level'] != 4) {
					$acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$data = array('token' => trim($_GPC['request_data']));
				break;
			case 'encodingaeskey':
				$oauth = (array)uni_setting($uniacid, array('oauth'));
				if($oauth['oauth'] == $acid && $account['level'] != 4) {
					$acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$data = array('encodingaeskey' => trim($_GPC['request_data']));
				break;
			case 'jointype':
				$original_type = pdo_get('account', array('uniacid' => $uniacid), 'type');
				if ($original_type['type'] == ACCOUNT_NORMAL_LOGIN) {
					$result = true;
				} else {
					$update_type = pdo_update('account', array('type' => ACCOUNT_NORMAL_LOGIN), array('uniacid' => $uniacid));
					$result = $update_type ? true : false;
				}
				break;
		}
		if(!in_array($type, array('qrcodeimgsrc', 'headimgsrc', 'name', 'endtime', 'jointype'))) {
			$result = pdo_update(uni_account_tablename(ACCOUNT_TYPE), $data, array('acid' => $acid, 'uniacid' => $uniacid));
		}
		if($result) {
			cache_delete("uniaccount:{$uniacid}");
			cache_delete("unisetting:{$uniacid}");
			cache_delete("accesstoken:{$acid}");
			cache_delete("jsticket:{$acid}");
			cache_delete("cardticket:{$acid}");
			module_build_privileges();
			iajax(0, '修改成功！', '');
		}else {
			iajax(1, '修改失败！', '');
		}
	}

	if ($_W['setting']['platform']['authstate']) {
		$account_platform = new WeiXinPlatform();
		$preauthcode = $account_platform->getPreauthCode();
		if (is_error($preauthcode)) {
			$authurl = array(
				'errno' => 1,
				'url' => "{$preauthcode['message']}"
			);
		} else {
			$authurl = array(
				'errno' => 0,
				'url' => sprintf(ACCOUNT_PLATFORM_API_LOGIN, $account_platform->appid, $preauthcode, urlencode(urlencode($GLOBALS['_W']['siteroot'] . 'index.php?c=account&a=auth&do=forward')))
			);
		}
	}
	$socket_url = str_replace(array('https', 'http'), 'wss', $_W['siteroot']);
	$account['end'] = $account['endtime'] == 0 ? '永久' : date('Y-m-d', $account['endtime']);
	$account['endtype'] = $account['endtime'] == 0 ? 1 : 2;
	$uniaccount = array();
	$uniaccount = pdo_get('uni_account', array('uniacid' => $uniacid));

	template('account/manage-base' . ACCOUNT_TYPE_TEMPLATE);
}

if($do == 'sms') {
	if ($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
		itoast('无权限操作！', url('account/post/modules_tpl', array('uniacid' => $uniacid, 'acid' => $acid)), 'error');
	}
	$settings = uni_setting($uniacid, array('notify'));
	$notify = $settings['notify'] ? $settings['notify'] : array();

	$sms_info = cloud_sms_info();
	$max_num = empty($sms_info['sms_count']) ? 0 : $sms_info['sms_count'];
	$signatures = $sms_info['sms_sign'];

	if ($_W['isajax'] && $_W['ispost'] && $_GPC['type'] == 'balance') {
		if ($max_num == 0) {
			iajax(-1, '您现有短信数量为0，请联系服务商购买短信！', '');
		}
		$balance = intval($_GPC['balance']);
		$notify['sms']['balance'] = $balance;
		$notify['sms']['balance'] = min(max(0, $notify['sms']['balance']), $max_num);
		$count_num = $max_num - $notify['sms']['balance'];
		$num = $notify['sms']['balance'];
		$notify = iserializer($notify);
		$updatedata['notify'] = $notify;
		$result = pdo_update('uni_settings', $updatedata , array('uniacid' => $uniacid));
		if($result){
			iajax(0, array('count' => $count_num, 'num' => $num), '');
		}else {
			iajax(1, '修改失败！', '');
		}
	}
	if($_W['isajax'] && $_W['ispost'] && $_GPC['type'] == 'signature') {
		if (!empty($_GPC['signature'])) {
			$signature = trim($_GPC['signature']);
			$setting = pdo_get('uni_settings', array('uniacid' => $uniacid));
			$notify = iunserializer($setting['notify']);
			$notify['sms']['signature'] = $signature;

			$notify = serialize($notify);
			$result = pdo_update('uni_settings', array('notify' => $notify), array('uniacid' => $uniacid));
			if($result) {
				iajax(0, '修改成功！', '');
			}else {
				iajax(1, '修改失败！', '');
			}
		}else {
			iajax(40035, '参数错误！', '');
		}
	}

	template('account/manage-sms' . ACCOUNT_TYPE_TEMPLATE);
}

if($do == 'modules_tpl') {
	$unigroups = uni_groups();
	$owner = account_owner($uniacid);

	if($_W['isajax'] && $_W['ispost'] && ($state == ACCOUNT_MANAGE_NAME_FOUNDER || $state == ACCOUNT_MANAGE_NAME_OWNER)) {
		if($_GPC['type'] == 'group') {
			$groups = $_GPC['groupdata'];
			if(!empty($groups)) {
								pdo_delete('uni_account_group', array('uniacid' => $uniacid));
				$group = pdo_get('users_group', array('id' => $owner['groupid']));
				$group['package'] = (array)iunserializer($group['package']);
				$group['package'] = array_unique($group['package']);
				foreach ($groups as $packageid) {
					if (!empty($packageid) && !in_array($packageid, $group['package'])) {
						pdo_insert('uni_account_group', array(
							'uniacid' => $uniacid,
							'groupid' => $packageid,
						));
					}
				}
				cache_build_account_modules($uniacid);
				cache_build_account($uniacid);
				iajax(0, '修改成功！', '');
			}else {
				pdo_delete('uni_account_group', array('uniacid' => $uniacid));
				cache_build_account_modules($uniacid);
				cache_build_account($uniacid);
				iajax(0, '修改成功！', '');
			}
		}

		if($_GPC['type'] == 'extend') {
						$module = $_GPC['module'];
			$tpl = $_GPC['tpl'];
			if (!empty($module) || !empty($tpl)) {
				$data = array(
					'modules' => iserializer($module),
					'templates' => iserializer($tpl),
					'uniacid' => $uniacid,
					'name' => '',
				);
				$id = pdo_fetchcolumn("SELECT id FROM ".tablename('uni_group')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
				if (empty($id)) {
					pdo_insert('uni_group', $data);
				} else {
					pdo_update('uni_group', $data, array('id' => $id));
				}
			} else {
				pdo_delete('uni_group', array('uniacid' => $uniacid));
			}
			cache_build_account_modules($uniacid);
			cache_build_account($uniacid);
			iajax(0, '修改成功！', '');
		}
		iajax(40035, '参数错误！', '');
	}
	$modules_tpl = $extend = array();

	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($owner['uid'], $founders)) {
		$modules_tpl[] = array(
			'id' => -1,
			'name' => '所有服务',
			'modules' => array(array('name' => 'all', 'title' => '所有模块')),
			'templates' => array(array('name' => 'all', 'title' => '所有模板')),
		);
	} else {
		$owner['group'] = pdo_get('users_group', array('id' => $owner['groupid']), array('id', 'name', 'package'));
		$owner['group']['package'] = iunserializer($owner['group']['package']);
		if(!empty($owner['group']['package'])){
			foreach ($owner['group']['package'] as $package_value) {
				if($package_value == -1){
					$modules_tpl[] = array(
						'id' => -1,
						'name' => '所有服务',
						'modules' => array(array('name' => 'all', 'title' => '所有模块')),
						'templates' => array(array('name' => 'all', 'title' => '所有模板')),
					);
				}elseif ($package_value == 0) {

				}else {
					$modules_tpl[] = $unigroups[$package_value];
				}
			}
		}
				$extendpackage = pdo_getall('uni_account_group', array('uniacid' => $uniacid), array(), 'groupid');
		if(!empty($extendpackage)) {
			foreach ($extendpackage as $extendpackage_val) {
				if($extendpackage_val['groupid'] == -1){
					$modules_tpl[] = array(
						'id' => -1,
						'name' => '所有服务',
						'modules' => array(array('name' => 'all', 'title' => '所有模块')),
						'templates' => array(array('name' => 'all', 'title' => '所有模板')),
					);
				}elseif ($extendpackage_val['groupid'] == 0) {

				}else {
					$modules_tpl[] = $unigroups[$extendpackage_val['groupid']];
				}
			}
		}
	}

	$modules = pdo_getall('modules', array('issystem !=' => 1), array('mid', 'name', 'title'), 'name');
	$templates = pdo_getall('site_templates', array(), array('id', 'name', 'title'));
	$extend = pdo_get('uni_group', array('uniacid' => $uniacid));
	$extend['modules'] = iunserializer($extend['modules']);
	$extend['templates'] = iunserializer($extend['templates']);
	if (!empty($extend['modules'])) {
		$extend['modules'] = pdo_getall('modules', array('name' => $extend['modules']), array('mid', 'title', 'name'));
	}
	if (!empty($extend['templates'])) {
		$extend['templates'] = pdo_getall('site_templates', array('id' => $extend['templates']), array('id', 'name', 'title'));
	}

	template('account/manage-modules-tpl' . ACCOUNT_TYPE_TEMPLATE);
}
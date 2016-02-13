<?php
/**
 * UserFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * UserFixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model
 */
class UserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ID | ログインID', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Password | パスワード', 'charset' => 'utf8'),
		'key' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Link identifier | リンク識別子', 'charset' => 'utf8'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_avatar_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_avatar_auto_created' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'handlename' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Handle | ハンドル', 'charset' => 'utf8'),
		'is_handlename_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_name_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'E-mail | eメール', 'charset' => 'utf8'),
		'is_email_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_email_reception' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'moblie_mail' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Mobile mail | 携帯メール', 'charset' => 'utf8'),
		'is_moblie_mail_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_moblie_mail_reception' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'sex' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Sex | 性別', 'charset' => 'utf8'),
		'is_sex_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'timezone' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'TimeZone | タイムゾーン', 'charset' => 'utf8'),
		'is_timezone_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'role_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Authority | 権限', 'charset' => 'utf8'),
		'is_role_key_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'status' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Status | 状態', 'charset' => 'utf8'),
		'is_status_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Created | 作成日時'),
		'is_created_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'Creator | 作成者'),
		'is_created_user_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Last modified | 更新日時'),
		'is_modified_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'Updater | 更新者'),
		'is_modified_user_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'password_modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Password has been changed | パスワード変更日時'),
		'is_password_modified_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Last login | 最終ログイン日時'),
		'is_last_login_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'previous_login' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'PreLast login | 前回ログイン日時'),
		'is_previous_login_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_profile_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_search_keywords_public' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'username' => 'system_administrator',
			'password' => 'system_administrator',
			'key' => 'system_admin',
			'is_avatar_public' => 1,
			'handlename' => 'System Administrator',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'system_administrator',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
		array(
			'id' => 2,
			'username' => 'room_administrator',
			'password' => 'room_administrator',
			'key' => 'room_administrator',
			'is_avatar_public' => 1,
			'handlename' => 'Room Administrator',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'administrator',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
		array(
			'id' => 3,
			'username' => 'chief_editor',
			'password' => 'chief_editor',
			'key' => 'chief_editor',
			'is_avatar_public' => 1,
			'handlename' => 'Chief Editor',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'common_user',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
		array(
			'id' => 4,
			'username' => 'editor',
			'password' => 'editor',
			'key' => 'editor',
			'is_avatar_public' => 1,
			'handlename' => 'Editor',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'common_user',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
		array(
			'id' => 5,
			'username' => 'general_user',
			'password' => 'general_user',
			'key' => 'general_user',
			'is_avatar_public' => 1,
			'handlename' => 'General User',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'common_user',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
		array(
			'id' => 6,
			'username' => 'visitor',
			'password' => 'visitor',
			'key' => 'visitor',
			'is_avatar_public' => 1,
			'handlename' => 'Visitor',
			'is_handlename_public' => 1,
			'is_name_public' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'is_email_public' => 1,
			'moblie_mail' => 'Lorem ipsum dolor sit amet',
			'is_moblie_mail_public' => 1,
			'sex' => 1,
			'is_sex_public' => 1,
			'timezone' => 'Lorem ipsum dolor sit amet',
			'is_timezone_public' => 1,
			'role_key' => 'common_user',
			'is_role_key_public' => 1,
			'status' => 1,
			'is_status_public' => 1,
			'created' => '2015-08-15 06:12:30',
			'is_created_public' => 1,
			'created_user' => 1,
			'is_created_user_public' => 1,
			'modified' => '2015-08-15 06:12:30',
			'is_modified_public' => 1,
			'modified_user' => 1,
			'is_modified_user_public' => 1,
			'password_modified' => '2015-08-15 06:12:30',
			'is_password_modified_public' => 1,
			'last_login' => '2015-08-15 06:12:30',
			'is_last_login_public' => 1,
			'is_profile_public' => 1,
			'is_search_keywords_public' => 1
		),
	);

}

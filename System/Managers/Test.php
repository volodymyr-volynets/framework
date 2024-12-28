<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Helper\Cmd;
use NF\Message;
use Numbers\Backend\System\ShellCommand\Class2\ShellCommand\Statuses;

// benchmark
$benchmark = microtime(true);

// command line parameters available in $argv variable

// must change working directory to public_html
chdir('public_html');

// autoloading composer first
if (file_exists('../libraries/vendor/autoload.php')) {
    require('../libraries/vendor/autoload.php');
}

// automatic class loading
require('../libraries/vendor/numbers/framework/application.php');
spl_autoload_register(array('application', 'autoloader'));

// running application
Application::run(['__run_only_bootstrap' => 2]);


// increase in memory and unlimited execution time
ini_set('memory_limit', '2048M');
set_time_limit(0);

// confirmation whether to run the script
//if (!\Helper\Cmd::confirm("Conitune?")) exit;

// define result variable to keep scripts messages
$result = [
    'success' => false,
    'error' => [],
    'hint' => []
];

// wrapping everything into try-catch block for system exceptions
try {
    /*
    $crypt = Application::get('crypt');
    foreach ($crypt as $crypt_link => $crypt_settings) {
        if (!empty($crypt_settings['submodule']) && !empty($crypt_settings['autoconnect'])) {
            $crypt_object = new Crypt($crypt_link, $crypt_settings['submodule'], $crypt_settings);
        }
    }
    $db = Application::get('db.default');
    $result = Db::connectToServers('default', $db);
    $cache = Application::get('cache');
    foreach ($cache as $cache_link => $cache_settings) {
        if (empty($cache_settings['submodule']) || empty($cache_settings['autoconnect'])) {
            continue;
        }
        $cache_result = Cache::connectToServers($cache_link, $cache_settings);
    }
    */
    // add your code here
    //$ar1 = new \Numbers\Users\Users\Model\UsersAR();
    //$data = $ar1->loadById(1);
    //print_r2($data->object_table_pk);
    //print_r2($data);

    //$ar2 = \Numbers\Users\Users\Model\UsersAR::loadByIdStatic(802);
    //print_r2($ar2);

    /*
    $ar3d = \Numbers\Users\Users\Model\UsersAR::get([
        'where' => [
            'um_user_id' => 1,
        ]
    ]);
    print_r2($ar3d);
    $ar4 = new \Numbers\Users\Users\Model\UsersAR();
    $ar4d = $ar4->get([
        'where' => [
            'um_user_id' => 1,
        ]
    ]);
    print_r2($ar4d);
    */

    /*
    $users1a = \Numbers\Users\Users\Model\Users::queryBuilderStatic()
            ->select()
            //->withScope(['!ActiveGlobal'])
            ->withRelation(['Roles' => 'Roles.OwnerTypes'])
            ->withRelation(['Organizations' => 'Organizations'])
            ->withRelation(['Teams' => 'Teams', 'Groups' => 'Groups'])
            //->orderInRandom()
            ->orderby(['um_user_id' => SORT_ASC])
            ->limit(10)
            ->sql(false)
            ->array2('um_user_id');

    print_r2($users1a);
    */

    /*
    $users2a = \Numbers\Users\Users\Model\Users::queryBuilderStatic()
        ->select()
        ->withScope('!ActiveGlobal', 'Inactive')
        ->withRelation('RelationUsersRoles')
        ->withScope('RelationUsersRoles.Active')
        ->limit(10)
        ->sql(false)
        ->array2('um_user_id');

    print_r2($users2a);
    */

    /*
    print_r2(\Db::uuid4());

    $uuid = \Db::uuidTenanted(2);
    print_r2($uuid);
    print_r2(strlen($uuid));
    print_r2(\Db::uuidTenantedDecode($uuid));
    */

    //print_r2(sha1('key'));
    //edd40085f256d36ae4687f3de2ca529d6722d7e22!oauth.link!YToxOntzOjg6InByb3ZpZGVyIjtzOjY6IkdPT0dMRSI7fQ==
    //$token = urldecode('ab1a83a2f010f473b53de2ccd8f053226722d7e22!oauth.link!YToxOntzOjg6InByb3ZpZGVyIjtzOjY6IkdPT0dMRSI7fQ%3D%3D');
    //$crypt = new \Crypt();
    //$decoded = $crypt->tokenValidate($token);
    //print_r2($decoded);

    /*
    $token = $crypt->tokenCreate(3, 'test_token', ["user_id" => 200], [
        'time' => 123456,
        'ip' => '127.0.0.1'
    ]);
    print_r2($token);
    */

    /*
    $result = SMS::send([
        'to' => '+14167102135',
        'message' => 'Hello!'
    ]);
    print_r2($result);
    */

    //$crypt = new Crypt();
    //echo $crypt->passwordPolicyGenerate(12);

    //echo \Crypt::nanoCreateStatic(1);
    //print_r2(Crypt::nanoVerifyStatic('AgAAAAAAAAA.b4FQ'));


    //I18n::init();
    //print_r2(loc(Message::NO_ROWS_FOUND));

    /*
    $statues = Statuses::New;
    $options = Statuses::options();
    print_r2($options);
    */

    $db = new Db();
    $db->begin();

    $result = Event::dispatch('UM::USER_LOG_LOGINS', [
        'user_id' => 1,
        'user_ip' => Request::ip(),
    ]);

    print_r2($result);

    $db->rollback();

    // error label
    error:
        // hint
        if (!empty($result['hint'])) {
            echo "\n" . Cmd::colorString(implode("\n", $result['hint']), null, null, false) . "\n\n";
        }
    // if we did not succeed
    if (!$result['success']) {
        echo "\n" . Cmd::colorString(implode("\n", $result['error']), 'red', null, true) . "\n\n";
        exit;
    }
} catch (Exception $e) {
    echo "\n" . Cmd::colorString($e->getMessage(), 'red', null, true) . "\n\n" . $e->getTraceAsString() . "\n\n";
    exit;
}

// success message
$seconds = Format::timeSeconds(microtime(true) - Application::get('application.system.request_time'));
echo "\nOperation completed in {$seconds} seconds!\n\n";

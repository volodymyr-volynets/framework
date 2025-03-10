<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace System;

use Helper\Cmd;
use Helper\File;
use Object\Data\Domains;
use Object\Override\Blank;
use Object\Reflection;
use Object\Widgets;
use Numbers\Backend\System\ShellCommand\Model\ShellCommands;
use Numbers\Backend\System\Modules\Model\Modules;
use Object\Table;

class Dependencies
{
    /**
     * Process dependencies
     *
     * @param array $options
     *		mode
     * @return array
     */
    public static function processDepsAll(array $options = []): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'data' => []
        ];
        do {
            $options['mode'] = $options['mode'] ?? 'test';
            // processing main dependency file
            $main_dep_filename = 'Config/application.ini';
            if (!file_exists($main_dep_filename)) {
                $result['error'][] = "Main dep. file not found!";
                break;
            }
            // some array arrangements
            $data = Config::ini($main_dep_filename, 'dependencies', [
                'ini_folder' => 'Config/',
                'libraries_folder' => \Application::get('application.libraries_folder'),
                'application_folder' => \Application::get('application.application_folder'),
            ]);
            $data = $data['dep'] ?? [];
            $data['composer'] = $data['composer'] ?? [];
            $data['submodule'] = $data['submodule'] ?? [];
            $registered_submodules = array2ini($data['submodule']);
            $data['submodule_dirs'] = [];
            $data['apache'] = $data['apache'] ?? [];
            $data['php'] = $data['php'] ?? [];
            $data['model'] = $data['model'] ?? [];
            $data['__model_dependencies'] = [];
            $data['model_import'] = [];
            $data['override'] = $data['override'] ?? [];
            $data['acl'] = $data['acl'] ?? [];
            $data['media'] = $data['media'] ?? [];
            $data['public_html'] = $data['public_html'] ?? [];
            $data['model_processed'] = [];
            $data['unit_tests'] = [];
            $data['form'] = $data['form'] ?? [];
            $data['loc'] = $data['loc'] ?? [];
            $data['loc_model'] = $data['model'] ?? [];
            $data['localize'] = $data['localize'] ?? [];
            $data['preset'] = $data['preset'] ?? [];
            $data['__submodule_dependencies'] = [];
            $data['components'] = [];
            $data['extra_configs'] = [];
            $data['route'] = $data['route'] ?? [];
            $data['api'] = $data['api'] ?? [];
            $data['constant'] = $data['constant'] ?? [];
            $data['module'] = $data['module'] ?? [];
            $data['middleware'] = $data['middleware'] ?? [];
            $dummy = $dummy2 = [];
            // we have small chicken and egg problem with composer
            $composer_data = [];
            $composer_dirs = [];
            $composer_dirs[] = 'Config/';
            if (file_exists('../libraries/composer.json')) {
                $composer_data = json_decode(file_get_contents('../libraries/composer.json'), true);
            }

            // if we have composer or submodules from main dep file
            if (!empty($data['composer']) || !empty($data['submodules'])) {
                $composer_data['require'] = [];
                if (!empty($data['composer'])) {
                    self::processDepsArray($data['composer'], $composer_data['require'], $composer_dirs, 'dummy', $dummy);
                }
                if (!empty($data['submodule'])) {
                    self::processDepsArray($data['submodule'], $dummy2, $composer_dirs, 'dummy', $dummy, 'vendor');
                    self::processDepsArray($data['submodule'], $dummy2, $composer_dirs, 'dummy', $dummy, 'private');
                }
            }

            // process components
            $components = File::iterate('../libraries/components/', ['recursive' => true, 'only_files' => ['module.ini']]);
            foreach ($components as $v) {
                $k = str_replace(['../libraries/components/', '/module.ini'], '', $v);
                $k2 = str_replace(['module.ini'], '', $v);
                $composer_dirs['Numbers/Components/' . $k] = $k2;
                $data['components'][$k] = $k2;
            }
            // processing submodules
            $mutex = [];
            $__any = [];
            if (!empty($composer_dirs)) {
                for ($i = 0; $i < 12; $i++) { // twelve runs to get all dependencies
                    foreach ($composer_dirs as $k => $v) {
                        if (isset($mutex[$k])) {
                            continue;
                        } else {
                            $mutex[$k] = 1;
                        }
                        if (file_exists($v . 'module.ini')) {
                            $config_dir = $v;
                            if ($v === 'Config/') {
                                $v = '';
                            }
                            $data['submodule_dirs'][$v] = $v;
                            $sub_data_all = Config::ini($config_dir . 'module.ini', 'dependencies');
                            $sub_data = $sub_data_all['dep'] ?? [];
                            // modules
                            if ($sub_data_all['module']) {
                                if (empty($sub_data_all['module']['repository'])) {
                                    if (empty($sub_data_all['module']['code'])) {
                                        $title = $sub_data_all['module']['title'] ?? $v ?? 'Unknown';
                                        throw new \Exception("Code for {$title} ? " . $v);
                                    }
                                    $data['module'][$sub_data_all['module']['code']] = $sub_data_all['module'];
                                    $data['module'][$sub_data_all['module']['code']]['dir'] = $v;
                                }
                            }
                            // composer
                            if (!empty($sub_data['composer'])) {
                                self::processDepsArray($sub_data['composer'], $composer_data['require'], $composer_dirs, $k, $dummy);
                                $data['composer'] = array_merge_hard($data['composer'], $sub_data['composer']);
                            }
                            // submodules
                            if (!empty($sub_data['submodule'])) {
                                self::processDepsArray($sub_data['submodule'], $composer_data['require'], $composer_dirs, $k, $data['__submodule_dependencies'], 'vendor');
                                self::processDepsArray($sub_data['submodule'], $composer_data['require'], $composer_dirs, $k, $data['__submodule_dependencies'], 'private');
                                $data['submodule'] = array_merge_hard($data['submodule'], $sub_data['submodule']);
                                // check if submodule in application.ini file
                                foreach (array2ini($sub_data['submodule']) ?? [] as $k12 => $v12) {
                                    if (str_ends_with($k12, '__any')) {
                                        continue;
                                    }
                                    if (str_ends_with($k12, '.Common')) {
                                        continue;
                                    }
                                    if (empty($registered_submodules[$k12])) {
                                        throw new \Exception('Module ' . $k12 . ' is not registered in applicaiton.ini');
                                    }
                                }
                            }
                            // apache
                            if (!empty($sub_data['apache'])) {
                                $data['apache'] = array_merge_hard($data['apache'], $sub_data['apache']);
                            }
                            // php
                            if (!empty($sub_data['php'])) {
                                $data['php'] = array_merge_hard($data['php'], $sub_data['php']);
                            }
                            // extra configs
                            if (!empty($sub_data['extra_configs'])) {
                                $data['extra_configs'] = array_merge_hard($data['extra_configs'], $sub_data['extra_configs']);
                            }
                            // model
                            if (!empty($sub_data['model'])) {
                                $data['model'] = array_merge_hard($data['model'], $sub_data['model']);
                                $temp = [];
                                array_keys_to_string($sub_data['model'], $temp);
                                foreach ($temp as $k0 => $v0) {
                                    $data['__model_dependencies'][$k][$k0] = $k0;
                                }
                            }
                            // override
                            if (!empty($sub_data['override'])) {
                                $data['override'] = array_merge_hard($data['override'], $sub_data['override']);
                            }
                            // acl
                            if (!empty($sub_data['acl'])) {
                                $data['acl'] = array_merge_hard($data['acl'], $sub_data['acl']);
                            }
                            // media
                            if (!empty($sub_data['media'])) {
                                foreach ($sub_data['media'] as $k78 => $v78) {
                                    if ($k78 == 'public_html') {
                                        $data['public_html'][] = $v . 'Media/PublicHTML';
                                        continue;
                                    }
                                    if (!isset($data['media'][$k78])) {
                                        $data['media'][$k78] = [];
                                    }
                                    foreach ($v78 as $v79) {
                                        $data['media'][$k78][] = $v79;
                                    }
                                }
                            }
                            // routes
                            if (!empty($sub_data['route'])) {
                                foreach ($sub_data['route'] as $k78 => $v78) {
                                    $data['route'][] = $v . $v78;
                                }
                            }
                            // middleware
                            if (!empty($sub_data['middleware'])) {
                                foreach ($sub_data['middleware'] as $k78 => $v78) {
                                    foreach ($v78 as $k79 => $v79) {
                                        $abbriviation = (new \String2($k78))->modulize()->toString();
                                        $module_name = (new \String2($k79))->spaceOnUpperCase()->toString();
                                        $data['middleware'][$k78 . ' ' . $k79] = $v79;
                                        $data['middleware'][$k78 . ' ' . $k79]['name'] = $abbriviation . ' ' . $module_name;
                                        $data['middleware'][$k78 . ' ' . $k79]['check'] = (new \String2($v79['check']))->replace(',', ' ')->ucfirst()->explode(' ')->toArray();
                                        $data['middleware'][$k78 . ' ' . $k79]['error'] = (new \String2($v79['error']))->replace(',', ' ')->ucfirst()->explode(' ')->toArray();
                                        $data['middleware'][$k78 . ' ' . $k79]['channel'] = (new \String2($v79['channel'] ?? ''))->replace(',', ' ')->ucfirst()->explode(' ')->toArray();
                                    }
                                }
                            }
                            // apis
                            if (!empty($sub_data['api'])) {
                                $data['api'] = array_merge_hard($data['api'], $sub_data['api']);
                            }
                            // constants
                            if (!empty($sub_data['constant'])) {
                                foreach ($sub_data['constant'] as $k78 => $v78) {
                                    $data['constant'][] = $v . $v78;
                                }
                            }
                            // forms
                            if (!empty($sub_data['form'])) {
                                $data['form'] = array_merge_hard($data['form'], $sub_data['form']);
                            }
                            // locs for both forms and models
                            if (!empty($sub_data['loc'])) {
                                $data['loc'][$v] = array_merge_hard($data['loc'][$v] ?? [], $sub_data['loc']);
                            }
                            if (!empty($sub_data['model'])) {
                                $data['loc_model'][$v] = array_merge_hard($data['loc_model'][$v] ?? [], $sub_data['model']);
                            }
                            // presets
                            if (!empty($sub_data['preset'])) {
                                $data['preset'] = array_merge_hard($data['preset'], $sub_data['preset']);
                            }
                            // processing unit tests
                            if (file_exists($v . 'UnitTests')) {
                                // we have to reload the module.ini file to get module name
                                $sub_data_temp = Config::ini($v . 'module.ini', 'module');
                                $data['unit_tests'][$sub_data_temp['module']['name']] = $v . 'UnitTests/';
                            }
                        } else {
                            $keys = explode('/', $k);
                            $last = end($keys);
                            if ($last == '__any') {
                                $temp2 = [];
                                foreach ($keys as $v2) {
                                    if ($v2 != '__any') {
                                        $temp2[] = $v2;
                                    }
                                }
                                $__any[$k] = $temp2;
                                unset($composer_dirs[$k]);
                            } elseif ($keys[0] == 'numbers') {
                                $result['error'][] = " - Submodule not found in {$v}module.ini";
                            }
                        }
                    }
                }
            }
            // processing any dependencies
            if (!empty($__any)) {
                foreach ($__any as $k => $v) {
                    $temp = array_key_get($data['submodule'], $v);
                    unset($temp['__any']);
                    if (empty($temp)) {
                        $result['error'][] = " - Any dependency required $k!";
                    }
                }
            }
            // processing composer
            if (empty($options['skip_confirmation'])) {
                if (!empty($composer_data['require'])) {
                    foreach ($composer_data['require'] as $k => $v) {
                        if (!file_exists('../libraries/vendor/' . $k) && !file_exists('../libraries/private/' . $k)) {
                            $result['error'][] = " - Composer library \"$k\" is not loaded!";
                        }
                    }
                }
            }
            // sometimes we need to make sure we have functions available
            $func_per_extension = [
                'pgsql' => 'pg_connect'
            ];
            // proceccing php extensions
            if (!empty($data['php']['extension'])) {
                foreach ($data['php']['extension'] as $k => $v) {
                    if ((isset($func_per_extension[$k]) && function_exists($func_per_extension[$k]) == false) || !extension_loaded($k)) {
                        $result['error'][] = " - PHP extension \"$k\" is not loaded!";
                    }
                }
            }
            // processing php ini settings
            if (!empty($data['php']['ini'])) {
                foreach ($data['php']['ini'] as $k => $v) {
                    foreach ($v as $k2 => $v2) {
                        $temp = ini_get($k . '.' . $k2);
                        if (ini_get($k . '.' . $k2) != $v2) {
                            $result['error'][] = " - PHP ini setting $k.$k2 is \"$temp\", should be $v2!";
                        }
                    }
                }
            }
            // processing apache modules
            if (!empty($data['apache']['module'])) {
                if (function_exists('apache_get_modules')) {
                    $ext_have = array_map('strtolower', apache_get_modules());
                    foreach ($data['apache']['module'] as $k => $v) {
                        if (!in_array($k, $ext_have)) {
                            $result['error'][] = " - Apache module \"$k\" is not loaded!";
                        }
                    }
                } elseif (!empty($options['show_warnings'])) {
                    echo "\n";
                    echo Cmd::colorString('Make sure following Apache modules are enabled:', 'red') . "\n";
                    echo "\t";
                    foreach ($data['apache']['module'] as $k => $v) {
                        echo $k . " ";
                    }
                    echo "\n";
                }
            }
            // extra configs
            if (!empty($data['extra_configs']['warnings']) && !empty($options['show_warnings'])) {
                echo "\n";
                echo Cmd::colorString('Additional configuration settings:', 'red') . "\n";
                foreach ($data['extra_configs']['warnings'] as $k => $v) {
                    echo "\t" . $k . ": " . $v . "\n";
                }
                echo "\n";
            }
            // processing models
            if (!empty($data['model'])) {
                $data['model_processed'] = $data['model'];
            }
            // need to sort models in order of dependencies
            $imports = [];
            foreach ($data['model_processed'] as $k => $v) {
                // find submodule
                foreach ($data['__model_dependencies'] as $k2 => $v2) {
                    if (!empty($v2[$k])) {
                        $imports[$k2][$k] = $k;
                        break;
                    }
                }
            }
            // clean up unused dependencies
            foreach ($data['__submodule_dependencies'] as $k2 => $v2) {
                if (empty($imports[$k2])) {
                    $data['__submodule_dependencies'][$k2] = [];
                } else {
                    foreach ($v2 as $k3 => $v3) {
                        if (empty($imports[$k3])) {
                            unset($data['__submodule_dependencies'][$k2][$k3]);
                        }
                    }
                }
            }
            // we need to go though an array few times to fix dependency issues
            for ($i = 0; $i < 12; $i++) {
                foreach ($imports as $k => $v) {
                    if (empty($data['__submodule_dependencies'][$k])) {
                        $data['model_import'][$k] = $v;
                        unset($imports[$k]);
                        // we need to remove file from dependency
                        foreach ($data['__submodule_dependencies'] as $k2 => $v2) {
                            unset($data['__submodule_dependencies'][$k2][$k]);
                        }
                    }
                }
            }
            // undependent import object go last
            if (!empty($imports)) {
                foreach ($imports as $k => $v) {
                    $data['model_import'][$k] = $v;
                }
            }
            foreach ($data['model_import'] as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    $temp = $data['model_processed'][$k2];
                    unset($data['model_processed'][$k2]);
                    $data['model_processed'][$k2] = $temp;
                }
            }
            unset($data['__submodule_dependencies'], $data['__model_dependencies'], $data['model_import']);
            // we do need to write files
            if ($options['mode'] == 'test') {
                $result['data'] = $data;
                $result['success'] = true;
                return $result;
            }
            // active record models, scopes, pivots and relations
            $override_pivot_scope_relation = [];
            File::delete('./Overrides/Class', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
            $period_tables = [];
            foreach ($data['model_processed'] as $k => $v) {
                if ($v != '\Object\Table') {
                    continue;
                }
                $reflector = new \ReflectionClass($k);
                $pathinfo = pathinfo($reflector->getFileName(), PATHINFO_ALL);
                $ar_name = $pathinfo['filename'] . 'AR';
                $ar_filename = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $ar_name . '.php';
                $ar_class = explode("\\", $k);
                $original_name = array_pop($ar_class);
                $ar_namespace = ltrim(implode("\\", $ar_class), "\\");
                array_push($ar_class, $ar_name);
                $ar_relative_filename = implode(DIRECTORY_SEPARATOR, $ar_class) . '.js';
                $ar_class = implode("\\", $ar_class);
                // delete file
                if (file_exists($ar_filename)) {
                    File::delete($ar_filename);
                }
                /** @var Table $model */
                $model = new $k(['skip_db_object' => true]);
                $constants = '';
                $js_constants = '';
                if (!empty($model->active_record_preset_constants)) {
                    foreach ($model->active_record_preset_constants as $k2 => $v2) {
                        $constants .= "    " . 'public const ' . $k2 . ' = ' . var_export($v2, true) . ';' . "\n";
                        $js_constants .= "    " . $k2 . ' = ' . json_encode($v2) . ';' . "\n";
                    }
                    $constants = "    " . trim($constants);
                }
                $model_pk = var_export_condensed($model->pk ?? []);
                $class_code = <<<TTT
<?php

namespace {$ar_namespace};
class {$ar_name} extends \Object\ActiveRecord {

{$constants}

    /**
     * @var string
     */
    public string \$object_table_class = {$k}::class;

    /**
     * @var array
     */
    public array \$object_table_pk = {$model_pk};
TTT;
                $js_model_name = json_encode($k);
                $js_model_code = <<<TTT
class {$ar_name}Class {

{$js_constants}

    object_table_class = $js_model_name;

TTT;
                if ($model->periods['type'] !== 'none') {
                    if ($model->periods['type'] == YEAR) {
                        for ($i = $model->periods['year_start']; $i <= $model->periods['year_end']; $i++) {
                            $period_short_name = str_replace(['[table]', '[year]'], [$original_name, $i], $model->periods['class']);
                            $period_tables[$period_short_name] = [
                                'model' => $model,
                                'class' => $k,
                                'namespace' => $ar_namespace,
                                'dirname' => $pathinfo['dirname'],
                                'periods' => $model->periods,
                                'short_name' => $period_short_name,
                                'original_name' => $pathinfo['dirname'] . '\\' . $original_name,
                                'table_name' => $model->name . '_generated_year_' . $i,
                                'original_table' => $model->name,
                                'filter' => [
                                    $model->column_prefix . 'year' => $i,
                                ],
                                'constraints' => $model->constraints,
                            ];
                        }
                    }
                    if ($model->periods['type'] == YEAR_AND_MONTH) {
                        for ($i = $model->periods['year_start']; $i <= $model->periods['year_end']; $i++) {
                            for ($j = $model->periods['month_start']; $j <= $model->periods['month_end']; $j++) {
                                $period_month = str_pad($j, 2, '0', STR_PAD_LEFT);
                                $period_short_name = str_replace(['[table]', '[year]', '[month]'], [$original_name, $i, $period_month], $model->periods['class']);
                                $period_tables[$period_short_name] = [
                                    'model' => $model,
                                    'class' => $k,
                                    'namespace' => $ar_namespace,
                                    'dirname' => $pathinfo['dirname'],
                                    'periods' => $model->periods,
                                    'short_name' => $period_short_name,
                                    'original_name' => $pathinfo['dirname'] . '\\' . $original_name,
                                    'table_name' => $model->name . '_generated_year_' . $i . '_month_' . $period_month,
                                    'original_table' => $model->name,
                                    'filter' => [
                                        $model->column_prefix . 'year' => $i,
                                        $model->column_prefix . 'month' => $j,
                                    ],
                                    'constraints' => $model->constraints,
                                ];
                            }
                        }
                    }
                }
                foreach ($model->columns as $k2 => $v2) {
                    $column_type = $v2['php_type'];
                    if ($column_type == 'integer') {
                        $column_type = 'int';
                    }
                    $column_js = $k2;
                    $nullable = '?';
                    if ($column_type == 'mixed') {
                        $nullable = '';
                    }
                    if ($column_type == 'bcnumeric') {
                        $column_type = 'mixed';
                        $nullable = '';
                    }
                    $column_setting = $model->column_settings[$k2] ?? null;
                    if ($column_setting && in_array(CASTABLE, $column_setting)) {
                        $column_type .= '|' . $column_setting['php_type'];
                    }
                    if (!empty($nullable)) {
                        $column_type .= '|' . 'null';
                    }
                    $column = 'public ' . $column_type . ' $' . $k2;
                    $hook = "{
                        get => \$this->$k2;
                        set {
                            \$this->setFullPkAndFilledColumn('$k2', \$value);
                            \$this->$k2 = \$value;
                        }
                    }";
                    if (array_key_exists('default', $v2)) {
                        $column .= ' = ' . var_export($v2['default'], true) . ' ' . $hook;
                        $column_js .= ' = ' . json_encode($v2['default']) . ';';
                    } else {
                        $column .= ' = null ' . $hook;
                        $column_js .= ' = null;';
                    }
                    $domain = '';
                    $comment_domain = '';
                    if (isset($v2['domain'])) {
                        $domain .= ' Domain: ' . $v2['domain'];
                        $comment_domain = ' {domain{' . $v2['domain'] . '}}';
                    }
                    if (isset($v2['type'])) {
                        $domain .= ' Type: ' . $v2['type'];
                    }
                    $domain = trim($domain);
                    $comment_generated = '';
                    $comment_column_settings = '';
                    $comment_options_model = '';
                    if (isset($v2['options_model'])) {
                        $comment_options_model = ' {options_model{' . $v2['options_model'] . '}}';
                    }
                    if (isset($model->column_settings[$k2])) {
                        $comment_generated = ' (Generated)';
                        $cs = $model->column_settings[$k2];
                        foreach (ACTION_KEYS as $v3) {
                            unset($cs[$v3]);
                        }
                        $comment_column_settings = ' ' . implode(', ', $cs);
                    }
                    $class_code .= <<<TTT

    /**
     * {$v2['name']}{$comment_generated}
     *
     *{$comment_column_settings}
     *{$comment_options_model}
     *{$comment_domain}
     *
     * @var {$column_type} {$domain}
     */
    {$column}

TTT;
                    $js_model_code .= <<<TTT

    /**
     * {$v2['name']}{$comment_generated}
     *
     *{$comment_column_settings}
     *{$comment_options_model}
     *{$comment_domain}
     *
     * @var {$column_type} {$domain}
     */
    {$column_js}

TTT;
                }
                // column settings for overrides
                if (!empty($model->column_settings)) {
                    foreach ($model->column_settings as $k2 => $v2) {
                        if (isset($model->columns[$k2])) {
                            continue;
                        }
                        $column = 'public $' . $k2 . ' = null;';
                        foreach (ACTION_KEYS as $v3) {
                            unset($v2[$v3]);
                        }
                        $comment = implode(', ', $v2);
                        $class_code .= <<<TTT

    /**
     * (Generated) (Non Database)
     *
     * {$comment}
     *
     * @var mixed
     */
    {$column}

TTT;
                    }
                }
                $class_code .= <<<TTT
}

TTT;
                $js_model_code .= <<<TTT

}

export const {$ar_name} = new {$ar_name}Class();
export default {
    {$ar_name}
}

TTT;
                File::write($ar_filename, $class_code);
                // sites
                $sites = \Application::get('application.sites') ?? [];
                foreach ($sites as $site => $site_data) {
                    if (!empty($site_data['model']['dir'])) {
                        $js_model_file_name = rtrim($site_data['model']['dir'], '/') . DIRECTORY_SEPARATOR . ltrim($ar_relative_filename, '/');
                        $js_model_directory = dirname($js_model_file_name);
                        if (!file_exists($js_model_directory)) {
                            File::mkdir($js_model_directory, 0777, ['skip_realpath' => true]);
                        }
                        File::write($js_model_file_name, $js_model_code);
                    }
                }
                // process pivots, relations and scopes
                $reflection = new \ReflectionClass($model);
                $short_name = $reflection->getShortName();
                foreach ($reflection->getMethods() as $method) {
                    $method_name = $method->getName();
                    if (str_starts_with($method_name, 'scope')) {
                        $method_name_new = str_replace('scope', '', $method_name);
                        if (isset($override_pivot_scope_relation[$short_name]['scope'][$method_name_new])) {
                            throw new \Exception("Scope $short_name $method_name_new already exists!");
                        }
                        $scope = [
                            'class' => $k,
                            'method' => $method_name,
                            'short_class' => $short_name,
                            'short_method' => $short_name
                        ];
                        $override_pivot_scope_relation[$short_name . '::' . 'scope' . '::' . $method_name_new] = $scope;
                        $override_pivot_scope_relation['Scope' . $short_name . $method_name_new] = $scope;
                    }
                }
                $class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($override_pivot_scope_relation, true) . ';';
                File::write('./Overrides/Class/Override_Object_Scopes_Relations_Pivots.php', $class_code);
            }
            // periods tables
            foreach ($period_tables as $k2 => $v2) {
                $filter = var_export($v2['filter'], true);
                $constraints = [];
                foreach ($v2['constraints'] ?? [] as $k3 => $v3) {
                    if (str_starts_with($k3, $v2['original_table'])) {
                        $constraints[str_replace($v2['original_table'], $v2['table_name'], $k3)] = $v3;
                    } else {
                        $constraints[$v2['table_name'] . '_' . $k3] = $v3;
                    }
                }
                $constraints = var_export($constraints, true);
                $class_code = <<<TTT
<?php

namespace {$v2['namespace']};
class {$v2['short_name']} extends {$v2['class']} {
    /**
     * Name
     *
     * @var string
     */
    public \$name = '{$v2['table_name']}';

    /**
     * Constraints
     *
     * @var array
     */
    public \$constraints = {$constraints};

    /**
     * Is period table
     *
     * @var bool
     */
    public bool \$is_period_table = true;

    /**
     * Filter
     *
     * @var array
     */
    public array \$filter = {$filter};
}

TTT;
                $period_filename = $v2['dirname'] . DIRECTORY_SEPARATOR . $v2['short_name'] . '.php';
                if (file_exists($period_filename)) {
                    File::delete($period_filename);
                }
                File::write($period_filename, $class_code);
            }
            // handling overrides, cleanup directory first
            if (!empty($data['override'])) {
                array_keys_to_string($data['override'], $data['override_processed']);
                $override_classes = [];
                $override_found = false;
                foreach ($data['override_processed'] as $k => $v) {
                    if (!isset($override_classes[$v])) {
                        $override_classes[$v] = [
                            'object' => new Blank(),
                            'found' => false
                        ];
                    }
                    $override_class = str_replace('.', '_', $k);
                    $override_object = new $override_class();
                    $vars = get_object_vars($override_object);
                    if (!empty($vars)) {
                        $override_classes[$v]['found'] = true;
                        $override_found = true;
                        object_merge_values($override_classes[$v]['object'], $vars);
                    }
                }
                // we need to write overrides to disk
                if ($override_found) {
                    foreach ($override_classes as $k => $v) {
                        if ($v['found']) {
                            $class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($v['object'], true) . ';';
                            $temp_name = str_replace('\\', '_', trim($k, '\\'));
                            File::write('./Overrides/Class/Override_' . $temp_name . '.php', $class_code);
                        }
                    }
                }
            }
            // acls
            if (!empty($data['acl'])) {
                $temp_models = [];
                foreach ($data['acl'] as $k => $v) {
                    $object = new $k();
                    $models = $object->models;
                    foreach ($models as $k2 => $v2) {
                        $temp_models[$k2][$k] = $v2;
                    }
                }
                $class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($temp_models, true) . ';';
                File::write('./Overrides/Class/Override_Object_ACL_Registered.php', $class_code);
            }
            // middleware
            if (!empty($data['middleware'])) {
                array_key_sort($data['middleware'], ['priority' => SORT_DESC]);
                $php_code = '$object_override_blank_object = ' . var_export($data['middleware'], true) . ';';
                File::write('./Miscellaneous/Middlewares/AllMiddlewares.php', "<?php" . "\n" . $php_code);
            }
            // routes
            if (!empty($data['route'])) {
                $php_code = '';
                foreach ($data['route'] as $v) {
                    $php_code .= str_replace('<?php', '', File::read($v));
                }
                File::write('./Miscellaneous/Routes/AllRoutes.php', "<?php" . "\n" . $php_code);
            }
            // apis
            if (!empty($data['api'])) {
                $php_code = '';
                $import = [];
                foreach ($data['api'] as $k => $v) {
                    $method_code = Reflection::getMethodCode($k, 'routes');
                    $method_code = str_replace('self::class', $k . '::class', $method_code);
                    $method_object = \Factory::model($k, true, [['skip_constructor_loading' => true]]);
                    $method_code = str_replace('$this->name', var_export($method_object->name, true), $method_code);
                    $method_code = str_replace('$this->base_url', var_export($method_object->base_url, true), $method_code);
                    $method_code = str_replace('$this->route_options', var_export_condensed($method_object->route_options, true), $method_code);
                    $php_code .= "\n\n" . $method_code;
                    // prepare for import
                    $methods = Reflection::getMethods($k, \ReflectionMethod::IS_PUBLIC, \Route::HTTP_REQUEST_METHOD_LOWER_CASE);
                    $api_methods = [
                        [
                            'sm_rsrcapimeth_method_code' => 'AllActions',
                            'sm_rsrcapimeth_method_name' => 'All Methods',
                            'sm_rsrcapimeth_inactive' => 0,
                        ],
                    ];
                    $method_counter = 0;
                    foreach ($methods as $k2 => $v2) {
                        foreach ($v2 as $k3 => $v3) {
                            $api_methods[] = [
                                'sm_rsrcapimeth_method_code' => $k3,
                                'sm_rsrcapimeth_method_name' => $v3['name_nice'],
                                'sm_rsrcapimeth_inactive' => 0,
                            ];
                            $method_counter++;
                        }
                    }
                    $import[$k] = [
                        'sm_resource_id' => '::id::' . $k,
                        'sm_resource_code' => $k,
                        'sm_resource_type' => 150,
                        'sm_resource_classification' => 'APIs',
                        'sm_resource_name' => $method_object->name,
                        'sm_resource_version_code' => $method_object->version,
                        'sm_resource_api_method_counter' => $method_counter,
                        'sm_resource_description' => null,
                        'sm_resource_icon' => 'fas fa-tape',
                        'sm_resource_module_code' => $method_object->group[0] ?? 'SM',
                        'sm_resource_group1_name' => $method_object->group[1] ?? null,
                        'sm_resource_group2_name' => $method_object->group[2] ?? null,
                        'sm_resource_group3_name' => $method_object->group[3] ?? null,
                        'sm_resource_group4_name' => $method_object->group[4] ?? null,
                        'sm_resource_group5_name' => $method_object->group[5] ?? null,
                        'sm_resource_group6_name' => $method_object->group[6] ?? null,
                        'sm_resource_group7_name' => $method_object->group[7] ?? null,
                        'sm_resource_group8_name' => $method_object->group[8] ?? null,
                        'sm_resource_group9_name' => $method_object->group[9] ?? null,
                        'sm_resource_acl_public' => $method_object->acl['public'] ? 1 : 0,
                        'sm_resource_acl_authorized' => $method_object->acl['authorized'] ? 1 : 0,
                        'sm_resource_acl_permission' => $method_object->acl['permission'] ? 1 : 0,
                        'sm_resource_menu_acl_resource_id' => null,
                        'sm_resource_menu_acl_method_code' => null,
                        'sm_resource_menu_acl_action_id' => null,
                        'sm_resource_menu_url' => null,
                        'sm_resource_menu_options_generator' => null,
                        'sm_resource_inactive' => 0,
                        '\Numbers\Backend\System\Modules\Model\Resource\Features' => [],
                        '\Numbers\Backend\System\Modules\Model\Resource\APIMethods' => $api_methods
                    ];
                }
                File::write('./Miscellaneous/Routes/APIRoutes.php', "<?php" . "\n" . $php_code);
                if (!empty($import)) {
                    $temp = var_export(array_values($import), true);
                    $php_code = <<<TTT
namespace Overrides\Imports;
class APIControllers extends \Object\Import {
    public \$data = [
        'controllers' => [
            'options' => [
                'pk' => ['sm_resource_id'],
                'model' => '\Numbers\Backend\System\Modules\Model\Collection\Resources',
                'method' => 'save'
            ],
            'data' => {$temp}
        ]
    ];
}

TTT;
                    File::write('./Overrides/Imports/APIControllers.php', "<?php" . "\n" . $php_code);
                }
            }
            // constants
            if (!empty($data['constant'])) {
                $php_code = '';
                foreach ($data['constant'] as $v) {
                    $php_code .= str_replace('<?php', '', File::read($v));
                }
                File::write('./Miscellaneous/Constants/AllConstants.php', "<?php" . "\n" . $php_code);
            }
            // unit tests
            File::delete('./Overrides/UnitTests', ['only_contents' => true, 'skip_files' => ['.gitkeep']]);
            // submodule tests first
            if (!empty($data['unit_tests'])) {
                $xml = '';
                $xml .= '<phpunit bootstrap="../../../libraries/vendor/Numbers/Framework/System/Managers/UnitTests.php">';
                $xml .= '<testsuites>';
                foreach ($data['unit_tests'] as $k => $v) {
                    $xml .= '<testsuite name="SkipMeNow\\' . $k . '">';
                    foreach (File::iterate($v, ['recursive' => true, 'only_extensions' => ['php']]) as $v2) {
                        $xml .= '<file>../../' . $v2 . '</file>';
                    }
                    $xml .= '</testsuite>';
                }
                $xml .= '</testsuites>';
                $xml .= '</phpunit>';
                File::write('./Overrides/UnitTests/submodules.xml', $xml);
            }
            // application test last
            $application_tests = File::iterate('Miscellaneous/UnitTests', ['recursive' => true, 'only_extensions' => ['php']]);
            if (!empty($application_tests)) {
                $xml = '';
                $xml .= '<phpunit bootstrap="../../../libraries/vendor/Numbers/Framework/System/Managers/UnitTests.php">';
                $xml .= '<testsuites>';
                $xml .= '<testsuite name="application/unit/tests">';
                foreach ($application_tests as $v) {
                    $xml .= '<file>../../' . $v . '</file>';
                }
                $xml .= '</testsuite>';
                $xml .= '</testsuites>';
                $xml .= '</phpunit>';
                File::write('./Overrides/UnitTests/application.xml', $xml);
            }
            // adding new localizations
            if ($options['mode'] == 'commit') {
                // we put localization into application folder
                $data['localize'] = [];
                $locs = [
                    'Miscellaneous' . DIRECTORY_SEPARATOR => []
                ];
                // process import models
                $suffixes = ['_name', '_classification', '_description'];
                foreach ($data['loc_model'] as $k => $v) {
                    foreach ($v as $k2 => $v2) {
                        if ($v2 === '\Object\Import') {
                            $object = new $k2();
                            foreach ($object->data as $k3 => $v3) {
                                foreach ($v3['data'] as $k4 => $v4) {
                                    foreach ($v4 as $k5 => $v5) {
                                        if ($v5 === null || $v5 === '') {
                                            continue;
                                        }
                                        foreach ($suffixes as $v6) {
                                            if (str_ends_with($k5, $v6) && strpos($k5, 'file') === false) {
                                                $filekey = \String2::createStatic($v5)->englishOnly(true)->toString();
                                                $filename = 'NF.System';
                                                $locs[$k][$filename][$filekey] = $v5;
                                                $locs['Miscellaneous' . DIRECTORY_SEPARATOR][$filename][$filekey] = $v5;
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($v2 === '\Object\Table' || $v2 === '\Object\Data') {
                            $object = new $k2(['skip_db_object' => true]);
                            $filekey = \String2::createStatic($object->title)->englishOnly(true)->toString();
                            $filename = 'NF.Model';
                            // title
                            $locs[$k][$filename][$filekey] = $object->title;
                            $locs['Miscellaneous' . DIRECTORY_SEPARATOR][$filename][$filekey] = $object->title;
                            // columns
                            foreach ($object->columns as $k3 => $v4) {
                                $filekey = \String2::createStatic($v4['name'])->englishOnly(true)->toString();
                                $locs[$k][$filename][$filekey] = $v4['name'];
                                $locs['Miscellaneous' . DIRECTORY_SEPARATOR][$filename][$filekey] = $v4['name'];
                            }
                        }
                    }
                }
                // php classes with messages and errors
                $php_errno_constants = $php_class_constants = $php_preset_constants = $php_mapping_constants = [
                    'NF.Error' => [],
                    'NF.Message' => [],
                    'NF.Status' => [],
                ];
                foreach ($php_class_constants as $k => $v) {
                    $class = "\\" . str_replace('.', "\\", $k);
                    if (!isset($php_errno_constants[$k]['prefix'])) {
                        $reflector = new \ReflectionClass($class);
                        $php_errno_constants[$k]['prefix'] = $reflector->getStaticPropertyValue('prefix');
                        $php_errno_constants[$k]['max'] = 0;
                        $php_errno_constants[$k]['filename'] = $reflector->getFileName();
                    }
                    $constants = Reflection::getConstants($class);
                    foreach ($constants as $k2 => $v2) {
                        $php_class_constants[$k][$k2] = $v2;
                        $temp = explode('.', array_key_first($v2), 3);
                        $filename = $temp[0] . '.' . $temp[1];
                        $php_mapping_constants[$k][$temp[2]] = $k2;
                        if (isset($v2['errno'])) {
                            $errno = (int) str_replace($php_errno_constants[$k]['prefix'], '', $v2['errno']);
                            if ($errno > $php_errno_constants[$k]['max']) {
                                $php_errno_constants[$k]['max'] = $errno;
                            }
                        }
                    }
                }
                // process localizations from classes
                foreach ($data['loc'] as $k => $v) {
                    // loop through models
                    foreach ($v as $k2 => $v2) {
                        if (!in_array($v2, ['Email', 'SMS', 'Form', 'List', 'Report', 'API', 'Other', 'Constant', 'Enum'])) {
                            continue;
                        }
                        $enum_loc = [];
                        if ($v2 == 'Enum') {
                            foreach (call_user_func([$k2, 'options']) as $k3 => $v3) {
                                if (!empty($v3['loc'])) {
                                    $enum_loc[$v3['loc']] = $v3['name'];
                                }
                                // descriptions have auto-generated locs
                                if (!empty($v3['description'])) {
                                    $temp = \String2::createStatic($v3['description'])->englishOnly(true)->toString();
                                    $enum_loc['NF.Message.' . $temp] = $v3['description'];
                                }
                            }
                            $object = null;
                        } elseif ($v2 == 'API') {
                            $object = new $k2(['skip_constructor_loading' => true, 'load_localization' => true]);
                        } else {
                            $object = new $k2(['skip_acl' => true, 'skip_db_object' => true, 'skip_processing' => true]);
                        }
                        if (empty($object->loc) && empty($object->loc_constants) && empty($enum_loc)) {
                            continue;
                        }
                        // if we have constants
                        if (!empty($object->loc_constants)) {
                            foreach ($object->loc_constants as $k3 => $v3) {
                                $temp = explode('.', array_key_first($v3), 3);
                                $filename = $temp[0] . '.' . $temp[1];
                                $loc_value = $v3[array_key_first($v3)];
                                $locs[$k][$filename][$temp[2]] = $loc_value;
                                $locs['Miscellaneous' . DIRECTORY_SEPARATOR][$filename][$temp[2]] = $loc_value;
                                $constant = explode('::', $k3);
                                if (class_exists($constant[0])) {
                                    $constant_name = $constant[1];
                                    if (!isset($php_mapping_constants[$filename][$temp[2]])) {
                                        if (empty($v3['errno'])) {
                                            $php_errno_constants[$filename]['max'] = $php_errno_constants[$filename]['max'] + 1;
                                            $v3['errno'] = $php_errno_constants[$filename]['prefix'] . str_pad($php_errno_constants[$filename]['max'] . '', 4, '0', STR_PAD_LEFT);
                                        }
                                        $php_preset_constants[$filename][$constant_name] = $v3;
                                        $php_mapping_constants[$filename][$temp[2]] = $constant_name;
                                    } else {
                                        $php_preset_constants[$filename][$constant_name] = $php_class_constants[$filename][$constant_name];
                                    }
                                }
                            }
                        }
                        // if we have loc
                        if (!empty($object->loc) || $enum_loc) {
                            foreach (($object->loc ?? $enum_loc) as $k3 => $v3) {
                                $temp = explode('.', $k3, 3);
                                $filename = $temp[0] . '.' . $temp[1];
                                $locs[$k][$filename][$temp[2]] = $v3;
                                $locs['Miscellaneous' . DIRECTORY_SEPARATOR][$filename][$temp[2]] = $v3;
                                // special constants to use in the code
                                if ($filename === 'NF.Error' || $filename === 'NF.Message') {
                                    $constant_name = (new \String2($temp[2]))->spaceOnUpperCase()->uppercase()->snakeCase()->toString();
                                    if (!isset($php_mapping_constants[$filename][$temp[2]])) {
                                        $php_errno_constants[$filename]['max'] = $php_errno_constants[$filename]['max'] + 1;
                                        $php_preset_constants[$filename][$constant_name] = [
                                            $k3 => $v3,
                                            'errno' => $php_errno_constants[$filename]['prefix'] . str_pad($php_errno_constants[$filename]['max'] . '', 4, '0', STR_PAD_LEFT),
                                        ];
                                        $php_mapping_constants[$filename][$temp[2]] = $constant_name;
                                    } else {
                                        $php_preset_constants[$filename][$constant_name] = $php_class_constants[$filename][$constant_name];
                                    }
                                }
                            }
                        }
                    }
                }
                // generate constants
                foreach ($php_preset_constants as $k => $v) {
                    ksort($v);
                    $php_code = [];
                    foreach ($v as $k2 => $v2) {
                        $php_code[] = '    public const ' . $k2 . ' = ' . var_export_condensed($v2) . ';';
                    }
                    $temp = explode('.', $k);
                    $template = File::read(__DIR__ . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'NFConstants.template.txt');
                    $template = str_replace([
                        '{namespace}',
                        '{classname}',
                        '{prefix}',
                        '{constants}',
                    ], [
                        $temp[0],
                        $temp[1],
                        $php_errno_constants[$k]['prefix'],
                        implode("\n", $php_code),
                    ], $template);
                    // save
                    if (!File::write($php_errno_constants[$k]['filename'], $template)) {
                        throw new \Exception('Cannot save template file: '. $php_errno_constants[$k]['filename']);
                    }
                }
                // generate files
                $default_locale = \Application::get('flag.global.loc.default_locale') ?? 'en_CA.UTF-8';
                foreach ($locs as $k => $v) {
                    foreach ($v as $k2 => $v2) {
                        // check if directory exists
                        $dir = $k . 'Localization' . DIRECTORY_SEPARATOR . $default_locale;
                        if (!is_dir($dir)) {
                            if (!File::mkdir($dir, 0777, ['skip_realpath' => true])) {
                                throw new \Exception('Cannot create directory: '. $dir);
                            }
                        }
                        // check if file exists
                        $filename = $dir . DIRECTORY_SEPARATOR . $k2 . '.json';
                        $data['localize'][$filename] = $default_locale;
                        if (!file_exists($filename)) {
                            // sort by key
                            ksort($v2);
                            // save
                            if (!File::writeJSON($filename, $v2)) {
                                throw new \Exception('Cannot save json file: '. $filename);
                            }
                        } else {
                            $decoded = File::readJSON($filename);
                            $v2 = array_merge_hard($decoded, $v2);
                            // sort by key
                            ksort($v2);
                            // save
                            if (!File::writeJSON($filename, $v2)) {
                                throw new \Exception('Cannot save json file: '. $filename);
                            }
                        }
                    }
                }
            }
            // adding presets
            if ($options['mode'] == 'commit' && \Application::get('application.structure.tenant_default_id')) {
                \Db::connectToServers('default', \Application::get('db.default'));
                $sites = \Application::get('application.sites') ?? [];
                foreach ($data['preset'] as $k => $v) {
                    $reflector = new \ReflectionClass($k);
                    $pathinfo = pathinfo($reflector->getFileName(), PATHINFO_ALL);
                    $ar_name = $pathinfo['filename'] . $v;
                    $ar_class = explode("\\", $k);
                    $original_name = array_pop($ar_class);
                    $ar_namespace = ltrim(implode("\\", $ar_class), "\\");
                    array_push($ar_class, $ar_name);
                    $ar_relative_filename = implode(DIRECTORY_SEPARATOR, $ar_class) . '.js';
                    $js_output = [];
                    if ($v == 'OptionsActive') {
                        $model = new $k();
                        $js_output = $model->optionsActive();
                    }
                    $js_output = json_encode($js_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $js_model_code = <<<TTT
class {$ar_name}Class {
    data = {$js_output};
}

export const {$ar_name} = new {$ar_name}Class();
export default {
    {$ar_name}
};

TTT;
                    foreach ($sites as $site_data) {
                        if (!empty($site_data['model']['dir'])) {
                            $js_model_file_name = rtrim($site_data['model']['dir'], '/') . DIRECTORY_SEPARATOR . ltrim($ar_relative_filename, '/');
                            $js_model_directory = dirname($js_model_file_name);
                            if (!file_exists($js_model_directory)) {
                                File::mkdir($js_model_directory, 0777);
                            }
                            File::write($js_model_file_name, $js_model_code);
                        }
                    }
                }
            }
            // adding comands
            if ($options['mode'] == 'commit' && \Can::submoduleExists('Numbers.Backend.System.ShellCommand')) {
                $temp = \Factory::get(['db', 'default']);
                if (empty($temp)) {
                    \Db::connectToServers('default', \Application::get('db.default'));
                }
                $commands = ShellCommands::getStatic([
                    'where' => [
                        'sm_shellcommand_inactive' => 0,
                    ],
                    'columns' => [
                        'code' => 'sm_shellcommand_code',
                        'name' => 'sm_shellcommand_name',
                        'description' => 'sm_shellcommand_description',
                        'model' => 'sm_shellcommand_model',
                        'command' => 'sm_shellcommand_command',
                        'module_code' => 'sm_shellcommand_module_code',
                    ],
                    'pk' => ['command'],
                    'orderby' => [
                        'name' => SORT_ASC,
                    ]
                ]);
                $class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($commands, true) . ';';
                File::write('./Miscellaneous/Commands/AllCommands.php', $class_code);
                $modules = Modules::getStatic([
                    'where' => [
                        'sm_module_inactive' => 0,
                    ],
                    'columns' => [
                        'code' => 'sm_module_code',
                        'name' => 'sm_module_name',
                        'abbreviation' => 'sm_module_abbreviation',
                    ],
                    'pk' => ['code'],
                    'orderby' => [
                        'name' => SORT_ASC,
                    ]
                ]);
                $class_code = "<?php\n\n" . '$object_override_blank_object = ' . var_export($modules, true) . ';';
                File::write('./Miscellaneous/Commands/AllModules.php', $class_code);
            }
            // updating composer.json file
            if ($options['mode'] == 'commit') {
                File::write('../libraries/composer.json', json_encode($composer_data, JSON_PRETTY_PRINT));
            }
            // assinging variables to return to the caller
            $result['data'] = $data;
            if (empty($result['error'])) {
                $result['success'] = true;
            }
        } while (0);
        return $result;
    }

    /**
     * Process models
     *
     * @param array $options
     * @return array
     */
    public static function processModels(array $options = []): array
    {
        $result = [
            'success' => false,
            'error' => [],
            'hint' => [],
            'data' => [],
            'changes' => 0
        ];
        do {
            // we need to process all dependencies first
            $dep = self::processDepsAll($options);
            if (!$dep['success']) {
                $result = $dep;
                $result['error'][] = 'You must fix all dependency related errors first before processing models.';
                break;
            }
            // proccesing models
            if (empty($dep['data']['model_processed'])) {
                $result['error'][] = 'You do not have models to process!';
                break;
            }
            $object_attributes = [];
            $object_relations = [];
            $object_forms = [];
            $flag_relation = true;
            $object_documentation = [];
            $object_import = [];
            $ddl = new numbers_backend_db_class_ddl();
            // run 1 to deterine virtual tables
            $first = true;
            $virtual_models = $dep['data']['model_processed'];
            run_again:
                        foreach ($virtual_models as $k => $v) {
                            $k2 = str_replace('.', '_', $k);
                            if ($v == '\Object\Table') {
                                $model = Factory::model($k2, true);
                                foreach (Widgets::widget_models as $v0) {
                                    if (!empty($model->{$v0})) {
                                        $v01 = $v0 . '_model';
                                        $virtual_models[str_replace('_', '.', $model->{$v01})] = '\Object\Table';
                                    }
                                }
                            }
                        }
            if ($first) {
                $first = false;
                goto run_again; // some widgets have attributes
            }
            $dep['data']['model_processed'] = array_merge_hard($dep['data']['model_processed'], $virtual_models);
            $domains = Domains::getStatic();
            // run 2
            foreach ($dep['data']['model_processed'] as $k => $v) {
                $k2 = str_replace('.', '_', $k);
                if ($v == '\Object\Table') {
                    $model = \Factory::model($k2, true);
                    // todo: disable non default db links
                    $temp_result = $ddl->process_table_model($model);
                    if (!$temp_result['success']) {
                        array_merge3($result['error'], $temp_result['error']);
                    }
                    $object_documentation[$v][$k2] = $k2;
                    // relation
                    if ($flag_relation) {
                        if (!empty($model->relation)) {
                            $domain = $model->columns[$model->relation['field']]['domain'] ?? null;
                            if (!empty($domain)) {
                                $domain = str_replace('_sequence', '', $domain);
                                $type = $domains[$domain]['type'];
                            } else {
                                $type = $model->columns[$model->relation['field']]['type'];
                            }
                            $object_relations[$k2] = [
                                'rn_relattr_code' => $model->relation['field'],
                                'rn_relattr_name' => $model->title,
                                'rn_relattr_model' => $k2,
                                'rn_relattr_domain' => $domain,
                                'rn_relattr_type' => $type,
                                'rn_relattr_inactive' => !empty($model->relation['inactive']) ? 1 : 0
                            ];
                        }
                        if (!empty($model->attributes)) {
                            $object_attributes[$k2] = [
                                'rn_attrmdl_code' => $k2,
                                'rn_attrmdl_name' => $model->title,
                                'rn_attrmdl_inactive' => 0
                            ];
                        }
                    }
                } elseif ($v == '\Object\Sequence') {
                    $temp_result = $ddl->process_sequence_model($k2);
                    if (!$temp_result['success']) {
                        array_merge3($result['error'], $temp_result['error']);
                    }
                    $object_documentation[$v][$k2] = $k2;
                } elseif ($v == '\Object\Function2') {
                    $temp_result = $ddl->process_function_model($k2);
                    if (!$temp_result['success']) {
                        array_merge3($result['error'], $temp_result['error']);
                    }
                    $object_documentation[$v][$k2] = $k2;
                } elseif ($v == '\Object\Extension') {
                    $temp_result = $ddl->process_function_extension($k2);
                    if (!$temp_result['success']) {
                        array_merge3($result['error'], $temp_result['error']);
                    }
                    $object_documentation[$v][$k2] = $k2;
                } elseif ($v == '\Object\Import') {
                    $object_import[$k2] = [
                        'model' => $k2
                    ];
                }
            }

            // if we have erros
            if (!empty($result['error'])) {
                break;
            }

            // db factory
            $db_factory = \Factory::get('db');

            // we load objects from database
            $loaded_objects = [];
            foreach ($ddl->db_links as $k => $v) {
                $ddl_object = $db_factory[$k]['ddl_object'];
                $temp_result = $ddl_object->load_schema($k);
                if (!$temp_result['success']) {
                    array_merge3($result['error'], $temp_result['error']);
                } else {
                    $loaded_objects[$k] = $temp_result['data'];
                }
            }

            // if we have erros
            if (!empty($result['error'])) {
                break;
            }

            // get a list of all db links
            $db_link_list = array_unique(array_merge(array_keys($ddl->objects), array_keys($loaded_objects)));

            // if we are dropping schema
            if ($options['mode'] == 'drop') {
                $ddl->objects = [];
            }

            // compare schemas per db link
            $schema_diff = [];
            $total_per_db_link = [];
            $total = 0;
            foreach ($db_link_list as $k) {
                // we need to have a back end for comparison
                $compare_options['backend'] = $db_factory[$k]['backend'];
                // comparing
                $temp_result = $ddl->compare_schemas(isset($ddl->objects[$k]) ? $ddl->objects[$k] : [], isset($loaded_objects[$k]) ? $loaded_objects[$k] : [], $compare_options);
                if (!$temp_result['success']) {
                    array_merge3($result['hint'], $temp_result['error']);
                } else {
                    $schema_diff[$k] = $temp_result['data'];
                    if (!isset($total_per_db_link[$k])) {
                        $total_per_db_link[$k] = 0;
                    }
                    $total_per_db_link[$k] += $temp_result['count'];
                    $total += $temp_result['count'];
                }
            }

            // if there's no schema changes
            if ($total == 0) {
                if ($options['mode'] == 'commit') {
                    goto import_data;
                } else {
                    $result['success'] = true;
                }
                break;
            }

            // we need to provide a list of changes
            foreach ($total_per_db_link as $k => $v) {
                // total changes
                $result['changes'] += $v;
                // printing summary
                $result['hint'][] = '    * Link ' . $k . ': ';
                foreach ($schema_diff[$k] as $k2 => $v2) {
                    $result['hint'][] = '       * ' . $k2 . ': ';
                    foreach ($v2 as $k3 => $v3) {
                        $result['hint'][] = '        * ' . $k3 . ' - ' . $v3['type'];
                    }
                }
            }

            // if we are in no commit mode we exit
            if (!in_array($options['mode'], ['commit', 'drop'])) {
                break;
            }

            // generating sql
            foreach ($total_per_db_link as $k => $v) {
                if ($v == 0) {
                    continue;
                }
                $ddl_object = $db_factory[$k]['ddl_object'];
                foreach ($schema_diff[$k] as $k2 => $v2) {
                    foreach ($v2 as $k3 => $v3) {
                        // we need to make fk constraints last to sort MySQL issues
                        if ($k2 == 'new_constraints' && $v3['type'] == 'constraint_new' && $v3['data']['type'] == 'fk') {
                            $schema_diff[$k][$k2 . '_fks'][$k3]['sql'] = $ddl_object->renderSql($v3['type'], $v3);
                        } else {
                            $schema_diff[$k][$k2][$k3]['sql'] = $ddl_object->renderSql($v3['type'], $v3, ['mode' => $options['mode']]);
                        }
                    }
                }
            }

            // executing sql
            foreach ($total_per_db_link as $k => $v) {
                if ($v == 0) {
                    continue;
                }
                $db_object = new \Db($k);
                // if we are dropping we need to disable foregn key checks
                if ($options['mode'] == 'drop') {
                    if ($db_object->backend == 'mysqli') {
                        $db_object->query('SET foreign_key_checks = 0;');
                        // we also need to unset sequences
                        unset($schema_diff[$k]['delete_sequences']);
                    }
                }
                foreach ($schema_diff[$k] as $k2 => $v2) {
                    foreach ($v2 as $k3 => $v3) {
                        if (empty($v3['sql'])) {
                            continue;
                        }
                        if (is_array($v3['sql'])) {
                            $temp = $v3['sql'];
                        } else {
                            $temp = [$v3['sql']];
                        }
                        foreach ($temp as $v4) {
                            $temp_result = $db_object->query($v4);
                            if (!$temp_result['success']) {
                                array_merge3($result['error'], $temp_result['error']);
                                goto error;
                            }
                        }
                    }
                }
            }

            // if we got here - we are ok
            $result['success'] = true;
        } while (0);
        error:
                return $result;
        // import data
        import_data:
                // we need to import data
                if (!empty($object_import) && $options['mode'] == 'commit') {
                    $result['hint'][] = '';
                    foreach ($object_import as $k => $v) {
                        $data_object = new $k();
                        $data_result = $data_object->process();
                        if (!$data_result['success']) {
                            throw new \Exception(implode("\n", $data_result['error']));
                        }
                        $result['hint'] = array_merge($result['hint'], $data_result['hint']);
                    }
                }
        // relation
        if ($flag_relation && $options['mode'] == 'commit') {
            $result['hint'][] = '';
            $model2 = \Factory::model('numbers_data_relations_model_relation_attributes');
            // insert new models
            if (!empty($object_relations)) {
                foreach ($object_relations as $k => $v) {
                    $result_insert = $model2->save($v, ['pk' => ['rn_relattr_code'], 'ignore_not_set_fields' => true]);
                }
                $result['hint'][] = ' * Imported relation models!';
            }
            // we need to process forms
            foreach ($dep['data']['submodule_dirs'] as $v) {
                $dir = $v . 'model/form/';
                if (!file_exists($dir)) {
                    continue;
                }
                $files = File::iterate($dir, ['only_extensions' => ['php']]);
                foreach ($files as $v2) {
                    $model_name = str_replace(['../libraries/vendor/', '.php'], '', $v2);
                    $model_name = str_replace('/', '_', $model_name);
                    $model = new $model_name(['skip_processing' => true]);
                    if (empty($model->form_object->misc_settings['option_models'])) {
                        continue;
                    }
                    // loop though fields
                    foreach ($model->form_object->misc_settings['option_models'] as $k3 => $v3) {
                        $object_forms[$model_name . '::' . $k3] = [
                            'rn_relfrmfld_form_code' => $model_name,
                            'rn_relfrmfld_form_name' => $model->title,
                            'rn_relfrmfld_field_code' => $k3,
                            'rn_relfrmfld_field_name' => $v3['field_name'],
                            'rn_relfrmfld_relattr_id' => $v3['model'],
                            'rn_relfrmfld_inactive' => 0
                        ];
                    }
                }
            }
            if (!empty($object_forms)) {
                // load all relation models
                $data = $model2->get(['pk' => ['rn_relattr_model']]);
                $model = \Factory::model('numbers_data_relations_model_relation_formfields');
                foreach ($object_forms as $k => $v) {
                    if (empty($data[$v['rn_relfrmfld_relattr_id']])) {
                        continue;
                    }
                    $v['rn_relfrmfld_relattr_id'] = $data[$v['rn_relfrmfld_relattr_id']]['rn_relattr_id'];
                    $result_insert = $model->save($v, ['pk' => ['rn_relfrmfld_form_code', 'rn_relfrmfld_field_code'], 'ignore_not_set_fields' => true]);
                }
                $result['hint'][] = ' * Imported relation form fields!';
            }
            // todo: import models
            if (!empty($object_attributes)) {
                $model = \Factory::model('numbers_data_relations_model_attribute_models');
                foreach ($object_attributes as $k => $v) {
                    $result_insert = $model->save($v, ['pk' => ['rn_attrmdl_code'], 'ignore_not_set_fields' => true]);
                }
                $result['hint'][] = ' * Imported attribute models!';
            }
        }
        // we need to generate documentation
        /*
        $system_documentation = Application::get('system_documentation');
        if (!empty($system_documentation) && $options['mode'] == 'commit') {
            $model = Factory::model($system_documentation['model']);
        }
        */
        return $result;
    }

    /**
     * Special function for data processing
     *
     * @param array $data
     * @param array $composer_data
     * @param array $composer_dirs
     */
    public static function processDepsArray($data, & $composer_data, & $composer_dirs, $origin_submodule, & $origin_dependencies, $type = 'vendor')
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $k => $v) {
            foreach ($v as $k2 => $v2) {
                if (!is_array($v2) && !empty($v2)) {
                    $name = $k . '/' . $k2;
                    $name = str_replace('__dot__', '.', $name);
                    $composer_data[$name] = $v2;
                    if ($type == 'vendor') {
                        $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/';
                    } else {
                        $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/';
                    }
                    if (!file_exists($dir)) {
                        continue;
                    }
                    $composer_dirs[$name] = $dir;
                    if ($k2 != '__any') {
                        $origin_dependencies[$origin_submodule][$name] = $name;
                    }
                } else {
                    foreach ($v2 as $k3 => $v3) {
                        if (!is_array($v3) && !empty($v3)) {
                            $name = $k . '/' . $k2 . '/' . $k3;
                            if ($type == 'vendor') {
                                $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/' . $k3 . '/';
                            } else {
                                $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/' . $k3 . '/';
                            }
                            if (!file_exists($dir)) {
                                continue;
                            }
                            $composer_dirs[$name] = $dir;
                            if ($k3 != '__any') {
                                $origin_dependencies[$origin_submodule][$name] = $name;
                            }
                        } else {
                            foreach ($v3 as $k4 => $v4) {
                                if (!is_array($v4) && !empty($v4)) {
                                    $name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4;
                                    if ($type == 'vendor') {
                                        $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/' . $k3 . '/' . $k4 . '/';
                                    } else {
                                        $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/';
                                    }
                                    if (!file_exists($dir)) {
                                        continue;
                                    }
                                    $composer_dirs[$name] = $dir;
                                    if ($k4 != '__any') {
                                        $origin_dependencies[$origin_submodule][$name] = $name;
                                    }
                                } else {
                                    foreach ($v4 as $k5 => $v5) {
                                        if (!is_array($v5) && !empty($v5)) {
                                            $name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5;
                                            if ($type == 'vendor') {
                                                $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/';
                                            } else {
                                                $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/';
                                            }
                                            if (!file_exists($dir)) {
                                                continue;
                                            }
                                            $composer_dirs[$name] = $dir;
                                            if ($k5 != '__any') {
                                                $origin_dependencies[$origin_submodule][$name] = $name;
                                            }
                                        } else {
                                            foreach ($v5 as $k6 => $v6) {
                                                if (!is_array($v6) && !empty($v6)) {
                                                    $name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6;
                                                    if ($type == 'vendor') {
                                                        $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/';
                                                    } else {
                                                        $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/';
                                                    }
                                                    if (!file_exists($dir)) {
                                                        continue;
                                                    }
                                                    $composer_dirs[$name] = $dir;
                                                    if ($k6 != '__any') {
                                                        $origin_dependencies[$origin_submodule][$name] = $name;
                                                    }
                                                } else {
                                                    foreach ($v6 as $k7 => $v7) {
                                                        if (!is_array($v7) && !empty($v7)) {
                                                            $name = $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/' . $k7;
                                                            if ($type == 'vendor') {
                                                                $dir = '../libraries/' . $type . '/' . strtolower($k) . '/' . strtolower($k2) . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/' . $k7 . '/';
                                                            } else {
                                                                $dir = '../libraries/' . $type . '/' . $k . '/' . $k2 . '/' . $k3 . '/' . $k4 . '/' . $k5 . '/' . $k6 . '/' . $k7 . '/';
                                                            }
                                                            if (!file_exists($dir)) {
                                                                continue;
                                                            }
                                                            $composer_dirs[$name] = $dir;
                                                            if ($k7 != '__any') {
                                                                $origin_dependencies[$origin_submodule][$name] = $name;
                                                            }
                                                        } else {
                                                            // we skip more than 7 part keys for now
                                                            throw new \Exception('we skip more than 7 part keys for now');
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

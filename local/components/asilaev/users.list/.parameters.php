<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
global $USER_FIELD_MANAGER;
if (!Loader::includeModule('iblock'))
    return;

$arComponentParameters = array(
    "PARAMETERS" => array(
        'STEP_TIME' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('STEP_TIME'),
            'TYPE' => 'INTEGER',
        ),
        'PAGER_TEMPLATE' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PAGER_TEMPLATE'),
            'TYPE' => 'STRING',
        ),
        'PAGE_ELEMENT_COUNT' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('PAGE_ELEMENT_COUNT'),
            'TYPE' => 'INTEGER',
        ),
        "CACHE_TIME" => Array("DEFAULT" => 36000000),
        'AJAX_MODE' => array(),
    ),
);

?>
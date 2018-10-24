<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<? if (!empty($arResult['USERS'])): ?>
    <div class="users">
        <ul>
            <? foreach ($arResult['USERS'] as $user): ?>
                <li>
                    <div class="login"><?= $user['LOGIN'] ?> [<?= $user['ID'] ?>]</div>
                    <div class="name"><?= $user['NAME'] ?></div>
                </li>
            <? endforeach ?>
        </ul>
    </div>
    <div class="navigation">
        <?= $arResult['NAV_STRING'] ?>
    </div>
<? endif ?>
<div class="export-types">
    <div class="export-type">
        <button data-type="csv" class="export"><?=Loc::getMessage('EXPORT_CSV')?></button>
        <div class="export-result-csv"></div>
    </div>
    <div class="export-type">
        <button data-type="xml" class="export"><?=Loc::getMessage('EXPORT_XML')?></button>
        <div class="export-result-xml"></div>
    </div>
</div>
<?
?>

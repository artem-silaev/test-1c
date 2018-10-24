<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\UserTable;

class CUsersList extends \CBitrixComponent implements Controllerable
{
    /**
     * @var
     * Объект посттаничной навигации
     */
    private $nav;
    /**
     * @var
     * Количество пользователей
     */
    private $countUsers;

    /**
     * CUsersList constructor.
     * @param $component
     */
    public function __construct($component)
    {
        parent::__construct($component);
        $this->countUsers = UserTable::getCount();
    }

    /**
     * @param $arParams
     * @return mixed
     */
    public function onPrepareComponentParams($arParams)
    {
        if (!$arParams['PAGE_ELEMENT_COUNT'])
            $arParams['PAGE_ELEMENT_COUNT'] = 10;
        
        $this->nav = new \Bitrix\Main\UI\PageNavigation("nav");
        $this->nav->allowAllRecords(true)
            ->setPageSize($arParams['PAGE_ELEMENT_COUNT'])
            ->initFromUri();
        $arParams["offset"] = $this->nav->getOffset();
        $arParams["limit"] = $this->nav->getLimit();
        $arParams['page_num'] = $this->nav->getCurrentPage();
        if (!$arParams['STEP_TIME']) {
            $arParams['STEP_TIME'] = 3;
        }

        return $arParams;
    }

    /**
     * @return array
     * Новая возможность в битрикс использовать определенные методы в компонентах для AJAX запросов
     * Конкртено тут определяются фильтры, возможность например использовать только PUT запросы
     */
    public function configureActions()
    {
        return [
            'startDataExport' => [ // Ajax-метод
                'prefilters' => [],
            ],
        ];
    }

    /**
     * @param $post
     * @return array
     * Функция экспорта данных пользователей
     * Ajax-методы должны быть с постфиксом Action
     */
    public function startDataExportAction($post)
    {
        $exportType = $post['type'];
        $step = $post['data']['step'];
        $num = $post['data']['num'];
        $exportFileName = $post['data']['filename'];
        $done = false;
        if (!$num)
            $num = 0;
        if (!$step)
            $step = 1;
        switch ($step) {
            case 1:
                /*
                на этом шаге генерируем имя файла, если это публичный функционал, то предусмотреть удаление файлов
                из этой директории спустя какое-то время
                */
                $exportFileName = "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/tmp/export_file_users";
                $exportFileName .= randString(16);
                $exportFileName .= '.' . $exportType;
                $step++;
            case 2:
                //TODO: Лучше конечно создать для каждого типа выгрузки отдельные классы с наследованием нужного интерфейса
                if ($exportType == 'csv') {
                    $csvFile = new \CCSVData();
                    $csvFile->SetDelimiter(';');
                    $csvFile->SetFieldsType('R');
                    $start = time();
                    while ($num < $this->countUsers && (time() - $start) < $this->arParams['STEP_TIME']) {
                        $arUsers = $this->getUsersList($num, 1000, false);
                        foreach ($arUsers as $user) {
                            $csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"] . $exportFileName, [$user['ID'], $user['NAME'], $user['LOGIN']]);
                            $num++;
                        }
                    }
                }
                if ($exportType == 'xml') {
                    $fp = fopen($_SERVER["DOCUMENT_ROOT"] . $exportFileName, "a+");
                    if ($num == 0) {
                        fwrite($fp, "<?xml version='1.0' encoding='utf-8'?>\r\n<users>\r\n");
                    }
                    $start = time();
                    while ($num < $this->countUsers && (time() - $start) < $this->arParams['STEP_TIME']) {
                        $arUsers = $this->getUsersList($num, 1000, false);
                        foreach ($arUsers as $user) {
                            $s = "\t<user>\r\n";
                            $s .= "\t\t<name>{$user['NAME']}</name>\r\n";
                            $s .= "\t\t<login>{$user['LOGIN']}</login>\r\n";
                            $s .= "\t\t<id>{$user['ID']}</id>\r\n";
                            $s .= "\t</user>\r\n";
                            fwrite($fp, $s);
                            $num++;
                        }
                    }
                }
                if ($num >= $this->countUsers) {
                    $done = true;
                    fwrite($fp, "</users>");
                }
                break;
        }

        return [
            'step' => $step,
            'filename' => $exportFileName,
            'num' => $num,
            'done' => $done,
        ];
    }

    /**
     * Получение списка пользователей
     * @param $offset
     * @param $limit
     * @param bool $pageNav
     * @return mixed
     */
    protected function getUsersList($offset, $limit, $pageNav = true)
    {
        $rsUsers = UserTable::getList(
            [
                'select' => ['ID', 'LOGIN', 'NAME'],
                "offset" => $offset,
                "limit" => $limit,
                'count_total' => true,
            ]
        );
        $this->nav->setRecordCount($rsUsers->getCount());
        $arUsers = $rsUsers->fetchAll();
        return $arUsers;
    }

    public function executeComponent()
    {
        if($this->startResultCache($this->arParams['CACHE_TIME'])) {
            $this->arResult['USERS'] = $this->getUsersList($this->arParams["offset"], $this->arParams["limit"]);
            $this->setNav();
            $this->setResultCacheKeys([]);
            $this->includeComponentTemplate();
        }
    }

    /**
     * Построение постраничной навигации
     */
    protected function setNav()
    {
        global $APPLICATION;
        ob_start();

        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            $this->arParams['PAGER_TEMPLATE'],
            array(
                "NAV_OBJECT" => $this->nav,
                "SEF_MODE" => "N",
            ),
            $this,
            array()
        );

        $this->arResult["NAV_STRING"] = ob_get_clean();
    }
}

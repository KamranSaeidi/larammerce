<?php


namespace App\Utils\CMS\Setting\Survey;


use App\Utils\CMS\Enums\DataSourceDriver;
use App\Utils\CMS\Enums\SettingType;
use App\Utils\CMS\Setting\BaseCMSConfigManager;

/**
 * @method static SurveyModel getRecord(string $name = "", ?string $parent_id = null)
 */
class SurveyService extends BaseCMSConfigManager
{
    protected static string $KEY_POSTFIX = 'survey_config';
    protected static int $SETTING_TYPE = SettingType::GLOBAL_SETTING;
    protected static string $DRIVER = DataSourceDriver::DATABASE;

    public static function defaultRecord($name): SurveyModel
    {
        return new SurveyModel();
    }
}

<?php

namespace mackiavelly\juidatetimepicker;

use yii\web\AssetBundle,
    Yii;


class JuiDatetimePickerLanguageAsset extends AssetBundle
{

    public $sourcePath = '@bower/jqueryui-timepicker-addon/dist/i18n';

    public $depends = [
        'mackiavelly\juidatetimepicker\JuiDatetimePickerAsset',
        'mackiavelly\juidatepicker\JuiDatePickerLanguageAsset'
    ];

    public function init()
    {
        $language = Yii::$app->language;
        if ($language != 'en-US') {
            $sourcePath = Yii::getAlias($this->sourcePath);
            $jsFile = 'jquery-ui-timepicker-' . $language . '.js';
            if (is_file($sourcePath . DIRECTORY_SEPARATOR . $jsFile)) {
                $this->js[] = $jsFile;
            } elseif (strlen($language) > 2) {
                $jsFile = 'jquery-ui-timepicker-' . substr($language, 0, 2) . '.js';
                if (is_file($sourcePath . DIRECTORY_SEPARATOR . $jsFile)) {
                    $this->js[] = $jsFile;
                }
            }
        }
        parent::init();
    }
}

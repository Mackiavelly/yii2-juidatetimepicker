<?php

namespace mackiavelly\juidatetimepicker;

use yii\web\AssetBundle;


class JuiDatetimePickerAsset extends AssetBundle
{

    public $sourcePath = '@bower/jqueryui-timepicker-addon/dist';

    public $depends = ['mackiavelly\juidatepicker\JuiDatePickerAsset'];

    public $js = ['jquery-ui-timepicker-addon.min.js'];

    public $css = ['jquery-ui-timepicker-addon.min.css'];
}

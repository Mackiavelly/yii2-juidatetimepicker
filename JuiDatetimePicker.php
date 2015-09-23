<?php

namespace mackiavelly\juidatetimepicker;

use yii\helpers\Html,
    yii\widgets\InputWidget,
    yii\base\InvalidParamException,
    yii\web\JsExpression,
    yii\helpers\Json,
    Yii;


class JuiDatetimePicker extends InputWidget
{

    const SEPARATOR = ' ';

    public $options = ['class' => 'form-control'];

    public $dateFormat = null;

    public $timeFormat = null;

    public $altDateFormat = null;

    public $altTimeFormat = null;

    public $numberOfMonths = 1;

    public $showButtonPanel = true;

    public $clientOptions = [];

    public function init()
    {
        $formatter = Yii::$app->getFormatter();
        if (is_null($this->dateFormat)) {
            $this->dateFormat = $formatter->dateFormat;
            if (is_null($this->dateFormat)) {
                $this->dateFormat = 'medium';
            }
        }
        if (is_null($this->timeFormat)) {
            $this->timeFormat = $formatter->timeFormat;
            if (is_null($this->timeFormat)) {
                $this->timeFormat = 'medium';
            }
        }
        if (is_null($this->altDateFormat)) {
            $this->altDateFormat = $this->dateFormat;
        }
        if (is_null($this->altTimeFormat)) {
            $this->altTimeFormat = $this->timeFormat;
        }
        parent::init();
    }

    public function run()
    {
        $inputId = $this->options['id'];
        $altInputId = $inputId . '-alt';
        $hasModel = $this->hasModel();
        if (array_key_exists('value', $this->options)) {
            $value = $this->options['value'];
        } elseif ($hasModel) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $value = $this->value;
        }
        $altOptions = ['id' => $altInputId];
        if (!is_null($value) && ($value !== '')) {
            $formatter = Yii::$app->getFormatter();
            try {
                $this->options['value'] = $formatter->asDate($value, $this->dateFormat) . self::SEPARATOR . $formatter->asTime($value, $this->timeFormat);
                $altOptions['value'] = $formatter->asDate($value, $this->altDateFormat) . self::SEPARATOR . $formatter->asTime($value, $this->altTimeFormat);
            } catch (InvalidParamException $e) {
                // ignore exception and keep original value if it is not a valid date
            }
        }
        $this->options['name'] = false;
        if ($hasModel) {
            $output = Html::activeTextInput($this->model, $this->attribute, $this->options) . Html::activeHiddenInput($this->model, $this->attribute, $altOptions);
        } else {
            $output = Html::textInput($this->name, $this->value, $this->options) . Html::hiddenInput($this->name, $this->value, $altOptions);
        }
        $this->clientOptions = array_merge([
            'numberOfMonths' => $this->numberOfMonths,
            'showButtonPanel' => $this->showButtonPanel
        ], $this->clientOptions, [
            'dateFormat' => FormatConverter::convertDatePhpOrIcuToJui($this->dateFormat),
            'separator' => self::SEPARATOR,
            'timeFormat' => FormatConverter::convertTimePhpOrIcuToJui($this->timeFormat),
            'altFormat' => FormatConverter::convertDatePhpOrIcuToJui($this->altDateFormat),
            'altSeparator' => self::SEPARATOR,
            'altTimeFormat' => FormatConverter::convertTimePhpOrIcuToJui($this->altTimeFormat),
            'altField' => '#' . $altInputId,
            'altFieldTimeOnly' => false
        ]);
        if (array_key_exists('readonly', $this->options) && $this->options['readonly']) {
            $this->clientOptions['beforeShow'] = new JsExpression('function (input, inst) { return false; }');
        }
        $js = 'jQuery(\'#' . $inputId . '\').datetimepicker(' . Json::htmlEncode($this->clientOptions) . ');';
        if (Yii::$app->getRequest()->getIsAjax()) {
            $output .= Html::script($js);
        } else {
            $view = $this->getView();
            JuiDatetimePickerAsset::register($view);
            JuiDatetimePickerLanguageAsset::register($view);
            $view->registerJs($js);
        }
        return $output;
    }
}

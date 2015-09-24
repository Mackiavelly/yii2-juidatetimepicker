<?php

namespace mackiavelly\juidatetimepicker;

use yii\helpers\Html,
    yii\widgets\InputWidget,
    yii\base\InvalidParamException,
    yii\web\JsExpression,
    yii\helpers\Json,
    Yii;


class JuiTimePicker extends InputWidget
{

    public $options = ['class' => 'form-control'];

    public $timeFormat = null;

    public $altTimeFormat = null;

    public $showButtonPanel = true;

    public $clientOptions = [];

    public $ignoreReadonly = false;

    public $disableAlt = false;

    public function init()
    {
        if (is_null($this->timeFormat)) {
            $this->timeFormat = Yii::$app->getFormatter()->timeFormat;
            if (is_null($this->timeFormat)) {
                $this->timeFormat = 'medium';
            }
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
        $altOptions = [
            'id' => $altInputId,
            'name' => $altInputId,
        ];
        if (!is_null($value) && ($value !== '')) {
            $formatter = Yii::$app->getFormatter();
            try {
                $this->options['value'] = $formatter->asTime($value, $this->timeFormat);
                $altOptions['value'] = $formatter->asTime($value, $this->altTimeFormat);
            } catch (InvalidParamException $e) {
                // ignore exception and keep original value if it is not a valid date
            }
        }
        if ($hasModel) {
            $output = Html::activeTextInput($this->model, $this->attribute, $this->options)
                .((!$this->disableAlt) ? Html::activeHiddenInput($this->model, $this->attribute, $altOptions) : null);
        } else {
            $output = Html::textInput($this->name, $this->value, $this->options)
                .((!$this->disableAlt) ? Html::hiddenInput($this->name, $this->value, $altOptions): null);
        }
        $this->clientOptions = array_merge([
            'showButtonPanel' => $this->showButtonPanel
        ], $this->clientOptions, [
            'timeFormat' => FormatConverter::convertTimePhpOrIcuToJui($this->timeFormat),
            'altTimeFormat' => FormatConverter::convertTimePhpOrIcuToJui($this->altTimeFormat),
            'altField' => '#' . $altInputId
        ]);
        if (!$this->ignoreReadonly && array_key_exists('readonly', $this->options) && $this->options['readonly']) {
            $this->clientOptions['beforeShow'] = new JsExpression('function (input, inst) { return false; }');
        }
        if ($this->disableAlt) {
            foreach ($this->clientOptions as $keyCO => $valueCO) {
                if (strrpos($keyCO, 'alt') !== false) {
                    unset($this->clientOptions[$keyCO]);
                }
            }
        }
        $js = 'jQuery(\'#' . $inputId . '\').timepicker(' . Json::htmlEncode($this->clientOptions) . ');';
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

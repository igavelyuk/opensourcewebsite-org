<?php

declare(strict_types=1);

namespace app\models\WebModels;

use Yii;
use app\models\Resume;
use app\modules\bot\components\helpers\LocationParser;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\web\JsExpression;

class WebResume extends Resume
{
    public function rules(): array
    {
        $mainValidators = parent::rules();

        $webSpecificValidators = [
            [
                'location',
                'required',
                'when' => function ($model) {
                    /** @var self $model */
                    if (!$model->remote_on) {
                        return true;
                    }
                    return false;
                },
                'whenClient' => new JsExpression("
                    function(attribute, value) {
                        return $('#offline-work-checkbox').prop('checked');
                    }
                ")
            ],
            [
                'location',
                function ($attribute) {
                    [$lat, $lon] = (new LocationParser($this->$attribute))->parse();
                    if (!(new LocationLatValidator())->validateLat($lat) ||
                        !(new LocationLonValidator())->validateLon($lon)
                    ) {
                        $this->addError($attribute, Yii::t('app', 'Incorrect Location!'));
                    }
                }
            ],
            [
                'search_radius',
                'required',
                'when' => function ($model) {
                    /** @var self $model */
                    if (!$model->remote_on) {
                        return true;
                    }
                    return false;
                },
                'whenClient' => new JsExpression("
                    function(attribute, value) {
                        return $('#offline-work-checkbox').prop('checked');
                    }
                ")
            ]
        ];

        return array_merge($mainValidators, $webSpecificValidators);
    }
}

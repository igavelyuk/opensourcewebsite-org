<?php

namespace app\models;

use yii\db\ActiveRecord;s

/**
 * This is the model class for table "stellar_croupier".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class StellarCroupierData extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stellar_croupier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key', 'value'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    public static function getLastPagingToken(): ?string
    {
        return self::findOne(['key' => 'last_paging_token'])->value ?? null;
    }

    public static function setLastPagingToken(string $value)
    {
        $model = StellarCroupierData::findOne(['key' => 'last_paging_token']) ?? new StellarCroupierData(['key' => 'last_paging_token']);

        $model->value = $value;
        $model->save();
    }
}

<?php

namespace common\models;

use Yii;

/**
 * 这是表 `product_price` 的模型
 */
class ProductPrice extends \common\components\ARModel
{
    public $data_stop_profit_price;
    public $data_stop_loss_price;
    public $data_hand;
    public $data_point_unit;
    public function rules()
    {
        return [
            [['product_id', 'deposit', 'one_profit'], 'required'],
            [['product_id', 'max_hand', 'hand'], 'integer'],
            [['deposit', 'one_profit', 'fee'], 'number'],
            [['stop_profit_price', 'stop_loss_price', 'point_unit'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'deposit' => '止损金额增幅',
            'one_profit' => '一点盈亏',
            'hand' => '手数',
            'stop_profit_price' => '止盈金额',
            'stop_loss_price' => '止损金额',
            'point_unit' => '计算点位',
            'fee' => '手续费',
            'max_hand' => '最大手数',
        ];
    }

    /****************************** 以下为设置关联模型的方法 ******************************/

    // public function getRelation()
    // {
    //     return $this->hasOne(Class::className(), ['foreign_key' => 'primary_key']);
    // }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'productPrice.id' => $this->id,
                'productPrice.product_id' => $this->product_id,
                'productPrice.deposit' => $this->deposit,
                'productPrice.one_profit' => $this->one_profit,
                'productPrice.fee' => $this->fee,
                'productPrice.max_hand' => $this->max_hand,
            ])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
}

<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;
use craft\helpers\Template;

use supercool\fetch\Fetch;
use supercool\fetch\models\Fetch as FetchModel;
use supercool\fetch\assetbundles\FetchAsset;

class FetchField extends Field
{

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this field type.
     *
     * @return string The display name of this field type.
     */
    public static function displayName(): string
    {
        return Craft::t('fetch', 'Fetch');
    }


    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }


    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }


    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ( ! empty($value) )
        {
            $model = new FetchModel;
            $model->url = $value;

            if ( $model->validate() )
            {
                return $model;
            }
            else
            {
                return $value;
            }
        }

        return $value;
    }


    /**
     * Modifies an element query.
     *
     * This method will be called whenever elements are being searched for that may have this field assigned to them.
     *
     * If the method returns `false`, the query will be stopped before it ever gets a chance to execute.
     *
     * @param ElementQueryInterface $query The element query
     * @param mixed                 $value The value that was set on this field’s corresponding [[ElementCriteriaModel]] param,
     *                                     if any.
     *
     * @return null|false `false` in the event that the method is sure that no elements are going to be found.
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if(is_object($value)) {
            $value = $value->url;
        }

        $value = trim($value);

        if ( ! empty($value) )
        {
          // check if there is a protocol, add if not
          if ( parse_url($value, PHP_URL_SCHEME) === null )
          {
            $value = 'http://' . $value;
          }
        }

        return parent::serializeValue($value, $element);
    }


    /**
     * Returns the field’s input HTML.
     *
     * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        // Register our asset bundle
        $view->registerAssetBundle(FetchAsset::class);

        $name = $this->handle;

        // init the js
        $view->registerJs('new Craft.Fetch('.Json::encode($view->namespaceInputId($name), JSON_UNESCAPED_UNICODE).');');

        return $view->renderTemplate('fetch/field/_input', [
            'name'  => $name,
            'value' => $value
        ]);
    }


}

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
use yii\db\Schema;
use craft\helpers\Json;

use supercool\fetch\Fetch;
use supercool\fetch\models\Fetch as FetchModel;
use supercool\fetch\assetbundles\FetchAsset;

class FetchField extends Field
{

    //  Properties
    // =========================================================================

    /**
     * @var bool  - Whether Flickr links are allowed for this field
     */
    public $allowFlickr = true;

    /**
     * @var bool - Whether Instagram links are allowed for this field
     */
    public $allowInstagram = true;

    /**
     * @var bool - Whether Pintrest links are allowed for this field
     */
    public $allowPintrest = true;

    /**
     * @var bool - Whether Soundcloud links are allowed for this field
     */
    public $allowSoundcloud = true;

    /**
     * @var bool - Whether Twitter links are allowed for this field
     */
    public $allowTwitter = true;

    /**
     * @var bool - Whether vimeo links are allowed for this field
     */
    public $allowVimeo = true;

    /**
     * @var bool - Whether youtube links are allowed for this field
     */
    public $allowYoutube = true;


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
    public function rules(): array
    {
        $rules = parent::rules();

        $booleanFields = [
            'allowFlickr', 'allowInstagram', 'allowPintrest', 'allowSoundcloud',
            'allowTwitter', 'allowVimeo', 'allowYoutube'
        ];

        $rules[] = [$booleanFields, 'boolean'];

        return $rules;
    }

    /**
     * @return string|null
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'fetch/field/_settings.twig',
            [
                'field' => $this,
            ]
        );
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
    public function normalizeValue($value, ElementInterface $element = null): mixed
    {
        if ( ! empty($value) )
        {
            $model = new FetchModel;
            $model->url = $value;
            $model->field = $this;
            $model->validate();

            return $model;
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
     * @param ElementInterface $element The element
     * @param mixed                 $value The value that was set on this field’s corresponding [[ElementCriteriaModel]] param,
     *                                     if any.
     *
     * @return null|false `false` in the event that the method is sure that no elements are going to be found.
     */
    public function serializeValue($value, ElementInterface $element = null): mixed
    {
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
     *
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
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

    /**
     * @return array|string[]
     */
    public function getElementValidationRules(): array
    {
        if(Fetch::$plugin->getSettings()->validateUrlsOnSave) {
            return ['validateUrlValue'];
        }

        return [];
    }

    /**
     * @param ElementInterface $element
     */
    public function validateUrlValue(ElementInterface $element) {
        $fieldValue = $element->getFieldValue($this->handle);

        if ( ! empty($fieldValue) ) {
            $model = new FetchModel;
            $model->url = $fieldValue;
            $model->field = $this;

            if (!$model->validate()) {
                foreach ($model->getErrors('url') as $error) {
                    $element->addError($this->handle, $error);
                }
            }
        }

    }


}

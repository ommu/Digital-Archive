<?php
/**
 * ViewDigitalTag
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2016 Ommu Platform (www.ommu.co)
 * @created date 3 November 2016, 16:06 WIB
 * @link https://github.com/ommu/mod-digital-archive
 *
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 *
 * --------------------------------------------------------------------------------------
 *
 * This is the model class for table "_view_digital_tag".
 *
 * The followings are the available columns in table '_view_digital_tag':
 * @property string $tag_id
 * @property string $digitals
 * @property string $digital_all
 */
class ViewDigitalTag extends CActiveRecord
{
	use UtilityTrait;

	public $defaultColumns = array();
	
	// Variable Search
	public $tag_search;

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ViewDigitalTag the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '_view_digital_tag';
	}

	/**
	 * @return string the primarykey column
	 */
	public function primaryKey()
	{
		return 'tag_id';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tag_id', 'required'),
			array('tag_id', 'length', 'max'=>11),
			array('digitals', 'length', 'max'=>21),
			array('digital_all', 'length', 'max'=>23),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('tag_id, digitals, digital_all,
				tag_search', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'tag' => array(self::BELONGS_TO, 'OmmuTags', 'tag_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'tag_id' => Yii::t('attribute', 'Tag'),
			'digitals' => Yii::t('attribute', 'Digitals'),
			'digital_all' => Yii::t('attribute', 'Digital All'),
			'tag_search' => Yii::t('attribute', 'Tag'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
		
		// Custom Search
		$criteria->with = array(
			'tag' => array(
				'alias' => 'tag',
				'select' => 'body',
			),
		);

		$criteria->compare('t.tag_id', strtolower($this->tag_id), true);
		$criteria->compare('t.digitals', strtolower($this->digitals), true);
		$criteria->compare('t.digital_all', strtolower($this->digital_all), true);
		
		$criteria->compare('tag.body',$this->urlTitle($this->tag_search), true);

		if(!Yii::app()->getRequest()->getParam('ViewDigitalTag_sort'))
			$criteria->order = 't.tag_id DESC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>30,
			),
		));
	}


	/**
	 * Get column for CGrid View
	 */
	public function getGridColumn($columns=null) {
		if($columns !== null) {
			foreach($columns as $val) {
				/*
				if(trim($val) == 'enabled') {
					$this->defaultColumns[] = array(
						'name'  => 'enabled',
						'value' => '$data->enabled == 1? "Ya": "Tidak"',
					);
				}
				*/
				$this->defaultColumns[] = $val;
			}
		} else {
			$this->defaultColumns[] = 'tag_id';
			$this->defaultColumns[] = 'digitals';
			$this->defaultColumns[] = 'digital_all';
		}

		return $this->defaultColumns;
	}

	/**
	 * Set default columns to display
	 */
	protected function afterConstruct() {
		if(count($this->defaultColumns) == 0) {
			$this->defaultColumns[] = array(
				'header' => 'No',
				'value' => '$this->grid->dataProvider->pagination->currentPage*$this->grid->dataProvider->pagination->pageSize + $row+1'
			);
			$this->defaultColumns[] = array(
				'name' => 'tag_search',
				'value' => 'str_replace(\'-\', \' \', $data->tag->body)',
			);
			$this->defaultColumns[] = array(
				'name' => 'digitals',
				'value' => 'CHtml::link($data->digitals, Yii::app()->controller->createUrl("o/tags/manage", array(\'tag\'=>$data->tag_id,\'publish\'=>1)))',
				'type' => 'raw',
			);
			$this->defaultColumns[] = array(
				'name' => 'digital_all',
				'value' => 'CHtml::link($data->digital_all, Yii::app()->controller->createUrl("o/tags/manage", array(\'tag\'=>$data->tag_id)))',
				'type' => 'raw',
			);
		}
		parent::afterConstruct();
	}

	/**
	 * User get information
	 */
	public static function getInfo($id, $column=null)
	{
		if($column != null) {
			$model = self::model()->findByPk($id, array(
				'select' => $column,
			));
			if(count(explode(',', $column)) == 1)
				return $model->$column;
			else
				return $model;
			
		} else {
			$model = self::model()->findByPk($id);
			return $model;
		}
	}

}
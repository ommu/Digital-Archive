<?php
/**
 * DigitalDownloads
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2016 Ommu Platform (www.ommu.co)
 * @created date 8 January 2017, 23:04 WIB
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
 * This is the model class for table "ommu_digital_downloads".
 *
 * The followings are the available columns in table 'ommu_digital_downloads':
 * @property string $download_id
 * @property integer $frontend
 * @property string $file_id
 * @property string $user_id
 * @property integer $downloads
 * @property string $download_date
 * @property string $download_ip
 *
 * The followings are the available model relations:
 * @property DigitalDownloadDetail[] $DigitalDownloadDetails
 * @property DigitalFile $file
 */
class DigitalDownloads extends CActiveRecord
{
	use GridViewTrait;

	public $defaultColumns = array();
	
	// Variable Search
	public $file_search;
	public $user_search;

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DigitalDownloads the static model class
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
		return 'ommu_digital_downloads';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('file_id, user_id', 'required'),
			array('frontend, downloads', 'numerical', 'integerOnly'=>true),
			array('file_id, user_id', 'length', 'max'=>11),
			array('download_ip', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('download_id, frontend, file_id, user_id, downloads, download_date, download_ip,
				file_search, user_search', 'safe', 'on'=>'search'),
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
			'downloads' => array(self::HAS_MANY, 'DigitalDownloadDetail', 'download_id'),
			'file' => array(self::BELONGS_TO, 'DigitalFile', 'file_id'),
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'download_id' => Yii::t('attribute', 'Download'),
			'frontend' => Yii::t('attribute', 'Frontend'),
			'file_id' => Yii::t('attribute', 'File'),
			'user_id' => Yii::t('attribute', 'User'),
			'downloads' => Yii::t('attribute', 'Downloads'),
			'download_date' => Yii::t('attribute', 'Download Date'),
			'download_ip' => Yii::t('attribute', 'Download Ip'),
			'file_search' => Yii::t('attribute', 'File'),
			'user_search' => Yii::t('attribute', 'User'),
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
			'file' => array(
				'alias' => 'file',
				'select' => 'digital_filename',
			),
			'user' => array(
				'alias' => 'user',
				'select' => 'displayname',
			),
		);

		$criteria->compare('t.download_id', strtolower($this->download_id), true);
		$criteria->compare('t.frontend', $this->frontend);
		if(Yii::app()->getRequest()->getParam('file'))
			$criteria->compare('t.file_id', Yii::app()->getRequest()->getParam('file'));
		else
			$criteria->compare('t.file_id', $this->file_id);
		if(Yii::app()->getRequest()->getParam('user'))
			$criteria->compare('t.user_id', Yii::app()->getRequest()->getParam('user'));
		else
			$criteria->compare('t.user_id', $this->user_id);
		$criteria->compare('t.downloads', $this->downloads);
		if($this->download_date != null && !in_array($this->download_date, array('0000-00-00 00:00:00','1970-01-01 00:00:00','0002-12-02 07:07:12','-0001-11-30 00:00:00')))
			$criteria->compare('date(t.download_date)', date('Y-m-d', strtotime($this->download_date)));
		$criteria->compare('t.download_ip', strtolower($this->download_ip), true);
		
		$criteria->compare('file.digital_filename', strtolower($this->file_search), true);
		$criteria->compare('user.displayname', strtolower($this->user_search), true);

		if(!Yii::app()->getRequest()->getParam('DigitalDownloads_sort'))
			$criteria->order = 't.download_id DESC';

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
			//$this->defaultColumns[] = 'download_id';
			$this->defaultColumns[] = 'frontend';
			$this->defaultColumns[] = 'file_id';
			$this->defaultColumns[] = 'user_id';
			$this->defaultColumns[] = 'downloads';
			$this->defaultColumns[] = 'download_date';
			$this->defaultColumns[] = 'download_ip';
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
			if(!Yii::app()->getRequest()->getParam('file')) {
				$this->defaultColumns[] = array(
					'name' => 'file_search',
					'value' => '$data->file->digital_filename',
				);
			}
			if(!Yii::app()->getRequest()->getParam('user')) {
				$this->defaultColumns[] = array(
					'name' => 'user_search',
					'value' => '$data->user_id != 0 ? $data->user->displayname : \'-\'',
				);
			}
			$this->defaultColumns[] = array(
				'name' => 'downloads',
				'value' => 'CHtml::link($data->downloads, Yii::app()->controller->createUrl("o/downloaddetail/manage", array(\'download\'=>$data->download_id)))',
				'htmlOptions' => array(
					'class' => 'center',
				),
				'type' => 'raw',
			);
			$this->defaultColumns[] = array(
				'name' => 'download_date',
				'value' => 'Yii::app()->dateFormatter->formatDateTime($data->download_date, \'medium\', false)',
				'htmlOptions' => array(
					'class' => 'center',
				),
				'filter' => $this->filterDatepicker($this, 'download_date'),
			);
			$this->defaultColumns[] = array(
				'name' => 'download_ip',
				'value' => '$data->download_ip',
				'htmlOptions' => array(
					//'class' => 'center',
				),
			);
			$this->defaultColumns[] = array(
				'name' => 'frontend',
				'value' => '$data->frontend == 1 ? Yii::t(\'phrase\', \'Front-end\') : Yii::t(\'phrase\', \'Back-end\')',
				'htmlOptions' => array(
					'class' => 'center',
				),
				'filter' =>array(
					1=>Yii::t('phrase', 'Front-end'),
					0=>Yii::t('phrase', 'Back-end'),
				),
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

	/**
	 * User get information
	 */
	public static function insertDownload($file_id, $frontend=null)
	{
		$findDownload = self::model()->find(array(
			'select' => 'download_id, frontend, file_id, user_id, downloads',
			'condition' => 'frontend = :frontend AND file_id = :file AND user_id = :user',
			'params' => array(
				':frontend' => $frontend != null ? $frontend : '0',
				':file' => $file_id,
				':user' => !Yii::app()->user->isGuest ? Yii::app()->user->id : '0',
			),
		));
		if($findDownload != null)
			self::model()->updateByPk($findDownload->download_id, array('downloads'=>$findDownload->downloads + 1));
		
		else {
			$download=new DigitalDownloads;
			$download->frontend = $frontend != null ? $frontend : '1';
			$download->file_id = $file_id;
			$download->save();
		}
	}

	/**
	 * before validate attributes
	 */
	protected function beforeValidate() {
		if(parent::beforeValidate()) {		
			if($this->isNewRecord)
				$this->user_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
			
			$this->download_ip = $_SERVER['REMOTE_ADDR'];
		}
		return true;
	}

}
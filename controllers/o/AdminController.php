<?php
/**
 * AdminController
 * @var $this AdminController
 * @var $model Digitals
 * @var $form CActiveForm
 *
 * Reference start
 * TOC :
 *	Index
 *	Suggest
 *	Manage
 *	Add
 *	Edit
 *	View
 *	Upload
 *	Runaction
 *	Delete
 *	Publish
 *	Headline
 *	Choice
 *	Getcover
 *	Insertcover
 *
 *	LoadModel
 *	performAjaxValidation
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2016 Ommu Platform (www.ommu.co)
 * @created date 20 October 2016, 10:14 WIB
 * @link https://github.com/ommu/mod-digital-archive
 *
 *----------------------------------------------------------------------------------------------------------
 */

class AdminController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	//public $layout='//layouts/column2';
	public $defaultAction = 'index';

	/**
	 * Initialize admin page theme
	 */
	public function init() 
	{
		if(!Yii::app()->user->isGuest) {
			if(in_array(Yii::app()->user->level, array(1,2))) {
				$arrThemes = $this->currentTemplate('admin');
				Yii::app()->theme = $arrThemes['folder'];
				$this->layout = $arrThemes['layout'];
			}
		} else
			$this->redirect(Yii::app()->createUrl('site/login'));
	}

	/**
	 * @return array action filters
	 */
	public function filters() 
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			//'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() 
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('suggest'),
				'users'=>array('@'),
				'expression'=>'isset(Yii::app()->user->level)',
				//'expression'=>'isset(Yii::app()->user->level) && (Yii::app()->user->level != 1)',
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('manage','add','edit','view','upload','runaction','delete','publish','headline','choice','getcover','insertcover'),
				'users'=>array('@'),
				'expression'=>'isset(Yii::app()->user->level) && in_array(Yii::app()->user->level, array(1,2))',
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array(),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/**
	 * Lists all models.
	 */
	public function actionIndex() 
	{
		$this->redirect(array('manage'));
	}
	
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionSuggest($limit=10) 
	{
		if(Yii::app()->request->isAjaxRequest) {
			if(Yii::app()->getRequest()->getParam('term')) {
				$criteria = new CDbCriteria;
				$criteria->condition = 'digital_title LIKE :digital_title';
				$criteria->select = "digital_id, digital_title";
				$criteria->limit = $limit;
				$criteria->order = "digital_id ASC";
				$criteria->params = array(':digital_title' => '%' . strtolower(Yii::app()->getRequest()->getParam('term')) . '%');
				$model = Digitals::model()->findAll($criteria);

				if($model) {
					foreach($model as $items)
						$result[] = array('id' => $items->digital_id, 'value' => $items->digital_title);
				}
			}
			echo CJSON::encode($result);
			Yii::app()->end();
			
		} else
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
	}

	/**
	 * Manages all models.
	 */
	public function actionManage() 
	{
		$model=new Digitals('search');
		$model->unsetAttributes();	// clear any default values
		if(isset($_GET['Digitals'])) {
			$model->attributes=$_GET['Digitals'];
		}

		$columns = $model->getGridColumn($this->gridColumnTemp());

		$this->pageTitle = Yii::t('phrase', 'Digitals Manage');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_manage', array(
			'model'=>$model,
			'columns' => $columns,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdd() 
	{
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'digital_global_file_type, cover_limit, cover_file_type, digital_file_type, form_standard, form_custom_field, headline, content_verified',
		));
		$cover_file_type = unserialize($setting->cover_file_type);
		if(empty($cover_file_type))
			$cover_file_type = array();
		$digital_file_type = unserialize($setting->digital_file_type);
		if(empty($digital_file_type))
			$digital_file_type = array();
		$form_custom_field = unserialize($setting->form_custom_field);
		if(empty($form_custom_field))
			$form_custom_field = array();
		if($setting->digital_global_file_type == 0 && ($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('cat_id', $form_custom_field)))) {
			if($model->getErrors())
				$digital_file_type = unserialize($model->category->cat_file_type);
			if(empty($digital_file_type))
				$digital_file_type = array();	
		}
		
		ini_set('max_execution_time', 0);
		ob_start();
		
		$model=new Digitals;
		if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field)))
			$publisher=new DigitalPublisher;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);
		if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field)))
			$this->performAjaxValidation($publisher);

		if(isset($_POST['Digitals'])) {
			$model->attributes=$_POST['Digitals'];
			if($setting->form_standard == 1)
				$model->scenario = 'standardForm';
			
			if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field))) {
				$publisher->attributes=$_POST['DigitalPublisher'];
				
				$publisher->validate();
				
				if($model->validate() && $publisher->validate()) {
					//if($model->publisher_id != '' && $model->publisher_id != 0) {
						$publisherFind = DigitalPublisher::model()->find(array(
							'select' => 'publisher_id, publisher_id',
							'condition' => 'publisher_name = :publisher',
							'params' => array(
								':publisher' => $publisher->publisher_name,
							),
						));
						if($publisherFind != null)
							$model->publisher_id = $publisherFind->publisher_id;
						else {
							if($publisher->save())
								$model->publisher_id = $publisher->publisher_id;
						}
					//}
					if($model->save()) {
						Yii::app()->user->setFlash('success', Yii::t('phrase', 'Digitals success created.'));
						//$this->redirect(array('edit','id'=>$model->digital_id));
						$this->redirect(array('manage'));
					}
				}
				
			} else {
				if($model->save()) {
					Yii::app()->user->setFlash('success', Yii::t('phrase', 'Digitals success created.'));
					//$this->redirect(array('edit','id'=>$model->digital_id));
					$this->redirect(array('manage'));
				}
			}
		}

		ob_end_flush();

		$this->pageTitle = Yii::t('phrase', 'Create Digitals');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_add', array(
			'model'=>$model,
			'publisher'=>$publisher,
			'setting'=>$setting,
			'cover_file_type'=>$cover_file_type,
			'digital_file_type'=>$digital_file_type,
			'form_custom_field'=>$form_custom_field,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionEdit($id) 
	{
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'cover_limit, cover_file_type, form_standard, form_custom_field, headline, content_verified',
		));
		$cover_file_type = unserialize($setting->cover_file_type);
		if(empty($cover_file_type))
			$cover_file_type = array();
		$form_custom_field = unserialize($setting->form_custom_field);
		if(empty($form_custom_field))
			$form_custom_field = array();
		
		$model=$this->loadModel($id);
		if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field))) {
			$publisher = DigitalPublisher::model()->findByPk($model->publisher_id);
			if($model->publisher_id == null)
				$publisher=new DigitalPublisher;			
		}

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);
		if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field)))
			$this->performAjaxValidation($publisher);
			
		if(!$model->getErrors() && ($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field)))) {
			$publisher_id = $model->publisher_id;
			$publisher_name = $publisher->publisher_name;
		}

		if(isset($_POST['Digitals'])) {
			$model->attributes=$_POST['Digitals'];
			if($setting->form_standard == 1)
				$model->scenario = 'standardForm';
			
			if($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('publisher_id', $form_custom_field))) {
				$publisher->attributes=$_POST['DigitalPublisher'];
				$publisher->validate();
				
				if($model->validate() && $publisher->validate()) {
					if($publisher_id != $model->publisher_id || $publisher_name != $publisher->publisher_name) {
						//if($model->publisher_id != '' && $model->publisher_id != 0) {
							$publisherFind = DigitalPublisher::model()->find(array(
								'select' => 'publisher_id, publisher_name',
								'condition' => 'publisher_name = :publisher',
								'params' => array(
									':publisher' => $publisher->publisher_name,
								),
							));
							if($publisherFind != null)
								$model->publisher_id = $publisherFind->publisher_id;
							else {
								$publishers=new DigitalPublisher;
								$publishers->publisher_name = $publisher->publisher_name;
								if($publishers->save())
									$model->publisher_id = $publishers->publisher_id;
							}
						//}
					}
					
					if($model->save()) {
						Yii::app()->user->setFlash('success', Yii::t('phrase', 'Digitals success updated.'));
						//$this->redirect(array('edit','id'=>$model->digital_id));
						$this->redirect(array('manage'));
					}
				}
				
			} else {
				if($model->save()) {
					Yii::app()->user->setFlash('success', Yii::t('phrase', 'Digitals success updated.'));
					//$this->redirect(array('edit','id'=>$model->digital_id));
					$this->redirect(array('manage'));
				}
			}
		}

		$this->pageTitle = Yii::t('phrase', 'Update Digitals');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_edit', array(
			'model'=>$model,
			'publisher'=>$publisher,
			'setting'=>$setting,
			'cover_file_type'=>$cover_file_type,
			'form_custom_field'=>$form_custom_field,
		));
	}
	
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) 
	{
		$model=$this->loadModel($id);

		$this->pageTitle = Yii::t('phrase', 'View Digitals');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_view', array(
			'model'=>$model,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpload($id) 
	{		
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'digital_global_file_type, digital_file_type, form_standard, form_custom_field',
		));
		$digital_file_type = unserialize($setting->digital_file_type);
		if(empty($digital_file_type))
			$digital_file_type = array();
		$form_custom_field = unserialize($setting->form_custom_field);
		if(empty($form_custom_field))
			$form_custom_field = array();
		
		ini_set('max_execution_time', 0);
		ob_start();
		
		$model=$this->loadModel($id);
		if($model != null) {
			if($setting->digital_global_file_type == 0 && ($setting->form_standard == 1 || ($setting->form_standard == 0 && in_array('cat_id', $form_custom_field))))
				$digital_file_type = unserialize($model->category->cat_file_type);
				if(empty($digital_file_type))
					$digital_file_type = array();
			
			// Uncomment the following line if AJAX validation is needed
			$this->performAjaxValidation($model);

			if(isset($_POST['Digitals'])) {
				$model->attributes=$_POST['Digitals'];
				
				if($model->save()) {
					Yii::app()->user->setFlash('success', Yii::t('phrase', 'Digitals success uploaded.'));
					$this->redirect(array('edit','id'=>$model->digital_id));
				}
			}
			
			$this->dialogDetail = true;
			$this->dialogGroundUrl = Yii::app()->controller->createUrl('edit', array('id'=>$model->digital_id));
			$this->dialogWidth = 600;

			$this->pageTitle = Yii::t('phrase', 'Create Digital Categories');
			$this->pageDescription = '';
			$this->pageMeta = '';
			$this->render('admin_upload', array(
				'model'=>$model,
				'digital_file_type'=>$digital_file_type,
			));
			
		} else
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));

		ob_end_flush();
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionRunaction() {
		$id       = $_POST['trash_id'];
		$criteria = null;
		$actions  = Yii::app()->getRequest()->getParam('action');

		if(count($id) > 0) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id', $id);

			if($actions == 'publish') {
				Digitals::model()->updateAll(array(
					'publish' => 1,
				),$criteria);
			} elseif($actions == 'unpublish') {
				Digitals::model()->updateAll(array(
					'publish' => 0,
				),$criteria);
			} elseif($actions == 'trash') {
				Digitals::model()->updateAll(array(
					'publish' => 2,
				),$criteria);
			} elseif($actions == 'delete') {
				Digitals::model()->deleteAll($criteria);
			}
		}

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!Yii::app()->getRequest()->getParam('ajax')) {
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('manage'));
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) 
	{
		$model=$this->loadModel($id);
		
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$model->publish = 2;
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
			
			if($model->update()) {
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-digitals',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', 'Digitals success deleted.').'</strong></div>',
				));
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', 'Digitals Delete.');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_delete');
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionPublish($id) 
	{
		$model=$this->loadModel($id);
		
		$title = $model->publish == 1 ? Yii::t('phrase', 'Unpublish') : Yii::t('phrase', 'Publish');
		$replace = $model->publish == 1 ? 0 : 1;

		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			//change value active or publish
			$model->publish = $replace;
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
			
			if($model->update()) {
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-digitals',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', 'Digitals success updated.').'</strong></div>',
				));
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = $title;
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_publish', array(
			'title'=>$title,
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionHeadline($id) 
	{
		$model=$this->loadModel($id);

		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			//change value active or publish
			$model->headline = 1;
			$model->publish = 1;
			$model->modified_id = !Yii::app()->user->isGuest ? Yii::app()->user->id : null;
			
			if($model->update()) {
				echo CJSON::encode(array(
					'type' => 5,
					'get' => Yii::app()->controller->createUrl('manage'),
					'id' => 'partial-digitals',
					'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', 'Digitals success updated.').'</strong></div>',
				));
			}
			Yii::app()->end();
		}

		$this->dialogDetail = true;
		$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
		$this->dialogWidth = 350;

		$this->pageTitle = Yii::t('phrase', 'Headline');
		$this->pageDescription = '';
		$this->pageMeta = '';
		$this->render('admin_headline');
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionChoice($id) 
	{
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'editor_choice_status, editor_choice_userlevel, editor_choice_limit',
		));
		$editor_choice_userlevel = unserialize($setting->editor_choice_userlevel);
			
		$choiceLimit = ViewDigitalChoiceUser::model()->find(array(
			'select'    => 'user_id, choices',
			'condition' => 'user_id = :user',
			'params'    => array(
				':user' => Yii::app()->user->id,
			),
		));
		
		if($setting->editor_choice_status == 1 && in_array(Yii::app()->user->level, $editor_choice_userlevel) && ($choiceLimit == null || $choiceLimit != null && $choiceLimit->choices <= $setting->editor_choice_limit)) {
			$model=$this->loadModel($id);
		
			$title = $model->editor_choice_input == 1 ? Yii::t('phrase', 'Unchoice') : Yii::t('phrase', 'Choice');
			$replace = $model->editor_choice_input == 1 ? 0 : 1;

			if(Yii::app()->request->isPostRequest) {
				// we only allow deletion via POST request
				$redirect = false;
				
				//change value active or publish
				if($replace == 1) {
					$choice = new DigitalChoice;
					$choice->digital_id = $model->digital_id;
					if($choice->save())
						$redirect = true;
					
				} else if($replace == 0) {
					if(DigitalChoice::model()->deleteAll('digital_id = :digital AND user_id = :user', array(':digital' => $id,':user' => Yii::app()->user->id,)))
						$redirect = true;
				}

				if($redirect == true) {
					echo CJSON::encode(array(
						'type' => 5,
						'get' => Yii::app()->controller->createUrl('manage'),
						'id' => 'partial-digitals',
						'msg' => '<div class="errorSummary success"><strong>'.Yii::t('phrase', 'Digitals success updated.').'</strong></div>',
					));
				}
				Yii::app()->end();
			}
	
			$this->dialogDetail = true;
			$this->dialogGroundUrl = Yii::app()->controller->createUrl('manage');
			$this->dialogWidth = 350;

			$this->pageTitle = $title;
			$this->pageDescription = '';
			$this->pageMeta = '';
			$this->render('admin_choice', array(
				'title'=>$title,
				'model'=>$model,
			));
			
		} else 
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionGetcover($id) 
	{
		$model=$this->loadModel($id);
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'cover_limit',
		));		
		$covers = $model->covers;

		$data = '';
		if(isset($_GET['replace']))
			$data .= $this->renderPartial('_form_cover', array('model'=>$model, 'covers'=>$covers, 'setting'=>$setting), true, false);
		
		if($covers != null) {			
			foreach($covers as $key => $val)
				$data .= $this->renderPartial('_form_view_covers', array('data'=>$val), true, false);
		}
		
		$data .= '';
		$result['data'] = $data;
		echo CJSON::encode($result);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionInsertcover($id) 
	{
		$setting = DigitalSetting::model()->findByPk(1, array(
			'select' => 'cover_limit, cover_file_type, digital_path',
		));
		$cover_file_type = unserialize($setting->cover_file_type);
			
		//if(Yii::app()->request->isAjaxRequest) {
			$model = $this->loadModel($id);
			$pathUnique = Digitals::getUniqueDirectory($model->digital_id, $model->salt, $model->view->md5path);
			if($setting != null)
				$digital_path = $setting->digital_path.'/'.$pathUnique;
			else
				$digital_path = YiiBase::getPathOfAlias('webroot.public.digital').'/'.$pathUnique;
			
			$uploadCover = CUploadedFile::getInstanceByName('namaFile');
			$fileName = time().'_'.$model->digital_id.'_'.$this->urlTitle($model->digital_title).'.'.strtolower($uploadCover->extensionName);
			if($uploadCover->saveAs($digital_path.'/'.$fileName)) {
				$cover = new DigitalCover;
				$photo->cover = $model->covers == null ? '1' : '0';
				$cover->digital_id = $model->digital_id;
				$cover->cover_filename = $fileName;
				if($cover->save()) {
					$url = Yii::app()->controller->createUrl('getcover', array('id'=>$model->digital_id,'replace'=>'true'));
					echo CJSON::encode(array(
						'id' => 'media-render',
						'get' => $url,
					));
				}
			}
			
		//} else
		//	throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) 
	{
		$model = Digitals::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404, Yii::t('phrase', 'The requested page does not exist.'));
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) 
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='digitals-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

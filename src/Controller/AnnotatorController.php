<?php

namespace Efront\Plugin\Annotator\Controller;
use Efront\Controller\UrlhelperController;
use Efront\Controller\BaseController;
use Efront\Controller\GridController;
use Efront\Controller\TemplateController;
use Efront\Model\Form;
use Efront\Model\User;

class AnnotatorController extends BaseController
{
    public $plugin;
    protected function _requestPermissionFor() {
        return [
//             UserType::USER_TYPE_PERMISSION_PLUGINS,
//             [UserType::USER_TYPE_ADMINISTRATOR, UserType::USER_TYPE_PROFESSOR, UserType::USER_TYPE_STUDENT]
        ];
    }
    
    public function index(){
        if(!User::getCurrentUser()->id){
            TemplateController::setMessage("You're not logged in");
            UrlhelperController::redirect(['ctg'=>'index']);
        }
        $smarty = self::getSmartyInstance();
        $this->_base_url = UrlhelperController::url(['ctg' => $this->plugin->name]);
        $smarty->assign("T_PLUGIN_TITLE", $this->plugin->title)->assign("T_PLUGIN_NAME", $this->plugin->name)->assign("T_BASE_URL", $this->_base_url);
        if (!empty($_GET['ajax']) && $_GET['ajax'] == 'myAnnotations') {
            $this->_listAnnotations();
        }
        else if (!empty($_GET['edit_annotation'])){
            $this->_formEditAnnotation($_GET['edit_annotation']);
            
        }
    }
    
    protected function _formEditAnnotation($annotation_id) {
        $annotation=AnnotationsController::getAnnotationWithTags($annotation_id);
        if(User::getCurrentUser()->id!=$annotation['user_ID']){
            TemplateController::setMessage("You do not have permissions to view this page");
            UrlhelperController::redirect(['ctg'=>'Annotator']);
        }
        $annotation=AnnotationsController::getAnnotationWithTags($annotation_id);
        $form = new Form('editAnnotation');        
        $form->addElement('textarea', 'ann-comment', translate("Comment"), 'class = "form-control " placeholder = "'.translate("Annotation comment").'" id="ann-comment"');
        $form->addElement("text", "ann-recipient", translate("Recipient"), 'data-url = "'.UrlhelperController::url(['ctg' => 'list']).'" data-type = "messages" multiple class="ef-autocomplete ef-popover-toggle" data-trigger = "focus" data-toggle="popover" data-content="'.translate("When you select a group, course or branch the message will be delivered to its members").'" id="ann-sharedData" placeholder = "'.translate("Start typing to select recipient").'"');
        $form->addElement("text", "ann-tags", translate("Tags"), 'id="annotation-tags" placeholder = "'.translate("Add tags").'"');
        $form->addElement("text", "ann-color", translate("Color"), 'id="annotation-color" placeholder = "'.translate("Add a color").'"');
        $form->addElement('button', 'ann-update', translate("Update"), 'data-processing-msg="' . translate('Updating...') . '" class = "btn btn-primary" id = "ann-update"');
        $smarty = self::getSmartyInstance();
        $smarty->assign("T_ANNOTATION", $annotation);
        $smarty->assign("T_EDIT_ANNOTATION", $form->toArray());   
    }
    
    protected function _listAnnotations(){
        try {
            if(isset($_GET['sort'])&&isset($_GET['order'])){
                $annotationsSQL= AnnotationsController::getAnnotations($sort=$_GET['sort'],$order=$_GET['order']);
            }
            else{
                $annotationsSQL= AnnotationsController::getAnnotations();
            }
            $annotationTags= AnnotationsController::getTags();
            $annotationIsShared=AnnotationsController::getIsShared();
            $selectedUser='';
            if(isset($_GET['selected_user'])){ //Has selected a user as a filter
                $selectedUser=urldecode($_GET['selected_user']);
            }
            
            $selectedTags=array();
            if(isset($_GET['selected_tags'])){ //Has selected tags as a filter
                $selectedTags= explode(',',($_GET['selected_tags']));
            }
            
            $textFilter='';
            if(isset($_GET['filter'])){ //Has made an input to filter with text
                $textFilter=$_GET['filter'];
            }
            
            foreach ($annotationsSQL as $key => $annotation) {
                $q=array_keys($annotationTags[$annotation['id']]);
                $isTagFiltered=count(array_intersect(array_keys($annotationTags[$annotation['id']]), $selectedTags))==count($selectedTags);
                $isUserFiltered=$selectedUser==''||$annotation['user_ID']==$selectedUser;
                $isTextFiltered=$textFilter==''||strpos($annotation['text'], $textFilter) !== false;
                if($isTagFiltered&&$isUserFiltered&&$isTextFiltered){
                    $entries[$key]['annotation_id'] = $annotation['id'];
                    if($annotation['user_ID']==$selectedUser)
                        $entries[$key]['user'] = '<a href="javascript:void(0);" data-userid="'.$annotation['user_ID'].'" style="background: #4dc34d;" class="badge ef-filter-annotations-by-user selected-user">'.$annotation['formatted_name'].'</a>';
                    else{
                        $entries[$key]['user'] = '<a href="javascript:void(0);" data-userid="'.$annotation['user_ID'].'" style="background: #99ccff;" class="badge ef-filter-annotations-by-user">'.$annotation['formatted_name'].'</a>';
                    }
                    $entries[$key]['color'] = '<svg width="10" height="10"><rect width="10" height="10" style="fill:'.$annotation['color'].';stroke-width:1;stroke:rgb(0,0,0)" /></svg>';
                    $entries[$key]['text'] = $this->formatAnnotationText($annotation['text']);
                    $entries[$key]['page'] = $annotation['page'];
                    $entries[$key]['isShared'] = array_key_exists (  $annotation['id'], $annotationIsShared )?true:false;
                    $entries[$key]['user_id'] = $annotation['user_ID'];
                    $entries[$key]['date'] = formatTimestamp($annotation['created_at'], 'time_nosec');
                    $tags=array();
                    foreach($annotationTags[$annotation['id']] as $annotationTagid=>$annotationTag){
                        if(in_array($annotationTagid,$selectedTags)){
                            array_push($tags,'<a href="javascript:void(0);" data-id="'.$annotationTagid.'" style="background: #4dc34d;" class="badge ef-filter-annotations-by-tags selected-tag">'.$annotationTag.'</a>');
                        }else{
                            array_push($tags,'<a href="javascript:void(0);" data-id="'.$annotationTagid.'" style="background: #99ccff;" class="badge ef-filter-annotations-by-tags">'.$annotationTag.'</a>');
                        }

                    }
                    $entries[$key]['tags'] = $tags;
                }
            }
            $grid = new GridController($entries, sizeof($entries), $_GET['ajax'], false);
            $grid->show();
        } catch (\Exception $e) {
            handleAjaxExceptions($e);
        }
        exit;
    }
    
    protected function formatAnnotationText($text){
        $text = trim(strip_tags($text));
	$text = preg_replace("#(^(&nbsp;|\s)+|(&nbsp;|\s)+$)#", "", $text);
        if(!$text){
            $text = "[Question without text, only media]";
        }
        $truncateLength=70;
        if(mb_strlen($text) > $truncateLength){
            $truncated = mb_substr($text, 0, $truncateLength).'...';
        }
        else{
            $truncated = $text;
        }
        return $truncated;
    }

}
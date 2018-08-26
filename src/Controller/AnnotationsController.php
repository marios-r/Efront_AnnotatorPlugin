<?php

namespace Efront\Plugin\Annotator\Controller;
use Efront\Controller\BaseController;
use Efront\Plugin\Annotator\Model\Annotation;
use Efront\Model\User;
use Efront\Model\Course;
use Efront\Model\TrainingSession\TrainingSessionToUser;
use Efront\Model\TrainingSession;
use Efront\Model\Branch;
use Efront\Model\Group;
use Efront\Model\UserType;
use Efront\Plugin\Annotator\Model\AnnotationSharedToUser;
use Efront\Plugin\Annotator\Model\AnnotationTag;
use Efront\Plugin\Annotator\Model\AnnotationToTag;
use Efront\Model\Database;
use Efront\Model\Configuration;
use Efront\Exception\EfrontException;

class AnnotationsController extends BaseController
{
    public $plugin;
    
    protected function _requestPermissionFor() {
	//return array(UserType::USER_TYPE_PERMISSION_PLUGINS);
    }
    
    public function index(){
        if(!User::getCurrentUser()->id){
            header('Content-Type: application/json');
            echo json_encode(array("success" => false, "message" => "You're not logged in"));
            exit;
        }
        if(isset($_GET['ajax'])&&$_GET['ajax']=='store'){
            $this->store();
        }
        elseif(isset($_GET['ajax'])&&$_GET['ajax']=='search'){
            $this->search();
        }
        elseif(isset($_GET['unshare'])){
            $this->unshare($_GET['unshare']);
        }
        elseif(isset($_GET['update'])){
            $this->update($_GET['update']);
        }
        elseif(isset($_GET['delete'])){
            $this->delete($_GET['delete']);
        }
    }
    
    //Gets all annotations on the page
    protected function search()
    {
        try{
            header('Content-Type: application/json');
            $annotationsSQL=$this->getAnnotations($sort='created_at',$order='desc',$page=parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
            $annotationsTags=$this->getTags(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
            $annotationsShared=$this->getIsShared(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
            $annotations=array();
            foreach ($annotationsSQL as $annotation) {
                $ranges=array('start'=>$annotation['start'],'end'=>$annotation['end'],'startOffset'=>(int)$annotation['startOffset'],'endOffset'=>(int)$annotation['endOffset']);
                $permissions=array('read'=>[],'admin'=>[(int)$annotation['user_ID']],'update'=>[(int)$annotation['user_ID']],'delete'=>[]);
                $user=array('id'=>(int)$annotation['user_ID'],'name'=>$annotation['formatted_name']);
                $isShared=array_key_exists (  $annotation['id'], $annotationsShared )?true:false;
                array_push($annotations,array('id'=>$annotation['id'],'user'=>  $user,'text'=>$annotation['text'],'quote'=>$annotation['quote'],'created_at'=>$annotation['created_at'],'updated_at'=>$annotation['updated_at'],'ranges'=>array($ranges),'permissions'=>$permissions,'tags'=>$annotationsTags[$annotation['id']], 'color'=>$annotation['color'], 'isShared'=>$isShared));
            }
            echo json_encode(array("total" => count($annotations), 'rows' => $annotations));
            exit;
        } catch (Exception $ex) {
            echo json_encode(array("success" => false));
            exit;
        }
    }

    //Saves a new annotation
    protected function store()
    {
        $time=time();
        $data = json_decode(file_get_contents('php://input'), true);
        $annotation = new Annotation();
        $ranges=$data['ranges'];
        $start=$data['ranges'][0]['start'];
        $startOffset=$data['ranges'][0]['startOffset'];
        $end=$data['ranges'][0]['end'];
        $endOffset=$data['ranges'][0]['endOffset'];
        $page=$data['page'];
        $sharedData=isset($data['sharedData'])?$data['sharedData']:null;
        $tags=isset($data['tags'])?$data['tags']:null;
	$color=isset($data['color'])?$data['color']:'#ffff0a';
        $annotation->setFields(array(
		'user_ID'	=> User::getCurrentUser()->id,
		'page'          => $page,
                'text'          => $data['text'],
                'quote'         => $data['quote'],
                'created_at'    => $time,
                'updated_at'    => $time,
		'start' 	=> $start,
		'end'		=> $end,
		'startOffset'	=> $startOffset,
		'endOffset'	=> $endOffset,
		'color'         => $color
	));
        try{
            $annotation->save();
            if(isset($sharedData)&&!empty($sharedData)&&!is_null($sharedData)&&$sharedData!=''){
                $this->share($annotation->id, $sharedData);
            }
            if(isset($tags)&&!empty($tags)&&!is_null($tags)&&is_array($tags)){
                if (0 != count($tags)){
                    $this->setAnnotationTags($annotation->id,$tags);
		}
            }
            header('Content-Type: application/json');
            echo json_encode(array("success" => 'success', 'id' => $annotation->id));
            exit;
        } catch (Exception $ex) {
            handleAjaxExceptions($ex);
        }
    }

    //Updates an annotation
    protected function update($id)
    {   
        header('Content-Type: application/json');
        $time=time();
        $data = json_decode(file_get_contents('php://input'), true);
        try{
            $this->checkId($id);
            $annotation = new Annotation($id);
            $sharedData=isset($data['sharedData'])?$data['sharedData']:null;
            $tags=isset($data['tags'])?$data['tags']:null;
            $color=isset($data['color'])?$data['color']:'#ffff0a';
            $annotation->setFields(array(
                'user_id'	=> User::getCurrentUser()->id,
                'text'          => $data['text'],
                'quote'         => $data['quote'],
                'updated_at'    => $time,
				'color'         => $color
            ));
            $annotation->save();
            if(isset($sharedData)&&!empty($sharedData)&&!is_null($sharedData)&&$sharedData!=''){
                $this->share($annotation->id, $sharedData);
            }
            if(isset($tags)&&!empty($tags)&&!is_null($tags)&&is_array($tags)){
		if (0 != count($tags)){
                    $this->setAnnotationTags($annotation->id,$tags);
                }
            }
            echo json_encode(array("success" => 'success', 'id' => $annotation->id));
            exit;
        }catch (\Exception $e){
            echo json_encode(array("success" => 'fail', 'id' => $annotation->id));
            exit;
        }
    }
    
    //Deletes an annotation
    protected function delete($id)
    {
        header('Content-Type: application/json');
        try {
            $this->checkId($id);
            $annotation = new Annotation($id);
            if($annotation->user_ID==User::getCurrentUser()->id){
                $annotation->delete();
            }else{
				(new AnnotationSharedToUser())->delete($id,User::getCurrentUser()->id);
            }
            echo json_encode(array("success" => true, 'id' => $annotation->id, 'data' => array()));
            exit;
        } catch (\Exception $e) {
            echo json_encode(array("success" => 'fail', 'id' => $annotation->id, 'data' => array()));
             exit;
        }
    }
    
    //Stop sharing an annotation
    protected function unshare($id){
        header('Content-Type: application/json');
        try {
            $this->checkId($id);
            (new AnnotationSharedToUser())->unshare($id);
            echo json_encode(array("success" => true, 'id' => $annotation->id, 'data' => array()));
            exit;
        } catch (\Exception $e) {
            echo json_encode(array("success" => false, 'id' => $annotation->id, 'data' => array()));
            exit;
        }
    }
    
    
    //Gets annotations the user has view permissions for 
    //Returns array of annotations
    public static function getAnnotations($sort='created_at',$order='desc',$page=false){
        try{
            if($page){
                $whereContstraintOwner='(a.user_ID,a.page)=('.User::getCurrentUser()->id.',"'.$page.'")';
                $whereContstraintShared='(sa.user_ID,a.page)=('.User::getCurrentUser()->id.',"'.$page.'")';
            }
            else{
                $whereContstraintOwner='(a.user_ID)=('.User::getCurrentUser()->id.')';
                $whereContstraintShared='(sa.user_ID)=('.User::getCurrentUser()->id.')';
            }
            $annotations = Database::getInstance()->execute (
                'SELECT a.*,u.formatted_name FROM '.Annotation::DATABASE_TABLE.' a JOIN '.User::DATABASE_TABLE.' u ON a.user_ID=u.id WHERE '.$whereContstraintOwner.' 
                UNION 
                SELECT a.*,u.formatted_name FROM '.Annotation::DATABASE_TABLE.' a JOIN '.AnnotationSharedToUser::DATABASE_TABLE.' sa ON a.id=sa.annotation_ID JOIN '.User::DATABASE_TABLE.' u ON a.user_ID=u.id WHERE '.$whereContstraintShared.' 
                ORDER BY '.$sort.' '.$order
            );
            return $annotations;
        }catch(\Exception $e){
            return false;
        } 
    }
    
    //Returns annotation
    public static function getAnnotationWithTags($annotation_id){
        $annotation = new Annotation($annotation_id); 
        $annotationTags=array();
        $annotationTagsSQL = Database::getInstance()->execute (
            'SELECT at.tag from '.AnnotationTag::DATABASE_TABLE.' at WHERE at.annotation_ID='.$annotation_id
        );
        foreach ($annotationTagsSQL as $tag) {
            array_push($annotationTags,$tag['tag']);
        }
        $annotation=array_merge($annotation->getFields(),  array('tags'=>$annotationTags));
        return $annotation;
    }
    
    //Gets tags for all annotations the user has view permissions for
    //Returns a key valye array where the key is the id of the annotation and the value is the tags of the annotation
    public static function getTags($page=false){
        try{
            if($page){
                $whereContstraintOwner='(a.user_ID,a.page)=('.User::getCurrentUser()->id.',"'.$page.'")';
                $whereContstraintShared='(sa.user_ID,a.page)=('.User::getCurrentUser()->id.',"'.$page.'")';
            }
            else{
                $whereContstraintOwner='(a.user_ID)=('.User::getCurrentUser()->id.')';
                $whereContstraintShared='(sa.user_ID)=('.User::getCurrentUser()->id.')';
            }
            $annotationTags=array();
            $annotationTagsSQL = Database::getInstance()->execute ('
                SELECT at.id, att.annotation_ID,at.tagname FROM '.Annotation::DATABASE_TABLE.' a JOIN '.User::DATABASE_TABLE.' u ON a.user_ID=u.id  JOIN '.AnnotationToTag::DATABASE_TABLE.' att ON att.annotation_ID=a.id JOIN '.AnnotationTag::DATABASE_TABLE.' at ON att.tag_ID=at.id WHERE '.$whereContstraintOwner.' 
                UNION 
                SELECT at.id, att.annotation_ID,at.tagname FROM '.Annotation::DATABASE_TABLE.' a JOIN '.AnnotationSharedToUser::DATABASE_TABLE.' sa ON a.id=sa.annotation_ID JOIN '.User::DATABASE_TABLE.' u ON a.user_ID=u.id JOIN '.AnnotationToTag::DATABASE_TABLE.' att ON att.annotation_ID=a.id JOIN '.AnnotationTag::DATABASE_TABLE.' at ON att.tag_ID=at.id WHERE '.$whereContstraintShared.' 
            ');
        
            foreach ($annotationTagsSQL as $row) {
                if(!is_array($annotationTags[$row['annotation_ID']])){
                    if($page){
                        $annotationTags[$row['annotation_ID']]=array($row['tagname']);
                    }else{
                        $annotationTags[$row['annotation_ID']]=array($row['id']=>$row['tagname']);
                    }
                }
                else{
                    if($page){
                        $annotationTags[$row['annotation_ID']][]=$row['tagname'];
                    }else{
                        $annotationTags[$row['annotation_ID']][$row['id']]=$row['tagname'];
                    }
                }
            }
            
            return $annotationTags;
        }catch(\Exception $e){
            return false;
        } 
    }
    
	//returns tags
	//gets string of coma separated ids
    public static function getTagsArrayByAnnotationTagIDs($ids){
        try{
            $tagsSQL=(new AnnotationTag)->getTagsByAnnotationTagIDs($ids);
            $tags=array();
            foreach ($tagsSQL as $row) {
                array_push($tags, $row['tag']);
            }
            return array_unique($tags);
        }catch(\Exception $e){
            return [];
        } 
    }
    
    public static function getIsShared($page=false){
        try{
            if($page){
                $whereContstraintOwner='(a.user_ID,a.page)=('.User::getCurrentUser()->id.',"'.$page.'")';
            }
            else{
                $whereContstraintOwner='(a.user_ID)=('.User::getCurrentUser()->id.')';
            }
            $isShared=array();
            $annotationSharedSQL = Database::getInstance()->execute ('
                SELECT at.annotation_ID,at.user_ID FROM '.Annotation::DATABASE_TABLE.' a JOIN '.User::DATABASE_TABLE.' u ON a.user_ID=u.id  JOIN '. AnnotationSharedToUser::DATABASE_TABLE.' at ON a.id=at.annotation_ID WHERE '.$whereContstraintOwner.' 
            ');
        
            foreach ($annotationSharedSQL as $row) {
                foreach ($row as $k=>$v){
                    if($k!='annotation_ID'){
                            $isShared[$row['annotation_ID']]=true;
                    }
                }
            }
            
            return $isShared;
        }catch(\Exception $e){
            return false;
        } 
    }
    
    
    //SHARE ANNOTATIONS START
    
    //Shares annotation with individual user
    protected function shareWithIndividual($annotation_ID,$user_id) {
        $this->checkId($user_id);
        $sharedAnnotation = new AnnotationSharedToUser();
        $sharedAnnotation->setFields(['user_ID' => $user_id, 'annotation_ID' => $annotation_ID]);
        try{
            $sharedAnnotation->save();
        }catch(\Exception $e){

        }
    }
    
    //Shares annotation with users. Individualizing first the sharedData (getIndividuals) 
    protected function share($annotation_ID,$sharedData){
        $users = [];
        foreach (explode(",", $sharedData) as $recipient) {
            $users = array_merge($users, $this->getIndividuals($recipient));
        }
        foreach ($users as $user_id) {
            $this->shareWithIndividual($annotation_ID,$user_id);
        }
    }
    
    //Individualizes the sharedData
    protected function getIndividuals($recipient){
        list($type, $id) = explode("-", $recipient);
        $this->checkId($id);
        $me = User::getCurrentUser();
        $recipients = [];
        switch ((string)$type) {
            case 'users':
                if ($me->isSupervisor()) {
                    $result = Database::getInstance()->getTableData (
                        User::DATABASE_TABLE,
                        'id',
                        'archive = 0 AND active = 1 AND (id IN (' . implode(',', array_keys($me->getSupervisors())) . ') OR id IN (' . implode(',', array_keys(User::getAdministrators())) . ') OR branches_ID IN (' . implode(',', $me->getSupervisedBranches()) . '))'
                    );
                    $valid_recipients = [];
                    foreach ($result as $uid) {
                        $valid_recipients[] = $uid['id'];
                    }
                } else if ($me->isAdministator()) {
                    $valid_recipients = array_keys(idToKey(User::getAll(['condition'=>'archive=0 AND active=1'], ['id'])));
                } else if ($me->isProfessor()) {
                    if ($me->branches_ID) {
                        $valid_recipients = array_merge(array_keys(User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getStudents()));
                    } else {
                        $valid_recipients = array_merge(array_keys(idToKey(User::getAll(['condition'=>'archive=0 AND active=1 AND user_types_ID IN ('.implode(",", array_keys(UserType::getAdministratorTypes())).')'], ['id']))),array_keys($me->getStudents()));
                    }
                } else {
                    $valid_recipients = array_merge(array_keys(User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getProfessors()));
                    switch (Configuration::getValue(Configuration::CONFIGURATION_USER_SEND_MESSAGES)) {
                        case Configuration::CONFIGURATION_USER_SEND_MESSAGES_DEFAULT:
                            $valid_recipients = array_merge(array_keys(User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getProfessors()));
                            break;

                        case Configuration::CONFIGURATION_USER_SEND_MESSAGES_BRANCH:
                            if ($me->branches_ID) {
                                $branch = new Branch($me->branches_ID);
                                $valid_recipients = array_merge(array_keys($branch->getBranchUsers()), (User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getProfessors()));
                            } else {
                                $valid_recipients = array_merge(array_keys(User::getPairs(["condition"=>"user_types_ID in (" . implode(",", array_keys(UserType::getStudentTypes())) . ") "], ["id", "login"])),array_keys(User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getProfessors()));
                            }
                            break;

                        case Configuration::CONFIGURATION_USER_SEND_MESSAGES_ALL:
                            $valid_recipients = array_merge(array_keys(User::getPairs(["condition"=>"user_types_ID in (" . implode(",", array_keys(UserType::getStudentTypes())) . ") "], ["id", "login"])),array_keys(User::getAdministrators()), array_keys($me->getSupervisors()),array_keys($me->getProfessors()));
                            break;

                        default:
                            break;
                    }
                }

                if (!in_array($id, $valid_recipients)) {
                  if ($id == User::getCurrentUser()->id) {
                    throw new EfrontException("You cannot send a message to yourself!");
                  } else {
                      $user = new User($id);
                    throw new EfrontException("You cannot send a message to user ".$user->formatted_name);
                  }
                }
                $recipients[] = $id;
                break;
            case 'courses':
                if (!$me->isStudent()) {		//a student can't send a message to a course's members
                    $course = new Course($id);
                    $me->canRead($course);
                    if ($me->isSupervisor()) {
                        $condition = ['condition' => "u.archive = 0 AND u.active=1 AND u.branches_ID in (".implode(",", $me->getSupervisedBranches()).")"];
                    } else if ($me->isProfessor()) {	//a professor can send to any student of his course, provided that he belongs to the same branch tree
                        $condition = ['condition' => "u.archive = 0 AND u.active=1"];
                        if ($me->branches_ID) {
                            $branch = new Branch($me->branches_ID);
                            $items = array_merge(array_keys($branch->getAncestors()), array_keys($branch->getChildren()));
                            $condition['condition'] .= " AND u.branches_ID in (".implode(",", $items).")";
                        }	//Note: If the professor does not belong to a branch, he can send to any user attending his course
                    } else {
                        $condition = ['condition' => "u.archive = 0 AND u.active=1"];
                    }

                    foreach($course->getUsers($condition) as $v) {
                        $recipients[] = $v['id'];
                    }
                }
                break;
            case 'training_sessions':
                if (!$me->isStudent()) {
                    $trainingSession = new TrainingSession($id);
                    $me->canRead($trainingSession);

                    $users = TrainingSessionToUser::getAll(['condition' => 'sessions_ID = ' . $id]);

                    foreach($users as $user) {
                        $recipients[] = $user['users_ID'];
                    }
                }

                break;
            case 'branches':
                $branch = new Branch($id);
                $me->canRead($branch);
                if ($me->isAdministator()) {	//only admins and supervisors can send to branches
                    foreach ($branch->getBranchUsers(['condition' => 'u.active=1']) as $value) {
                        $recipients[] = $value['id'];
                    }
                }
                break;
            case 'groups':
                $group = new Group($id);
                $me->canRead($group);
                $conditions = [];

                if ($me->isSupervisor()) {
                    $conditions = array('condition' => 'u.active=1 AND branches_ID is null OR branches_ID in ('.implode(",", User::getCurrentUser()->getSupervisedBranches()).')');
                } elseif ($me->isAdministator()) {
                  $conditions = ['condition' => 'u.active=1'];
                }

                foreach ($group->getGroupUsers($conditions) as $value) {
                    $recipients[] = $value['id'];
                }

                break;
            default:
                break;
        }

        return $recipients;
    }
    //SHARE ANNOTATIONS END
    
    //Sets tags for annotation
    protected function setAnnotationTags($annotation_id,$tags){
        if(is_array($tags)){
            $annotationTag=new AnnotationTag();
            $existingTags=$annotationTag->getTagsByTagName($tags);// We want to insert tags that do not already exist
            $newTags=$tags;
            foreach($existingTags as $existingTag){
                if (($key = array_search($existingTag['tagname'], $newTags)) !== false) {
                    unset($newTags[$key]);
                }
            }
            if(!empty($newTags)){
                $annotationTag->insertMultipleAnnotationTags($newTags);
                $tagsToAddToAnnotation=$annotationTag->getTagsByTagName($tags);
                 $this->setAnnotationToTags($annotation_id, $tagsToAddToAnnotation);
            }else{
                $this->setAnnotationToTags($annotation_id, $existingTags);
            }
        }
    }
    
    protected function setAnnotationToTags($annotation_ID,$tags){
            $tagIds=array();
            foreach($tags as $annotationTagSQL){
                $tagIds[]=$annotationTagSQL['id'];
            }
            $annotationToTag=new AnnotationToTag();
            $annotationToTag->deleteTagsOfAnnotation($annotation_ID);
            $annotationToTag->insertMultipleAnnotationToTags($annotation_ID,$tagIds);
    }

}

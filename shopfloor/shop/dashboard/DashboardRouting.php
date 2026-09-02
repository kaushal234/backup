<?php

/**
 * Class DashboardRouting
 */
class DashboardRouting
{
    private $post = [];

    private $get = [];

    public function __construct($post,$get){

        if(is_array($post)){
           foreach($post as $key => $value){
               $this->post[$key] = TldDatabase::escape($value);
           }
        }
        if(is_array($get)){
            $this->get=$get;
        }
    }

    /**
     * @param $Url string
     * @return string
     */
    public function getRoute()
    {
        $route = implode('/',$this->get['m']);
        switch($route){
            case 'dashboard/selection':
                return "selection";
                break;
            case 'dashboard/displayResearch':
                return "foreman_report_result";
                break;
            case 'dashboard/list':
                return "list";
                break;
            default :
                return "ERROR404";
                break;
        }
    }

    public function getPost(){
        return $this->post;
    }

//    private function controlFormValues(array $postedData){
//        //check ER fields
//        if($postedData['ERfrom']){
//            $postedData['ERfrom'] =
//        }
//        if($postedData['ERTo']){
//
//        }
//    }
}

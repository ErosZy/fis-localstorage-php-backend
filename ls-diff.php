<?php
/**
 * Created by PhpStorm.
 * User: wangcheng
 * Date: 14-9-9
 * Time: 23:44
 */

define("TYPE_LIST", "list");
define("TYPE_DATA", "data");

define("DS", DIRECTORY_SEPARATOR);

$req_type = $_GET["type"]; //请求类型
$req_post = $_POST;

$res_obj = array(
    'data' => array(),
    'errno' => 0,
    'msg' => ""
);

/**
 * todo : 支持gzip
 * todo : 设置header
 */
if($req_type == TYPE_LIST){
    $pkgStr = $req_post["pids"];
    $pids = explode(",", $pkgStr);
    $result_list = array();
    foreach($pids as $pid){
        $result_list[$pid] = StaticReader::getList($pid);
    }
    $res_obj["data"] = $result_list;
    echo json_encode($res_obj);
}else if($req_type == TYPE_DATA){
    $result_data = array();
    foreach($req_post as $pid=>$hashStr){
        $package_data = array();
        if(strlen($hashStr) == 0){
            $hashs = null;
        }else{
            $hashs = explode(",", $hashStr);
        }
        $tmp_list = StaticReader::getList($pid);
        $package_data["data"] = StaticReader::getData($pid, $hashs);
        $package_data["type"] = $tmp_list["type"];
        $package_data["hash"] = $tmp_list["hash"];
        $result_data[$pid] = $package_data;
    }
    $res_obj["data"] = $result_data;
    echo json_encode($res_obj);
}else{
    $res_obj["errno"] = 1;
    $res_obj["msg"] = "Wrong request type!";
    echo json_encode($res_obj);
}


class StaticReader {

    private static $LIST_EXT = ".lslist.json";
    private static $DATA_EXT = ".lsdata.json";
    private static $CONNECTOR = "_";

    private static $module_list = array();
    private static $module_data = array();

    private static function getDataDir(){
        return dirname(__FILE__);
    }


    public static function getList($pid){
        $tokens = explode(self::$CONNECTOR, $pid);
        $module = $tokens[0];
        if(isset(self::$module_list[$module])){
            if(self::$module_list[$module][$pid]){
                return self::$module_list[$module][$pid];
            }else{
                return array();
            }
        }else{
            //todo 正式版本修改目录
            $listFile = self::getDataDir() . DS . $module . self::$LIST_EXT;
            //$listFile = self::getDataDir() . DS . "test" . DS . "data" . DS . $module . self::$LIST_EXT;
            if(file_exists($listFile)){
                $listObj = json_decode(file_get_contents($listFile), true);
                self::$module_list[$module] = $listObj;
                if($listObj[$pid]){
                    return $listObj[$pid];
                }else{
                    return array();
                }
            }else{
                trigger_error($listFile . "does not exist", E_USER_NOTICE);
                return array();
            }
        }
    }

    public static function getData($pid, $hashs=null){
        $tokens = explode(self::$CONNECTOR, $pid);
        $module = $tokens[0];
        if(isset(self::$module_data[$module])){
            if(isset(self::$module_data[$module][$pid])){
                $pkg_data = self::$module_data[$module][$pid];
            }else{
                $pkg_data = array();
            }
        }else{
            //todo 正式版本修改目录
            //$listFile = self::getDataDir() . DS . $pid . self::$LIST_EXT;
            $dataFile = self::getDataDir() . DS . "test" . DS . "data" . DS . $module . self::$DATA_EXT;
            if(file_exists($dataFile)){
                self::$module_data[$module] = json_decode(file_get_contents($dataFile), true);
                if(isset(self::$module_data[$module][$pid])){
                    $pkg_data = self::$module_data[$module][$pid];
                }else{
                    $pkg_data = array();
                }
            }else{
                trigger_error($dataFile . "does not exist", E_USER_ERROR);
                $pkg_data = array();
            }
        }

        $newData = array();
        if(isset($hashs)){
            foreach($hashs as $hash){
                if(isset($pkg_data[$hash])){
                    $newData[] = array(
                        $hash => $pkg_data[$hash]
                    );
                }
            }
        }else{
            foreach($pkg_data as $hash => $content){
                $newData[] = array(
                    $hash => $content
                );
            }
        }
        return $newData;
    }
}
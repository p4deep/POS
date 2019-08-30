<?php

namespace App\Utils;
use \Module;

class ModuleUtil extends Util
{
    public function getModulesViewData($function_name, $params, $modules = []){

        $data = [];

        if(empty($modules)){
            $modules = Module::all();
        }

        foreach ($modules as $value) {
            $class = '\Modules\\' . $value . '\Http\Controllers\\DataController';
            $obj = new $class;
            $data[strtolower($value)] = $obj->$function_name($params);
        }

        return (object)$data;
    }

    /**
     * Calls modules
     *
     * @return null
     */
    public function storeModulesData($function_name, $params){
        $allEnabled = $this->allModulesEnabled();

        foreach ($params as $module => $value) {
            if(in_array($module, $allEnabled)){
                $class = '\Modules\\' . $module . '\Http\Controllers\\DataController';
                $obj = new $class;
                $obj->$function_name($value);
            }
        }
    }

    /**
     * Calls module function
     *
     * @return mixed
     */
    public function callModuleFunction($module, $function_name, $params = null){

        $class = '\Modules\\' . $module . '\Http\Controllers\\DataController';
        $obj = new $class;

        if(is_null($params)){
            return $obj->$function_name();
        } else {
            return $obj->$function_name($params);
        }
    }
}

?>
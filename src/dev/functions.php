<?php

function base_path($path = null)
{
    return BASEDIR . ($path ? '/' .ltrim($path, '/'):'');
}


function getFields($table = null, $inline = false){
    $table = schema($table);
    if($inline){
        return "['".implode("', '", $table->getColumns())."']";
    }

    return $table;

}
function getColumns($table = null){
    $table = schema($table);
    
    return $table->getColumns();

}

function getResource($table = null){
    $fillable = schema($table)->getColumns();

    $a = "";
    foreach ($fillable as $field) {
        $a.= "\n            '$field' => \$this->$field,";
        // echo "\n$field:";
    }
    $a .= "\n";
    return $a;
}

function getRules($table = null){
    $fillable = schema($table)->getData();

    $a = "";
    foreach ($fillable as $field => $type) {
        $a.= "\n            '$field' => '$type',";
        // echo "\n$field:";
    }
    $a .= "\n";
    return $a;
}
function getMessages($table = null){
    $fillable = schema($table)->getData();

    $a = "";
    foreach ($fillable as $field => $type) {
        $a.= "\n            '$field.$type' => '$field Không hợp lệ',";
        // echo "\n$field:";
    }
    $a .= "\n";
    return $a;
}


function getProperties($table = null){
    $fillable = schema($table)->getData();

    $a = "";
    foreach ($fillable as $field => $type) {
        $a.= "\n * @property $type \$$field";
        // echo "\n$field:";
    }
    $a .= "\n";
    return $a;
}


function defaultJson($table = null){
    $fields = schema($table)->getData();

    $a = [];
    foreach ($fields as $field => $type) {
        $a[$field] = [
            'type' => $type == 'boolean'?'switch':($type == 'integer' || $type == 'float'?'number':'text'),
            'label' => '',
            'placeholder' => 'nhập '
        ];
    }
    return $a;
}


function show($data)
{
    if(is_array($data)) $data = json_encode($data);
    echo $data;
}

function show_list($params, ...$args){
    $t = count($args);
    if(isset($args[0])){
        switch($args[0]){
            case 'controller':
                case 'ctl':
                    if($t > 1){
                        $l = strtolower($args[1]);
                        if($l == 'methods' || $l == 'method' || $l == 'mt'){
                            echo '
void save(Request $request) - lưu dữ liệu sau khi validate
                            ';
                        }
                    }
                    break;

        }
    }
}
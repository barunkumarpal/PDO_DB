<?php
require_once('config.php');

if(!defined('BASEPATH')){
    exit('Direct access not allowed!');
}


class db{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pwd = DB_PWD;
    private $db_name = DB_NAME;

    public $link;
    public $error;

  
    public function connect(){
        $dsn = "mysql:host=".$this->host.";dbname=".$this->db_name;
        $pdo = new PDO($dsn, $this->user, $this->pwd);

        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    
    }
    function fetch($table_name, $select_array=[], $or = 0){ 
        $response['success'] = 0;
        
        $select = '';
        foreach($select_array as $key=>$value){
            
            if(empty($select) && $select == ''){
                $select = "$key=:$key";
            }else{
                if(isset($or) && $or == 1){
                    $select .= " OR $key=:$key";
                }else{
                    $select .= " AND $key=:$key";
                }
            }
        }
        $sql ="SELECT * FROM `$table_name` WHERE $select";       

        $result = $this->connect()->prepare($sql); 
        
        foreach($select_array as $key => &$value){  

            $value = $this->cleanData($value);
             
            $result->bindParam($key, $value);    
                    
        }          

        $result->execute();
        $results = $result -> fetch();
        $row_count = $result->rowCount();                      

        if($row_count > 0)
        {
            $results['success'] = 1;                

        }else{
            $results['success'] = 0;                
        }

        return $results;
    }
    function fetch_all($table_name, $where_array = [], $not_in = [], $orderby = 0, $limit = 0, $or = 0, $like = 0){ 
        $response['success'] = 0;
        $rule = '';

        if(isset($where_array) && count($where_array) >= 1 ){

            $where = '';
            foreach($where_array as $key=>$value){
                
                // if(empty($where) && $where == ''){
                //     $where = "$key=:$key";
                // }else{
                //     if($or == 1){
                //         $where .= " OR $key=:$key";
                //     }else{
                //         $where .= " AND $key=:$key";
                //     }
                // }

                if(empty($where) && $where == ''){
                    if($like == 1){
                        $where = "$key LIKE :%$key%";
                    }else{
                        $where = "$key=:$key";
                    }
                }else{
                    if($or == 1){
                        if($like == 1){
                            $where .= " OR $key LIKE :%$key%";
                        }else{
                            $where .= " OR $key =:$key";
                        }
                        
                    }else{
                        if($like == 1){
                            $where .= " AND $key LIKE :%$key%";
                        }else{
                            $where .= " AND $key=:$key";
                        }
                        
                    }
                    // if($or == 1){
                    //     $where .= " OR $key=:$key";
                    // }else{
                    //     $where .= " AND $key=:$key";
                    // }
                }
            }


            $rule ="WHERE $where"; 
        }
        if(isset($not_in) && !empty($not_in) && count($not_in) >= 1 ){
            
            $not = '';
            foreach($not_in as $key=>$value){
                
                if(empty($not) && $not == ''){
                    $not = " AND NOT $key=:$key";
                }else{
                    $not .= " AND NOT $key=:$key";
                }
            }   
            $rule .=  $not;               
        }

        if( isset($orderby) && $orderby !== 0 ){
            if(!empty($rule)){
                $rule .=" ORDER BY $orderby";
            }else{
                $rule ="ORDER BY $orderby";
            }
        }

        if(isset($limit) && $limit !== 0 ){
            if(!empty($rule)){
                $rule .=" LIMIT $limit";
            }else{
                $rule ="LIMIT $limit";
            }            
        }        
        
        $sql = "SELECT * FROM `$table_name` $rule";


        $result = $this->connect()->prepare($sql); 
        
        if(isset($where_array) && count($where_array) >= 1){
            
            foreach($where_array as $key => &$value){  
                $value = $this->cleanData($value);
             
                $result->bindParam($key, $value);    
                        
            }             
        }

        if(isset($not_in) && !empty($not_in) && count($not_in) >= 1 ){            
           
            foreach($not_in as $key => &$value){
                $value = $this->cleanData($value);             
                $result->bindParam($key, $value); 
            }                    
        }
        // $result->debugDumpParams();
        $result->execute();

        $row_count = $result->rowCount();

        if($row_count > 0){
            $results = $result -> fetchAll();    
        }else{
            $results = 0;                
        }

        return $results;
    }
    
    function insert($table_name, $values_array = []){   

        $response = 0;       

        foreach($values_array as $key=>$value){
            if(empty($select)){
                $select = "$key";
                $values = ":$key";                
            }else{
                $select .= ",$key";
                $values .= ",:$key";                
            }
            // if(empty($select)){
            //     $select = "$key";
            //     $values = "$value";                
            // }else{
            //     $select .= ",$key";
            //     $values .= ",$value";                
            // }
        }      
        
        $sql ="INSERT INTO `$table_name`($select) VALUES ($values)";    //print_r($sql); exit;
   
        $result = $this->connect()->prepare($sql);

        foreach($values_array as $key => &$value){ 
            // $value = $this->cleanData($value);
             
            $result->bindParam($key, $value);    
                    
        }  
        //$result->debugDumpParams();
        $execute = $result->execute();              
    
        if($execute==0){            
              
            return $response = 0;           
        }else{
             
            return $response = 1;         
        }
    
        return $response;
    }    
      

    function update($table_name, $values = [], $where_array = []){

        foreach($values as $key=>$value){
            if(empty($data)){                
                $data = '`'.$key.'`'.'='.":".$key;
            }else{                
                $data .= ','.'`'.$key.'`'.'='.":".$key;
            }           
            
        }     
          

        $where = '';
        foreach($where_array as $key=>$value){
            
            if(empty($where) && $where == ''){
                $where = "$key=:$key";
            }else{
                $where .= " AND $key=:$key";
            }
        }
        
        // foreach($values as $key=>$value){
        //     if(empty($data)){                
        //         $data = '`'.$key.'`'.'='.$value;
        //     }else{                
        //         $data .= ','.'`'.$key.'`'.'='.$value;
        //     }           
            
        // } 
        // $where = '';
        // foreach($where_array as $key=>$value){
            
        //     if(empty($where) && $where == ''){
        //         $where = "$key=$value";
        //     }else{
        //         $where .= " AND $key=$value";
        //     }
        // } 

        $sql ="UPDATE `$table_name` SET $data WHERE $where";    //print_r($sql); exit;

        $result = $this->connect()->prepare($sql);

        foreach($values as $key => &$value){                     
            $result->bindParam($key, $value);                     
        } 
        foreach($where_array as $key => &$value){                          
            $result->bindParam($key, $value);                     
        }  
        
        $execute = $result->execute();

            if($execute == 1)
            {
                $response = 1;                

            }else{
                $response = 0;                
            }
            
            return $response;           
    }
 
    function delete($table_name, $where_array = []){  
        
        $where = '';
        foreach($where_array as $key=>$value){
            
            if(empty($where) && $where == ''){
                $where = "$key=:$key";
            }else{
                $where .= " AND $key=:$key";
            }
        } 
       

        $sql ="DELETE FROM `$table_name` WHERE $where";   

        $result = $this->connect()->prepare($sql);
        foreach($where_array as $key => &$value){  
            $value = $this->cleanData($value);             
            $result->bindParam($key, $value);                     
        } 
        $execute = $result->execute();

        if($execute==0){         
            $response = 0;                          
        }else{   
           
            $response = 1;                      
        }
        return $response;
        
    }   
    
    function cleanData($data){
        // $data = str_replace("\\","\\\\","$data");
        // $data = str_replace("'","\'","$data");
        // $data = str_replace('"','\"',"$data");
        $data = str_replace('<script>','',"$data");
        $data = str_replace('</script>','',"$data");

        return $data;
    }
    function escHtml($data){
        $data = htmlspecialchars($data);
        $data = htmlentities($data);
        
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = stripcslashes($data);
        $data = stripslashes($data);

        $data = str_replace("\\","\\\\","$data");
        $data = str_replace("'","\'","$data");
        $data = str_replace('"','\"',"$data");
        
        // $data = str_replace('-','',"$data");
        $data = str_replace('--','',"$data");
        $data = str_replace(';','',"$data");
        $data = str_replace('<script>','',"$data");
        $data = str_replace('</script>','',"$data");

        return $data;
    }

    function set_session($data = []){
        foreach($data as $key=>$value){
            $_SESSION[$key] = $value;
        }
    }

}
$db = new db();
global $db;

function get_name_initials($name){
    $breake_name = explode(" ", $name);
    $name_initials = "";
    
    foreach ($breake_name as $w) {
        $name_initials .= $w[0];
    }

    return $name_initials;
}












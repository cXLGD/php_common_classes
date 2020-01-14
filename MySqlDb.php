<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/1/14
 * Time: 14:07
 */

class MySqlDb {
    private $host;  //主机
    private $user;  //用户
    private $pwd;   //密码
    private $dbname;//数据表名
    private $prefix;//表前缀
    private $charset;//数据库编码
    private $conn;  //数据库的链接
    private static $instance;   //保存当前对象

    //用私有的构造函数阻止在类的外部实例化
    private function __construct($config)
    {
        $this->init($config);
        $this->connect();
    }

    //连接数据库
    private function connect(){
        $this->conn = mysqli_connect($this->host, $this->user, $this->pwd, $this->dbname);
        if(!$this->conn){
            die('数据库连接失败!'.mysqli_connect_error().'<br />');
        }

        //设置字符集编码
        mysqli_set_charset($this->conn, $this->charset);
    //print_r($this->conn);
    }

    //初始化参数
    private function init($config){
        $this->host = !isset($config['host']) ? '127.0.0.1' : $config['host'];
        $this->user = empty($config['user']) ? '' : $config['user'];
        $this->pwd = empty($config['pwd']) ? '' : $config['pwd'];
        $this->dbname = empty($config['dbname']) ? '' : $config['dbname'];
        $this->charset = empty($config['charset']) ? 'utf8' : $config['charset'];
    }

    //阻止克隆
    private function __clone()
    {

    }

    public static function getInstance($config){
        if(!self::$instance instanceof self){   //如果没有实例化 就创建一个实例化
            return self::$instance = new self($config);
        }
        return self::$instance; //如果存在一个实例化 返回这个实例化
    }

//    执行函数
    private function query($sql){
        $res = mysqli_query($this->conn, $sql); //查询到就返回一个对象
        if(!$res){
//            echo '数据库连接失败!<br />';
            echo '错误代码'.mysqli_error($this->conn).'<br />';
            echo '错误信息'.mysqli_error($this->conn).'<br />';
            echo '错误的SQL语句'.$sql.'<br />';
            die;
        }

        return $res;
    }

    /**
     * 查询多条记录
     * @param $table                [数据库表名]
     * @param string $ele           [查询元素]
     * @param null $condition       [查询条件]
     * @param string $fetch_type    [获取的数据类型 array|assoc|row]
     * @return array                [返回结果集]
     */
    function fetchAll($table, $ele='*', $condition=null, $fetch_type='array'){
        $sql = "SELECT $ele FROM $table $condition";
        $res = $this->query($sql);

        $fetch_types = ['array', 'assoc', 'row'];

        if(!in_array($fetch_type, $fetch_types)){
            $fetch_type = 'array';
        }else{
            $fetch_type = $fetch_type;
        }

        $mysqli_fetch = "mysqli_fetch_{$fetch_type}";

        if($res && mysqli_num_rows($res) > 0){
            $res_arr = [];
            while($row = $mysqli_fetch($res)){
                $res_arr[] = $row;
            }
            return empty($res_arr) ? null : $res_arr;
        }else{
            $this->query($sql);
        }
    }

    /**
     * 查询一条
     * @param $table                [数据库表名]
     * @param string $ele           [查询元素]
     * @param null $condition       [查询条件]
     * @param string $fetch_type    [获取的数据类型 array|assoc|row]
     * @return mixed
     */
    function fetchOne($table, $ele='*', $condition=null, $fetch_type='array'){
        $res = $this->fetchAll($table, $ele, $condition, $fetch_type);
        return empty($res) ? null : $res[0];
    }

    /**
     * @param $table
     * @param $data_arr
     */
    function insert($table, $data_arr){
        $key = '';
        $value = '';

        foreach($data_arr as $k => $v){
            $key .= "`$k`, ";
            $value .= "`$v`, ";
        }

        $key = rtrim($key, ',');
        $value = rtrim($value, ',');

        $sql = "INSERT INTO $table $key VALUE $value";

        $res = $this->query($sql);

        if($res){
            $ins['code'] = 1;
            $ins['msg'] = '添加成功';
        }else{
            $ins['code'] = 0;
            $ins['msg'] = '添加失败';
        }
        return $ins;
    }
}

$config = [
    'host'=> 'localhost',
    'user'=> 'root',
    'pwd'=> 123456,
    'dbname'=> 'nndou',
    'charset'=> 'utf8'
];

$db = MySqlDb::getInstance($config);
//$admin = $db->fetchAll('nnd_admin', '*', '', 'assoc');
//$admin = $db->fetchOne('nnd_admin', '*', 'WHERE admin_name = "admin"', 'assoc');
echo '<pre>';
//print_r($admin);

$arr = [
    'admin_name' => 'aaa',
    'admin_pwd' => md5('aaa'),
    'admin_last_login' => '1122112212'
];

$add = $db->insert('nnd_admin', $arr);
print_r($add);
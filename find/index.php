<?php
function _session_open($save_path = null, $session_name = null)
{
    global $handle;
    $handle = mysql_connect('localhost', 'root', '19970214') or die("GG");
    mysql_select_db("mysql");
}

function _session_close()
{
    global $handle;
    mysql_close($handle);
    return true;
}

function _session_read($key)
{
    global $handle;
    $time = time();
    $sql = "select session_data from tb_session where session_key = '$key' and session_time > $time";
    //$sql= "select session_data from tb_session where  session_time > $time";
    $result = mysql_query($sql, $handle);
    $row = mysql_fetch_array($result);
    if ($row) {
        return $row['session_data'];
    } else {
        return false;
    }
}

function _session_write($key, $data)
{
    global $handle;
    $time = 1 * 60;
    $lapse_time = time() + $time;
    $sql = "select session_data from tb_session where session_key = '$key' ";
    $result = mysql_query($sql, $handle);
    if (mysql_num_rows($result) == 0) {
        $sql = "insert into tb_session values('$key','$data','$lapse_time')";
        $result = mysql_query($sql, $handle);
    } else {
        $sql = "delete from tb_session where session_key = '$key' ;";
        mysql_query($sql, $handle);
        $sql = "insert into tb_session values('$key','$data','$lapse_time')";
        $result = mysql_query($sql, $handle);
    }
    return $result;
}

function _session_destroy($key)
{
    global $handle;
    //$sql="delete from tb_session where session_key = '$key'";
    //$result=mysql_query($sql, $handle);
    //return ($result);
    return true;
}

function _session_gc($expiry_time = 0)
{
    global $handle;
    $lapse_time = time();
    $sql = "delete from tb_session where session_time < $lapse_time";
    $result = mysql_query($sql, $handle);
    return ($result);
}

function run_time()
{
    list($msec, $sec) = explode(" ", microtime());
    return ((float)$msec + (float)$sec);
}

if ($_POST != null) {
    session_start();
    _session_open();
    _session_gc();
    $pos = Array($_POST);
    $json_str = json_encode($pos);
    $json_str = trim($json_str, "[ ]");
    _session_write(session_id(), $json_str);
    _session_read(session_id());
    global $handle;
    $id = session_id();
    $sql = "select DISTINCT session_data from tb_session WHERE session_key!='$id';";
    $result = mysql_query($sql, $handle);
    $line_count = 0;
    while ($info = mysql_fetch_array($result)) {
        echo $info['session_data'];
        $line_count++;
    }
    if ($line_count == 0) {
        echo "{\"x\":\"-5\",\"y\":\"-5\"}";
    }
    _session_close();
} else {
    session_start();
    echo session_id();
}
?>


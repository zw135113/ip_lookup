<?php

require 'vendor/autoload.php';
use GeoIp2\Database\Reader;

function is_valid_ip_format($ip) {
    return preg_match('/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip);
}

function get_ip_type($ip) {
    $long_ip = ip2long($ip);
    if ($long_ip !== false) {
        // 私有地址范围
        if (($long_ip >= ip2long('10.0.0.0') && $long_ip <= ip2long('10.255.255.255')) || 
            ($long_ip >= ip2long('172.16.0.0') && $long_ip <= ip2long('172.31.255.255')) || 
            ($long_ip >= ip2long('192.168.0.0') && $long_ip <= ip2long('192.168.255.255'))) {
            return "私网地址";
        }
        // 组播地址范围
        if ($long_ip >= ip2long('224.0.0.0') && $long_ip <= ip2long('239.255.255.255')) {
            return "组播地址";
        }
        // 保留地址范围
        if ($long_ip >= ip2long('240.0.0.0') && $long_ip <= ip2long('255.255.255.255')) {
            return "保留地址";
        }
        // 运营商级别的保留私网地址
        if ($long_ip >= ip2long('100.64.0.0') && $long_ip <= ip2long('100.127.255.255')) {
            return "运营商级别的保留私网地址";
        }
        // 自动配置地址范围
        if ($long_ip >= ip2long('169.254.0.0') && $long_ip <= ip2long('169.254.255.255')) {
            return "自动配置地址";
        }
        // 回环地址范围
        if ($long_ip >= ip2long('127.0.0.0') && $long_ip <= ip2long('127.255.255.255')) {
            return "回环地址";
        }
    }
    return "公网地址";
}



function get_ip_location($ip) {
    try {
        $reader = new Reader('mmdb/ip.mmdb');
        $record = $reader->city($ip);
        $country = $record->country->names['zh-CN'] ?? $record->country->name;
        $subdivision = $record->mostSpecificSubdivision->names['zh-CN'] ?? $record->mostSpecificSubdivision->name;
        $city = $record->city->names['zh-CN'] ?? $record->city->name;
        return $country . '  ' . $subdivision . '  ' . $city;
    } catch (Exception $e) {
        return false;
    }
}

function get_as_information($ip) {
    try {
        $reader = new Reader('mmdb/as.mmdb');
        $record = $reader->asn($ip);
        $asNumber = $record->autonomousSystemNumber;
        $asOrganization = $record->autonomousSystemOrganization;
        return 'AS' . $asNumber . ' - ' . $asOrganization;
    } catch (Exception $e) {
        return false;
    }
}




$response = [];

if (isset($_POST['ip_address'])) {
    $ip = $_POST['ip_address'];
    if (is_valid_ip_format($ip)) {
        $ipType = get_ip_type($ip);
        if ($ipType == "公网地址") {
            $location = get_ip_location($ip);
            $asInfo = get_as_information($ip);
            if ($location && $asInfo) {
                $response['status'] = 'success';
                $response['message'] = "IP 地址 " . $ip . " 的归属地是: " . $location . "<br>AS信息: " . $asInfo;
            } else {
                $response['status'] = 'error';
                $response['message'] = "没有查询到该IP的信息。";
            }
        } else {
	    $response['status'] = 'info';
	    $response['message'] = sprintf("您输入的 IP 地址 %s 是 %s。", $ip, $ipType);

        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "请输入一个有效的 IP 地址。";
    }
}

// 输出响应的Content-Type为JSON
header('Content-Type: application/json; charset=utf-8');

// 输出JSON格式的响应
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



?>


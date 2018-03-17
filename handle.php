<?php
/**
 * Created by PhpStorm.
 * User: L
 * Date: 2018-3-11
 * Time: 17:19
 */


require "./Image.php";
require "./http.php";


//上传到服务器的文件路径
define('FILE_PATH', 'upload/');
//最大的图片大小
define('UPLOAD_FILE_MAX_B', 2 * 1024 * 1024);
//允许图片类型
define('ALLOW_FILE_TYPES', 'return array(
                "image/png" => ".png",
                "image/x-png" => ".png",
                "image/jpeg" => ".jpg",
                "image/pjpeg" => ".jpg"
            );'
);

// 补充其他格式
//                "image/tiff" => ".tiff",
//                "image/vnd.wap.wbmp" => ".wbmp",
//                "image/x-icon" => ".ico",
//                "image/x-jng"  => ".jng",
//                "image/x-ms-bmp" => ".bmp",
//                "image/svg+xml" => ".svg",
//                "image/webp" => ".webp"





try {
    //html文件里 上传文件的input框name属性值
    $inputName = 'file';
//    上传的文件路径 按日期分文件夹，方便日后的文件转移
    $dir = FILE_PATH . date('Y/m') . '/';

    $image = new \Image\Image($inputName);
//    图片检查
    $image->check();

//    图片转移保存
    $image->move($dir);
//  无异常
    echo "图片保存成功";

} catch (\Exception $e) {
    //http 报错
    httpStatus($e->getCode());
    //输出报错信息
    echo $e->getMessage();
}

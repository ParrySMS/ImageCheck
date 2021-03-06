# ImageCheck
一个php的图片检查与保存工具类

## 详细使用说明：
[调用流程的详细说明 http://exp.szer.me/parry/blog/wordpress/index.php/2018/05/01/imagecheckrm/](http://exp.szer.me/parry/blog/wordpress/index.php/2018/05/01/imagecheckrm/)


- **此文件检查类通过throw Exception（抛出异常）来进行检查报错，所有函数为单向值传递，不影响`$_FILES`文件变量的任何值**
- 默认允许图片类型为 `jpg` `png`
- 默认关闭自定义图片宽高比例校验，详见 Image.php L139
- 默认文件名会按照 `时间参数 Ymd_His.后缀名` 重命名，形如 20180313_151525.jpg 

**更新说明**

`2018-03-17 21:22:06 星期六` 
- 修改常量名
- 默认关闭自定义图片宽高比例检查
- 补充文件类型关联数组的参数提示



**文件说明**

--- submit.html 示例前端文件，用于提交图片

--- handle.php 示例后端文件，用于处理图片

--- Image.php 实现图片检查与图片保存的工具类

--- http.php  一个用于http报错的函数文件


**使用说明**

- 预定义常量：

需要用户预先定义以下常量：

`FILE_PATH` 上传到服务器的文件路径

`UPLOAD_FILE_MAX_B` 最大的图片大小，单位是b，1`Mb` = 1024`Kb` = 1024^2 `b`

`ALLOW_FILE_TYPES` 允许的文件类型转化数组

示例如下：

```

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

```

- 在处理图片的后端文件中需要引入

```
require "./Image.php";
require "./http.php";
```
接着，需要在try-catch块中创建该工具类的实例，并执行函数。
需要用到的参数如下：

`$inputName`  html文件里 上传文件的input框name属性值

`$dir` 上传的文件路径，根据具体情况进行调整，可以直接取`FILE_PATH`

```

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
```




<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2018-2-24
 * Time: 21:07
 */

namespace Image;

use \Exception;

class Image
{
    private $inputFile;
    private $fileName;
    private $fileTmpname;

    /**
     * @return mixed
     */
    public function getFileTmpname()
    {
        return $this->fileTmpname;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Image constructor.
     */
    public function __construct($inputFile)
    {
        $this->inputFile = $inputFile;

    }

    /** 基本的检查（空与异常），接着进行自定义的图片检查
     * @throws Exception
     */
    public function check()
    {

        //清除文件缓存
        clearstatcache();
        //文件空检查
        if (empty($_FILES)) {
            throw new Exception('未检查到图片文件,请检查是否成功上传', 500);
        }
        //获得文件数据
        $image = $_FILES[$this->inputFile];

        //文件数据异常
        if ($image['error'] != 0) {
            $error = $image['error'];
            throw new Exception($error, 500);
        }
        //进入自定义图片检查
        $this->imageCheck($image);


    }


    /** 自定义图片检查
     * @param $image
     * @throws Exception
     */
    private function imageCheck($image)
    {

        //文件不是通过HTTP post 上传的
        $filetmpname = $image['tmp_name'];
        if (!is_uploaded_file($filetmpname)) {
            throw new Exception('文件非法上传', 500);
        }



        //上传文件过大 报错
        $size = $image['size'];
        if ($size > UPLOAD_FILE_MAX_KB) {
            $sizeM = UPLOAD_FILE_MAX_KB/1024/1024;
            throw new Exception('文件大小不得超过' . $sizeM . 'M。 当前文件大小为 $size', 500);
        }

        //上传文件的文件类型检查

        //后缀名检查
        $type = $image['type'];
        $typeRegion = eval(ALLOW_FILE_TYPES);
        if (!array_key_exists($type, $typeRegion)) {
            throw new Exception("不支持 $type 类型的文件", 500);
        }

        //文件内容检查
        if (!is_array(getimagesize($filetmpname))) {
            //文件为图片后缀 但无法识别不出图片 可能是冒充图片的文件
            throw new Exception("无法识别该图片文件", 200);
        }

        list($width, $height, $attrNum) = getimagesize($filetmpname);
        /** 注意：这段注释非常重要 请不要删除
         * 文件类型检查。
         * 当限制的文件类型都出现再下面的标记中时，可开启该检查。
         * 索引 2 attrNum 是图像类型的标记：
         * 1 = GIF，2 = JPG，3 = PNG，4 = SWF，
         * 5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，
         * 9 = JPC，10 = JP2，11 = JPX，12 = JB2，
         * 13 = SWC，14 = IFF,15 = WBMP，16 = XBM
         */

        //将图片类型标记存入数组,键表示attrNum,值表示类型
        $attr =['unknown',
            'GIF','JPG','PNG', 'SWF',
            'PSD','BMP','TIFF(intel byte order)','TIFF(motorola byte order)',
            'JPC','JP2','JPX','JB2',
            'SWC','IFF','WBMP','XBM'
        ];
        //只允许的类型标记  2 = JPG，3 = PNG
        $attrArray = [2, 3];
        //支持格式较少 若允许的文件类型并未全部出现在类型标记中 则暂不启用 注释掉attrTypeCheck函数所在行即可
        $this->attrTypeCheck($attrNum,$attrArray);

        /*
         * 自定义的图片比例要求
         * 如果页面有固定的图片裁切器
         * 例如：用户上传图片后，再网页上会进行一个1:1矩形的裁切操作后，才能上传图片（常见于头像上传）
         * 则下面的检查能够保证图片的比例
         */

        //宽高比例为 1：1
        $this->RatioCheck($width,$height,1,1);

        /**
         * 文件改名以具体情况具体分析
         * 此处为避免图片重名问题
         * 取文件名改名为 时间参数.后缀名
         */

        // 获取略去后缀名的文件名:
//        $filename = substr($filename, 0, strrpos($filename, '.'));
        // 添加时间与后缀名的改名
        $filename = date('Ymd_His') . $typeRegion[$image['type']];

        //保存到参数中
        $this->fileTmpname = $filetmpname;
        $this->fileName = $filename;
    }

    /** 图片类型标记检查
     * @param $attrNum
     * @param $attrArray
     * @throws Exception
     */
    private function attrTypeCheck($attrNum,$attrArray){

        //将图片类型标记存入数组,键表示attrNum,值表示类型
        $attr =['unknown',
            'GIF','JPG','PNG', 'SWF',
            'PSD','BMP','TIFF(intel byte order)','TIFF(motorola byte order)',
            'JPC','JP2','JPX','JB2',
            'SWC','IFF','WBMP','XBM'
        ];
        if (!in_array($attrNum, $attrArray)) {
            //不在类型标记的未知类型
            if($attrNum<0||$attrNum>16){
                $attrNum = 0;
            }

            throw new Exception("不支持的文件类型:$attr[$attrNum]",500);
        }
    }

    /** 图片比例检查
     * @param $width
     * @param $height
     * @param $ratio
     * @throws Exception
     */
    public function RatioCheck($width,$height,$widthNum,$heightNum)
    {
        if($width/$height - $widthNum/$heightNum > 0.001){
            //不符合比例
            throw new Exception("不符合图片比例要求,要求宽高比例为 $widthNum ：$heightNum",500);

        }
    }


    /** 移动图片并保存
     * @param $dir
     * @param null $tmpname
     * @param null $filename
     * @throws Exception
     */
    public function move($dir, $tmpname = null , $filename = null )
    {
        //默认取本类的文件参数
        $tmpname = empty($tmpname)?$this->fileTmpname:$tmpname;
        $filename = empty($filename)?$this->fileName:$filename;
        //中文转码
        $filename=iconv("utf-8","gbk",$filename);



        //无目录则创建目录
        if (!is_dir('./'.$dir)) {
            if (!mkdir('./'.$dir, 0777, true)) {
                throw new Exception('图片路径错误', 500);
            }
        }

        //中文转码
        $dirFilename =iconv("utf-8","gbk",$dir.$filename);

        if(!move_uploaded_file($tmpname, './'.$dirFilename)){
            throw new Exception('图片转移失败 ' , 500);
        }

        if (!is_file($dirFilename)) {
            throw new Exception('图片保存失败 ' , 500);
        }

//        获取文件类型
//        $filetype = mime_content_type($filename);


    }
}
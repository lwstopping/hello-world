<?php
/**
 *  uuencode编码
 *  模拟convert_uuencode()函数
 */
function uu_encode($input){
    $totalByte = strlen($input);
	
    /**
     *  uu的规则,
     *  每次取3个字节编码,
     *  不够3字节的填充空字节"\0",
     *  @var int $each3byte 3个字节为一组,
     */
	$each3byte = 3;

    //通过该值可以知道是否需要填充空字节 */
    $modByte = $totalByte % $each3byte;
    
    /** @var int 需要填充多少个空字节"\0", 初始值为零 */
    $padAmount = 0; 
	
    //不够3字节的填充处理"
    if( $modByte ) {
        //填充的空字节"\0"数量
        $padAmount += ($each3byte - $modByte);
        //填充后的总字节数
        $totalByte += $padAmount;
        //格式化填充
        $input = sprintf("%-'\0{$totalByte}s", $input);
    }
    
    /** @var array 存储6bit字符串*/
    $byte3to4 = array();
    
    /** @var int 计算循环次数 */
    $cycleIndex = $totalByte / $each3byte;
     
	for( $i = 0; $i < $cycleIndex; $i++) {
		//每次截取3个字节
		$chars = substr($input, $i * $each3byte, $each3byte);
        
        $dec = array();
        $dec[] = ord($chars[0]);
        $dec[] = ord($chars[1]);
        $dec[] = ord($chars[2]);
        
        $temp   = array();
        //第一个字节,源第1字节高6位
        $temp[] = $dec[0] >> 2;
		//第二个字节,源第1字节低2位 + 源第2字节高4位
		$temp[] = ($dec[0] & 0x03) << 4 | $dec[1] >> 4;
        //第三个字节,源第2字节的低4位 + 源第2字节的高2位
		$temp[] = ($dec[1] & 0x0F) << 2 | $dec[2] >> 6;
		//第四个字节,源第3字节去掉高2位
        $temp[] = $dec[2] & 0x3F;
        
        /**
         *  uu规则,
         *  空字节"\0"用"\x60"代替,
         *  最后每字节再加32
         */
		foreach( $temp as &$v ) {
			$v = $v === 0 ? chr(0x60) : chr($v + 32);
            $byte3to4[] = $v;
		}
        
        unset($dec);
        unset($temp);
    }
    //var_dump($byte3to4);return;

	/**
     *  uu规则,
     *  每60个字节编码的输出相当于 60 / 4 * 3 == 45个输入的字节,视为独立的一行,
     *  每60个编码的行以长度标识符M(45+32)开头,
     *  所以不足60个编码的行的开头标识符是,
     *  以剩下输入的字节数-填充的空字节数 + 32, 
     */
    
    //每60个编码分割
    $eachRows = str_split(implode('', $byte3to4), 60);

    //不足60个编码的行的字节数(也可能没有)
	$modRow  = count($byte3to4) % 60;
    
    //对不足60个编码的行的处理
    if( $modRow ) {
        $lastRow = array_pop($eachRows);
        //行的开头标识符
        $identifier  = chr($modRow / 4 * 3 - $padAmount + 32);
        $identifier .= $lastRow."\r\n\x60";    
    }
    
    /** @var string 存储了uu规则的编码 */
    $result = '';
    
    //每60个编码的行的处理
    foreach($eachRows as $row) {
        $row = sprintf("M%s\r\n", $row);
        $result .= $row;
    }

    return $modRow ? $result .= $identifier : $result;
}
$str = 'ROT-13 编码是一种每一个字母被另一个字母代替的方法。这个代替字母是由原来的字母向前移动 13 个字母而得到的。数字和非字母字符保持不变';

echo uu_encode($str);
echo "\n";
echo convert_uuencode($str);

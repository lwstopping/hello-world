<?php
/*一个简单的ASCII字符编码解码的转换*/
function ascii_code($input) {
	$i = 0;
    $length = strlen($input);
    while( false !== ($char = $input[$i]) ) {
		$input[$i] = chr(127 - ord($char)); 
        $i++;
        if( $i >= $length )
            break;
	}
	return $input;
}
       
$zz = ascii_code("ASCII字符编码解码的转换");
echo 'encode: ',$zz,'<br/>';
file_put_contents('./a.txt', $zz);
$str = file_get_contents('./a.txt');

$yy = ascii_code($str);
echo 'decode: ',$yy,'<br/>';

<?php
require __DIR__ . '/vendor/autoload.php';
use \Curl\Curl;
$outfile=md5($_SERVER['REMOTE_ADDR']).'.txt';
if('POST'==$_SERVER['REQUEST_METHOD']){
	$curl = new Curl();
	$curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:64.0) Gecko/20100101 Firefox/64.0');
	$content=file_get_contents($_FILES['txt']['tmp_name']);
	$content=str_replace("\r",'',$content);
	//$content=str_replace("\n",$_POST['replace1'],$content);
	$content=preg_replace("/\n{2,}/","\n",$content);//40000 str
	$puts=explode("\n",$content);
	$transFlag=array(array());
	$j=0;
	$count=0;
	for($i=0;$i<count($puts);$i++){
		$count+=strlen($puts[$i]);
		if($count>=40000){
			$j++;
			$count=0;
		}
		$transFlag[$j][]=[
			'id'=>$i,
			'text'=>$puts[$i]
		];
	}
	//$translation='';
	
	$file=fopen($outfile,'w');
	ob_start();
	ob_end_flush(); 
	ob_implicit_flush(1);
	?>
	<html>
    			<head>
    				<title>Result - Upload To Translate</title>
    			</head>
    			<meta charset="UTF-8"/>
    			<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1">
    			<body>
    				<h1>Upload To Translate - Result</h1>
    				<a target="_blank" href="https://github.com/juzeon/upload-to-translate">Open Source</a>
    				<h4>Output: <a href="<?php echo $outfile; ?>">/<?php echo $outfile; ?></a></h4>
    	<?php
	foreach($transFlag as $transText){
		$curl->post('https://translate.sogoucdn.com/commontranslate','{"from_lang":"en","to_lang":"zh-CHS","trans_frag":'.json_encode($transText).'}');
		if ($curl->error) {
			echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
			exit;
		}
		foreach($curl->response->trans_result as $key=>$item){
    			//$translation.=$item->trans_text.'<br/>';
			echo $transText[$key]['text'].'<br/>'.$item->trans_text.'<br/><br/>';
			fwrite($file,mb_convert_encoding($transText[$key]['text']."\r\n".$item->trans_text."\r\n\r\n",'GB2312','UTF-8'));
    		}
	}
    		?>
    			</body>
    		</html>
    		
    		<?php
    		fclose($file);
}else{
	?>
	<html>
		<head>
			<title>Upload To Translate</title>
			<meta charset="UTF-8"/>
			<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1">
		</head>
		<body>
			<h1>Upload To Translate - Upload</h1>
			<a target="_blank" href="https://github.com/juzeon/upload-to-translate">Open Source</a>
			<h4>Last Output: <a href="<?php echo $outfile; ?>">/<?php echo $outfile; ?></a></h4>
			<form action="index.php" method="post" enctype="multipart/form-data">
				<input type="file" name="txt" id="txt" value="" /><br /><br />
				<input type="submit" value="上传TXT"/>
			</form>
		</body>
	</html>
	<?php
}

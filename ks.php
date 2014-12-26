<!DOCTYPE html>
<html lang="kr">
	<head>
		<title>Korea SNS Utility</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="./kakao.link.js"></script>
	</head>

	<body OnLoad="Send()">	
		
	<h1>Korea SNS</h1>
	<h2>Send to kakaostory...</h2>
	
	<h2>(mobile only)</h2>
	
	<script>
		
		function Send(){
		
			SendKakaostory(	'<?php echo $_GET['siteurl']; ?>',
											'<?php echo $_GET['sitetitle']; ?>',
											'<?php echo $_GET['title']; ?>',
											'<?php echo $_GET['url']; ?>',
											'<?php echo $_GET['excerpt']; ?>',
											'<?php echo $_GET['image']; ?>');
		}
	</script>
	<body>
</html> 
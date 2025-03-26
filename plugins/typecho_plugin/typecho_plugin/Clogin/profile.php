<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}?>
<script>
$(document).ready(function() {
	$.ajax({
		type : "GET",
		url : "<?php echo $url?>",
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code == 0){
				var html = '<br/><section><h3>第三方账号绑定</h3>';
				$.each(data.data, function(k, v) {
					if(v.isbind){
						var str = '<font color="green" title="'+v.openid+'">已绑定</font>&nbsp;&nbsp;[<a href="'+v.url+'">重新绑定</a>]';
					}else{
						var str = '<font color="blue">未绑定</font>&nbsp;&nbsp;[<a href="'+v.url+'">立即绑定</a>]';
					}
					html += '<ul class="typecho-option"><li><strong>'+v.name+'登录：</strong>'+str+'</li></ul>';
				});
				html += '</section>';
				$('.typecho-content-panel').append(html);
			}
		}
	});
})
</script>
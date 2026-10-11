$(function(){
	$('.menu .btn_ico.childs').live('click',function(){
		$(this).toggleClass('minus').parent('.menu').nextAll('.lvl').toggle();
	});
		
	$('.lang_tabs span').click(function(){
		var lang = $(this).data('lang');
		$('.block_lang').hide();
		$('.block_'+lang).show();
		
		$('.lang_tabs span').addClass('cancel');
		$(this).removeClass('cancel');
	});
})
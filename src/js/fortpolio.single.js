(function($){
	var $MediaUl,$MediaUlA,$Preview;
	$(initFortpolio);
	function initFortpolio(){
		initDOM();
		initEvents();
		$Preview.attr('src',$MediaUlA.filter(':first').attr('href'));
	}
	function initDOM(){
		$MediaUl = $('.fortpolio-media');
		$MediaUlA = $MediaUl.find('a');
		$Preview = $('<img />').insertBefore($MediaUl);
	}
	function initEvents(){
		$MediaUlA.on('click',handleThumbClick);
	}
	function handleThumbClick(e){
		var $A = $(e.currentTarget)
			,sHref = $A.attr('href');
		$Preview.attr('src',sHref);
		e.preventDefault();
	}
})(jQuery);
var loading = {
    active: false,
    img: '',
    start: function(){
        if(!loading.active){
            loading.active = true;
            // add the loading-overlay with loading image to the page
            var over = '<div id="loading-overlay"><img id="loading" src="'+loading.img+'" alt="loading"/></div>';
            $(over).appendTo('body');

            // click on the loading-overlay to remove it
            $('#loading-overlay').click(function() {
                $(this).remove();
                loading.active = false;
            });

            // hit escape to close the overlay
            $(document).keyup(function(e) {
                if (e.which === 27) {
                    $('#loading-overlay').remove();
                    loading.active = false;
                }
            });
        }
    },
    end: function(){
        if(loading.active){
            loading.active = false;
            $('#loading-overlay').delay(250).queue(function(){
                $(this).remove();
                $(this).dequeue();
            });
        }
    }
};
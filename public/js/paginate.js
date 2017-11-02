var paginate = {
    current: 0,
    init: function(){
        $("#next").click(paginate.next);
        $("#prev").click(paginate.previous);
    },
    next: function(){
        paginate.current++;
        entries.reload(paginate.current);
    },
    previous: function(){
        paginate.current--;
        if(paginate.current < 0){
            paginate.current = 0;
        }

        entries.reload(paginate.current);
    },
    processPageNumber: function(pageNumber){
        pageNumber = parseInt(pageNumber);
        pageNumber = isNaN(pageNumber) ? 0 : pageNumber;
        return pageNumber;
    },
    display:{
        next: function(display){
            $("#next").toggle(display);
        },
        previous: function(display){
            $("#prev").toggle(display);
        }
    }
};
var paginate = {
    current: 0,
    filterState: {},
    init: function(){
        $("#next").click(paginate.next);
        $("#prev").click(paginate.previous);
    },
    next: function(){
        paginate.current++;
        entries.reload(paginate.current, paginate.filterState);
    },
    previous: function(){
        paginate.current--;
        if(paginate.current < 0){
            paginate.current = 0;
        }

        entries.reload(paginate.current, paginate.filterState);
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
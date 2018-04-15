import { ObjectBaseClass } from './objectBaseClass';
import Store from './store';

export class Tags extends ObjectBaseClass {
    
    constructor(){
        super();
        this.storeType = Store.getters.STORE_TYPE_TAGS;
        this.uri = '/api/tags';
    }

    axiosFailure(error){
        if(error.response){
            switch(error.response.status){
                case 404:
                    this.set([]);
                    break;
                case 500:
                default:
                    // TODO: notify users of issue
                    // notice.display(notice.typeDanger, "Error occurred when attempting to retrieve tags");
                    break;
            }
        }
    }

    getNameById(id){
        let tag = this.find(id);
        return (tag.hasOwnProperty('name')) ? tag.name : '';
    }

    getAllNames(){
        return this.get().map(function(element){
            return element.name;
        });
    }

}

// var tags = {
//     display: function () {
//         entryModal.initTagsInput();
//         filterModal.initTagsInput();
//     },
//     getIdByName: function(tagName){
//         var tagObjects = $.grep(tags.value, function(element){
//             return element.name === tagName;
//         });
//         if(tagObjects.length > 0){
//             return tagObjects[0].id;
//         } else {
//             return -1;
//         }
//     }
// };